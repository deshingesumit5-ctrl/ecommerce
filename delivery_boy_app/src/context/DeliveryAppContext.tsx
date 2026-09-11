import React, { createContext, useContext, useState, useEffect, useRef } from 'react';
import { Platform, NativeModules } from 'react-native';
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { DeliveryBoy, DeliveryOrder, AppNotification, DeliveryStatus } from '../types';

export const getDetectedHost = (): string => {
  if (Platform.OS === 'web') {
    if (typeof window !== 'undefined' && window.location?.hostname) {
      return window.location.hostname;
    }
    return '127.0.0.1';
  }

  // For native mobile: extract host IP from Metro packager script URL
  try {
    const scriptURL: string | undefined = NativeModules?.SourceCode?.scriptURL;
    if (scriptURL) {
      const match = scriptURL.match(/:\/\/([^:/]+)/);
      if (match && match[1] && match[1] !== 'localhost' && match[1] !== '127.0.0.1') {
        return match[1];
      }
    }
  } catch (e) {}

  return '192.168.1.2';
};

export const getApiBaseUrl = (): string => {
  const host = getDetectedHost();
  return `http://${host}:8000/api`;
};

export const getCandidateApiUrls = (): string[] => {
  const detected = getApiBaseUrl();
  const candidates = [
    detected,
    'http://192.168.1.2:8000/api',
    'http://127.0.0.1:8000/api',
    'http://localhost:8000/api',
    'http://10.0.2.2:8000/api',
  ];
  return Array.from(new Set(candidates));
};

interface DeliveryAppContextType {
  deliveryBoy: DeliveryBoy | null;
  isAuthenticated: boolean;
  orders: DeliveryOrder[];
  notifications: AppNotification[];
  unreadNotificationCount: number;
  selectedOrderForModal: DeliveryOrder | null;
  loading: boolean;
  activeBaseUrl: string;
  
  // Actions
  login: (username: string, pass: string) => Promise<{ success: boolean; message?: string }>;
  logout: () => void;
  toggleOnlineStatus: () => void;
  updateOrderStatus: (orderId: string, nextStatus: DeliveryStatus) => Promise<void>;
  markCodAsCollected: (orderId: string) => Promise<void>;
  markNotificationAsRead: (notificationId: string) => Promise<void>;
  setSelectedOrderForModal: (order: DeliveryOrder | null) => void;
  openOrderById: (orderId: string) => void;
  refreshOrders: (customBaseUrl?: string) => Promise<void>;
}

const DeliveryAppContext = createContext<DeliveryAppContextType | undefined>(undefined);

export const DeliveryAppProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [deliveryBoy, setDeliveryBoy] = useState<DeliveryBoy | null>(null);
  const [isAuthenticated, setIsAuthenticated] = useState<boolean>(false);
  const [activeBaseUrl, setActiveBaseUrl] = useState<string>(getApiBaseUrl());
  const activeBaseUrlRef = useRef<string>(getApiBaseUrl());
  
  // Real data state - no hardcoded orders!
  const [orders, setOrders] = useState<DeliveryOrder[]>([]);
  const [notifications, setNotifications] = useState<AppNotification[]>([]);
  const [selectedOrderForModal, setSelectedOrderForModal] = useState<DeliveryOrder | null>(null);
  const [loading, setLoading] = useState<boolean>(false);

  const unreadNotificationCount = notifications.filter((n) => !n.is_read).length;

  const updateBaseUrl = (url: string) => {
    setActiveBaseUrl(url);
    activeBaseUrlRef.current = url;
  };

  // Restore saved login session on app launch
  useEffect(() => {
    const restoreSession = async () => {
      try {
        const savedUrl = await AsyncStorage.getItem('delivery_app_base_url');
        const urlToUse = savedUrl || getApiBaseUrl();
        updateBaseUrl(urlToUse);

        const saved = await AsyncStorage.getItem('delivery_boy_session');
        if (saved) {
          const parsed = JSON.parse(saved);
          setDeliveryBoy(parsed);
          setIsAuthenticated(true);
          // Sync fresh profile data with real stats from server
          try {
            const profileRes = await axios.get(
              `${urlToUse}/delivery/profile?id=${parsed.id || ''}&username=${parsed.username || ''}`,
              { timeout: 3500 }
            );
            if (profileRes.data && profileRes.data.success && profileRes.data.delivery_boy) {
              setDeliveryBoy(profileRes.data.delivery_boy);
              await AsyncStorage.setItem('delivery_boy_session', JSON.stringify(profileRes.data.delivery_boy));
            }
          } catch (e) {}
        } else {
          setIsAuthenticated(false);
          setDeliveryBoy(null);
        }
      } catch (e) {
        setIsAuthenticated(false);
        setDeliveryBoy(null);
      }
    };
    restoreSession();
  }, []);

  const refreshOrders = async (customBaseUrl?: string) => {
    const baseUrl = customBaseUrl || activeBaseUrlRef.current || getApiBaseUrl();
    try {
      setLoading(true);
      const boyId = deliveryBoy?.id;
      const [orderRes, notifRes, profileRes] = await Promise.allSettled([
        axios.get(`${baseUrl}/delivery/orders${boyId ? `?delivery_boy_id=${boyId}` : ''}`, { timeout: 4000 }),
        axios.get(`${baseUrl}/notifications?app=delivery${boyId ? `&delivery_boy_id=${boyId}` : ''}`, { timeout: 4000 }),
        boyId
          ? axios.get(`${baseUrl}/delivery/profile?id=${boyId}`, { timeout: 4000 })
          : Promise.resolve(null),
      ]);

      if (orderRes.status === 'fulfilled' && Array.isArray(orderRes.value.data)) {
        setOrders(orderRes.value.data);
      }
      if (notifRes.status === 'fulfilled' && Array.isArray(notifRes.value.data)) {
        setNotifications(notifRes.value.data);
      }
      if (
        profileRes.status === 'fulfilled' &&
        profileRes.value?.data?.success &&
        profileRes.value?.data?.delivery_boy
      ) {
        setDeliveryBoy(profileRes.value.data.delivery_boy);
      }
    } catch (e) {
      console.warn('Error fetching delivery orders:', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (isAuthenticated) {
      refreshOrders();
      const interval = setInterval(() => refreshOrders(), 8000);
      return () => clearInterval(interval);
    }
  }, [isAuthenticated]);

  const login = async (usernameInput: string, passInput: string): Promise<{ success: boolean; message?: string }> => {
    try {
      setLoading(true);
      const currentUrl = activeBaseUrlRef.current || getApiBaseUrl();
      const candidates = [currentUrl, ...getCandidateApiUrls().filter((u) => u !== currentUrl)];
      
      let lastError: any = null;
      let res: any = null;
      let workingBaseUrl = currentUrl;

      for (const candidate of candidates) {
        try {
          const attempt = await axios.post(
            `${candidate}/delivery/login`,
            {
              username: usernameInput.trim(),
              password: passInput.trim(),
            },
            { timeout: 4000 }
          );

          if (attempt.data) {
            res = attempt;
            workingBaseUrl = candidate;
            updateBaseUrl(candidate);
            try {
              await AsyncStorage.setItem('delivery_app_base_url', candidate);
            } catch (e) {}
            break;
          }
        } catch (err: any) {
          lastError = err;
          // If server actually responded with 400, 401, 403, 404, we found the right server!
          if (err?.response) {
            res = err.response;
            workingBaseUrl = candidate;
            updateBaseUrl(candidate);
            try {
              await AsyncStorage.setItem('delivery_app_base_url', candidate);
            } catch (e) {}
            break;
          }
        }
      }

      if (res?.data && res.data.success && res.data.delivery_boy) {
        const boyData = res.data.delivery_boy;
        setDeliveryBoy(boyData);
        setIsAuthenticated(true);
        try {
          await AsyncStorage.setItem('delivery_boy_session', JSON.stringify(boyData));
        } catch (e) {}
        refreshOrders(workingBaseUrl);
        return { success: true };
      }

      if (res?.data?.message) {
        return { success: false, message: res.data.message };
      }

      if (!res && lastError) {
        return {
          success: false,
          message: 'Unable to connect to server. Ensure backend is running and mobile is on Wi-Fi.',
        };
      }

      return { success: false, message: 'Invalid username or password. Contact Admin.' };
    } catch (err: any) {
      const msg = err?.response?.data?.message || 'Invalid username or password. Please contact Admin.';
      return { success: false, message: msg };
    } finally {
      setLoading(false);
    }
  };

  const logout = async () => {
    setIsAuthenticated(false);
    setDeliveryBoy(null);
    try {
      await AsyncStorage.removeItem('delivery_boy_session');
    } catch (e) {}
  };

  const toggleOnlineStatus = () => {
    if (deliveryBoy) {
      setDeliveryBoy({ ...deliveryBoy, is_online: !deliveryBoy.is_online });
    }
  };

  const updateOrderStatus = async (orderId: string, nextStatus: DeliveryStatus) => {
    const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    const baseUrl = activeBaseUrlRef.current || getApiBaseUrl();
    
    // Optimistic UI update
    setOrders((prev) =>
      prev.map((o) => {
        if (o.id === orderId || o.order_number === orderId) {
          const updated = {
            ...o,
            delivery_status: nextStatus,
            delivered_time: nextStatus === 'DELIVERED' ? time : o.delivered_time,
            payment_status:
              nextStatus === 'DELIVERED' && o.payment_mode === 'COD' ? ('PAID' as const) : o.payment_status,
            is_cod_collected:
              nextStatus === 'DELIVERED' && o.payment_mode === 'COD' ? true : o.is_cod_collected,
          };
          if (selectedOrderForModal?.id === orderId || selectedOrderForModal?.order_number === orderId) {
            setSelectedOrderForModal(updated);
          }
          return updated;
        }
        return o;
      })
    );

    try {
      await axios.post(`${baseUrl}/delivery/orders/${orderId}/status`, {
        delivery_status: nextStatus,
        is_cod_collected: nextStatus === 'DELIVERED',
      });
      refreshOrders();
    } catch (e) {
      console.warn('Failed to update status on server:', e);
    }
  };

  const markCodAsCollected = async (orderId: string) => {
    const baseUrl = activeBaseUrlRef.current || getApiBaseUrl();
    setOrders((prev) =>
      prev.map((o) =>
        o.id === orderId || o.order_number === orderId
          ? { ...o, is_cod_collected: true, payment_status: 'PAID' }
          : o
      )
    );

    try {
      await axios.post(`${baseUrl}/delivery/orders/${orderId}/status`, {
        is_cod_collected: true,
      });
      refreshOrders();
    } catch (e) {
      console.warn('Failed to mark COD on server:', e);
    }
  };

  const markNotificationAsRead = async (notificationId: string) => {
    const baseUrl = activeBaseUrlRef.current || getApiBaseUrl();
    setNotifications((prev) =>
      prev.map((n) => (n.id === notificationId ? { ...n, is_read: true } : n))
    );
    try {
      await axios.post(`${baseUrl}/notifications/${notificationId}/read?app=delivery`);
    } catch (e) {
      // ignore
    }
  };

  const openOrderById = (orderId: string) => {
    const order = orders.find((o) => o.id === orderId || o.order_number === orderId);
    if (order) {
      setSelectedOrderForModal(order);
    }
  };

  return (
    <DeliveryAppContext.Provider
      value={{
        deliveryBoy,
        isAuthenticated,
        orders,
        notifications,
        unreadNotificationCount,
        selectedOrderForModal,
        loading,
        activeBaseUrl,
        login,
        logout,
        toggleOnlineStatus,
        updateOrderStatus,
        markCodAsCollected,
        markNotificationAsRead,
        setSelectedOrderForModal,
        openOrderById,
        refreshOrders,
      }}
    >
      {children}
    </DeliveryAppContext.Provider>
  );
};

export const useDeliveryApp = () => {
  const context = useContext(DeliveryAppContext);
  if (!context) {
    throw new Error('useDeliveryApp must be used within a DeliveryAppProvider');
  }
  return context;
};


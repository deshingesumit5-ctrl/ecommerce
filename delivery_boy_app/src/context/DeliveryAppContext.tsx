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
    'https://adminweb.13.60.31.32.sslip.io/api',
    'http://13.60.31.32/api',
    detected,
    'http://127.0.0.1:8000/api',
    'http://localhost:8000/api',
    'http://192.168.1.2:8000/api',
    'http://10.0.2.2:8000/api',
  ];
  return Array.from(new Set(candidates.filter(Boolean)));
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

const riderDataCacheKey = (boyId: number | string) => `delivery_app_data_${boyId}`;

const normalizeOrder = (o: any): DeliveryOrder => ({
  id: String(o?.id ?? o?.order_number ?? ''),
  order_number: String(o?.order_number ?? o?.id ?? ''),
  store_name: o?.store_name ?? '',
  customer_name: o?.customer_name ?? '',
  customer_mobile: String(o?.customer_mobile ?? ''),
  delivery_address: o?.delivery_address ?? '',
  items: Array.isArray(o?.items) ? o.items : [],
  order_amount: Number(o?.order_amount ?? 0),
  payment_mode: o?.payment_mode === 'ONLINE' ? 'ONLINE' : 'COD',
  payment_status: o?.payment_status === 'PAID' ? 'PAID' : 'PENDING',
  cod_amount_to_collect: Number(o?.cod_amount_to_collect ?? 0),
  is_cod_collected: Boolean(o?.is_cod_collected),
  delivery_status: (o?.delivery_status as DeliveryStatus) || 'ASSIGNED',
  assigned_time: o?.assigned_time ?? '',
  delivered_time: o?.delivered_time ?? '',
  customer_notes: o?.customer_notes ?? '',
});

const loadRiderCache = async (boyId: number | string) => {
  try {
    const raw = await AsyncStorage.getItem(riderDataCacheKey(boyId));
    if (!raw) {
      return { orders: [] as DeliveryOrder[], notifications: [] as AppNotification[] };
    }
    const parsed = JSON.parse(raw);
    return {
      orders: Array.isArray(parsed?.orders) ? parsed.orders.map(normalizeOrder) : [],
      notifications: Array.isArray(parsed?.notifications) ? parsed.notifications : [],
    };
  } catch (e) {
    return { orders: [] as DeliveryOrder[], notifications: [] as AppNotification[] };
  }
};

const saveRiderCache = async (
  boyId: number | string | undefined,
  nextOrders: DeliveryOrder[],
  nextNotifications: AppNotification[]
) => {
  if (!boyId) return;
  try {
    await AsyncStorage.setItem(
      riderDataCacheKey(boyId),
      JSON.stringify({ orders: nextOrders, notifications: nextNotifications })
    );
  } catch (e) {}
};

export const DeliveryAppProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [deliveryBoy, setDeliveryBoy] = useState<DeliveryBoy | null>(null);
  const [isAuthenticated, setIsAuthenticated] = useState<boolean>(false);
  const [activeBaseUrl, setActiveBaseUrl] = useState<string>(getApiBaseUrl());
  const activeBaseUrlRef = useRef<string>(getApiBaseUrl());
  const deliveryBoyRef = useRef<DeliveryBoy | null>(null);
  
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
        const preferred = getCandidateApiUrls()[0];
        const urlToUse =
          savedUrl && !/localhost|127\.0\.0\.1/.test(savedUrl)
            ? savedUrl
            : preferred || savedUrl || getApiBaseUrl();
        updateBaseUrl(urlToUse);
        try {
          await AsyncStorage.setItem('delivery_app_base_url', urlToUse);
        } catch (e) {}

        const saved = await AsyncStorage.getItem('delivery_boy_session');
        if (saved) {
          const parsed = JSON.parse(saved);
          deliveryBoyRef.current = parsed;
          setDeliveryBoy(parsed);
          setIsAuthenticated(true);
          const cached = await loadRiderCache(parsed.id);
          if (cached.orders.length) {
            setOrders(cached.orders);
          }
          if (cached.notifications.length) {
            setNotifications(cached.notifications);
          }
          // Sync fresh profile data with real stats from server
          try {
            const profileRes = await axios.get(
              `${urlToUse}/delivery/profile?id=${parsed.id || ''}&username=${parsed.username || ''}`,
              { timeout: 3500 }
            );
            if (profileRes.data && profileRes.data.success && profileRes.data.delivery_boy) {
              deliveryBoyRef.current = profileRes.data.delivery_boy;
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
    const boyId = deliveryBoyRef.current?.id;
    if (!boyId) {
      return;
    }
    try {
      setLoading(true);
      const [orderRes, notifRes, profileRes] = await Promise.allSettled([
        axios.get(`${baseUrl}/delivery/orders?delivery_boy_id=${boyId}`, { timeout: 4000 }),
        axios.get(`${baseUrl}/notifications?app=delivery&delivery_boy_id=${boyId}`, { timeout: 4000 }),
        axios.get(`${baseUrl}/delivery/profile?id=${boyId}`, { timeout: 4000 }),
      ]);

      let nextOrders: DeliveryOrder[] | null = null;
      let nextNotifications: AppNotification[] | null = null;

      if (orderRes.status === 'fulfilled' && Array.isArray(orderRes.value.data)) {
        nextOrders = orderRes.value.data.map(normalizeOrder);
        setOrders(nextOrders);
      }
      if (notifRes.status === 'fulfilled' && Array.isArray(notifRes.value.data)) {
        nextNotifications = notifRes.value.data;
        setNotifications(nextNotifications);
      }
      if (
        profileRes.status === 'fulfilled' &&
        profileRes.value?.data?.success &&
        profileRes.value?.data?.delivery_boy
      ) {
        deliveryBoyRef.current = profileRes.value.data.delivery_boy;
        setDeliveryBoy(profileRes.value.data.delivery_boy);
      }

      if (nextOrders || nextNotifications) {
        const existing = await loadRiderCache(boyId);
        await saveRiderCache(
          boyId,
          nextOrders ?? existing.orders,
          nextNotifications ?? existing.notifications
        );
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
      let authErrorRes: any = null;

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

          if (attempt?.data && attempt.data.success && attempt.data.delivery_boy) {
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
          if (err?.response) {
            // If credentials failed (401) or account inactive (403), record error.
            // On 404 (account not found), continue checking other candidate servers!
            if (err.response.status === 401 || err.response.status === 403) {
              authErrorRes = err.response;
              workingBaseUrl = candidate;
              updateBaseUrl(candidate);
              break;
            }
            if (!authErrorRes) {
              authErrorRes = err.response;
            }
          }
        }
      }

      if (!res && authErrorRes) {
        res = authErrorRes;
      }

      if (res?.data && res.data.success && res.data.delivery_boy) {
        const boyData = res.data.delivery_boy;
        deliveryBoyRef.current = boyData;
        const cached = await loadRiderCache(boyData.id);
        if (cached.orders.length) {
          setOrders(cached.orders);
        }
        if (cached.notifications.length) {
          setNotifications(cached.notifications);
        }
        setDeliveryBoy(boyData);
        setIsAuthenticated(true);
        try {
          await AsyncStorage.setItem('delivery_boy_session', JSON.stringify(boyData));
        } catch (e) {}
        await refreshOrders(workingBaseUrl);
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
    const boyId = deliveryBoyRef.current?.id;
    if (boyId) {
      await saveRiderCache(boyId, orders, notifications);
    }
    deliveryBoyRef.current = null;
    setIsAuthenticated(false);
    setDeliveryBoy(null);
    setOrders([]);
    setNotifications([]);
    setSelectedOrderForModal(null);
    try {
      await AsyncStorage.removeItem('delivery_boy_session');
    } catch (e) {}
  };

  const toggleOnlineStatus = () => {
    if (deliveryBoy) {
      const updated = { ...deliveryBoy, is_online: !deliveryBoy.is_online };
      deliveryBoyRef.current = updated;
      setDeliveryBoy(updated);
      AsyncStorage.setItem('delivery_boy_session', JSON.stringify(updated)).catch(() => {});
    }
  };

  const updateOrderStatus = async (orderId: string, nextStatus: DeliveryStatus) => {
    const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    const baseUrl = activeBaseUrlRef.current || getApiBaseUrl();
    const target = orders.find((o) => o.id === orderId || o.order_number === orderId);
    const statusKey = encodeURIComponent(target?.order_number || orderId);

    let nextOrders = orders;
    // Optimistic UI update
    nextOrders = orders.map((o) => {
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
    });
    setOrders(nextOrders);
    await saveRiderCache(deliveryBoyRef.current?.id, nextOrders, notifications);

    try {
      await axios.post(`${baseUrl}/delivery/orders/${statusKey}/status`, {
        delivery_status: nextStatus,
        is_cod_collected: nextStatus === 'DELIVERED',
      });
      await refreshOrders();
    } catch (e) {
      console.warn('Failed to update status on server:', e);
    }
  };

  const markCodAsCollected = async (orderId: string) => {
    const baseUrl = activeBaseUrlRef.current || getApiBaseUrl();
    const target = orders.find((o) => o.id === orderId || o.order_number === orderId);
    const statusKey = encodeURIComponent(target?.order_number || orderId);
    const nextOrders = orders.map((o) =>
      o.id === orderId || o.order_number === orderId
        ? { ...o, is_cod_collected: true, payment_status: 'PAID' as const }
        : o
    );
    setOrders(nextOrders);
    await saveRiderCache(deliveryBoyRef.current?.id, nextOrders, notifications);

    try {
      await axios.post(`${baseUrl}/delivery/orders/${statusKey}/status`, {
        is_cod_collected: true,
      });
      await refreshOrders();
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
    await saveRiderCache(deliveryBoyRef.current?.id, orders, notifications.map((n) => (n.id === notificationId ? { ...n, is_read: true } : n)));
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


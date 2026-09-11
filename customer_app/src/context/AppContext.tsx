import React, { createContext, useContext, useState, useEffect } from 'react';
import { AppState, Platform, NativeModules } from 'react-native';
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import {
  Store,
  Category,
  Product,
  CartItem,
  Customer,
  Coupon,
  Order,
  OrderStatus,
  PaymentMode,
  AppNotification,
} from '../types';
import { isWithinStoreRadius } from '../utils/distanceCalculator';

export const getDetectedHost = (): string => {
  if (Platform.OS === 'web') {
    if (typeof window !== 'undefined' && window.location?.hostname) {
      return window.location.hostname;
    }
    return '127.0.0.1';
  }

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

const API_BASE_URL = `http://${getDetectedHost()}:8000/api`;

interface AppContextType {
  customer: Customer | null;
  isAuthenticated: boolean;
  stores: Store[];
  selectedStore: Store | null;
  customerLocation: { label: string; lat: number; lng: number };
  isDeliverable: boolean;
  deliveryDistanceKm: number;
  categories: Category[];
  products: Product[];
  cart: CartItem[];
  appliedCoupon: Coupon | null;
  orders: Order[];
  activeOrder: Order | null;
  notifications: AppNotification[];
  unreadNotificationCount: number;
  loading: boolean;
  
  // Auth Actions
  loginWithPassword: (username: string, password: string) => Promise<{ success: boolean; message?: string }>;
  registerCustomer: (data: any) => Promise<{ success: boolean; message?: string; otp?: string; customer?: any }>;
  sendOtp: (mobile: string) => Promise<{ success: boolean; message?: string; otp?: string }>;
  verifyOtp: (mobile: string, otp: string) => Promise<{ success: boolean; message?: string; customer?: any }>;
  resendOtp: (mobile: string) => Promise<{ success: boolean; message?: string; otp?: string }>;
  updateCustomerProfile: (data: any) => Promise<{ success: boolean; message?: string }>;
  login: (mobile: string, name: string) => void;
  logout: () => void;

  // App Actions
  setCustomerLocationCoords: (label: string, lat: number, lng: number) => void;
  setSelectedStore: (store: Store) => void;
  addToCart: (product: Product) => void;
  removeFromCart: (productId: number) => void;
  updateQuantity: (productId: number, qty: number) => void;
  clearCart: () => void;
  applyCoupon: (code: string) => { success: boolean; message: string };
  removeCoupon: () => void;
  placeOrder: (paymentMode: PaymentMode, address: string) => Promise<Order | null>;
  markNotificationAsRead: (id: string) => void;
  refreshData: () => Promise<void>;
  getCartSummary: () => {
    itemCount: number;
    subtotal: number;
    discount: number;
    deliveryCharge: number;
    finalAmount: number;
  };
}

const AVAILABLE_COUPONS: Coupon[] = [
  {
    code: 'FIRST50',
    discount_type: 'FIXED',
    discount_value: 50,
    min_order_value: 299,
  },
  {
    code: 'SAVE10',
    discount_type: 'PERCENTAGE',
    discount_value: 10,
    min_order_value: 499,
    max_discount: 100,
  },
];

const AppContext = createContext<AppContextType | undefined>(undefined);

export const AppProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [customer, setCustomer] = useState<Customer | null>(null);
  const [isAuthenticated, setIsAuthenticated] = useState<boolean>(false);

  const [stores, setStores] = useState<Store[]>([]);
  const [selectedStore, setSelectedStore] = useState<Store | null>(null);
  const [customerLocation, setCustomerLocation] = useState({
    label: 'Powai Naka, Satara',
    lat: 17.6850,
    lng: 73.9950,
  });

  const [isDeliverable, setIsDeliverable] = useState(true);
  const [deliveryDistanceKm, setDeliveryDistanceKm] = useState(0.8);

  // Real data state
  const [categories, setCategories] = useState<Category[]>([]);
  const [products, setProducts] = useState<Product[]>([]);
  const [cart, setCart] = useState<CartItem[]>([]);
  const [notifications, setNotifications] = useState<AppNotification[]>([]);
  const [appliedCoupon, setAppliedCoupon] = useState<Coupon | null>(null);
  const [orders, setOrders] = useState<Order[]>([]);
  const [activeOrder, setActiveOrder] = useState<Order | null>(null);
  const [loading, setLoading] = useState(false);

  // Restore authenticated customer session on launch
  useEffect(() => {
    const restoreSession = async () => {
      try {
        const saved = await AsyncStorage.getItem('customer_session');
        if (saved) {
          const parsed = JSON.parse(saved);
          setCustomer(parsed);
          setIsAuthenticated(true);
          if (parsed.address) {
            setCustomerLocation((prev) => ({
              ...prev,
              label: parsed.address + (parsed.city ? ', ' + parsed.city : ''),
            }));
          }
        }
      } catch (e) {
        console.warn('Error restoring customer session:', e);
      }
    };
    restoreSession();
  }, []);

  // Fetch real data from backend API & DB
  const refreshData = async (isBackground = false) => {
    try {
      if (!isBackground) setLoading(true);
      const timestamp = Date.now();
      const [prodRes, catRes, storeRes, orderRes, notifRes] = await Promise.allSettled([
        axios.get(`${API_BASE_URL}/products?_t=${timestamp}`),
        axios.get(`${API_BASE_URL}/categories?_t=${timestamp}`),
        axios.get(`${API_BASE_URL}/stores?_t=${timestamp}`),
        axios.get(`${API_BASE_URL}/orders?_t=${timestamp}`),
        axios.get(`${API_BASE_URL}/notifications?app=customer&_t=${timestamp}`),
      ]);

      if (prodRes.status === 'fulfilled' && Array.isArray(prodRes.value.data)) {
        setProducts(prodRes.value.data);
      }
      if (catRes.status === 'fulfilled' && Array.isArray(catRes.value.data)) {
        setCategories(catRes.value.data);
      }
      if (storeRes.status === 'fulfilled' && Array.isArray(storeRes.value.data) && storeRes.value.data.length > 0) {
        setStores(storeRes.value.data);
        if (!selectedStore) {
          setSelectedStore(storeRes.value.data[0]);
        }
      }
      if (orderRes.status === 'fulfilled' && Array.isArray(orderRes.value.data)) {
        setOrders(orderRes.value.data);
        if (orderRes.value.data.length > 0 && !activeOrder) {
          setActiveOrder(orderRes.value.data[0]);
        }
      }
      if (notifRes.status === 'fulfilled' && Array.isArray(notifRes.value.data)) {
        setNotifications(notifRes.value.data);
      }
    } catch (e) {
      console.warn('API fetch error:', e);
    } finally {
      if (!isBackground) setLoading(false);
    }
  };

  useEffect(() => {
    refreshData(false);

    const interval = setInterval(() => {
      refreshData(true);
    }, 2500);

    const handleFocus = () => {
      refreshData(true);
    };

    if (typeof window !== 'undefined') {
      window.addEventListener('focus', handleFocus);
      document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
          refreshData(true);
        }
      });
    }

    const appStateSubscription = AppState.addEventListener('change', (nextAppState) => {
      if (nextAppState === 'active') {
        refreshData(true);
      }
    });

    return () => {
      clearInterval(interval);
      if (typeof window !== 'undefined') {
        window.removeEventListener('focus', handleFocus);
      }
      appStateSubscription?.remove?.();
    };
  }, []);

  // Re-calculate deliverability when location or store changes
  useEffect(() => {
    if (selectedStore) {
      const { isDeliverable: deliverable, distance } = isWithinStoreRadius(
        customerLocation.lat,
        customerLocation.lng,
        selectedStore.latitude,
        selectedStore.longitude,
        selectedStore.delivery_radius_km
      );
      setIsDeliverable(deliverable);
      setDeliveryDistanceKm(distance);
    }
  }, [customerLocation, selectedStore]);

  const setCustomerLocationCoords = (label: string, lat: number, lng: number) => {
    setCustomerLocation({ label, lat, lng });
  };

  // Customer Auth Methods
  const loginWithPassword = async (username: string, pass: string): Promise<{ success: boolean; message?: string }> => {
    try {
      const res = await axios.post(`${API_BASE_URL}/customer/login`, {
        username,
        password: pass,
      });

      if (res.data && res.data.success && res.data.customer) {
        const custData: Customer = {
          id: res.data.customer.id,
          name: res.data.customer.name,
          mobile: res.data.customer.mobile,
          email: res.data.customer.email,
          address: res.data.customer.address,
          city: res.data.customer.city,
          pincode: res.data.customer.pincode,
          addresses: res.data.customer.addresses || [
            {
              id: `addr-${res.data.customer.id}`,
              label: 'Saved Address',
              address_line: res.data.customer.address || customerLocation.label,
              latitude: customerLocation.lat,
              longitude: customerLocation.lng,
              is_default: true,
            },
          ],
        };

        setCustomer(custData);
        setIsAuthenticated(true);
        await AsyncStorage.setItem('customer_session', JSON.stringify(custData));
        if (custData.address) {
          setCustomerLocation((prev) => ({
            ...prev,
            label: custData.address + (custData.city ? ', ' + custData.city : ''),
          }));
        }
        return { success: true };
      }
      return { success: false, message: res.data?.message || 'Login failed' };
    } catch (e: any) {
      const msg = e.response?.data?.message || e.message || 'Unable to connect to server';
      return { success: false, message: msg };
    }
  };

  const registerCustomer = async (data: any): Promise<{ success: boolean; message?: string; otp?: string; customer?: any }> => {
    try {
      const res = await axios.post(`${API_BASE_URL}/customer/register`, data);
      if (res.data && res.data.success) {
        return {
          success: true,
          message: res.data.message,
          otp: res.data.otp,
          customer: res.data.customer,
        };
      }
      return { success: false, message: res.data?.message || 'Registration failed' };
    } catch (e: any) {
      const msg = e.response?.data?.message || e.message || 'Registration failed. Please try again.';
      return { success: false, message: msg };
    }
  };

  const verifyOtp = async (mobile: string, otp: string): Promise<{ success: boolean; message?: string; customer?: any }> => {
    try {
      const res = await axios.post(`${API_BASE_URL}/customer/verify-otp`, {
        mobile,
        otp,
      });

      if (res.data && res.data.success && res.data.customer) {
        const custData: Customer = {
          id: res.data.customer.id,
          name: res.data.customer.name,
          mobile: res.data.customer.mobile,
          email: res.data.customer.email,
          address: res.data.customer.address,
          city: res.data.customer.city,
          pincode: res.data.customer.pincode,
          addresses: res.data.customer.addresses || [
            {
              id: `addr-${res.data.customer.id}`,
              label: 'Saved Address',
              address_line: res.data.customer.address || customerLocation.label,
              latitude: customerLocation.lat,
              longitude: customerLocation.lng,
              is_default: true,
            },
          ],
        };

        setCustomer(custData);
        setIsAuthenticated(true);
        await AsyncStorage.setItem('customer_session', JSON.stringify(custData));
        if (custData.address) {
          setCustomerLocation((prev) => ({
            ...prev,
            label: custData.address + (custData.city ? ', ' + custData.city : ''),
          }));
        }
        return { success: true, customer: custData };
      }
      return { success: false, message: res.data?.message || 'Verification failed' };
    } catch (e: any) {
      const msg = e.response?.data?.message || e.message || 'Invalid OTP code';
      return { success: false, message: msg };
    }
  };

  const sendOtp = async (mobile: string): Promise<{ success: boolean; message?: string; otp?: string }> => {
    try {
      const res = await axios.post(`${API_BASE_URL}/customer/send-otp`, { mobile });
      if (res.data && res.data.success) {
        return {
          success: true,
          message: res.data.message,
          otp: res.data.otp,
        };
      }
      return { success: false, message: res.data?.message || 'Failed to send OTP' };
    } catch (e: any) {
      const msg = e.response?.data?.message || e.message || 'Failed to send OTP. Please try again.';
      return { success: false, message: msg };
    }
  };

  const resendOtp = async (mobile: string): Promise<{ success: boolean; message?: string; otp?: string }> => {
    try {
      const res = await axios.post(`${API_BASE_URL}/customer/resend-otp`, { mobile });
      if (res.data && res.data.success) {
        return {
          success: true,
          message: res.data.message,
          otp: res.data.otp,
        };
      }
      return { success: false, message: res.data?.message || 'Failed to resend OTP' };
    } catch (e: any) {
      const msg = e.response?.data?.message || e.message || 'Failed to resend OTP. Please try again.';
      return { success: false, message: msg };
    }
  };

  const updateCustomerProfile = async (data: any): Promise<{ success: boolean; message?: string }> => {
    try {
      const payload = {
        id: customer?.id,
        mobile: customer?.mobile,
        ...data,
      };
      const res = await axios.post(`${API_BASE_URL}/customer/update-profile`, payload);

      if (res.data && res.data.success && res.data.customer) {
        const updated: Customer = {
          ...customer,
          ...res.data.customer,
          addresses: res.data.customer.addresses || customer?.addresses || [],
        };
        setCustomer(updated);
        await AsyncStorage.setItem('customer_session', JSON.stringify(updated));
        return { success: true, message: res.data.message || 'Profile updated successfully!' };
      }
      return { success: false, message: res.data?.message || 'Failed to update profile' };
    } catch (e: any) {
      const msg = e.response?.data?.message || e.message || 'Failed to update profile';
      return { success: false, message: msg };
    }
  };

  const login = (mobile: string, name: string) => {
    const cust: Customer = {
      id: Date.now(),
      name,
      mobile,
      addresses: [
        {
          id: `addr-${Date.now()}`,
          label: 'Default Address',
          address_line: `${customerLocation.label}`,
          latitude: customerLocation.lat,
          longitude: customerLocation.lng,
          is_default: true,
        },
      ],
    };
    setCustomer(cust);
    setIsAuthenticated(true);
    AsyncStorage.setItem('customer_session', JSON.stringify(cust)).catch(() => {});
  };

  const logout = async () => {
    setCustomer(null);
    setIsAuthenticated(false);
    try {
      await AsyncStorage.removeItem('customer_session');
    } catch (e) {}
  };

  const addToCart = (product: Product) => {
    setCart((prev) => {
      const existing = prev.find((item) => item.product.id === product.id);
      if (existing) {
        return prev.map((item) =>
          item.product.id === product.id ? { ...item, quantity: item.quantity + 1 } : item
        );
      }
      return [...prev, { product, quantity: 1 }];
    });
  };

  const removeFromCart = (productId: number) => {
    setCart((prev) => prev.filter((item) => item.product.id !== productId));
  };

  const updateQuantity = (productId: number, qty: number) => {
    if (qty <= 0) {
      removeFromCart(productId);
      return;
    }
    setCart((prev) =>
      prev.map((item) =>
        item.product.id === productId ? { ...item, quantity: qty } : item
      )
    );
  };

  const clearCart = () => {
    setCart([]);
    setAppliedCoupon(null);
  };

  const applyCoupon = (code: string) => {
    const coupon = AVAILABLE_COUPONS.find(
      (c) => c.code.toUpperCase() === code.trim().toUpperCase()
    );
    if (!coupon) {
      return { success: false, message: 'Invalid coupon code.' };
    }
    const { subtotal } = getCartSummary();
    if (subtotal < coupon.min_order_value) {
      return {
        success: false,
        message: `Min order of ₹${coupon.min_order_value} required for this coupon.`,
      };
    }
    setAppliedCoupon(coupon);
    return { success: true, message: `Coupon ${coupon.code} applied successfully!` };
  };

  const removeCoupon = () => {
    setAppliedCoupon(null);
  };

  const getCartSummary = () => {
    const itemCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    const subtotal = cart.reduce(
      (sum, item) => sum + item.product.daily_price * item.quantity,
      0
    );

    let discount = 0;
    if (appliedCoupon && subtotal >= appliedCoupon.min_order_value) {
      if (appliedCoupon.discount_type === 'FIXED') {
        discount = appliedCoupon.discount_value;
      } else {
        discount = (subtotal * appliedCoupon.discount_value) / 100;
        if (appliedCoupon.max_discount && discount > appliedCoupon.max_discount) {
          discount = appliedCoupon.max_discount;
        }
      }
    }

    const deliveryCharge = subtotal >= 500 || subtotal === 0 ? 0 : 40;
    const finalAmount = Math.max(0, subtotal - discount + deliveryCharge);

    return {
      itemCount,
      subtotal,
      discount,
      deliveryCharge,
      finalAmount,
    };
  };

  const markNotificationAsRead = async (id: string) => {
    setNotifications((prev) =>
      prev.map((n) => (n.id === id ? { ...n, is_read: true } : n))
    );
    try {
      await axios.post(`${API_BASE_URL}/notifications/${id}/read?app=customer`);
    } catch (e) {
      // ignore
    }
  };

  const placeOrder = async (paymentMode: PaymentMode, address: string): Promise<Order | null> => {
    if (!isDeliverable) {
      return null;
    }
    const summary = getCartSummary();
    if (cart.length === 0) return null;

    const payload = {
      store_id: selectedStore?.id || 1,
      store_name: selectedStore?.name || 'Satara Main Branch',
      customer_name: customer?.name || 'Customer',
      customer_mobile: customer?.mobile || '9876543210',
      delivery_address: address,
      items: cart.map((item) => ({
        product_id: item.product.id,
        product_name: item.product.name,
        unit_price: item.product.daily_price,
        quantity: item.quantity,
        total_price: item.product.daily_price * item.quantity,
      })),
      subtotal: summary.subtotal,
      discount: summary.discount,
      delivery_charge: summary.deliveryCharge,
      final_amount: summary.finalAmount,
      payment_mode: paymentMode,
    };

    try {
      const res = await axios.post(`${API_BASE_URL}/orders`, payload);
      const createdOrder: Order = {
        ...res.data,
        status_timeline: [
          {
            status: 'PLACED',
            timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            description: 'Order placed by customer & saved to DB',
          },
        ],
      };

      setOrders((prev) => [createdOrder, ...prev]);
      setActiveOrder(createdOrder);
      clearCart();
      refreshData();
      return createdOrder;
    } catch (e) {
      // Fallback local creation if server offline
      const fallbackOrder: Order = {
        id: `ord_${Date.now()}`,
        order_number: `ORD-${Date.now().toString().slice(-6)}`,
        store_id: selectedStore?.id || 1,
        store_name: selectedStore?.name || 'Satara Main Branch',
        customer_name: customer?.name || 'Customer',
        customer_mobile: customer?.mobile || '9876543210',
        delivery_address: address,
        items: payload.items,
        subtotal: summary.subtotal,
        discount: summary.discount,
        delivery_charge: summary.deliveryCharge,
        final_amount: summary.finalAmount,
        payment_mode: paymentMode,
        payment_status: paymentMode === 'ONLINE' ? 'PAID' : 'PENDING',
        order_status: 'PLACED',
        placed_at: new Date().toISOString(),
        status_timeline: [
          {
            status: 'PLACED',
            timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            description: 'Order placed by customer',
          },
        ],
      };
      setOrders((prev) => [fallbackOrder, ...prev]);
      setActiveOrder(fallbackOrder);
      clearCart();
      return fallbackOrder;
    }
  };

  const unreadNotificationCount = notifications.filter((n) => !n.is_read).length;

  return (
    <AppContext.Provider
      value={{
        customer,
        isAuthenticated,
        stores,
        selectedStore,
        customerLocation,
        isDeliverable,
        deliveryDistanceKm,
        categories,
        products,
        cart,
        appliedCoupon,
        orders,
        activeOrder,
        notifications,
        unreadNotificationCount,
        loading,
        loginWithPassword,
        registerCustomer,
        sendOtp,
        verifyOtp,
        resendOtp,
        updateCustomerProfile,
        login,
        logout,
        setCustomerLocationCoords,
        setSelectedStore,
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        applyCoupon,
        removeCoupon,
        placeOrder,
        markNotificationAsRead,
        refreshData,
        getCartSummary,
      }}
    >
      {children}
    </AppContext.Provider>
  );
};

export const useApp = () => {
  const context = useContext(AppContext);
  if (!context) {
    throw new Error('useApp must be used within an AppProvider');
  }
  return context;
};

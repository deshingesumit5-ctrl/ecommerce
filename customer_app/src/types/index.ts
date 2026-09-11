export type PaymentMode = 'ONLINE' | 'COD';
export type PaymentStatus = 'PENDING' | 'PAID' | 'FAILED';
export type OrderStatus = 'PLACED' | 'CONFIRMED' | 'PACKED' | 'OUT_FOR_DELIVERY' | 'DELIVERED' | 'CANCELLED';

export interface Store {
  id: number;
  name: string;
  address: string;
  contact: string;
  latitude: number;
  longitude: number;
  delivery_radius_km: number;
  is_active: boolean;
}

export interface Category {
  id: number;
  name: string;
  image?: string;
  icon?: string;
}

export interface SubCategory {
  id: number;
  category_id: number;
  name: string;
}

export interface Product {
  id: number;
  category_id: number;
  sub_category_id?: number;
  name: string;
  description: string;
  unit: string;
  base_price: number;
  daily_price: number;
  is_available: boolean;
  image_url: string;
}

export interface CartItem {
  product: Product;
  quantity: number;
}

export interface CustomerAddress {
  id: string;
  label: string;
  address_line: string;
  landmark?: string;
  latitude: number;
  longitude: number;
  is_default: boolean;
}

export interface Customer {
  id: number;
  name: string;
  mobile: string;
  email?: string;
  address?: string;
  city?: string;
  pincode?: string;
  addresses: CustomerAddress[];
}

export interface Coupon {
  id?: number;
  code: string;
  description?: string | null;
  discount_type: 'percentage' | 'fixed' | 'PERCENTAGE' | 'FIXED';
  discount_value: number;
  min_order_amount: number;
  min_order_value?: number;
  max_discount_amount?: number | null;
  max_discount?: number | null;
  usage_limit?: number;
  times_used?: number;
  start_date?: string | null;
  end_date?: string | null;
  status?: string;
}

export interface OrderItem {
  product_id: number;
  product_name: string;
  unit_price: number;
  quantity: number;
  total_price: number;
}

export interface Order {
  id: string;
  order_number: string;
  store_id: number;
  store_name: string;
  customer_name: string;
  customer_mobile: string;
  delivery_address: string;
  items: OrderItem[];
  subtotal: number;
  discount: number;
  delivery_charge: number;
  final_amount: number;
  payment_mode: PaymentMode;
  payment_status: PaymentStatus;
  order_status: OrderStatus;
  placed_at: string;
  status_timeline: {
    status: OrderStatus;
    timestamp: string;
    description: string;
  }[];
}

export interface AppNotification {
  id: string;
  title: string;
  message: string;
  is_read: boolean;
  time?: string;
  order_id?: string;
}


export type DeliveryStatus = 'ASSIGNED' | 'OUT_FOR_DELIVERY' | 'DELIVERED' | 'CANCELLED';
export type PaymentMode = 'ONLINE' | 'COD';
export type PaymentStatus = 'PENDING' | 'PAID';

export interface DeliveryBoy {
  id: number;
  name: string;
  username?: string;
  email: string;
  mobile: string;
  assigned_store_id: number;
  assigned_store_name: string;
  vehicle_type: string;
  vehicle_number: string;
  driving_license: string;
  is_online: boolean;
  rating: number;
  total_completed_orders: number;
}

export interface OrderItem {
  name: string;
  quantity: number;
  unit: string;
  price: number;
}

export interface DeliveryOrder {
  id: string;
  order_number: string;
  store_name: string;
  customer_name: string;
  customer_mobile: string;
  delivery_address: string;
  items: OrderItem[];
  order_amount: number;
  payment_mode: PaymentMode;
  payment_status: PaymentStatus;
  cod_amount_to_collect: number;
  is_cod_collected: boolean;
  delivery_status: DeliveryStatus;
  assigned_time: string;
  delivered_time?: string;
  customer_notes?: string;
}

export interface AppNotification {
  id: string;
  title: string;
  message: string;
  time: string;
  is_read: boolean;
  order_id?: string;
}

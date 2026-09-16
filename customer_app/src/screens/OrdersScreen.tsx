import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Image,
} from 'react-native';
import { useApp } from '../context/AppContext';
import { Order, OrderItem, OrderStatus } from '../types';
import { Package, Clock, CheckCircle2, Truck, ReceiptText, ChevronUp, ChevronDown } from 'lucide-react-native';

const STATUS_STEPS: { key: OrderStatus; label: string; icon: any }[] = [
  { key: 'PLACED', label: 'Order Placed', icon: Clock },
  { key: 'CONFIRMED', label: 'Confirmed', icon: CheckCircle2 },
  { key: 'PACKED', label: 'Packed & Ready', icon: Package },
  { key: 'ASSIGNED', label: 'Rider Assigned', icon: Truck },
  { key: 'OUT_FOR_DELIVERY', label: 'Out for Delivery', icon: Truck },
  { key: 'DELIVERED', label: 'Delivered', icon: CheckCircle2 },
];

const formatIstDateTime = (value?: string | null) => {
  if (!value) return '';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleString('en-IN', {
    timeZone: 'Asia/Kolkata',
    day: '2-digit',
    month: 'short',
    year: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
  });
};

const formatIstDate = (value?: string | null) => {
  if (!value) return '';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleDateString('en-IN', {
    timeZone: 'Asia/Kolkata',
    weekday: 'short',
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
};

const getStatusTitle = (status: OrderStatus) => {
  if (status === 'DELIVERED') return 'Delivered';
  if (status === 'CANCELLED') return 'Order Cancelled';
  const step = STATUS_STEPS.find((s) => s.key === status);
  return step ? step.label : status;
};

const getItemImage = (item: OrderItem, products: { id: number; image_url: string }[]) => {
  if (item?.image_url) return item.image_url;
  const match = products.find((p) => p.id === item?.product_id);
  return match?.image_url || 'https://images.unsplash.com/photo-1546094096-0df4bcaaa337?w=400';
};

const getOrderDisplayDate = (order: Order) => {
  if (order.order_status === 'DELIVERED') {
    return formatIstDate(order.delivered_at || order.placed_at);
  }
  return formatIstDate(order.placed_at);
};

const getStepLog = (order: Order, status: OrderStatus) => {
  return (order.status_timeline || []).find((t) => String(t.status).toUpperCase() === status);
};

const getStepTimestamp = (order: Order, status: OrderStatus) => {
  const hit = getStepLog(order, status);
  if (hit?.timestamp) return formatIstDateTime(hit.timestamp);

  const stepIndex = STATUS_STEPS.findIndex((s) => s.key === status);
  const currentStep = STATUS_STEPS.findIndex((s) => s.key === order.order_status);
  if (stepIndex < 0 || currentStep < 0 || stepIndex > currentStep) return '';

  if (status === 'PLACED') return formatIstDateTime(order.placed_at);
  if (status === 'DELIVERED') return formatIstDateTime(order.delivered_at || order.placed_at);

  const start = order.placed_at ? new Date(order.placed_at).getTime() : NaN;
  const timeline = order.status_timeline || [];
  const endSource = order.delivered_at || timeline[timeline.length - 1]?.timestamp;
  const end = endSource ? new Date(endSource).getTime() : Date.now();
  const safeStart = Number.isNaN(start) ? end : start;
  const ratio = currentStep === 0 ? 0 : stepIndex / currentStep;
  return formatIstDateTime(new Date(safeStart + (end - safeStart) * ratio).toISOString());
};

export const OrdersScreen: React.FC<{ onBrowseCatalog: () => void }> = ({ onBrowseCatalog }) => {
  const { orders, activeOrder, products } = useApp();
  const [isExpanded, setIsExpanded] = useState(false);
  const [selectedOrderId, setSelectedOrderId] = useState<string | null>(null);

  const orderList = Array.isArray(orders) ? orders : [];
  const productList = Array.isArray(products) ? products : [];
  const currentOrder =
    orderList.find((o) => o.id === selectedOrderId) || activeOrder || orderList[0] || null;

  const getStepIndex = (status?: OrderStatus) => {
    if (!status) return -1;
    return STATUS_STEPS.findIndex((s) => s.key === status);
  };

  if (orderList.length === 0 && !activeOrder) {
    return (
      <View style={styles.emptyContainer}>
        <Package size={56} color="#94a3b8" />
        <Text style={styles.emptyTitle}>No Orders Yet</Text>
        <Text style={styles.emptySub}>
          When you place an order, you will be able to track live delivery status here.
        </Text>
        <TouchableOpacity style={styles.startBtn} onPress={onBrowseCatalog}>
          <Text style={styles.startBtnText}>Browse Products</Text>
        </TouchableOpacity>
      </View>
    );
  }

  const openOrderDetails = (orderId: string) => {
    if (selectedOrderId === orderId && isExpanded) {
      setIsExpanded(false);
      return;
    }
    setSelectedOrderId(orderId);
    setIsExpanded(true);
  };

  const renderTrackingDetails = (order: Order) => {
    const currentStep = getStepIndex(order.order_status);
    const items = Array.isArray(order.items) ? order.items : [];
    return (
      <View style={styles.activeCard}>
        <View style={styles.activeHeader}>
          <View>
            <Text style={styles.orderNumber}>{order.order_number}</Text>
            <Text style={styles.storeName}>Store: {order.store_name}</Text>
          </View>
          <View style={{ flexDirection: 'row', alignItems: 'center' }}>
            <View style={styles.statusPill}>
              <Text style={styles.statusPillText}>{order.order_status}</Text>
            </View>
            <TouchableOpacity
              style={styles.minimizeBtn}
              onPress={() => setIsExpanded(false)}
              activeOpacity={0.8}
            >
              <ChevronUp size={18} color="#475569" />
            </TouchableOpacity>
          </View>
        </View>

        <Text style={styles.trackingTitle}>Tracking Details</Text>

        <View style={styles.stepperContainer}>
          {STATUS_STEPS.map((step, idx) => {
            const isCompleted = idx <= currentStep;
            const isCurrent = idx === currentStep;
            const Icon = step.icon;
            const stepTime = isCompleted ? getStepTimestamp(order, step.key) : '';
            const stepLog = getStepLog(order, step.key);
            return (
              <View key={step.key} style={styles.stepItem}>
                <View style={styles.stepIconWrapper}>
                  <View
                    style={[
                      styles.stepDot,
                      isCompleted && styles.stepDotActive,
                      isCurrent && styles.stepDotCurrent,
                    ]}
                  >
                    <Icon size={14} color={isCompleted ? '#ffffff' : '#64748b'} />
                  </View>
                  {idx < STATUS_STEPS.length - 1 && (
                    <View
                      style={[
                        styles.stepLine,
                        idx < currentStep && styles.stepLineActive,
                      ]}
                    />
                  )}
                </View>
                <View style={styles.stepTextWrapper}>
                  <Text
                    style={[
                      styles.stepLabel,
                      isCompleted && styles.stepLabelActive,
                      isCurrent && { fontWeight: '800', color: '#10b981' },
                    ]}
                  >
                    {step.label}
                  </Text>
                  {!!stepLog?.description && isCompleted && (
                    <Text style={styles.stepDescription}>{stepLog.description}</Text>
                  )}
                  {!!stepTime && (
                    <Text style={styles.stepTime}>{stepTime} IST</Text>
                  )}
                </View>
              </View>
            );
          })}
        </View>

        <View style={styles.orderSummarySection}>
          <View style={styles.summaryHeader}>
            <ReceiptText size={16} color="#0f172a" />
            <Text style={styles.summaryTitle}>Itemized Invoice</Text>
          </View>
          {items.map((item, i) => (
            <View key={i} style={styles.itemRow}>
              <Image
                source={{ uri: getItemImage(item, productList) }}
                style={styles.invoiceThumb}
              />
              <Text style={styles.itemName}>
                {item.quantity}x {item.product_name}
              </Text>
              <Text style={styles.itemTotal}>₹{item.total_price}</Text>
            </View>
          ))}
          <View style={styles.divider} />
          <View style={styles.summaryFooter}>
            <Text style={styles.footerPayment}>
              Payment: <Text style={{ fontWeight: '700' }}>{order.payment_mode}</Text> ({order.payment_status})
            </Text>
            <Text style={styles.footerGrandTotal}>₹{order.final_amount}</Text>
          </View>
        </View>
      </View>
    );
  };

  const renderOrderCard = (order: Order) => {
    const items = Array.isArray(order.items) ? order.items : [];
    const firstItem = items[0];
    const isCancelled = order.order_status === 'CANCELLED';
    const isDelivered = order.order_status === 'DELIVERED';
    const expanded = !!(isExpanded && currentOrder && currentOrder.id === order.id);
    return (
      <View key={order.id}>
        <TouchableOpacity
          style={styles.historyCard}
          activeOpacity={0.8}
          onPress={() => openOrderDetails(order.id)}
        >
          <View style={styles.orderCardRow}>
            {firstItem ? (
              <Image
                source={{ uri: getItemImage(firstItem, productList) }}
                style={styles.orderProductImage}
              />
            ) : (
              <View style={[styles.orderProductImage, styles.orderProductFallback]}>
                <Package size={22} color="#94a3b8" />
              </View>
            )}
            <View style={styles.orderCardInfo}>
              <Text
                style={[
                  styles.orderStatusTitle,
                  isDelivered && styles.orderStatusDelivered,
                  isCancelled && styles.orderStatusCancelled,
                ]}
              >
                {getStatusTitle(order.order_status)}
              </Text>
              <Text style={styles.historyDate}>{getOrderDisplayDate(order)}</Text>
              {firstItem ? (
                <Text style={styles.historyMeta}>
                  {firstItem.unit ? `${firstItem.unit}` : 'Item'}  •  Qty: {firstItem.quantity}
                </Text>
              ) : (
                <Text style={styles.historyMeta}>{items.length} items</Text>
              )}
              {items.length > 1 && (
                <View style={styles.moreImagesRow}>
                  {items.slice(0, 3).map((item, idx) => (
                    <Image
                      key={`${order.id}-thumb-${idx}`}
                      source={{ uri: getItemImage(item, productList) }}
                      style={styles.moreThumb}
                    />
                  ))}
                  <Text style={styles.moreItemsText}>+{items.length} items</Text>
                </View>
              )}
            </View>
            {expanded ? (
              <ChevronUp size={18} color="#94a3b8" />
            ) : (
              <ChevronDown size={18} color="#94a3b8" />
            )}
          </View>
        </TouchableOpacity>
        {expanded && renderTrackingDetails(order)}
      </View>
    );
  };

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>My Orders</Text>
      </View>

      {orderList.map((order) => renderOrderCard(order))}

      <View style={{ height: 100 }} />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f8fafc', paddingHorizontal: 16 },
  header: { paddingTop: 48, paddingBottom: 16 },
  headerTitle: { fontSize: 20, fontWeight: '800', color: '#0f172a' },
  emptyContainer: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: 24 },
  emptyTitle: { fontSize: 18, fontWeight: '800', color: '#0f172a', marginTop: 12 },
  emptySub: { fontSize: 13, color: '#64748b', textAlign: 'center', marginTop: 6, marginBottom: 20 },
  startBtn: { backgroundColor: '#10b981', paddingHorizontal: 20, paddingVertical: 10, borderRadius: 10 },
  startBtnText: { color: '#ffffff', fontWeight: '800', fontSize: 13 },
  activeCard: { backgroundColor: '#ffffff', borderRadius: 16, padding: 16, marginBottom: 16, marginTop: 6 },
  activeHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
  orderNumber: { fontSize: 15, fontWeight: '800', color: '#0f172a' },
  storeName: { fontSize: 12, color: '#64748b', marginTop: 2 },
  trackingTitle: { fontSize: 14, fontWeight: '800', color: '#0f172a', marginBottom: 8 },
  statusPill: { backgroundColor: '#ecfdf5', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8 },
  statusPillText: { color: '#059669', fontSize: 11, fontWeight: '800' },
  stepperContainer: { marginVertical: 8 },
  stepItem: { flexDirection: 'row', alignItems: 'flex-start', minHeight: 58 },
  stepIconWrapper: { alignItems: 'center', width: 30, alignSelf: 'stretch' },
  stepDot: { width: 26, height: 26, borderRadius: 13, backgroundColor: '#e2e8f0', alignItems: 'center', justifyContent: 'center' },
  stepDotActive: { backgroundColor: '#10b981' },
  stepDotCurrent: { backgroundColor: '#059669', transform: [{ scale: 1.1 }] },
  stepLine: { width: 2, flex: 1, minHeight: 24, backgroundColor: '#e2e8f0', marginVertical: 2 },
  stepLineActive: { backgroundColor: '#10b981' },
  stepTextWrapper: { marginLeft: 10, justifyContent: 'center', paddingTop: 3, flex: 1, paddingBottom: 10 },
  stepLabel: { fontSize: 13, color: '#64748b' },
  stepLabelActive: { color: '#0f172a', fontWeight: '700' },
  stepDescription: { fontSize: 11, color: '#64748b', marginTop: 2 },
  stepTime: { fontSize: 11, color: '#94a3b8', marginTop: 2, fontWeight: '600' },
  orderSummarySection: { backgroundColor: '#f8fafc', borderRadius: 12, padding: 12, marginTop: 12 },
  summaryHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 8 },
  summaryTitle: { fontSize: 13, fontWeight: '700', color: '#0f172a', marginLeft: 6 },
  itemRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginVertical: 3 },
  invoiceThumb: { width: 32, height: 32, borderRadius: 6, backgroundColor: '#e2e8f0', marginRight: 8 },
  itemName: { fontSize: 12, color: '#334155', flex: 1 },
  itemTotal: { fontSize: 12, fontWeight: '700', color: '#0f172a' },
  divider: { height: 1, backgroundColor: '#e2e8f0', marginVertical: 8 },
  summaryFooter: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  footerPayment: { fontSize: 11, color: '#64748b' },
  footerGrandTotal: { fontSize: 15, fontWeight: '800', color: '#10b981' },
  historyCard: { backgroundColor: '#ffffff', borderRadius: 12, padding: 14, marginBottom: 10 },
  orderCardRow: { flexDirection: 'row', alignItems: 'center' },
  orderProductImage: { width: 64, height: 64, borderRadius: 8, backgroundColor: '#f1f5f9' },
  orderProductFallback: { alignItems: 'center', justifyContent: 'center' },
  orderCardInfo: { flex: 1, marginLeft: 12, marginRight: 8 },
  orderStatusTitle: { fontSize: 15, fontWeight: '800', color: '#0f172a' },
  orderStatusDelivered: { color: '#10b981' },
  orderStatusCancelled: { color: '#0f172a' },
  historyDate: { fontSize: 12, color: '#64748b', marginTop: 4 },
  historyMeta: { fontSize: 12, color: '#94a3b8', marginTop: 4 },
  moreImagesRow: { flexDirection: 'row', alignItems: 'center', marginTop: 8 },
  moreThumb: { width: 22, height: 22, borderRadius: 4, marginRight: 4, backgroundColor: '#f1f5f9' },
  moreItemsText: { fontSize: 11, color: '#64748b', marginLeft: 4 },
  minimizeBtn: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#f1f5f9',
    alignItems: 'center',
    justifyContent: 'center',
    marginLeft: 8,
  },
});

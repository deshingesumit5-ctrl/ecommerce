import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
} from 'react-native';
import { useApp } from '../context/AppContext';
import { OrderStatus } from '../types';
import { Package, Clock, CheckCircle2, Truck, ShoppingBag, ReceiptText } from 'lucide-react-native';

const STATUS_STEPS: { key: OrderStatus; label: string; icon: any }[] = [
  { key: 'PLACED', label: 'Order Placed', icon: Clock },
  { key: 'CONFIRMED', label: 'Confirmed', icon: CheckCircle2 },
  { key: 'PACKED', label: 'Packed & Ready', icon: Package },
  { key: 'ASSIGNED', label: 'Rider Assigned', icon: Truck },
  { key: 'OUT_FOR_DELIVERY', label: 'Out for Delivery', icon: Truck },
  { key: 'DELIVERED', label: 'Delivered', icon: CheckCircle2 },
];

export const OrdersScreen: React.FC<{ onBrowseCatalog: () => void }> = ({ onBrowseCatalog }) => {
  const { orders, activeOrder } = useApp();

  const getStepIndex = (status: OrderStatus) => {
    return STATUS_STEPS.findIndex((s) => s.key === status);
  };

  if (orders.length === 0 && !activeOrder) {
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

  const currentOrder = activeOrder || orders[0];
  const currentStep = getStepIndex(currentOrder.order_status);

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>My Orders & Tracking</Text>
      </View>

      {/* Active Order Live Tracker */}
      {currentOrder && (
        <View style={styles.activeCard}>
          <View style={styles.activeHeader}>
            <View>
              <Text style={styles.orderNumber}>{currentOrder.order_number}</Text>
              <Text style={styles.storeName}>Store: {currentOrder.store_name}</Text>
            </View>
            <View style={styles.statusPill}>
              <Text style={styles.statusPillText}>{currentOrder.order_status}</Text>
            </View>
          </View>

          {/* Stepper Flow */}
          <View style={styles.stepperContainer}>
            {STATUS_STEPS.map((step, idx) => {
              const isCompleted = idx <= currentStep;
              const isCurrent = idx === currentStep;
              const Icon = step.icon;
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
                  </View>
                </View>
              );
            })}
          </View>

          {/* Order Details & Invoices */}
          <View style={styles.orderSummarySection}>
            <View style={styles.summaryHeader}>
              <ReceiptText size={16} color="#0f172a" />
              <Text style={styles.summaryTitle}>Itemized Invoice</Text>
            </View>
            {currentOrder.items.map((item, i) => (
              <View key={i} style={styles.itemRow}>
                <Text style={styles.itemName}>
                  {item.quantity}x {item.product_name}
                </Text>
                <Text style={styles.itemTotal}>₹{item.total_price}</Text>
              </View>
            ))}
            <View style={styles.divider} />
            <View style={styles.summaryFooter}>
              <Text style={styles.footerPayment}>
                Payment: <Text style={{ fontWeight: '700' }}>{currentOrder.payment_mode}</Text> ({currentOrder.payment_status})
              </Text>
              <Text style={styles.footerGrandTotal}>₹{currentOrder.final_amount}</Text>
            </View>
          </View>
        </View>
      )}

      {/* Previous Order History */}
      {orders.length > 1 && (
        <View style={styles.historySection}>
          <Text style={styles.sectionTitle}>Order History</Text>
          {orders.slice(1).map((order) => (
            <View key={order.id} style={styles.historyCard}>
              <View style={styles.historyHeader}>
                <Text style={styles.historyOrderNum}>{order.order_number}</Text>
                <Text style={styles.historyAmount}>₹{order.final_amount}</Text>
              </View>
              <Text style={styles.historyDate}>
                {new Date(order.placed_at).toLocaleDateString()} • {order.items.length} items
              </Text>
              <Text style={styles.historyAddress} numberOfLines={1}>
                {order.delivery_address}
              </Text>
            </View>
          ))}
        </View>
      )}

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
  activeCard: { backgroundColor: '#ffffff', borderRadius: 16, padding: 16, marginBottom: 16 },
  activeHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
  orderNumber: { fontSize: 15, fontWeight: '800', color: '#0f172a' },
  storeName: { fontSize: 12, color: '#64748b', marginTop: 2 },
  statusPill: { backgroundColor: '#ecfdf5', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8 },
  statusPillText: { color: '#059669', fontSize: 11, fontWeight: '800' },
  stepperContainer: { marginVertical: 8 },
  stepItem: { flexDirection: 'row', alignItems: 'flex-start', minHeight: 44 },
  stepIconWrapper: { alignItems: 'center', width: 30 },
  stepDot: { width: 26, height: 26, borderRadius: 13, backgroundColor: '#e2e8f0', alignItems: 'center', justifyContent: 'center' },
  stepDotActive: { backgroundColor: '#10b981' },
  stepDotCurrent: { backgroundColor: '#059669', transform: [{ scale: 1.1 }] },
  stepLine: { width: 2, height: 20, backgroundColor: '#e2e8f0', marginVertical: 2 },
  stepLineActive: { backgroundColor: '#10b981' },
  stepTextWrapper: { marginLeft: 10, justifyContent: 'center', paddingTop: 3 },
  stepLabel: { fontSize: 13, color: '#64748b' },
  stepLabelActive: { color: '#0f172a', fontWeight: '700' },
  orderSummarySection: { backgroundColor: '#f8fafc', borderRadius: 12, padding: 12, marginTop: 12 },
  summaryHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 8 },
  summaryTitle: { fontSize: 13, fontWeight: '700', color: '#0f172a', marginLeft: 6 },
  itemRow: { flexDirection: 'row', justifyContent: 'space-between', marginVertical: 3 },
  itemName: { fontSize: 12, color: '#334155' },
  itemTotal: { fontSize: 12, fontWeight: '700', color: '#0f172a' },
  divider: { height: 1, backgroundColor: '#e2e8f0', marginVertical: 8 },
  summaryFooter: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  footerPayment: { fontSize: 11, color: '#64748b' },
  footerGrandTotal: { fontSize: 15, fontWeight: '800', color: '#10b981' },
  historySection: { marginTop: 8 },
  sectionTitle: { fontSize: 16, fontWeight: '800', color: '#0f172a', marginBottom: 12 },
  historyCard: { backgroundColor: '#ffffff', borderRadius: 12, padding: 14, marginBottom: 10 },
  historyHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  historyOrderNum: { fontSize: 13, fontWeight: '700', color: '#0f172a' },
  historyAmount: { fontSize: 14, fontWeight: '800', color: '#0f172a' },
  historyDate: { fontSize: 11, color: '#64748b', marginTop: 4 },
  historyAddress: { fontSize: 12, color: '#475569', marginTop: 4 },
});

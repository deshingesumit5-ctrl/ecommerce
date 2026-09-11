import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Switch,
} from 'react-native';
import { useDeliveryApp } from '../context/DeliveryAppContext';
import {
  Bell,
  MapPin,
  Phone,
  Truck,
  CheckCircle2,
  Clock,
  Banknote,
  Navigation,
  ChevronRight,
} from 'lucide-react-native';

export const DashboardScreen: React.FC<{
  onOpenNotifications: () => void;
  onNavigateToOrders: () => void;
}> = ({ onOpenNotifications, onNavigateToOrders }) => {
  const {
    deliveryBoy,
    orders,
    unreadNotificationCount,
    toggleOnlineStatus,
    updateOrderStatus,
    setSelectedOrderForModal,
  } = useDeliveryApp();

  const assignedCount = orders.filter((o) => o.delivery_status !== 'DELIVERED').length;
  const pendingCount = orders.filter((o) => o.delivery_status === 'ASSIGNED').length;
  const outForDeliveryCount = orders.filter((o) => o.delivery_status === 'OUT_FOR_DELIVERY').length;
  const completedCount = orders.filter((o) => o.delivery_status === 'DELIVERED').length;

  const totalCodToCollect = orders
    .filter((o) => o.delivery_status !== 'DELIVERED' && o.payment_mode === 'COD')
    .reduce((sum, o) => sum + o.cod_amount_to_collect, 0);

  const displayOrders = orders;

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      {/* Top Header */}
      <View style={styles.header}>
        <View style={{ flex: 1 }}>
          <View style={styles.storeBadge}>
            <MapPin size={12} color="#10b981" />
            <Text style={styles.storeBadgeText}>
              {deliveryBoy?.assigned_store_name || 'Satara Main Store'}
            </Text>
          </View>
          <Text style={styles.riderName}>Hi, {deliveryBoy?.name || 'Rohan'} 👋</Text>
          <Text style={styles.vehicleText}>
            {deliveryBoy?.vehicle_type} ({deliveryBoy?.vehicle_number})
          </Text>
        </View>

        {/* Header Actions: Online Switch & Notification Bell with (1) in RED */}
        <View style={styles.headerActions}>
          <View style={styles.onlineContainer}>
            <Text style={[styles.onlineText, deliveryBoy?.is_online ? styles.textGreen : styles.textGray]}>
              {deliveryBoy?.is_online ? 'ONLINE' : 'OFFLINE'}
            </Text>
            <Switch
              value={deliveryBoy?.is_online}
              onValueChange={toggleOnlineStatus}
              trackColor={{ false: '#cbd5e1', true: '#a7f3d0' }}
              thumbColor={deliveryBoy?.is_online ? '#10b981' : '#64748b'}
            />
          </View>

          {/* Notification Bell Icon with Red (1) Badge */}
          <TouchableOpacity style={styles.notifButton} onPress={onOpenNotifications}>
            <Bell size={20} color="#0f172a" />
            {unreadNotificationCount > 0 && (
              <View style={styles.redBadge}>
                <Text style={styles.redBadgeText}>{unreadNotificationCount}</Text>
              </View>
            )}
          </TouchableOpacity>
        </View>
      </View>

      {/* KPI Stats Grid */}
      <View style={styles.statsGrid}>
        <View style={[styles.statCard, { borderLeftColor: '#3b82f6' }]}>
          <Text style={styles.statLabel}>Active Assigned</Text>
          <Text style={[styles.statVal, { color: '#3b82f6' }]}>{assignedCount}</Text>
          <Text style={styles.statSub}>
            {pendingCount} Pickup • {outForDeliveryCount} On Road
          </Text>
        </View>

        <View style={[styles.statCard, { borderLeftColor: '#10b981' }]}>
          <Text style={styles.statLabel}>Completed</Text>
          <Text style={[styles.statVal, { color: '#10b981' }]}>{completedCount}</Text>
          <Text style={styles.statSub}>Orders delivered today</Text>
        </View>

        <View style={[styles.statCardFull, { borderLeftColor: '#f59e0b' }]}>
          <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }}>
            <View>
              <Text style={styles.statLabel}>Pending COD Cash to Collect</Text>
              <Text style={[styles.statVal, { color: '#d97706' }]}>₹{totalCodToCollect}</Text>
            </View>
            <Banknote size={28} color="#d97706" />
          </View>
        </View>
      </View>

      {/* Assigned Delivery Orders Pipeline Section */}
      <View style={styles.sectionHeader}>
        <Text style={styles.sectionTitle}>Assigned Delivery Orders ({displayOrders.length})</Text>
        <TouchableOpacity onPress={onNavigateToOrders}>
          <Text style={styles.seeAllText}>View All</Text>
        </TouchableOpacity>
      </View>

      {displayOrders.length === 0 ? (
        <View style={styles.emptyBox}>
          <CheckCircle2 size={36} color="#10b981" />
          <Text style={styles.emptyTitle}>No data available</Text>
          <Text style={styles.emptySub}>No assigned deliveries found. New orders from admin will appear here.</Text>
        </View>
      ) : (
        displayOrders.map((order) => {
          const isDelivered = order.delivery_status === 'DELIVERED';
          const isOut = order.delivery_status === 'OUT_FOR_DELIVERY';
          return (
            <TouchableOpacity
              key={order.id}
              style={[
                styles.orderCard,
                isDelivered && styles.orderCardDelivered,
              ]}
              activeOpacity={0.8}
              onPress={() => setSelectedOrderForModal(order)}
            >
              <View style={styles.orderTopRow}>
                <View style={styles.orderNumTag}>
                  <Text style={styles.orderNumText}>{order.order_number}</Text>
                </View>
                <View
                  style={[
                    styles.statusBadge,
                    isDelivered
                      ? styles.statusBadgeDelivered
                      : isOut
                      ? styles.statusBadgeOut
                      : styles.statusBadgeAssigned,
                  ]}
                >
                  <Text
                    style={[
                      styles.statusBadgeText,
                      isDelivered
                        ? styles.textDelivered
                        : isOut
                        ? styles.textOut
                        : styles.textAssigned,
                    ]}
                  >
                    {isDelivered ? 'DELIVERED' : isOut ? 'OUT FOR DELIVERY' : 'READY FOR PICKUP'}
                  </Text>
                </View>
              </View>

              <Text style={styles.customerName}>{order.customer_name}</Text>

              <View style={styles.infoRow}>
                <MapPin size={14} color="#64748b" />
                <Text style={styles.addressText} numberOfLines={2}>
                  {order.delivery_address}
                </Text>
              </View>

              <View style={styles.paymentInfoRow}>
                <View style={styles.paymentModeBadge}>
                  <Text style={styles.paymentModeText}>
                    {order.payment_mode === 'COD' ? '💵 COD: ' + (isDelivered || order.is_cod_collected ? 'Received ₹' + order.cod_amount_to_collect : 'Collect ₹' + order.cod_amount_to_collect) : '💳 Online: Already Paid'}
                  </Text>
                </View>
                <Text style={styles.itemCountText}>
                  {order.items.reduce((s, i) => s + i.quantity, 0)} Items
                </Text>
              </View>

              {/* Action Buttons or Delivered Status */}
              <View style={styles.actionRow}>
                {isDelivered ? (
                  <View style={styles.deliveredBanner}>
                    <CheckCircle2 size={16} color="#10b981" style={{ marginRight: 6 }} />
                    <Text style={styles.deliveredBannerText}>
                      Delivered {order.delivered_time ? `at ${order.delivered_time}` : 'Successfully'} • {order.payment_mode === 'COD' ? 'Cash Received' : 'Paid Online'}
                    </Text>
                  </View>
                ) : !isOut ? (
                  <TouchableOpacity
                    style={styles.startBtn}
                    onPress={() => updateOrderStatus(order.id, 'OUT_FOR_DELIVERY')}
                  >
                    <Truck size={16} color="#ffffff" style={{ marginRight: 6 }} />
                    <Text style={styles.btnTextWhite}>Start Delivery</Text>
                  </TouchableOpacity>
                ) : (
                  <TouchableOpacity
                    style={styles.deliverBtn}
                    onPress={() => updateOrderStatus(order.id, 'DELIVERED')}
                  >
                    <CheckCircle2 size={16} color="#ffffff" style={{ marginRight: 6 }} />
                    <Text style={styles.btnTextWhite}>
                      {order.payment_mode === 'COD' ? 'Mark Delivered & Cash Received' : 'Mark Delivered'}
                    </Text>
                  </TouchableOpacity>
                )}
              </View>
            </TouchableOpacity>
          );
        })
      )}

      <View style={{ height: 100 }} />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f8fafc', paddingHorizontal: 16 },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: 48,
    paddingBottom: 16,
  },
  storeBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ecfdf5',
    alignSelf: 'flex-start',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
    marginBottom: 4,
  },
  storeBadgeText: { fontSize: 11, fontWeight: '700', color: '#065f46', marginLeft: 4 },
  riderName: { fontSize: 20, fontWeight: '800', color: '#0f172a' },
  vehicleText: { fontSize: 11, color: '#64748b', marginTop: 1 },
  headerActions: { flexDirection: 'row', alignItems: 'center' },
  onlineContainer: { alignItems: 'center', marginRight: 10 },
  onlineText: { fontSize: 9, fontWeight: '800', marginBottom: 2 },
  textGreen: { color: '#10b981' },
  textGray: { color: '#64748b' },
  notifButton: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: '#ffffff',
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOpacity: 0.06,
    shadowRadius: 6,
    elevation: 3,
  },
  redBadge: {
    position: 'absolute',
    top: 6,
    right: 6,
    backgroundColor: '#ef4444',
    minWidth: 18,
    height: 18,
    borderRadius: 9,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 3,
  },
  redBadgeText: { color: '#ffffff', fontSize: 10, fontWeight: '900' },
  statsGrid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between', marginBottom: 16 },
  statCard: {
    width: '48%',
    backgroundColor: '#ffffff',
    borderRadius: 14,
    padding: 14,
    borderLeftWidth: 4,
    shadowColor: '#000',
    shadowOpacity: 0.04,
    shadowRadius: 6,
    elevation: 2,
    marginBottom: 10,
  },
  statCardFull: {
    width: '100%',
    backgroundColor: '#ffffff',
    borderRadius: 14,
    padding: 14,
    borderLeftWidth: 4,
    shadowColor: '#000',
    shadowOpacity: 0.04,
    shadowRadius: 6,
    elevation: 2,
  },
  statLabel: { fontSize: 11, color: '#64748b', fontWeight: '700', textTransform: 'uppercase' },
  statVal: { fontSize: 22, fontWeight: '800', marginVertical: 2 },
  statSub: { fontSize: 10, color: '#94a3b8' },
  sectionHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
  sectionTitle: { fontSize: 15, fontWeight: '800', color: '#0f172a' },
  seeAllText: { fontSize: 12, fontWeight: '700', color: '#10b981' },
  emptyBox: { backgroundColor: '#ffffff', borderRadius: 16, padding: 24, alignItems: 'center', marginTop: 10 },
  emptyTitle: { fontSize: 16, fontWeight: '800', color: '#0f172a', marginTop: 8 },
  emptySub: { fontSize: 12, color: '#64748b', textAlign: 'center', marginTop: 4 },
  orderCard: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOpacity: 0.04,
    shadowRadius: 8,
    elevation: 2,
  },
  orderTopRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
  orderNumTag: { backgroundColor: '#f1f5f9', paddingHorizontal: 8, paddingVertical: 3, borderRadius: 6 },
  orderNumText: { fontSize: 12, fontWeight: '800', color: '#0f172a' },
  statusBadge: { paddingHorizontal: 8, paddingVertical: 3, borderRadius: 6 },
  statusBadgeAssigned: { backgroundColor: '#eff6ff' },
  statusBadgeOut: { backgroundColor: '#fef3c7' },
  statusBadgeDelivered: { backgroundColor: '#ecfdf5' },
  statusBadgeText: { fontSize: 10, fontWeight: '800' },
  textAssigned: { color: '#2563eb' },
  textOut: { color: '#d97706' },
  textDelivered: { color: '#059669' },
  customerName: { fontSize: 15, fontWeight: '800', color: '#0f172a', marginBottom: 6 },
  infoRow: { flexDirection: 'row', alignItems: 'center', marginVertical: 2 },
  addressText: { fontSize: 12, color: '#475569', marginLeft: 6, flex: 1, lineHeight: 16 },
  paymentInfoRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 10 },
  paymentModeBadge: { backgroundColor: '#f8fafc', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6 },
  paymentModeText: { fontSize: 11, fontWeight: '700', color: '#0f172a' },
  itemCountText: { fontSize: 11, color: '#64748b' },
  actionRow: { marginTop: 12 },
  orderCardDelivered: {
    borderLeftWidth: 4,
    borderLeftColor: '#10b981',
    backgroundColor: '#ffffff',
  },
  deliveredBanner: {
    backgroundColor: '#f0fdf4',
    borderWidth: 1,
    borderColor: '#bbf7d0',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 10,
    paddingHorizontal: 12,
    borderRadius: 10,
  },
  deliveredBannerText: {
    color: '#166534',
    fontWeight: '700',
    fontSize: 12,
  },
  startBtn: {
    backgroundColor: '#0284c7',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 10,
    borderRadius: 10,
  },
  deliverBtn: {
    backgroundColor: '#10b981',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 10,
    borderRadius: 10,
  },
  btnTextWhite: { color: '#ffffff', fontWeight: '800', fontSize: 13 },
});

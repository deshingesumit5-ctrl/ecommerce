import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
} from 'react-native';
import { useDeliveryApp } from '../context/DeliveryAppContext';
import { DeliveryStatus } from '../types';
import { MapPin, Phone, CheckCircle2, Truck, Clock, PackageX } from 'lucide-react-native';

export const OrdersScreen: React.FC = () => {
  const { orders, setSelectedOrderForModal } = useDeliveryApp();
  const [statusFilter, setStatusFilter] = useState<'ALL' | DeliveryStatus>('ALL');

  const filteredOrders = orders.filter((o) => {
    if (statusFilter === 'ALL') return true;
    return o.delivery_status === statusFilter;
  });

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Assigned Deliveries ({orders.length})</Text>
      </View>

      {/* Status Filter Tabs */}
      <View style={{ height: 42, marginBottom: 12 }}>
        <ScrollView horizontal showsHorizontalScrollIndicator={false}>
          {(['ALL', 'ASSIGNED', 'OUT_FOR_DELIVERY', 'DELIVERED'] as const).map((st) => (
            <TouchableOpacity
              key={st}
              style={[styles.filterTab, statusFilter === st && styles.filterTabActive]}
              onPress={() => setStatusFilter(st)}
            >
              <Text
                style={[
                  styles.filterTabText,
                  statusFilter === st && styles.filterTabTextActive,
                ]}
              >
                {st === 'ALL'
                  ? 'All Orders'
                  : st === 'ASSIGNED'
                  ? 'Ready for Pickup'
                  : st === 'OUT_FOR_DELIVERY'
                  ? 'Out for Delivery'
                  : 'Delivered'}
              </Text>
            </TouchableOpacity>
          ))}
        </ScrollView>
      </View>

      {/* Orders List */}
      <ScrollView showsVerticalScrollIndicator={false}>
        {filteredOrders.length === 0 ? (
          <View style={styles.noDataBox}>
            <PackageX size={44} color="#94a3b8" />
            <Text style={styles.noDataTitle}>No data available</Text>
            <Text style={styles.noDataSubtitle}>No assigned deliveries found in this section.</Text>
          </View>
        ) : (
          filteredOrders.map((order) => {
          const isDelivered = order.delivery_status === 'DELIVERED';
          const isOut = order.delivery_status === 'OUT_FOR_DELIVERY';
          return (
            <TouchableOpacity
              key={order.id}
              style={styles.card}
              activeOpacity={0.8}
              onPress={() => setSelectedOrderForModal(order)}
            >
              <View style={styles.cardTop}>
                <Text style={styles.orderNum}>{order.order_number}</Text>
                <View
                  style={[
                    styles.badge,
                    isDelivered
                      ? styles.badgeDelivered
                      : isOut
                      ? styles.badgeOut
                      : styles.badgeAssigned,
                  ]}
                >
                  <Text
                    style={[
                      styles.badgeText,
                      isDelivered
                        ? styles.textDelivered
                        : isOut
                        ? styles.textOut
                        : styles.textAssigned,
                    ]}
                  >
                    {order.delivery_status}
                  </Text>
                </View>
              </View>

              <Text style={styles.customerName}>{order.customer_name}</Text>

              <View style={styles.addressRow}>
                <MapPin size={14} color="#64748b" />
                <Text style={styles.addressText} numberOfLines={2}>
                  {order.delivery_address}
                </Text>
              </View>

              <View style={styles.footerRow}>
                <View>
                  <Text style={styles.amountLabel}>
                    {order.payment_mode === 'COD' ? '💵 COD Amount' : '💳 Online Payment'}
                  </Text>
                  <Text style={styles.amountValue}>₹{order.order_amount}</Text>
                </View>
                <Text style={styles.viewDetailsText}>View Details →</Text>
              </View>
            </TouchableOpacity>
          );
        }))}
        <View style={{ height: 100 }} />
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f8fafc', paddingHorizontal: 16 },
  header: { paddingTop: 48, paddingBottom: 12 },
  headerTitle: { fontSize: 20, fontWeight: '800', color: '#0f172a' },
  filterTab: {
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 20,
    backgroundColor: '#ffffff',
    borderWidth: 1,
    borderColor: '#e2e8f0',
    marginRight: 8,
    height: 36,
  },
  filterTabActive: { backgroundColor: '#0f172a', borderColor: '#0f172a' },
  filterTabText: { fontSize: 12, fontWeight: '700', color: '#64748b' },
  filterTabTextActive: { color: '#ffffff' },
  card: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOpacity: 0.04,
    shadowRadius: 6,
    elevation: 2,
  },
  cardTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 6 },
  orderNum: { fontSize: 13, fontWeight: '800', color: '#0f172a' },
  badge: { paddingHorizontal: 8, paddingVertical: 3, borderRadius: 6 },
  badgeAssigned: { backgroundColor: '#eff6ff' },
  badgeOut: { backgroundColor: '#fef3c7' },
  badgeDelivered: { backgroundColor: '#ecfdf5' },
  badgeText: { fontSize: 10, fontWeight: '800' },
  textAssigned: { color: '#2563eb' },
  textOut: { color: '#d97706' },
  textDelivered: { color: '#059669' },
  customerName: { fontSize: 15, fontWeight: '800', color: '#0f172a', marginBottom: 6 },
  addressRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 10 },
  addressText: { fontSize: 12, color: '#475569', marginLeft: 6, flex: 1 },
  footerRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderTopWidth: 1,
    borderTopColor: '#f1f5f9',
    paddingTop: 10,
  },
  amountLabel: { fontSize: 10, color: '#64748b', fontWeight: '600' },
  amountValue: { fontSize: 15, fontWeight: '800', color: '#0f172a' },
  viewDetailsText: { fontSize: 12, fontWeight: '700', color: '#10b981' },
  noDataBox: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 48,
    paddingHorizontal: 20,
    backgroundColor: '#ffffff',
    borderRadius: 16,
    marginVertical: 12,
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  noDataTitle: { fontSize: 16, fontWeight: '700', color: '#334155', marginTop: 12 },
  noDataSubtitle: { fontSize: 12, color: '#94a3b8', marginTop: 4, textAlign: 'center' },
});


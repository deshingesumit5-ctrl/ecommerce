import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
} from 'react-native';
import { useDeliveryApp } from '../context/DeliveryAppContext';
import { Banknote, CheckCircle2, Clock } from 'lucide-react-native';

export const CODCollectionScreen: React.FC = () => {
  const { orders } = useDeliveryApp();

  const codOrders = orders.filter((o) => o.payment_mode === 'COD');
  const totalCollected = codOrders
    .filter((o) => o.is_cod_collected)
    .reduce((sum, o) => sum + o.cod_amount_to_collect, 0);

  const pendingCollection = codOrders
    .filter((o) => !o.is_cod_collected)
    .reduce((sum, o) => sum + o.cod_amount_to_collect, 0);

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>COD Cash Collections</Text>
      </View>

      {/* Summary Cards */}
      <View style={styles.summaryCard}>
        <View style={styles.summaryRow}>
          <View>
            <Text style={styles.summaryLabel}>Total Cash Collected Today</Text>
            <Text style={styles.collectedAmount}>₹{totalCollected}</Text>
          </View>
          <View style={styles.iconBgGreen}>
            <CheckCircle2 size={24} color="#10b981" />
          </View>
        </View>
        <Text style={styles.depositNote}>
          Please deposit all collected cash at the store branch counter by the end of your shift.
        </Text>
      </View>

      <View style={styles.pendingCard}>
        <View style={styles.summaryRow}>
          <View>
            <Text style={styles.summaryLabel}>Pending Cash to Collect</Text>
            <Text style={styles.pendingAmount}>₹{pendingCollection}</Text>
          </View>
          <View style={styles.iconBgYellow}>
            <Clock size={24} color="#d97706" />
          </View>
        </View>
      </View>

      {/* COD Itemized List */}
      <Text style={styles.sectionTitle}>COD Transactions</Text>
      {codOrders.map((order) => (
        <View key={order.id} style={styles.codItemCard}>
          <View style={styles.codTop}>
            <Text style={styles.codOrderNum}>{order.order_number}</Text>
            <View
              style={[
                styles.statusBadge,
                order.is_cod_collected ? styles.badgeGreen : styles.badgeYellow,
              ]}
            >
              <Text
                style={[
                  styles.statusText,
                  order.is_cod_collected ? styles.textGreen : styles.textYellow,
                ]}
              >
                {order.is_cod_collected ? 'CASH RECEIVED' : 'PENDING COLLECTION'}
              </Text>
            </View>
          </View>
          <Text style={styles.customerName}>{order.customer_name}</Text>
          <Text style={styles.addressText} numberOfLines={1}>
            {order.delivery_address}
          </Text>

          <View style={styles.codFooter}>
            <Text style={styles.amountToCollect}>Amount: ₹{order.cod_amount_to_collect}</Text>
            <Text style={styles.deliveryStatusText}>Delivery: {order.delivery_status}</Text>
          </View>
        </View>
      ))}

      <View style={{ height: 100 }} />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f8fafc', paddingHorizontal: 16 },
  header: { paddingTop: 48, paddingBottom: 16 },
  headerTitle: { fontSize: 20, fontWeight: '800', color: '#0f172a' },
  summaryCard: { backgroundColor: '#ffffff', borderRadius: 16, padding: 16, marginBottom: 12, borderLeftWidth: 4, borderLeftColor: '#10b981' },
  pendingCard: { backgroundColor: '#ffffff', borderRadius: 16, padding: 16, marginBottom: 16, borderLeftWidth: 4, borderLeftColor: '#f59e0b' },
  summaryRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  summaryLabel: { fontSize: 11, color: '#64748b', fontWeight: '700', textTransform: 'uppercase' },
  collectedAmount: { fontSize: 24, fontWeight: '900', color: '#10b981', marginTop: 2 },
  pendingAmount: { fontSize: 24, fontWeight: '900', color: '#d97706', marginTop: 2 },
  iconBgGreen: { width: 44, height: 44, borderRadius: 22, backgroundColor: '#ecfdf5', alignItems: 'center', justifyContent: 'center' },
  iconBgYellow: { width: 44, height: 44, borderRadius: 22, backgroundColor: '#fef3c7', alignItems: 'center', justifyContent: 'center' },
  depositNote: { fontSize: 11, color: '#64748b', marginTop: 10, lineHeight: 16 },
  sectionTitle: { fontSize: 15, fontWeight: '800', color: '#0f172a', marginBottom: 12 },
  codItemCard: { backgroundColor: '#ffffff', borderRadius: 14, padding: 14, marginBottom: 10 },
  codTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 4 },
  codOrderNum: { fontSize: 13, fontWeight: '800', color: '#0f172a' },
  statusBadge: { paddingHorizontal: 8, paddingVertical: 3, borderRadius: 6 },
  badgeGreen: { backgroundColor: '#ecfdf5' },
  badgeYellow: { backgroundColor: '#fef3c7' },
  statusText: { fontSize: 10, fontWeight: '800' },
  textGreen: { color: '#059669' },
  textYellow: { color: '#d97706' },
  customerName: { fontSize: 14, fontWeight: '700', color: '#0f172a', marginTop: 2 },
  addressText: { fontSize: 12, color: '#64748b', marginTop: 2 },
  codFooter: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 8, borderTopWidth: 1, borderTopColor: '#f1f5f9', paddingTop: 8 },
  amountToCollect: { fontSize: 13, fontWeight: '800', color: '#0f172a' },
  deliveryStatusText: { fontSize: 11, color: '#64748b' },
});

import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  Modal,
  TouchableOpacity,
  ScrollView,
  Linking,
} from 'react-native';
import { useDeliveryApp } from '../context/DeliveryAppContext';
import { DeliveryOrder } from '../types';
import { X, Phone, MapPin, Truck, CheckCircle2, Banknote, ShoppingBag } from 'lucide-react-native';

export const OrderDetailsModal: React.FC = () => {
  const { selectedOrderForModal, setSelectedOrderForModal, updateOrderStatus } = useDeliveryApp();

  if (!selectedOrderForModal) return null;

  const order = selectedOrderForModal;
  const isOut = order.delivery_status === 'OUT_FOR_DELIVERY';
  const isDelivered = order.delivery_status === 'DELIVERED';

  const handleCall = () => {
    Linking.openURL(`tel:${order.customer_mobile}`);
  };

  return (
    <Modal visible={!!selectedOrderForModal} animationType="slide" transparent>
      <View style={styles.overlay}>
        <View style={styles.modalCard}>
          {/* Header */}
          <View style={styles.header}>
            <View>
              <Text style={styles.orderNum}>{order.order_number}</Text>
              <Text style={styles.storeName}>Store: {order.store_name}</Text>
            </View>
            <TouchableOpacity
              onPress={() => setSelectedOrderForModal(null)}
              style={styles.closeBtn}
            >
              <X size={18} color="#64748b" />
            </TouchableOpacity>
          </View>

          <ScrollView showsVerticalScrollIndicator={false}>
            {/* Customer Details & Call Button */}
            <View style={styles.sectionCard}>
              <Text style={styles.sectionTitle}>Customer Information</Text>
              <Text style={styles.customerName}>{order.customer_name}</Text>
              <View style={styles.contactRow}>
                <Text style={styles.phoneText}>Mobile: +91 {order.customer_mobile}</Text>
                <TouchableOpacity style={styles.callBtn} onPress={handleCall}>
                  <Phone size={14} color="#ffffff" />
                  <Text style={styles.callBtnText}>Call</Text>
                </TouchableOpacity>
              </View>

              <View style={styles.addressBox}>
                <MapPin size={16} color="#10b981" />
                <Text style={styles.addressText}>{order.delivery_address}</Text>
              </View>
              {order.customer_notes ? (
                <Text style={styles.notesText}>Note: {order.customer_notes}</Text>
              ) : null}
            </View>

            {/* Ordered Products */}
            <View style={styles.sectionCard}>
              <View style={styles.itemsHeader}>
                <ShoppingBag size={16} color="#0f172a" />
                <Text style={styles.sectionTitle}>Ordered Products ({order.items.length})</Text>
              </View>
              {order.items.map((item, idx) => (
                <View key={idx} style={styles.itemRow}>
                  <Text style={styles.itemName}>
                    {item.quantity}x {item.name} ({item.unit})
                  </Text>
                  <Text style={styles.itemPrice}>₹{item.price * item.quantity}</Text>
                </View>
              ))}
              <View style={styles.divider} />
              <View style={styles.totalRow}>
                <Text style={styles.totalLabel}>Total Order Value</Text>
                <Text style={styles.totalValue}>₹{order.order_amount}</Text>
              </View>
            </View>

            {/* Payment & COD Details */}
            <View style={styles.sectionCard}>
              <Text style={styles.sectionTitle}>Payment & Collection</Text>
              <View style={styles.paymentRow}>
                <Text style={styles.payLabel}>Payment Mode</Text>
                <Text style={styles.payVal}>{order.payment_mode}</Text>
              </View>
              <View style={styles.paymentRow}>
                <Text style={styles.payLabel}>Payment Status</Text>
                <Text style={[styles.payVal, { color: order.payment_status === 'PAID' ? '#10b981' : '#f59e0b' }]}>
                  {order.payment_status}
                </Text>
              </View>

              {order.payment_mode === 'COD' && (
                <View style={styles.codBox}>
                  <Banknote size={20} color="#d97706" />
                  <View style={{ marginLeft: 10 }}>
                    <Text style={styles.codTitle}>Amount to Collect (COD)</Text>
                    <Text style={styles.codAmount}>₹{order.cod_amount_to_collect}</Text>
                  </View>
                </View>
              )}
            </View>

            <View style={{ height: 20 }} />
          </ScrollView>

          {/* Bottom Action Button */}
          {!isDelivered ? (
            <View style={styles.modalFooter}>
              {!isOut ? (
                <TouchableOpacity
                  style={styles.actionBtnOut}
                  onPress={() => updateOrderStatus(order.id, 'OUT_FOR_DELIVERY')}
                >
                  <Truck size={18} color="#ffffff" style={{ marginRight: 6 }} />
                  <Text style={styles.btnWhite}>Set Out For Delivery</Text>
                </TouchableOpacity>
              ) : (
                <TouchableOpacity
                  style={styles.actionBtnDelivered}
                  onPress={() => updateOrderStatus(order.id, 'DELIVERED')}
                >
                  <CheckCircle2 size={18} color="#ffffff" style={{ marginRight: 6 }} />
                  <Text style={styles.btnWhite}>
                    {order.payment_mode === 'COD'
                      ? 'Confirm Delivery & Cash Collected'
                      : 'Confirm Order Delivered'}
                  </Text>
                </TouchableOpacity>
              )}
            </View>
          ) : (
            <View style={styles.deliveredBanner}>
              <CheckCircle2 size={16} color="#10b981" />
              <Text style={styles.deliveredText}>
                Delivered successfully at {order.delivered_time || 'today'}
              </Text>
            </View>
          )}
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  overlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
  modalCard: { backgroundColor: '#ffffff', borderTopLeftRadius: 20, borderTopRightRadius: 20, padding: 20, maxHeight: '85%' },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 14 },
  orderNum: { fontSize: 18, fontWeight: '800', color: '#0f172a' },
  storeName: { fontSize: 11, color: '#64748b', marginTop: 2 },
  closeBtn: { width: 32, height: 32, borderRadius: 16, backgroundColor: '#f1f5f9', alignItems: 'center', justifyContent: 'center' },
  sectionCard: { backgroundColor: '#f8fafc', borderRadius: 14, padding: 14, marginBottom: 12 },
  sectionTitle: { fontSize: 13, fontWeight: '800', color: '#0f172a', marginBottom: 8 },
  customerName: { fontSize: 15, fontWeight: '700', color: '#0f172a' },
  contactRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginVertical: 6 },
  phoneText: { fontSize: 13, color: '#475569' },
  callBtn: { backgroundColor: '#10b981', flexDirection: 'row', alignItems: 'center', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 8 },
  callBtnText: { color: '#ffffff', fontSize: 11, fontWeight: '800', marginLeft: 4 },
  addressBox: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#ffffff', padding: 10, borderRadius: 10, marginTop: 6 },
  addressText: { fontSize: 12, color: '#334155', marginLeft: 6, flex: 1, lineHeight: 16 },
  notesText: { fontSize: 11, color: '#d97706', fontStyle: 'italic', marginTop: 6 },
  itemsHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 8 },
  itemRow: { flexDirection: 'row', justifyContent: 'space-between', marginVertical: 4 },
  itemName: { fontSize: 12, color: '#334155' },
  itemPrice: { fontSize: 12, fontWeight: '700', color: '#0f172a' },
  divider: { height: 1, backgroundColor: '#e2e8f0', marginVertical: 8 },
  totalRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  totalLabel: { fontSize: 13, fontWeight: '800', color: '#0f172a' },
  totalValue: { fontSize: 16, fontWeight: '800', color: '#10b981' },
  paymentRow: { flexDirection: 'row', justifyContent: 'space-between', marginVertical: 4 },
  payLabel: { fontSize: 12, color: '#64748b' },
  payVal: { fontSize: 12, fontWeight: '700', color: '#0f172a' },
  codBox: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#fef3c7', padding: 12, borderRadius: 10, marginTop: 8 },
  codTitle: { fontSize: 11, color: '#92400e', fontWeight: '700' },
  codAmount: { fontSize: 16, color: '#92400e', fontWeight: '900', marginTop: 2 },
  modalFooter: { marginTop: 10 },
  actionBtnOut: { backgroundColor: '#0284c7', flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 12, borderRadius: 12 },
  actionBtnDelivered: { backgroundColor: '#10b981', flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 12, borderRadius: 12 },
  btnWhite: { color: '#ffffff', fontWeight: '800', fontSize: 14 },
  deliveredBanner: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', backgroundColor: '#ecfdf5', padding: 12, borderRadius: 10, marginTop: 10 },
  deliveredText: { color: '#065f46', fontWeight: '700', fontSize: 12, marginLeft: 6 },
});

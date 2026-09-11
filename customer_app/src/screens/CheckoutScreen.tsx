import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
} from 'react-native';
import { useApp } from '../context/AppContext';
import { PaymentMode } from '../types';
import { MapPin, CreditCard, Banknote, ShieldCheck, CheckCircle2, ArrowLeft } from 'lucide-react-native';

export const CheckoutScreen: React.FC<{ onBack: () => void; onOrderPlaced: () => void }> = ({
  onBack,
  onOrderPlaced,
}) => {
  const { customer, customerLocation, selectedStore, getCartSummary, placeOrder } = useApp();
  const [paymentMode, setPaymentMode] = useState<PaymentMode>('COD');
  const [isProcessing, setIsProcessing] = useState(false);

  const { subtotal, discount, deliveryCharge, finalAmount } = getCartSummary();

  const handlePlaceOrder = () => {
    setIsProcessing(true);
    setTimeout(async () => {
      const order = await placeOrder(paymentMode, customerLocation.label);
      setIsProcessing(false);
      if (order) {
        onOrderPlaced();
      }
    }, 1000);
  };

  return (
    <View style={{ flex: 1, backgroundColor: '#f8fafc' }}>
      <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
        {/* Top bar */}
        <View style={styles.topBar}>
          <TouchableOpacity onPress={onBack} style={styles.backBtn}>
            <ArrowLeft size={20} color="#0f172a" />
          </TouchableOpacity>
          <Text style={styles.topTitle}>Confirm & Pay</Text>
        </View>

        {/* Delivery Address Card */}
        <View style={styles.card}>
          <View style={styles.cardHeader}>
            <MapPin size={18} color="#10b981" />
            <Text style={styles.cardTitle}>Delivery Address</Text>
          </View>
          <Text style={styles.nameText}>{customer?.name || 'Customer'}</Text>
          <Text style={styles.addressText}>{customerLocation.label}</Text>
          <Text style={styles.phoneText}>Mobile: +91 {customer?.mobile || '9876543210'}</Text>

          <View style={styles.storeBadge}>
            <Text style={styles.storeBadgeText}>
              Assigned Store: <Text style={{ fontWeight: '800' }}>{selectedStore?.name}</Text>
            </Text>
          </View>
        </View>

        {/* Payment Mode Selection */}
        <View style={styles.card}>
          <Text style={styles.cardTitle}>Select Payment Option</Text>

          {/* Cash on Delivery */}
          <TouchableOpacity
            style={[
              styles.paymentOption,
              paymentMode === 'COD' && styles.paymentActive,
            ]}
            onPress={() => setPaymentMode('COD')}
          >
            <View style={styles.optionLeft}>
              <Banknote size={22} color={paymentMode === 'COD' ? '#10b981' : '#64748b'} />
              <View style={{ marginLeft: 12 }}>
                <Text style={styles.paymentName}>Cash on Delivery (COD)</Text>
                <Text style={styles.paymentSub}>Pay with cash or UPI upon home delivery</Text>
              </View>
            </View>
            {paymentMode === 'COD' && <CheckCircle2 size={20} color="#10b981" />}
          </TouchableOpacity>

          {/* Online Payment */}
          <TouchableOpacity
            style={[
              styles.paymentOption,
              paymentMode === 'ONLINE' && styles.paymentActive,
            ]}
            onPress={() => setPaymentMode('ONLINE')}
          >
            <View style={styles.optionLeft}>
              <CreditCard size={22} color={paymentMode === 'ONLINE' ? '#10b981' : '#64748b'} />
              <View style={{ marginLeft: 12 }}>
                <Text style={styles.paymentName}>Online Payment</Text>
                <Text style={styles.paymentSub}>Instant UPI, Cards & Netbanking</Text>
              </View>
            </View>
            {paymentMode === 'ONLINE' && <CheckCircle2 size={20} color="#10b981" />}
          </TouchableOpacity>
        </View>

        {/* Final Price Breakdown */}
        <View style={styles.card}>
          <Text style={styles.cardTitle}>Order Summary</Text>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Subtotal</Text>
            <Text style={styles.summaryVal}>₹{subtotal}</Text>
          </View>
          {discount > 0 && (
            <View style={styles.summaryRow}>
              <Text style={[styles.summaryLabel, { color: '#10b981' }]}>Discount</Text>
              <Text style={[styles.summaryVal, { color: '#10b981' }]}>- ₹{discount}</Text>
            </View>
          )}
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Delivery Fee</Text>
            <Text style={styles.summaryVal}>{deliveryCharge === 0 ? 'FREE' : `₹${deliveryCharge}`}</Text>
          </View>
          <View style={styles.divider} />
          <View style={styles.summaryRow}>
            <Text style={styles.totalLabel}>Grand Total</Text>
            <Text style={styles.totalVal}>₹{finalAmount}</Text>
          </View>
        </View>

        <View style={{ height: 120 }} />
      </ScrollView>

      {/* Place Order CTA */}
      <View style={styles.bottomBar}>
        <View>
          <Text style={styles.barModeText}>
            {paymentMode === 'COD' ? 'Cash on Delivery' : 'Online Payment'}
          </Text>
          <Text style={styles.barAmount}>₹{finalAmount}</Text>
        </View>
        <TouchableOpacity
          style={[styles.placeBtn, isProcessing && { opacity: 0.7 }]}
          disabled={isProcessing}
          onPress={handlePlaceOrder}
        >
          <ShieldCheck size={18} color="#ffffff" style={{ marginRight: 6 }} />
          <Text style={styles.placeBtnText}>
            {isProcessing ? 'Processing...' : 'Place Order'}
          </Text>
        </TouchableOpacity>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, paddingHorizontal: 16 },
  topBar: { flexDirection: 'row', alignItems: 'center', paddingTop: 48, paddingBottom: 16 },
  backBtn: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#ffffff', alignItems: 'center', justifyContent: 'center' },
  topTitle: { fontSize: 18, fontWeight: '800', color: '#0f172a', marginLeft: 12 },
  card: { backgroundColor: '#ffffff', borderRadius: 16, padding: 16, marginBottom: 14 },
  cardHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 8 },
  cardTitle: { fontSize: 15, fontWeight: '800', color: '#0f172a', marginLeft: 6 },
  nameText: { fontSize: 14, fontWeight: '700', color: '#0f172a', marginTop: 4 },
  addressText: { fontSize: 13, color: '#475569', marginTop: 2, lineHeight: 18 },
  phoneText: { fontSize: 12, color: '#64748b', marginTop: 4 },
  storeBadge: { backgroundColor: '#f1f5f9', padding: 8, borderRadius: 8, marginTop: 10 },
  storeBadgeText: { fontSize: 11, color: '#334155' },
  paymentOption: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 14,
    borderRadius: 12,
    borderWidth: 1.5,
    borderColor: '#e2e8f0',
    marginTop: 10,
  },
  paymentActive: { borderColor: '#10b981', backgroundColor: '#f0fdf4' },
  optionLeft: { flexDirection: 'row', alignItems: 'center', flex: 1 },
  paymentName: { fontSize: 14, fontWeight: '700', color: '#0f172a' },
  paymentSub: { fontSize: 11, color: '#64748b', marginTop: 2 },
  summaryRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 8 },
  summaryLabel: { fontSize: 13, color: '#64748b' },
  summaryVal: { fontSize: 13, fontWeight: '700', color: '#0f172a' },
  divider: { height: 1, backgroundColor: '#e2e8f0', marginVertical: 8 },
  totalLabel: { fontSize: 15, fontWeight: '800', color: '#0f172a' },
  totalVal: { fontSize: 17, fontWeight: '800', color: '#10b981' },
  bottomBar: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: '#ffffff',
    borderTopWidth: 1,
    borderTopColor: '#e2e8f0',
    paddingHorizontal: 16,
    paddingTop: 12,
    paddingBottom: 32,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  barModeText: { fontSize: 11, color: '#64748b', fontWeight: '600' },
  barAmount: { fontSize: 18, fontWeight: '800', color: '#0f172a' },
  placeBtn: {
    backgroundColor: '#10b981',
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 24,
    paddingVertical: 12,
    borderRadius: 12,
  },
  placeBtnText: { color: '#ffffff', fontWeight: '800', fontSize: 14 },
});

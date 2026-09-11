import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Image,
  TextInput,
} from 'react-native';
import { useApp } from '../context/AppContext';
import { Trash2, Plus, Minus, Tag, ArrowRight, ShieldCheck, AlertTriangle } from 'lucide-react-native';

export const CartScreen: React.FC<{ onNavigateToCheckout: () => void; onBrowseCatalog: () => void }> = ({
  onNavigateToCheckout,
  onBrowseCatalog,
}) => {
  const {
    cart,
    updateQuantity,
    removeFromCart,
    appliedCoupon,
    applyCoupon,
    removeCoupon,
    getCartSummary,
    isDeliverable,
    deliveryDistanceKm,
  } = useApp();

  const [couponInput, setCouponInput] = useState('');
  const [couponMsg, setCouponMsg] = useState<{ text: string; success: boolean } | null>(null);

  const { subtotal, discount, deliveryCharge, finalAmount } = getCartSummary();

  const handleApplyCoupon = () => {
    if (!couponInput.trim()) return;
    const res = applyCoupon(couponInput);
    setCouponMsg({ text: res.message, success: res.success });
    if (res.success) setCouponInput('');
  };

  if (cart.length === 0) {
    return (
      <View style={styles.emptyContainer}>
        <Text style={styles.emptyEmoji}>🛒</Text>
        <Text style={styles.emptyTitle}>Your Cart is Empty</Text>
        <Text style={styles.emptySub}>
          Explore fresh vegetables, fruits, and groceries with today's updated market prices!
        </Text>
        <TouchableOpacity style={styles.exploreBtn} onPress={onBrowseCatalog}>
          <Text style={styles.exploreBtnText}>Start Shopping</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <View style={{ flex: 1, backgroundColor: '#f8fafc' }}>
      <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
        <View style={styles.header}>
          <Text style={styles.headerTitle}>Shopping Cart ({cart.length})</Text>
        </View>

        {!isDeliverable && (
          <View style={styles.alertBox}>
            <AlertTriangle size={18} color="#ef4444" />
            <Text style={styles.alertText}>
              Delivery address is outside service radius ({deliveryDistanceKm} km). Change location to proceed.
            </Text>
          </View>
        )}

        {/* Free Delivery Goal Banner */}
        <View style={styles.deliveryGoalCard}>
          {subtotal >= 500 ? (
            <View style={styles.freeDelRow}>
              <ShieldCheck size={18} color="#10b981" />
              <Text style={styles.freeDelText}>You unlocked FREE Delivery on this order!</Text>
            </View>
          ) : (
            <Text style={styles.goalText}>
              Add <Text style={styles.boldAmount}>₹{500 - subtotal}</Text> more items for FREE Delivery!
            </Text>
          )}
        </View>

        {/* Cart Items List */}
        <View style={styles.itemsCard}>
          {cart.map((item) => (
            <View key={item.product.id} style={styles.itemRow}>
              <Image source={{ uri: item.product.image_url }} style={styles.itemImage} />
              <View style={styles.itemDetails}>
                <Text style={styles.itemName}>{item.product.name}</Text>
                <Text style={styles.itemUnit}>{item.product.unit}</Text>
                <Text style={styles.itemPrice}>₹{item.product.daily_price * item.quantity}</Text>
              </View>

              <View style={styles.qtyContainer}>
                <TouchableOpacity
                  style={styles.qtyBtn}
                  onPress={() => updateQuantity(item.product.id, item.quantity - 1)}
                >
                  {item.quantity === 1 ? (
                    <Trash2 size={13} color="#ef4444" />
                  ) : (
                    <Minus size={13} color="#0f172a" />
                  )}
                </TouchableOpacity>
                <Text style={styles.qtyText}>{item.quantity}</Text>
                <TouchableOpacity
                  style={styles.qtyBtn}
                  onPress={() => updateQuantity(item.product.id, item.quantity + 1)}
                >
                  <Plus size={13} color="#0f172a" />
                </TouchableOpacity>
              </View>
            </View>
          ))}
        </View>

        {/* Coupon Code Section */}
        <View style={styles.couponCard}>
          <View style={styles.couponHeader}>
            <Tag size={16} color="#10b981" />
            <Text style={styles.couponTitle}>Apply Discount Coupon</Text>
          </View>
          {appliedCoupon ? (
            <View style={styles.appliedRow}>
              <View>
                <Text style={styles.appliedCode}>Coupon: {appliedCoupon.code}</Text>
                <Text style={styles.appliedSaving}>Saved ₹{discount} on this order</Text>
              </View>
              <TouchableOpacity style={styles.removeCouponBtn} onPress={removeCoupon}>
                <Text style={styles.removeCouponText}>Remove</Text>
              </TouchableOpacity>
            </View>
          ) : (
            <View style={styles.couponInputRow}>
              <TextInput
                style={styles.couponInput}
                placeholder="Enter Coupon (e.g. FIRST50)"
                placeholderTextColor="#94a3b8"
                autoCapitalize="characters"
                value={couponInput}
                onChangeText={setCouponInput}
              />
              <TouchableOpacity style={styles.applyBtn} onPress={handleApplyCoupon}>
                <Text style={styles.applyBtnText}>APPLY</Text>
              </TouchableOpacity>
            </View>
          )}

          {couponMsg && (
            <Text
              style={[
                styles.msgText,
                couponMsg.success ? styles.msgSuccess : styles.msgError,
              ]}
            >
              {couponMsg.text}
            </Text>
          )}
        </View>

        {/* Bill Summary */}
        <View style={styles.billCard}>
          <Text style={styles.billTitle}>Bill Details</Text>
          <View style={styles.billRow}>
            <Text style={styles.billLabel}>Item Total (Daily Rates)</Text>
            <Text style={styles.billVal}>₹{subtotal}</Text>
          </View>
          {discount > 0 && (
            <View style={styles.billRow}>
              <Text style={[styles.billLabel, { color: '#10b981' }]}>Coupon Discount</Text>
              <Text style={[styles.billVal, { color: '#10b981' }]}>- ₹{discount}</Text>
            </View>
          )}
          <View style={styles.billRow}>
            <Text style={styles.billLabel}>Delivery Fee (3 KM Hyperlocal)</Text>
            <Text style={styles.billVal}>
              {deliveryCharge === 0 ? (
                <Text style={{ color: '#10b981', fontWeight: '700' }}>FREE</Text>
              ) : (
                `₹${deliveryCharge}`
              )}
            </Text>
          </View>
          <View style={styles.divider} />
          <View style={styles.billRow}>
            <Text style={styles.totalLabel}>To Pay</Text>
            <Text style={styles.totalVal}>₹{finalAmount}</Text>
          </View>
        </View>

        <View style={{ height: 140 }} />
      </ScrollView>

      {/* Sticky Bottom Checkout Bar */}
      <View style={styles.bottomBar}>
        <View>
          <Text style={styles.barLabel}>Total Payable</Text>
          <Text style={styles.barAmount}>₹{finalAmount}</Text>
        </View>
        <TouchableOpacity
          style={[styles.checkoutBtn, !isDeliverable && { backgroundColor: '#94a3b8' }]}
          disabled={!isDeliverable}
          onPress={onNavigateToCheckout}
        >
          <Text style={styles.checkoutBtnText}>Proceed to Checkout</Text>
          <ArrowRight size={18} color="#ffffff" />
        </TouchableOpacity>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, paddingHorizontal: 16 },
  header: { paddingTop: 48, paddingBottom: 16 },
  headerTitle: { fontSize: 20, fontWeight: '800', color: '#0f172a' },
  emptyContainer: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: 24 },
  emptyEmoji: { fontSize: 64, marginBottom: 12 },
  emptyTitle: { fontSize: 20, fontWeight: '800', color: '#0f172a', marginBottom: 8 },
  emptySub: { fontSize: 13, color: '#64748b', textAlign: 'center', marginBottom: 24, lineHeight: 20 },
  exploreBtn: { backgroundColor: '#10b981', paddingHorizontal: 24, paddingVertical: 12, borderRadius: 12 },
  exploreBtnText: { color: '#ffffff', fontWeight: '800', fontSize: 14 },
  alertBox: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#fef2f2',
    borderWidth: 1,
    borderColor: '#fecaca',
    padding: 12,
    borderRadius: 12,
    marginBottom: 12,
  },
  alertText: { color: '#991b1b', fontSize: 12, fontWeight: '600', marginLeft: 8, flex: 1 },
  deliveryGoalCard: {
    backgroundColor: '#ecfdf5',
    borderWidth: 1,
    borderColor: '#a7f3d0',
    padding: 12,
    borderRadius: 12,
    marginBottom: 14,
  },
  freeDelRow: { flexDirection: 'row', alignItems: 'center' },
  freeDelText: { color: '#065f46', fontSize: 13, fontWeight: '700', marginLeft: 6 },
  goalText: { color: '#065f46', fontSize: 13 },
  boldAmount: { fontWeight: '800' },
  itemsCard: { backgroundColor: '#ffffff', borderRadius: 16, padding: 14, marginBottom: 14 },
  itemRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
  },
  itemImage: { width: 50, height: 50, borderRadius: 8, backgroundColor: '#f1f5f9' },
  itemDetails: { flex: 1, marginLeft: 12 },
  itemName: { fontSize: 13, fontWeight: '700', color: '#0f172a' },
  itemUnit: { fontSize: 11, color: '#64748b', marginTop: 2 },
  itemPrice: { fontSize: 14, fontWeight: '800', color: '#0f172a', marginTop: 4 },
  qtyContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f1f5f9',
    borderRadius: 8,
    paddingHorizontal: 4,
    paddingVertical: 4,
  },
  qtyBtn: { width: 28, height: 26, alignItems: 'center', justifyContent: 'center' },
  qtyText: { fontSize: 13, fontWeight: '800', color: '#0f172a', marginHorizontal: 6 },
  couponCard: { backgroundColor: '#ffffff', borderRadius: 16, padding: 14, marginBottom: 14 },
  couponHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 10 },
  couponTitle: { fontSize: 14, fontWeight: '700', color: '#0f172a', marginLeft: 6 },
  couponInputRow: { flexDirection: 'row', alignItems: 'center' },
  couponInput: {
    flex: 1,
    backgroundColor: '#f8fafc',
    borderWidth: 1,
    borderColor: '#e2e8f0',
    borderRadius: 10,
    paddingHorizontal: 12,
    height: 42,
    fontSize: 13,
    color: '#0f172a',
  },
  applyBtn: {
    backgroundColor: '#0f172a',
    paddingHorizontal: 16,
    height: 42,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
    marginLeft: 8,
  },
  applyBtnText: { color: '#ffffff', fontWeight: '800', fontSize: 12 },
  appliedRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: '#ecfdf5',
    padding: 10,
    borderRadius: 10,
  },
  appliedCode: { fontSize: 13, fontWeight: '800', color: '#065f46' },
  appliedSaving: { fontSize: 11, color: '#047857', marginTop: 2 },
  removeCouponBtn: { paddingVertical: 4, paddingHorizontal: 8 },
  removeCouponText: { color: '#ef4444', fontSize: 12, fontWeight: '700' },
  msgText: { fontSize: 12, marginTop: 6, fontWeight: '600' },
  msgSuccess: { color: '#10b981' },
  msgError: { color: '#ef4444' },
  billCard: { backgroundColor: '#ffffff', borderRadius: 16, padding: 14 },
  billTitle: { fontSize: 14, fontWeight: '800', color: '#0f172a', marginBottom: 12 },
  billRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 8 },
  billLabel: { fontSize: 13, color: '#64748b' },
  billVal: { fontSize: 13, fontWeight: '700', color: '#0f172a' },
  divider: { height: 1, backgroundColor: '#e2e8f0', marginVertical: 10 },
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
  barLabel: { fontSize: 11, color: '#64748b', fontWeight: '600' },
  barAmount: { fontSize: 18, fontWeight: '800', color: '#0f172a' },
  checkoutBtn: {
    backgroundColor: '#10b981',
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 12,
  },
  checkoutBtnText: { color: '#ffffff', fontWeight: '800', fontSize: 14, marginRight: 8 },
});

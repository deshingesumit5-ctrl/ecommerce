import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TextInput,
  TouchableOpacity,
  Image,
  Modal,
  Platform,
  RefreshControl,
} from 'react-native';
import { useApp } from '../context/AppContext';
import {
  MapPin,
  Search,
  Plus,
  Minus,
  ShieldAlert,
  Sparkles,
  Bell,
  BellOff,
  X,
  PackageX,
  User,
} from 'lucide-react-native';

export const HomeScreen: React.FC<{
  onOpenLocation: () => void;
  onNavigateToCart: () => void;
  onNavigateToCatalog: (categoryId?: number) => void;
  onNavigateToProfile?: () => void;
}> = ({ onOpenLocation, onNavigateToCart, onNavigateToCatalog, onNavigateToProfile }) => {
  const {
    customerLocation,
    selectedStore,
    isDeliverable,
    deliveryDistanceKm,
    categories,
    products,
    cart,
    notifications,
    unreadNotificationCount,
    markNotificationAsRead,
    addToCart,
    updateQuantity,
    loading,
    refreshData,
  } = useApp();

  const [searchQuery, setSearchQuery] = useState('');
  const [showNotifModal, setShowNotifModal] = useState(false);

  const filteredProducts = products.filter((p) =>
    p.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    (p.description && p.description.toLowerCase().includes(searchQuery.toLowerCase()))
  );

  const getProductCartQty = (productId: number) => {
    return cart.find((item) => item.product.id === productId)?.quantity || 0;
  };

  return (
    <ScrollView
      style={styles.container}
      showsVerticalScrollIndicator={false}
      refreshControl={
        <RefreshControl
          refreshing={loading}
          onRefresh={refreshData}
          colors={['#10b981']}
          tintColor="#10b981"
        />
      }
    >
      {/* Header with Location & Notification Bell (replaces cart icon) */}
      <View style={styles.header}>
        <View style={{ flex: 1 }}>
          <Text style={styles.locationTitle}>Delivery Location</Text>
          <TouchableOpacity
            style={styles.locationSelector}
            onPress={onOpenLocation}
            activeOpacity={0.7}
          >
            <MapPin size={16} color="#10b981" />
            <Text style={styles.locationText} numberOfLines={1}>
              {customerLocation.label}
            </Text>
            <Text style={styles.changeText}>Change</Text>
          </TouchableOpacity>
        </View>

        {/* Header Right Actions: Notification Bell + Profile Icon */}
        <View style={styles.headerRightActions}>
          {/* Functional Notification Bell Icon */}
          <TouchableOpacity
            style={styles.notifButton}
            onPress={() => setShowNotifModal(true)}
            activeOpacity={0.8}
          >
            <Bell size={20} color="#0f172a" />
            {unreadNotificationCount > 0 && (
              <View style={styles.notifBadge}>
                <Text style={styles.notifBadgeText}>{unreadNotificationCount}</Text>
              </View>
            )}
          </TouchableOpacity>

          {/* Profile Icon button after Notification Bell */}
          <TouchableOpacity
            style={styles.profileHeaderBtn}
            onPress={onNavigateToProfile}
            activeOpacity={0.8}
          >
            <User size={19} color="#0f172a" />
          </TouchableOpacity>
        </View>
      </View>

      {/* Hyperlocal Radius Status Banner */}
      <View
        style={[
          styles.serviceBanner,
          isDeliverable ? styles.serviceActive : styles.serviceInactive,
        ]}
      >
        <View style={styles.bannerRow}>
          {isDeliverable ? (
            <>
              <View style={styles.statusDotGreen} />
              <Text style={styles.bannerText}>
                Delivering from <Text style={styles.boldText}>{selectedStore?.name || 'Local Store'}</Text> (
                {deliveryDistanceKm} km away • Within {selectedStore?.delivery_radius_km || 3} km radius)
              </Text>
            </>
          ) : (
            <>
              <ShieldAlert size={18} color="#ef4444" />
              <Text style={styles.bannerTextRed}>
                Outside Delivery Radius ({deliveryDistanceKm} km away). Orders cannot be placed.
              </Text>
            </>
          )}
        </View>
      </View>

      {/* Search Input */}
      <View style={styles.searchBox}>
        <Search size={18} color="#64748b" />
        <TextInput
          style={styles.searchInput}
          placeholder="Search fresh vegetables, fruits, groceries..."
          placeholderTextColor="#94a3b8"
          value={searchQuery}
          onChangeText={setSearchQuery}
        />
      </View>

      {/* Promotional Daily Deals Banner */}
      <View style={styles.promoCard}>
        <View style={styles.promoBadge}>
          <Sparkles size={14} color="#f59e0b" />
          <Text style={styles.promoBadgeText}>Today's Non-Veg Prices</Text>
        </View>
        <Text style={styles.promoTitle}>Fresh Mutton, Chicken, Fish & Eggs</Text>
        <Text style={styles.promoSub}>
          Free delivery on all orders above ₹500 in your 3 KM zone!
        </Text>
      </View>

      {/* Categories */}
      {categories.length > 0 && (
        <>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Shop by Category</Text>
            <TouchableOpacity onPress={() => onNavigateToCatalog()}>
              <Text style={styles.seeAllText}>See All</Text>
            </TouchableOpacity>
          </View>

          <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.categoryScroll}>
            {categories.map((cat) => (
              <TouchableOpacity
                key={cat.id}
                style={styles.categoryItem}
                onPress={() => onNavigateToCatalog(cat.id)}
              >
                <View style={styles.categoryIconBg}>
                  {cat.image ? (
                    <Image source={{ uri: cat.image }} style={styles.categoryImg} />
                  ) : (
                    <Text style={styles.categoryEmoji}>🛒</Text>
                  )}
                </View>
                <Text style={styles.categoryName} numberOfLines={2}>
                  {cat.name}
                </Text>
              </TouchableOpacity>
            ))}
          </ScrollView>
        </>
      )}

      {/* Daily Price Products Section */}
      <View style={styles.sectionHeader}>
        <Text style={styles.sectionTitle}>Daily Fresh Essentials</Text>
      </View>

      {/* If No Products, Show "No data available" */}
      {filteredProducts.length === 0 ? (
        <View style={styles.noDataBox}>
          <PackageX size={44} color="#94a3b8" />
          <Text style={styles.noDataTitle}>No data available</Text>
          <Text style={styles.noDataSubtitle}>
            {searchQuery ? 'No products match your search query.' : 'Admin has not added any products yet.'}
          </Text>
        </View>
      ) : (
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          nestedScrollEnabled
          contentContainerStyle={styles.productRow}
        >
          {filteredProducts.map((product) => {
            const qty = getProductCartQty(product.id);
            return (
              <View key={product.id} style={styles.productCard}>
                {/* Product Image - resizeMode contain ensures FULL image is shown on web & mobile */}
                <View style={styles.imageContainer}>
                  <Image
                    source={{ uri: product.image_url }}
                    style={styles.productImage}
                    resizeMode="contain"
                  />
                </View>

                <View style={styles.productInfo}>
                  <Text style={styles.unitBadge}>{product.unit}</Text>
                  <Text style={styles.productName} numberOfLines={2}>
                    {product.name}
                  </Text>
                  {product.description ? (
                    <Text style={styles.productDesc} numberOfLines={2}>
                      {product.description}
                    </Text>
                  ) : null}
                  <View style={styles.priceRow}>
                    <Text style={styles.dailyPrice}>₹{product.daily_price}</Text>
                    {product.base_price > product.daily_price && (
                      <Text style={styles.basePrice}>₹{product.base_price}</Text>
                    )}
                  </View>
                </View>

                {/* Add / Qty Control */}
                <View style={styles.actionRow}>
                  {qty === 0 ? (
                    <TouchableOpacity
                      style={[
                        styles.addButton,
                        !isDeliverable && { backgroundColor: '#cbd5e1' },
                      ]}
                      disabled={!isDeliverable}
                      onPress={() => addToCart(product)}
                    >
                      <Plus size={16} color="#ffffff" />
                      <Text style={styles.addButtonText}>Add to cart</Text>
                    </TouchableOpacity>
                  ) : (
                    <View style={styles.qtyContainer}>
                      <TouchableOpacity
                        style={styles.qtyBtn}
                        onPress={() => updateQuantity(product.id, qty - 1)}
                      >
                        <Minus size={14} color="#0f172a" />
                      </TouchableOpacity>
                      <Text style={styles.qtyText}>{qty}</Text>
                      <TouchableOpacity
                        style={styles.qtyBtn}
                        onPress={() => updateQuantity(product.id, qty + 1)}
                      >
                        <Plus size={14} color="#0f172a" />
                      </TouchableOpacity>
                    </View>
                  )}
                </View>
              </View>
            );
          })}
        </ScrollView>
      )}

      {/* Notifications Modal */}
      <Modal
        visible={showNotifModal}
        transparent
        animationType="fade"
        onRequestClose={() => setShowNotifModal(false)}
      >
        <View style={styles.modalBackdrop}>
          <View style={styles.notifCard}>
            <View style={styles.notifHeader}>
              <View style={styles.notifHeaderLeft}>
                <Bell size={18} color="#10b981" />
                <Text style={styles.notifTitle}>Notifications</Text>
              </View>
              <TouchableOpacity onPress={() => setShowNotifModal(false)} style={styles.closeBtn}>
                <X size={18} color="#64748b" />
              </TouchableOpacity>
            </View>

            <ScrollView style={styles.notifList} showsVerticalScrollIndicator={false}>
              {notifications.length === 0 ? (
                <View style={styles.emptyNotifBox}>
                  <BellOff size={36} color="#cbd5e1" />
                  <Text style={styles.noDataTitle}>No data available</Text>
                  <Text style={styles.noDataSubtitle}>You're all caught up! No notifications yet.</Text>
                </View>
              ) : (
                notifications.map((notif) => (
                  <TouchableOpacity
                    key={notif.id}
                    style={[
                      styles.notifItem,
                      !notif.is_read && styles.notifItemUnread,
                    ]}
                    onPress={() => markNotificationAsRead(notif.id)}
                    activeOpacity={0.8}
                  >
                    <View style={{ flex: 1 }}>
                      <Text style={styles.notifItemTitle}>{notif.title}</Text>
                      <Text style={styles.notifItemMsg}>{notif.message}</Text>
                      {notif.time && <Text style={styles.notifItemTime}>{notif.time}</Text>}
                    </View>
                    {!notif.is_read && <View style={styles.unreadDot} />}
                  </TouchableOpacity>
                ))
              )}
            </ScrollView>
          </View>
        </View>
      </Modal>

      <View style={{ height: 100 }} />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f8fafc', paddingHorizontal: 16 },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingTop: 48,
    paddingBottom: 12,
  },
  locationTitle: { fontSize: 11, color: '#64748b', fontWeight: '600', textTransform: 'uppercase' },
  locationSelector: { flexDirection: 'row', alignItems: 'center', marginTop: 2 },
  locationText: { fontSize: 14, fontWeight: '700', color: '#0f172a', marginHorizontal: 4, maxWidth: 200 },
  changeText: { fontSize: 12, color: '#10b981', fontWeight: '700' },
  headerRightActions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  notifButton: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: '#ffffff',
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOpacity: 0.08,
    shadowRadius: 8,
    elevation: 3,
    borderWidth: 1,
    borderColor: '#f1f5f9',
  },
  profileHeaderBtn: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: '#ffffff',
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOpacity: 0.08,
    shadowRadius: 8,
    elevation: 3,
    borderWidth: 1,
    borderColor: '#f1f5f9',
  },
  notifBadge: {
    position: 'absolute',
    top: 6,
    right: 6,
    backgroundColor: '#ef4444',
    borderRadius: 10,
    minWidth: 18,
    height: 18,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 4,
  },
  notifBadgeText: { color: '#ffffff', fontSize: 10, fontWeight: '800' },
  serviceBanner: { padding: 10, borderRadius: 10, marginVertical: 8 },
  serviceActive: { backgroundColor: '#ecfdf5', borderWidth: 1, borderColor: '#a7f3d0' },
  serviceInactive: { backgroundColor: '#fef2f2', borderWidth: 1, borderColor: '#fecaca' },
  bannerRow: { flexDirection: 'row', alignItems: 'center' },
  statusDotGreen: { width: 8, height: 8, borderRadius: 4, backgroundColor: '#10b981', marginRight: 6 },
  bannerText: { fontSize: 12, color: '#065f46', flex: 1 },
  bannerTextRed: { fontSize: 12, color: '#991b1b', fontWeight: '600', marginLeft: 6, flex: 1 },
  boldText: { fontWeight: '700' },
  searchBox: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderRadius: 12,
    paddingHorizontal: 12,
    height: 46,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    marginTop: 8,
    marginBottom: 16,
  },
  searchInput: { flex: 1, marginLeft: 8, fontSize: 14, color: '#0f172a' },
  promoCard: {
    backgroundColor: '#0f172a',
    borderRadius: 16,
    padding: 16,
    marginBottom: 20,
  },
  promoBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(245, 158, 11, 0.2)',
    alignSelf: 'flex-start',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 6,
    marginBottom: 8,
  },
  promoBadgeText: { color: '#f59e0b', fontSize: 11, fontWeight: '700', marginLeft: 4 },
  promoTitle: { color: '#ffffff', fontSize: 17, fontWeight: '800', marginBottom: 4 },
  promoSub: { color: '#94a3b8', fontSize: 12 },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  sectionTitle: { fontSize: 16, fontWeight: '800', color: '#0f172a' },
  seeAllText: { fontSize: 13, color: '#10b981', fontWeight: '700' },
  categoryScroll: { marginBottom: 20 },
  categoryItem: { alignItems: 'center', width: 80, marginRight: 12 },
  categoryIconBg: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#ffffff',
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOpacity: 0.05,
    shadowRadius: 6,
    elevation: 2,
    marginBottom: 6,
    overflow: 'hidden',
  },
  categoryImg: { width: 44, height: 44, borderRadius: 22 },
  categoryEmoji: { fontSize: 24 },
  categoryName: { fontSize: 11, color: '#334155', fontWeight: '600', textAlign: 'center' },
  productRow: {
    flexDirection: 'row',
    alignItems: 'stretch',
    paddingBottom: 8,
    paddingRight: 8,
  },
  productCard: {
    width: 170,
    backgroundColor: '#ffffff',
    borderRadius: 14,
    padding: 10,
    marginRight: 14,
    justifyContent: 'space-between',
    shadowColor: '#000',
    shadowOpacity: 0.04,
    shadowRadius: 6,
    elevation: 2,
    borderWidth: 1,
    borderColor: '#f1f5f9',
  },
  imageContainer: {
    width: '100%',
    height: Platform.OS === 'web' ? 150 : 120,
    backgroundColor: '#f8fafc',
    borderRadius: 10,
    overflow: 'hidden',
    alignItems: 'center',
    justifyContent: 'center',
  },
  productImage: {
    width: '100%',
    height: '100%',
  },
  productInfo: { marginTop: 8, flexGrow: 1 },
  unitBadge: { fontSize: 10, color: '#64748b', fontWeight: '600' },
  productName: { fontSize: 13, fontWeight: '700', color: '#0f172a', marginTop: 2 },
  productDesc: { fontSize: 11, color: '#64748b', marginTop: 2, lineHeight: 15 },
  priceRow: { flexDirection: 'row', alignItems: 'center', marginTop: 6 },
  dailyPrice: { fontSize: 15, fontWeight: '800', color: '#0f172a' },
  basePrice: { fontSize: 12, color: '#94a3b8', textDecorationLine: 'line-through', marginLeft: 6 },
  actionRow: { marginTop: 10 },
  addButton: {
    backgroundColor: '#10b981',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 8,
    borderRadius: 8,
  },
  addButtonText: { color: '#ffffff', fontWeight: '800', fontSize: 11, marginLeft: 4 },
  qtyContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: '#f1f5f9',
    borderRadius: 8,
    paddingHorizontal: 4,
    paddingVertical: 4,
  },
  qtyBtn: { width: 28, height: 26, alignItems: 'center', justifyContent: 'center' },
  qtyText: { fontSize: 13, fontWeight: '800', color: '#0f172a' },
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
  modalBackdrop: {
    flex: 1,
    backgroundColor: 'rgba(15, 23, 42, 0.6)',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 16,
  },
  notifCard: {
    width: '100%',
    maxWidth: 420,
    backgroundColor: '#ffffff',
    borderRadius: 20,
    padding: 16,
    shadowColor: '#000',
    shadowOpacity: 0.15,
    shadowRadius: 15,
    elevation: 8,
  },
  notifHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingBottom: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
  },
  notifHeaderLeft: { flexDirection: 'row', alignItems: 'center' },
  notifTitle: { fontSize: 16, fontWeight: '800', color: '#0f172a', marginLeft: 8 },
  closeBtn: { padding: 4 },
  notifList: { maxHeight: 360, marginTop: 8 },
  notifItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 12,
    borderRadius: 12,
    backgroundColor: '#f8fafc',
    marginBottom: 8,
    borderWidth: 1,
    borderColor: '#f1f5f9',
  },
  notifItemUnread: {
    backgroundColor: '#ecfdf5',
    borderColor: '#a7f3d0',
  },
  notifItemTitle: { fontSize: 13, fontWeight: '700', color: '#0f172a' },
  notifItemMsg: { fontSize: 11, color: '#475569', marginTop: 2 },
  notifItemTime: { fontSize: 10, color: '#94a3b8', marginTop: 4 },
  unreadDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: '#10b981',
    marginLeft: 8,
  },
  emptyNotifBox: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 36,
  },
});

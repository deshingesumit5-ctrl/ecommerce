import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Image,
  TextInput,
  RefreshControl,
} from 'react-native';
import { useApp } from '../context/AppContext';
import { Search, Plus, Minus, PackageX } from 'lucide-react-native';

export const CatalogScreen: React.FC<{ initialCategoryId?: number }> = ({ initialCategoryId }) => {
  const { categories, products, cart, addToCart, updateQuantity, isDeliverable, loading, refreshData } = useApp();
  const [selectedCatId, setSelectedCatId] = useState<number | null>(initialCategoryId || null);
  const [search, setSearch] = useState('');

  const filteredProducts = products.filter((p) => {
    const matchCat = selectedCatId ? p.category_id === selectedCatId : true;
    const matchSearch = p.name.toLowerCase().includes(search.toLowerCase()) ||
      (p.description && p.description.toLowerCase().includes(search.toLowerCase()));
    return matchCat && matchSearch;
  });

  const getProductCartQty = (productId: number) => {
    return cart.find((item) => item.product.id === productId)?.quantity || 0;
  };

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Product Catalogue</Text>
      </View>

      {/* Search Input */}
      <View style={styles.searchBox}>
        <Search size={18} color="#64748b" />
        <TextInput
          style={styles.searchInput}
          placeholder="Search products & descriptions..."
          placeholderTextColor="#94a3b8"
          value={search}
          onChangeText={setSearch}
        />
      </View>

      {/* Category Pills */}
      {categories.length > 0 && (
        <View style={{ height: 44, marginBottom: 12 }}>
          <ScrollView horizontal showsHorizontalScrollIndicator={false}>
            <TouchableOpacity
              style={[styles.pill, selectedCatId === null && styles.pillActive]}
              onPress={() => setSelectedCatId(null)}
            >
              <Text style={[styles.pillText, selectedCatId === null && styles.pillTextActive]}>
                All Items
              </Text>
            </TouchableOpacity>
            {categories.map((cat) => (
              <TouchableOpacity
                key={cat.id}
                style={[styles.pill, selectedCatId === cat.id && styles.pillActive]}
                onPress={() => setSelectedCatId(cat.id)}
              >
                <Text
                  style={[
                    styles.pillText,
                    selectedCatId === cat.id && styles.pillTextActive,
                  ]}
                >
                  {cat.name}
                </Text>
              </TouchableOpacity>
            ))}
          </ScrollView>
        </View>
      )}

      {/* Products List */}
      <ScrollView
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
        {filteredProducts.length === 0 ? (
          <View style={styles.noDataBox}>
            <PackageX size={44} color="#94a3b8" />
            <Text style={styles.noDataTitle}>No data available</Text>
            <Text style={styles.noDataSubtitle}>
              {search ? 'No products match your search.' : 'No products available in this category yet.'}
            </Text>
          </View>
        ) : (
          filteredProducts.map((product) => {
            const qty = getProductCartQty(product.id);
            return (
              <View key={product.id} style={styles.productRow}>
                <View style={styles.imgWrapper}>
                  <Image
                    source={{ uri: product.image_url }}
                    style={styles.productImg}
                    resizeMode="contain"
                  />
                </View>
                <View style={styles.productDetails}>
                  <Text style={styles.unitText}>{product.unit}</Text>
                  <Text style={styles.prodName}>{product.name}</Text>
                  {product.description ? (
                    <Text style={styles.prodDesc} numberOfLines={2}>
                      {product.description}
                    </Text>
                  ) : null}
                  <View style={styles.priceRow}>
                    <Text style={styles.priceText}>₹{product.daily_price}</Text>
                    {product.base_price > product.daily_price && (
                      <Text style={styles.basePriceText}>₹{product.base_price}</Text>
                    )}
                    <View style={styles.stockBadge}>
                      <Text style={styles.stockText}>Daily Fresh</Text>
                    </View>
                  </View>
                </View>

                <View style={styles.btnWrapper}>
                  {qty === 0 ? (
                    <TouchableOpacity
                      style={[
                        styles.addBtn,
                        !isDeliverable && { backgroundColor: '#cbd5e1' },
                      ]}
                      disabled={!isDeliverable}
                      onPress={() => addToCart(product)}
                    >
                      <Plus size={14} color="#ffffff" />
                      <Text style={styles.addBtnText}>ADD</Text>
                    </TouchableOpacity>
                  ) : (
                    <View style={styles.qtyContainer}>
                      <TouchableOpacity
                        style={styles.qtyBtn}
                        onPress={() => updateQuantity(product.id, qty - 1)}
                      >
                        <Minus size={13} color="#0f172a" />
                      </TouchableOpacity>
                      <Text style={styles.qtyText}>{qty}</Text>
                      <TouchableOpacity
                        style={styles.qtyBtn}
                        onPress={() => updateQuantity(product.id, qty + 1)}
                      >
                        <Plus size={13} color="#0f172a" />
                      </TouchableOpacity>
                    </View>
                  )}
                </View>
              </View>
            );
          })
        )}
        <View style={{ height: 100 }} />
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f8fafc', paddingHorizontal: 16 },
  header: { paddingTop: 48, paddingBottom: 12 },
  headerTitle: { fontSize: 20, fontWeight: '800', color: '#0f172a' },
  searchBox: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderRadius: 12,
    paddingHorizontal: 12,
    height: 44,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    marginBottom: 10,
  },
  searchInput: { flex: 1, marginLeft: 8, fontSize: 14, color: '#0f172a' },
  pill: {
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 20,
    backgroundColor: '#ffffff',
    borderWidth: 1,
    borderColor: '#e2e8f0',
    marginRight: 8,
    height: 36,
  },
  pillActive: { backgroundColor: '#0f172a', borderColor: '#0f172a' },
  pillText: { fontSize: 12, fontWeight: '700', color: '#475569' },
  pillTextActive: { color: '#ffffff' },
  productRow: {
    flexDirection: 'row',
    backgroundColor: '#ffffff',
    borderRadius: 14,
    padding: 12,
    marginBottom: 10,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#f1f5f9',
  },
  imgWrapper: {
    width: 76,
    height: 76,
    borderRadius: 10,
    backgroundColor: '#f8fafc',
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
  },
  productImg: { width: '100%', height: '100%' },
  productDetails: { flex: 1, marginLeft: 12 },
  unitText: { fontSize: 10, color: '#64748b', fontWeight: '600' },
  prodName: { fontSize: 13, fontWeight: '700', color: '#0f172a', marginTop: 2 },
  prodDesc: { fontSize: 11, color: '#64748b', marginVertical: 2, lineHeight: 14 },
  priceRow: { flexDirection: 'row', alignItems: 'center', marginTop: 4 },
  priceText: { fontSize: 14, fontWeight: '800', color: '#0f172a' },
  basePriceText: { fontSize: 11, color: '#94a3b8', textDecorationLine: 'line-through', marginLeft: 4 },
  stockBadge: { backgroundColor: '#ecfdf5', paddingHorizontal: 6, paddingVertical: 2, borderRadius: 4, marginLeft: 8 },
  stockText: { fontSize: 9, color: '#059669', fontWeight: '700' },
  btnWrapper: { marginLeft: 8 },
  addBtn: {
    backgroundColor: '#10b981',
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 8,
  },
  addBtnText: { color: '#ffffff', fontSize: 11, fontWeight: '800', marginLeft: 2 },
  qtyContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f1f5f9',
    borderRadius: 8,
    padding: 2,
  },
  qtyBtn: { width: 24, height: 24, alignItems: 'center', justifyContent: 'center' },
  qtyText: { fontSize: 12, fontWeight: '800', color: '#0f172a', marginHorizontal: 4 },
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

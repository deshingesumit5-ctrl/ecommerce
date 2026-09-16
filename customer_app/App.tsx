import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, SafeAreaView, StatusBar } from 'react-native';
import { AppProvider, useApp } from './src/context/AppContext';
import { HomeScreen } from './src/screens/HomeScreen';
import { CatalogScreen } from './src/screens/CatalogScreen';
import { CartScreen } from './src/screens/CartScreen';
import { CheckoutScreen } from './src/screens/CheckoutScreen';
import { OrdersScreen } from './src/screens/OrdersScreen';
import { ProfileScreen } from './src/screens/ProfileScreen';
import { LocationPickerModal } from './src/screens/LocationPickerModal';
import { LoginScreen } from './src/screens/LoginScreen';
import { OtpScreen } from './src/screens/OtpScreen';
import { Home, LayoutGrid, ShoppingBag, Package, User } from 'lucide-react-native';

type Tab = 'home' | 'catalog' | 'cart' | 'orders' | 'profile';

const AppContent: React.FC = () => {
  const { isAuthenticated, cart, activeOrder } = useApp();
  const [authScreen, setAuthScreen] = useState<'login' | 'otp'>('login');
  const [otpMobile, setOtpMobile] = useState<string>('');

  const [currentTab, setCurrentTab] = useState<Tab>('home');
  const [selectedCatId, setSelectedCatId] = useState<number | undefined>(undefined);
  const [isCheckout, setIsCheckout] = useState(false);
  const [showLocationModal, setShowLocationModal] = useState(false);

  // If not authenticated, render LoginScreen / OtpScreen
  if (!isAuthenticated) {
    if (authScreen === 'otp') {
      return (
        <OtpScreen
          mobile={otpMobile}
          onBackToLogin={() => setAuthScreen('login')}
          onVerificationSuccess={() => {
            setAuthScreen('login');
            setCurrentTab('home');
          }}
        />
      );
    }

    return (
      <LoginScreen
        onNavigateToOtp={(mobile) => {
          setOtpMobile(mobile);
          setAuthScreen('otp');
        }}
      />
    );
  }

  const cartCount = cart.reduce((sum, item) => sum + item.quantity, 0);

  const navigateToCatalog = (catId?: number) => {
    setSelectedCatId(catId);
    setCurrentTab('catalog');
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <StatusBar barStyle="dark-content" backgroundColor="#f8fafc" />
      <View style={styles.container}>
        {/* Active Screen Content */}
        <View style={styles.screenContainer}>
          {currentTab === 'home' && (
            <HomeScreen
              onOpenLocation={() => setShowLocationModal(true)}
              onNavigateToCart={() => setCurrentTab('cart')}
              onNavigateToCatalog={navigateToCatalog}
              onNavigateToProfile={() => setCurrentTab('profile')}
            />
          )}

          {currentTab === 'catalog' && (
            <CatalogScreen initialCategoryId={selectedCatId} />
          )}

          {currentTab === 'cart' && (
            isCheckout ? (
              <CheckoutScreen
                onBack={() => setIsCheckout(false)}
                onOrderPlaced={() => {
                  setIsCheckout(false);
                  setCurrentTab('orders');
                }}
              />
            ) : (
              <CartScreen
                onNavigateToCheckout={() => setIsCheckout(true)}
                onBrowseCatalog={() => setCurrentTab('catalog')}
                onChangeAddress={() => setShowLocationModal(true)}
              />
            )
          )}

          {currentTab === 'orders' && (
            <OrdersScreen onBrowseCatalog={() => setCurrentTab('catalog')} />
          )}

          {currentTab === 'profile' && (
            <ProfileScreen onOpenLocation={() => setShowLocationModal(true)} />
          )}
        </View>

        {/* Location Picker Modal */}
        <LocationPickerModal
          visible={showLocationModal}
          onClose={() => setShowLocationModal(false)}
        />

        {/* Bottom Tab Bar */}
        {!isCheckout && (
          <View style={styles.tabBar}>
            <TouchableOpacity
              style={styles.tabItem}
              onPress={() => setCurrentTab('home')}
            >
              <Home
                size={22}
                color={currentTab === 'home' ? '#10b981' : '#94a3b8'}
              />
              <Text
                style={[
                  styles.tabLabel,
                  currentTab === 'home' && styles.tabLabelActive,
                ]}
              >
                Home
              </Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.tabItem}
              onPress={() => {
                setSelectedCatId(undefined);
                setCurrentTab('catalog');
              }}
            >
              <LayoutGrid
                size={22}
                color={currentTab === 'catalog' ? '#10b981' : '#94a3b8'}
              />
              <Text
                style={[
                  styles.tabLabel,
                  currentTab === 'catalog' && styles.tabLabelActive,
                ]}
              >
                Catalog
              </Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.tabItem}
              onPress={() => setCurrentTab('cart')}
            >
              <View>
                <ShoppingBag
                  size={22}
                  color={currentTab === 'cart' ? '#10b981' : '#94a3b8'}
                />
                {cartCount > 0 && (
                  <View style={styles.badge}>
                    <Text style={styles.badgeText}>{cartCount}</Text>
                  </View>
                )}
              </View>
              <Text
                style={[
                  styles.tabLabel,
                  currentTab === 'cart' && styles.tabLabelActive,
                ]}
              >
                My Cart
              </Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.tabItem}
              onPress={() => setCurrentTab('orders')}
            >
              <View>
                <Package
                  size={22}
                  color={currentTab === 'orders' ? '#10b981' : '#94a3b8'}
                />
                {activeOrder && activeOrder.order_status !== 'DELIVERED' && (
                  <View style={styles.activeOrderDot} />
                )}
              </View>
              <Text
                style={[
                  styles.tabLabel,
                  currentTab === 'orders' && styles.tabLabelActive,
                ]}
              >
                My Orders
              </Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.tabItem}
              onPress={() => setCurrentTab('profile')}
            >
              <User
                size={22}
                color={currentTab === 'profile' ? '#10b981' : '#94a3b8'}
              />
              <Text
                style={[
                  styles.tabLabel,
                  currentTab === 'profile' && styles.tabLabelActive,
                ]}
              >
                Profile
              </Text>
            </TouchableOpacity>
          </View>
        )}
      </View>
    </SafeAreaView>
  );
};

interface ErrorBoundaryState {
  hasError: boolean;
  error: Error | null;
}

class ErrorBoundary extends React.Component<{ children: React.ReactNode }, ErrorBoundaryState> {
  constructor(props: { children: React.ReactNode }) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error: Error): ErrorBoundaryState {
    return { hasError: true, error };
  }

  componentDidCatch(error: Error, errorInfo: React.ErrorInfo) {
    console.error('App ErrorBoundary caught an error:', error, errorInfo);
  }

  render() {
    if (this.state.hasError) {
      return (
        <SafeAreaView style={{ flex: 1, backgroundColor: '#0f172a', justifyContent: 'center', alignItems: 'center', padding: 24 }}>
          <Text style={{ color: '#ef4444', fontSize: 20, fontWeight: 'bold', marginBottom: 12 }}>Something went wrong</Text>
          <Text style={{ color: '#94a3b8', fontSize: 13, textAlign: 'center', marginBottom: 20 }}>
            {this.state.error?.message || 'App initialization error'}
          </Text>
          <TouchableOpacity
            onPress={() => this.setState({ hasError: false, error: null })}
            style={{ backgroundColor: '#10b981', paddingHorizontal: 20, paddingVertical: 10, borderRadius: 8 }}
          >
            <Text style={{ color: '#ffffff', fontWeight: 'bold' }}>Retry</Text>
          </TouchableOpacity>
        </SafeAreaView>
      );
    }
    return this.props.children;
  }
}

export default function App() {
  return (
    <ErrorBoundary>
      <AppProvider>
        <AppContent />
      </AppProvider>
    </ErrorBoundary>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1, backgroundColor: '#f8fafc' },
  container: { flex: 1 },
  screenContainer: { flex: 1 },
  tabBar: {
    flexDirection: 'row',
    backgroundColor: '#ffffff',
    borderTopWidth: 1,
    borderTopColor: '#e2e8f0',
    paddingTop: 8,
    paddingBottom: 16,
    paddingHorizontal: 8,
    justifyContent: 'space-around',
    elevation: 8,
    shadowColor: '#000',
    shadowOpacity: 0.05,
    shadowRadius: 10,
  },
  tabItem: { alignItems: 'center', justifyContent: 'center', minWidth: 60 },
  tabLabel: { fontSize: 10, color: '#94a3b8', fontWeight: '600', marginTop: 4 },
  tabLabelActive: { color: '#10b981', fontWeight: '800' },
  badge: {
    position: 'absolute',
    top: -4,
    right: -8,
    backgroundColor: '#ef4444',
    borderRadius: 8,
    minWidth: 16,
    height: 16,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 3,
  },
  badgeText: { color: '#ffffff', fontSize: 9, fontWeight: '800' },
  activeOrderDot: {
    position: 'absolute',
    top: -2,
    right: -4,
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: '#10b981',
  },
});

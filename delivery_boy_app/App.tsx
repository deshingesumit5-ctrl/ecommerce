import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, SafeAreaView, StatusBar } from 'react-native';
import { DeliveryAppProvider, useDeliveryApp } from './src/context/DeliveryAppContext';
import { LoginScreen } from './src/screens/LoginScreen';
import { DashboardScreen } from './src/screens/DashboardScreen';
import { OrdersScreen } from './src/screens/OrdersScreen';
import { CODCollectionScreen } from './src/screens/CODCollectionScreen';
import { ProfileScreen } from './src/screens/ProfileScreen';
import { NotificationsModal } from './src/components/NotificationsModal';
import { OrderDetailsModal } from './src/components/OrderDetailsModal';
import { LayoutDashboard, Truck, Banknote, User } from 'lucide-react-native';

type Tab = 'dashboard' | 'orders' | 'cod' | 'profile';

const MainNavigation: React.FC = () => {
  const { isAuthenticated, orders, unreadNotificationCount } = useDeliveryApp();
  const [currentTab, setCurrentTab] = useState<Tab>('dashboard');
  const [showNotifications, setShowNotifications] = useState(false);

  if (!isAuthenticated) {
    return <LoginScreen />;
  }

  const activeOrdersCount = orders.filter((o) => o.delivery_status !== 'DELIVERED').length;

  return (
    <SafeAreaView style={styles.safeArea}>
      <StatusBar barStyle="dark-content" backgroundColor="#f8fafc" />
      <View style={styles.container}>
        {/* Active Screen */}
        <View style={styles.screenContainer}>
          {currentTab === 'dashboard' && (
            <DashboardScreen
              onOpenNotifications={() => setShowNotifications(true)}
              onNavigateToOrders={() => setCurrentTab('orders')}
            />
          )}

          {currentTab === 'orders' && <OrdersScreen />}

          {currentTab === 'cod' && <CODCollectionScreen />}

          {currentTab === 'profile' && <ProfileScreen />}
        </View>

        {/* Notifications Modal */}
        <NotificationsModal
          visible={showNotifications}
          onClose={() => setShowNotifications(false)}
        />

        {/* Order Details Modal (Triggered directly or via Notification click) */}
        <OrderDetailsModal />

        {/* Bottom Tab Bar */}
        <View style={styles.tabBar}>
          <TouchableOpacity
            style={styles.tabItem}
            onPress={() => setCurrentTab('dashboard')}
          >
            <LayoutDashboard
              size={22}
              color={currentTab === 'dashboard' ? '#10b981' : '#94a3b8'}
            />
            <Text
              style={[
                styles.tabLabel,
                currentTab === 'dashboard' && styles.tabLabelActive,
              ]}
            >
              Dashboard
            </Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.tabItem}
            onPress={() => setCurrentTab('orders')}
          >
            <View>
              <Truck
                size={22}
                color={currentTab === 'orders' ? '#10b981' : '#94a3b8'}
              />
              {activeOrdersCount > 0 && (
                <View style={styles.badge}>
                  <Text style={styles.badgeText}>{activeOrdersCount}</Text>
                </View>
              )}
            </View>
            <Text
              style={[
                styles.tabLabel,
                currentTab === 'orders' && styles.tabLabelActive,
              ]}
            >
              Deliveries
            </Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.tabItem}
            onPress={() => setCurrentTab('cod')}
          >
            <Banknote
              size={22}
              color={currentTab === 'cod' ? '#10b981' : '#94a3b8'}
            />
            <Text
              style={[
                styles.tabLabel,
                currentTab === 'cod' && styles.tabLabelActive,
              ]}
            >
              COD Cash
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
      </View>
    </SafeAreaView>
  );
};

export default function App() {
  return (
    <DeliveryAppProvider>
      <MainNavigation />
    </DeliveryAppProvider>
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
  tabItem: { alignItems: 'center', justifyContent: 'center', minWidth: 65 },
  tabLabel: { fontSize: 11, color: '#94a3b8', fontWeight: '600', marginTop: 4 },
  tabLabelActive: { color: '#10b981', fontWeight: '800' },
  badge: {
    position: 'absolute',
    top: -4,
    right: -8,
    backgroundColor: '#3b82f6',
    borderRadius: 8,
    minWidth: 16,
    height: 16,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 3,
  },
  badgeText: { color: '#ffffff', fontSize: 9, fontWeight: '800' },
});

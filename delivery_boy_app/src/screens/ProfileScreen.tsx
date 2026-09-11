import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
} from 'react-native';
import { useDeliveryApp } from '../context/DeliveryAppContext';
import { User, MapPin, Bike, Award, LogOut, CheckCircle2 } from 'lucide-react-native';

export const ProfileScreen: React.FC = () => {
  const { deliveryBoy, logout, orders } = useDeliveryApp();

  const liveDeliveredCount = orders?.filter((o) => o.delivery_status === 'DELIVERED').length || 0;
  const totalDelivered = Math.max(deliveryBoy?.total_completed_orders ?? 0, liveDeliveredCount);

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Delivery Partner Profile</Text>
      </View>

      {/* Partner Info Card */}
      <View style={styles.card}>
        <View style={styles.avatarRow}>
          <View style={styles.avatar}>
            <User size={32} color="#ffffff" />
          </View>
          <View style={{ marginLeft: 14 }}>
            <Text style={styles.userName}>{deliveryBoy?.name || 'Rohan Patil'}</Text>
            {deliveryBoy?.username && (
              <Text style={[styles.userEmail, { color: '#10b981', fontWeight: '700' }]}>@{deliveryBoy.username}</Text>
            )}
            <Text style={styles.userEmail}>{deliveryBoy?.email}</Text>
            <Text style={styles.userMobile}>+91 {deliveryBoy?.mobile}</Text>
          </View>
        </View>

        <View style={styles.badgeRow}>
          <View style={styles.ratingBadge}>
            <Award size={14} color="#f59e0b" />
            <Text style={styles.ratingText}>⭐ {deliveryBoy?.rating || 4.9} Rating</Text>
          </View>
          <View style={styles.tripsBadge}>
            <Text style={styles.tripsText}>
              🏆 {totalDelivered} Orders Delivered
            </Text>
          </View>
        </View>
      </View>

      {/* Assigned Store / Branch info (Access provided from admin_web) */}
      <View style={styles.card}>
        <Text style={styles.sectionTitle}>Store Assignment (From Admin Panel)</Text>
        <View style={styles.infoRow}>
          <MapPin size={16} color="#10b981" />
          <Text style={styles.infoText}>
            Assigned Branch: <Text style={styles.bold}>{deliveryBoy?.assigned_store_name || 'Satara Main Store'}</Text>
          </Text>
        </View>
        <View style={styles.infoRow}>
          <CheckCircle2 size={16} color="#0284c7" />
          <Text style={styles.infoText}>
            Status: <Text style={[styles.bold, { color: '#0284c7' }]}>Active Partner</Text>
          </Text>
        </View>
      </View>

      {/* Vehicle & License Info */}
      <View style={styles.card}>
        <Text style={styles.sectionTitle}>Vehicle & License</Text>
        <View style={styles.infoRow}>
          <Bike size={16} color="#64748b" />
          <Text style={styles.infoText}>
            Vehicle: <Text style={styles.bold}>{deliveryBoy?.vehicle_type}</Text>
          </Text>
        </View>
        <View style={styles.infoRow}>
          <Text style={styles.labelBullet}>•</Text>
          <Text style={styles.infoText}>
            Registration No: <Text style={styles.bold}>{deliveryBoy?.vehicle_number}</Text>
          </Text>
        </View>
        <View style={styles.infoRow}>
          <Text style={styles.labelBullet}>•</Text>
          <Text style={styles.infoText}>
            Driving License: <Text style={styles.bold}>{deliveryBoy?.driving_license}</Text>
          </Text>
        </View>
      </View>

      {/* Logout */}
      <TouchableOpacity style={styles.logoutBtn} onPress={logout}>
        <LogOut size={16} color="#ef4444" />
        <Text style={styles.logoutText}>Log Out</Text>
      </TouchableOpacity>

      <View style={{ height: 100 }} />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f8fafc', paddingHorizontal: 16 },
  header: { paddingTop: 48, paddingBottom: 16 },
  headerTitle: { fontSize: 20, fontWeight: '800', color: '#0f172a' },
  card: { backgroundColor: '#ffffff', borderRadius: 16, padding: 16, marginBottom: 14 },
  avatarRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 14 },
  avatar: { width: 56, height: 56, borderRadius: 28, backgroundColor: '#0f172a', alignItems: 'center', justifyContent: 'center' },
  userName: { fontSize: 17, fontWeight: '800', color: '#0f172a' },
  userEmail: { fontSize: 12, color: '#64748b', marginTop: 1 },
  userMobile: { fontSize: 12, color: '#64748b' },
  badgeRow: { flexDirection: 'row', marginTop: 4 },
  ratingBadge: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#fef3c7', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6, marginRight: 8 },
  ratingText: { fontSize: 11, fontWeight: '800', color: '#92400e', marginLeft: 4 },
  tripsBadge: { backgroundColor: '#ecfdf5', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6 },
  tripsText: { fontSize: 11, fontWeight: '800', color: '#065f46' },
  sectionTitle: { fontSize: 14, fontWeight: '800', color: '#0f172a', marginBottom: 10 },
  infoRow: { flexDirection: 'row', alignItems: 'center', marginVertical: 4 },
  infoText: { fontSize: 13, color: '#475569', marginLeft: 8 },
  labelBullet: { fontSize: 16, color: '#94a3b8', width: 16, textAlign: 'center' },
  bold: { fontWeight: '700', color: '#0f172a' },
  logoutBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#fef2f2',
    borderWidth: 1,
    borderColor: '#fecaca',
    paddingVertical: 12,
    borderRadius: 12,
  },
  logoutText: { color: '#ef4444', fontWeight: '800', fontSize: 13, marginLeft: 6 },
});

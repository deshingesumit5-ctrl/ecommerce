import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  Modal,
  TouchableOpacity,
  ScrollView,
} from 'react-native';
import { useDeliveryApp } from '../context/DeliveryAppContext';
import { Bell, X, CheckCheck, ChevronRight } from 'lucide-react-native';

export const NotificationsModal: React.FC<{
  visible: boolean;
  onClose: () => void;
}> = ({ visible, onClose }) => {
  const { notifications, markNotificationAsRead, openOrderById } = useDeliveryApp();

  const handleNotificationPress = (notifId: string, orderId?: string) => {
    markNotificationAsRead(notifId);
    onClose();
    if (orderId) {
      openOrderById(orderId);
    }
  };

  return (
    <Modal visible={visible} animationType="slide" transparent>
      <View style={styles.overlay}>
        <View style={styles.modalCard}>
          <View style={styles.header}>
            <View style={{ flexDirection: 'row', alignItems: 'center' }}>
              <Bell size={20} color="#10b981" />
              <Text style={styles.title}>Notifications</Text>
            </View>
            <TouchableOpacity onPress={onClose} style={styles.closeBtn}>
              <X size={18} color="#64748b" />
            </TouchableOpacity>
          </View>

          <Text style={styles.subText}>
            Tap a notification to navigate directly to that order or event:
          </Text>

          <ScrollView showsVerticalScrollIndicator={false}>
            {notifications.map((notif) => (
              <TouchableOpacity
                key={notif.id}
                style={[
                  styles.notifItem,
                  !notif.is_read && styles.notifItemUnread,
                ]}
                onPress={() => handleNotificationPress(notif.id, notif.order_id)}
              >
                {!notif.is_read && <View style={styles.unreadDot} />}
                <View style={{ flex: 1, marginLeft: !notif.is_read ? 8 : 0 }}>
                  <View style={styles.itemTopRow}>
                    <Text style={[styles.itemTitle, !notif.is_read && { color: '#0f172a' }]}>
                      {notif.title}
                    </Text>
                    <Text style={styles.itemTime}>{notif.time}</Text>
                  </View>
                  <Text style={styles.itemMessage}>{notif.message}</Text>
                </View>
                <ChevronRight size={16} color="#94a3b8" style={{ marginLeft: 8 }} />
              </TouchableOpacity>
            ))}
          </ScrollView>
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  overlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
  modalCard: { backgroundColor: '#ffffff', borderTopLeftRadius: 20, borderTopRightRadius: 20, padding: 20, maxHeight: '80%' },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 6 },
  title: { fontSize: 18, fontWeight: '800', color: '#0f172a', marginLeft: 8 },
  closeBtn: { width: 32, height: 32, borderRadius: 16, backgroundColor: '#f1f5f9', alignItems: 'center', justifyContent: 'center' },
  subText: { fontSize: 12, color: '#64748b', marginBottom: 16 },
  notifItem: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderWidth: 1,
    borderColor: '#e2e8f0',
    borderRadius: 14,
    padding: 14,
    marginBottom: 10,
  },
  notifItemUnread: {
    backgroundColor: '#f0fdf4',
    borderColor: '#a7f3d0',
  },
  unreadDot: { width: 8, height: 8, borderRadius: 4, backgroundColor: '#ef4444' },
  itemTopRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  itemTitle: { fontSize: 13, fontWeight: '800', color: '#334155' },
  itemTime: { fontSize: 10, color: '#94a3b8' },
  itemMessage: { fontSize: 12, color: '#475569', marginTop: 4, lineHeight: 16 },
});

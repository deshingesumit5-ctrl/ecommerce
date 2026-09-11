import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  Modal,
  TouchableOpacity,
} from 'react-native';
import { useApp } from '../context/AppContext';
import { MapPin, Check, X, ShieldAlert } from 'lucide-react-native';

const PRESET_LOCATIONS = [
  {
    label: 'Powai Naka, Satara (0.8 KM - Within Satara Branch Radius)',
    lat: 17.6850,
    lng: 73.9950,
    storeId: 1,
  },
  {
    label: 'Godoli, Satara (2.1 KM - Within Satara Branch Radius)',
    lat: 17.6700,
    lng: 74.0050,
    storeId: 1,
  },
  {
    label: 'Station Road, Koregaon (0.5 KM - Within Koregaon Branch Radius)',
    lat: 17.6990,
    lng: 74.1780,
    storeId: 2,
  },
  {
    label: 'Wai Outskirts (18.5 KM - Outside Service Radius)',
    lat: 17.9400,
    lng: 73.8900,
    storeId: 1,
  },
];

export const LocationPickerModal: React.FC<{
  visible: boolean;
  onClose: () => void;
}> = ({ visible, onClose }) => {
  const { customerLocation, setCustomerLocationCoords, stores, setSelectedStore } = useApp();

  const handleSelect = (loc: (typeof PRESET_LOCATIONS)[0]) => {
    setCustomerLocationCoords(loc.label, loc.lat, loc.lng);
    const store = stores.find((s) => s.id === loc.storeId) || stores[0];
    setSelectedStore(store);
    onClose();
  };

  return (
    <Modal visible={visible} animationType="slide" transparent>
      <View style={styles.overlay}>
        <View style={styles.modalCard}>
          <View style={styles.modalHeader}>
            <View style={{ flexDirection: 'row', alignItems: 'center' }}>
              <MapPin size={20} color="#10b981" />
              <Text style={styles.modalTitle}>Select Delivery Location</Text>
            </View>
            <TouchableOpacity onPress={onClose} style={styles.closeBtn}>
              <X size={18} color="#64748b" />
            </TouchableOpacity>
          </View>

          <Text style={styles.modalSub}>
            Select or test a hyperlocal delivery location to verify 3 KM radius validation:
          </Text>

          {PRESET_LOCATIONS.map((loc, idx) => {
            const isSelected = customerLocation.label === loc.label;
            const isOut = loc.label.includes('Outside');
            return (
              <TouchableOpacity
                key={idx}
                style={[
                  styles.locOption,
                  isSelected && styles.locSelected,
                  isOut && { borderColor: '#fecaca', backgroundColor: '#fff5f5' },
                ]}
                onPress={() => handleSelect(loc)}
              >
                <View style={{ flex: 1 }}>
                  <Text style={[styles.locName, isSelected && { color: '#0f172a' }]}>
                    {loc.label}
                  </Text>
                  {isOut ? (
                    <View style={styles.warnRow}>
                      <ShieldAlert size={13} color="#ef4444" />
                      <Text style={styles.warnText}>Outside delivery radius (Ordering disabled)</Text>
                    </View>
                  ) : (
                    <Text style={styles.inRadiusText}>Deliverable within 3 KM</Text>
                  )}
                </View>
                {isSelected && <Check size={18} color="#10b981" />}
              </TouchableOpacity>
            );
          })}
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  overlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
  modalCard: { backgroundColor: '#ffffff', borderTopLeftRadius: 20, borderTopRightRadius: 20, padding: 20, maxHeight: '80%' },
  modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
  modalTitle: { fontSize: 16, fontWeight: '800', color: '#0f172a', marginLeft: 8 },
  closeBtn: { width: 32, height: 32, borderRadius: 16, backgroundColor: '#f1f5f9', alignItems: 'center', justifyContent: 'center' },
  modalSub: { fontSize: 12, color: '#64748b', marginBottom: 16 },
  locOption: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 14,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    backgroundColor: '#ffffff',
    marginBottom: 10,
  },
  locSelected: { borderColor: '#10b981', backgroundColor: '#f0fdf4' },
  locName: { fontSize: 13, fontWeight: '700', color: '#334155' },
  inRadiusText: { fontSize: 11, color: '#059669', fontWeight: '600', marginTop: 2 },
  warnRow: { flexDirection: 'row', alignItems: 'center', marginTop: 3 },
  warnText: { fontSize: 11, color: '#dc2626', fontWeight: '600', marginLeft: 4 },
});

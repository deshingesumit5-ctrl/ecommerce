import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  TextInput,
  ActivityIndicator,
  Platform,
} from 'react-native';
import { useApp } from '../context/AppContext';
import { sanitizeMobileInput, isValidMobile } from '../utils/validations';
import {
  User,
  Mail,
  Lock,
  Eye,
  EyeOff,
  MapPin,
  LogOut,
  CheckCircle2,
  AlertCircle,
  Building,
  KeyRound,
  ShieldCheck,
} from 'lucide-react-native';

export const ProfileScreen: React.FC<{ onOpenLocation: () => void }> = ({ onOpenLocation }) => {
  const {
    customer,
    updateCustomerProfile,
    logout,
    customerLocation,
    selectedStore,
    isDeliverable,
    deliveryDistanceKm,
  } = useApp();

  const [name, setName] = useState(customer?.name || '');
  const [mobile, setMobile] = useState(customer?.mobile || '');
  const [email, setEmail] = useState(customer?.email || '');
  const [address, setAddress] = useState(customer?.address || customer?.addresses?.[0]?.address_line || '');
  const [city, setCity] = useState(customer?.city || 'Satara');
  const [pincode, setPincode] = useState(customer?.pincode || '415001');

  // Password edit fields
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showNewPass, setShowNewPass] = useState(false);
  const [showConfirmPass, setShowConfirmPass] = useState(false);

  const [msg, setMsg] = useState('');
  const [isError, setIsError] = useState(false);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (customer) {
      setName(customer.name || '');
      setMobile(customer.mobile || '');
      setEmail(customer.email || '');
      setAddress(customer.address || customer.addresses?.[0]?.address_line || '');
      setCity(customer.city || 'Satara');
      setPincode(customer.pincode || '415001');
    }
  }, [customer]);

  const handleSave = async () => {
    if (!name.trim()) {
      setMsg('Please enter your full name');
      setIsError(true);
      return;
    }
    if (!isValidMobile(mobile)) {
      setMsg('Please enter a valid 10-digit mobile number');
      setIsError(true);
      return;
    }
    if (newPassword) {
      if (newPassword.length < 4) {
        setMsg('New password must be at least 4 characters');
        setIsError(true);
        return;
      }
      if (newPassword !== confirmPassword) {
        setMsg('New password and confirm password do not match');
        setIsError(true);
        return;
      }
    }

    setSaving(true);
    setMsg('');
    setIsError(false);

    const updatePayload: any = {
      name: name.trim(),
      mobile: mobile.replace(/\D/g, ''),
      email: email.trim(),
      address: address.trim(),
      city: city.trim(),
      pincode: pincode.trim(),
    };

    if (newPassword) {
      updatePayload.password = newPassword;
    }

    const result = await updateCustomerProfile(updatePayload);
    setSaving(false);

    if (result.success) {
      setMsg('Profile & credentials updated successfully!');
      setIsError(false);
      setNewPassword('');
      setConfirmPassword('');
      setTimeout(() => setMsg(''), 3500);
    } else {
      setMsg(result.message || 'Failed to update profile');
      setIsError(true);
    }
  };

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>My Profile & Settings</Text>
      </View>

      {/* Customer Info Card */}
      <View style={styles.card}>
        <View style={styles.avatarRow}>
          <View style={styles.avatar}>
            <User size={28} color="#ffffff" />
          </View>
          <View style={{ marginLeft: 12, flex: 1 }}>
            <Text style={styles.userName}>{customer?.name || 'Customer'}</Text>
            <Text style={styles.userPhone}>+91 {customer?.mobile || '9876543210'}</Text>
            {customer?.email ? (
              <Text style={styles.userEmail}>{customer.email}</Text>
            ) : null}
          </View>
        </View>

        {/* Full Name */}
        <View style={styles.formGroup}>
          <Text style={styles.label}>Full Name *</Text>
          <TextInput
            style={styles.input}
            placeholder="Enter Full Name"
            placeholderTextColor="#94a3b8"
            value={name}
            onChangeText={setName}
          />
        </View>

        {/* Email Address */}
        <View style={styles.formGroup}>
          <Text style={styles.label}>Email Address</Text>
          <View style={styles.inputWithIcon}>
            <Mail size={16} color="#64748b" style={{ marginRight: 8 }} />
            <TextInput
              style={styles.innerInput}
              placeholder="Enter Email Address"
              placeholderTextColor="#94a3b8"
              keyboardType="email-address"
              autoCapitalize="none"
              value={email}
              onChangeText={setEmail}
            />
          </View>
        </View>

        {/* Mobile Number */}
        <View style={styles.formGroup}>
          <Text style={styles.label}>Mobile Number (10 Digits) *</Text>
          <View style={styles.phoneInputRow}>
            <Text style={styles.countryCode}>+91</Text>
            <TextInput
              style={styles.phoneInput}
              placeholder="10-digit mobile"
              placeholderTextColor="#94a3b8"
              keyboardType="number-pad"
              maxLength={10}
              value={mobile}
              onChangeText={(t) => setMobile(sanitizeMobileInput(t))}
            />
          </View>
        </View>

        {/* Saved Delivery Address */}
        <View style={styles.formGroup}>
          <Text style={styles.label}>Delivery Address *</Text>
          <TextInput
            style={[styles.input, styles.textArea]}
            placeholder="Flat, Building, Street, Landmark..."
            placeholderTextColor="#94a3b8"
            multiline
            numberOfLines={2}
            value={address}
            onChangeText={setAddress}
          />
        </View>

        {/* City & Pincode */}
        <View style={styles.twoColRow}>
          <View style={[styles.formGroup, { flex: 1, marginRight: 6 }]}>
            <Text style={styles.label}>City *</Text>
            <TextInput
              style={styles.input}
              placeholder="Satara"
              placeholderTextColor="#94a3b8"
              value={city}
              onChangeText={setCity}
            />
          </View>

          <View style={[styles.formGroup, { flex: 1, marginLeft: 6 }]}>
            <Text style={styles.label}>Pincode</Text>
            <TextInput
              style={styles.input}
              placeholder="415001"
              placeholderTextColor="#94a3b8"
              keyboardType="number-pad"
              maxLength={6}
              value={pincode}
              onChangeText={setPincode}
            />
          </View>
        </View>

        {/* Password Update Section */}
        <View style={styles.passwordSection}>
          <View style={styles.passwordSectionHeader}>
            <KeyRound size={15} color="#10b981" />
            <Text style={styles.passwordSectionTitle}>Change Password (Optional)</Text>
          </View>

          {/* New Password */}
          <View style={styles.formGroup}>
            <Text style={styles.label}>New Password</Text>
            <View style={styles.passInputRow}>
              <Lock size={15} color="#64748b" style={{ marginRight: 8 }} />
              <TextInput
                style={styles.passInput}
                placeholder="Leave blank to keep unchanged"
                placeholderTextColor="#94a3b8"
                secureTextEntry={!showNewPass}
                value={newPassword}
                onChangeText={setNewPassword}
              />
              <TouchableOpacity
                onPress={() => setShowNewPass(!showNewPass)}
                style={styles.eyeBtn}
                activeOpacity={0.7}
              >
                {showNewPass ? (
                  <EyeOff size={15} color="#64748b" />
                ) : (
                  <Eye size={15} color="#64748b" />
                )}
              </TouchableOpacity>
            </View>
          </View>

          {/* Confirm New Password */}
          {newPassword ? (
            <View style={styles.formGroup}>
              <Text style={styles.label}>Confirm New Password *</Text>
              <View style={styles.passInputRow}>
                <Lock size={15} color="#64748b" style={{ marginRight: 8 }} />
                <TextInput
                  style={styles.passInput}
                  placeholder="Repeat new password"
                  placeholderTextColor="#94a3b8"
                  secureTextEntry={!showConfirmPass}
                  value={confirmPassword}
                  onChangeText={setConfirmPassword}
                />
                <TouchableOpacity
                  onPress={() => setShowConfirmPass(!showConfirmPass)}
                  style={styles.eyeBtn}
                  activeOpacity={0.7}
                >
                  {showConfirmPass ? (
                    <EyeOff size={15} color="#64748b" />
                  ) : (
                    <Eye size={15} color="#64748b" />
                  )}
                </TouchableOpacity>
              </View>
            </View>
          ) : null}
        </View>

        {/* Alert / Status Message */}
        {msg ? (
          <View style={[styles.msgContainer, isError ? styles.msgContainerErr : styles.msgContainerSuccess]}>
            {isError ? (
              <AlertCircle size={15} color="#dc2626" style={{ marginRight: 6 }} />
            ) : (
              <CheckCircle2 size={15} color="#059669" style={{ marginRight: 6 }} />
            )}
            <Text style={[styles.msgText, isError ? styles.msgErr : styles.msgSuccess]}>
              {msg}
            </Text>
          </View>
        ) : null}

        {/* Save Button */}
        <TouchableOpacity
          style={[styles.saveBtn, saving && { opacity: 0.7 }]}
          onPress={handleSave}
          disabled={saving}
          activeOpacity={0.85}
        >
          {saving ? (
            <ActivityIndicator size="small" color="#ffffff" />
          ) : (
            <Text style={styles.saveBtnText}>Save Profile Changes</Text>
          )}
        </TouchableOpacity>
      </View>

      {/* Hyperlocal Active Store Info */}
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Hyperlocal Service Radius</Text>
        <View style={styles.infoRow}>
          <MapPin size={16} color="#10b981" />
          <Text style={styles.infoText}>
            Selected Location: <Text style={{ fontWeight: '700' }}>{customerLocation.label}</Text>
          </Text>
        </View>
        <View style={styles.infoRow}>
          <CheckCircle2 size={16} color="#0284c7" />
          <Text style={styles.infoText}>
            Assigned Store: <Text style={{ fontWeight: '700' }}>{selectedStore?.name || 'Satara Main Branch'}</Text>
          </Text>
        </View>
        <View style={styles.infoRow}>
          <Text style={styles.distanceBadge}>
            Distance: {deliveryDistanceKm} km ({isDeliverable ? 'Deliverable within 3 KM' : 'Outside 3 KM Radius'})
          </Text>
        </View>

        <TouchableOpacity style={styles.changeLocBtn} onPress={onOpenLocation}>
          <Text style={styles.changeLocText}>Change Location / Test Other Zones</Text>
        </TouchableOpacity>
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
  card: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    marginBottom: 14,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 6,
    elevation: 2,
  },
  avatarRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 16 },
  avatar: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: '#0f172a',
    alignItems: 'center',
    justifyContent: 'center',
  },
  userName: { fontSize: 16, fontWeight: '800', color: '#0f172a' },
  userPhone: { fontSize: 12, color: '#64748b', marginTop: 2 },
  userEmail: { fontSize: 11, color: '#10b981', marginTop: 1, fontWeight: '600' },
  formGroup: { marginBottom: 12 },
  label: { fontSize: 11, color: '#64748b', fontWeight: '700', marginBottom: 5 },
  twoColRow: { flexDirection: 'row' },
  input: {
    backgroundColor: '#f8fafc',
    borderWidth: 1,
    borderColor: '#e2e8f0',
    borderRadius: 10,
    paddingHorizontal: 12,
    height: 42,
    fontSize: 13,
    color: '#0f172a',
    ...Platform.select({
      web: { outlineStyle: 'none' } as any,
    }),
  },
  textArea: {
    height: 58,
    paddingTop: 8,
    textAlignVertical: 'top',
  },
  inputWithIcon: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f8fafc',
    borderWidth: 1,
    borderColor: '#e2e8f0',
    borderRadius: 10,
    paddingHorizontal: 12,
    height: 42,
  },
  innerInput: {
    flex: 1,
    fontSize: 13,
    color: '#0f172a',
    ...Platform.select({
      web: { outlineStyle: 'none' } as any,
    }),
  },
  phoneInputRow: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f8fafc',
    borderWidth: 1,
    borderColor: '#e2e8f0',
    borderRadius: 10,
    paddingHorizontal: 12,
    height: 42,
  },
  countryCode: { fontSize: 13, fontWeight: '700', color: '#64748b', marginRight: 8 },
  phoneInput: {
    flex: 1,
    fontSize: 13,
    color: '#0f172a',
    ...Platform.select({
      web: { outlineStyle: 'none' } as any,
    }),
  },
  passwordSection: {
    backgroundColor: '#f8fafc',
    borderWidth: 1,
    borderColor: '#e2e8f0',
    borderRadius: 12,
    padding: 12,
    marginTop: 4,
    marginBottom: 12,
  },
  passwordSectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 10,
  },
  passwordSectionTitle: {
    fontSize: 12,
    fontWeight: '700',
    color: '#0f172a',
    marginLeft: 6,
  },
  passInputRow: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderWidth: 1,
    borderColor: '#cbd5e1',
    borderRadius: 8,
    paddingHorizontal: 10,
    height: 40,
  },
  passInput: {
    flex: 1,
    fontSize: 12,
    color: '#0f172a',
    ...Platform.select({
      web: { outlineStyle: 'none' } as any,
    }),
  },
  eyeBtn: {
    padding: 4,
  },
  msgContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 10,
    borderRadius: 8,
    marginBottom: 10,
  },
  msgContainerSuccess: {
    backgroundColor: '#ecfdf5',
    borderWidth: 1,
    borderColor: '#a7f3d0',
  },
  msgContainerErr: {
    backgroundColor: '#fef2f2',
    borderWidth: 1,
    borderColor: '#fecaca',
  },
  msgText: { fontSize: 12, fontWeight: '600', flex: 1 },
  msgSuccess: { color: '#065f46' },
  msgErr: { color: '#dc2626' },
  saveBtn: {
    backgroundColor: '#0f172a',
    paddingVertical: 12,
    borderRadius: 10,
    alignItems: 'center',
    marginTop: 4,
  },
  saveBtnText: { color: '#ffffff', fontWeight: '800', fontSize: 13 },
  cardTitle: { fontSize: 14, fontWeight: '800', color: '#0f172a', marginBottom: 10 },
  infoRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 8 },
  infoText: { fontSize: 12, color: '#475569', marginLeft: 8, flex: 1 },
  distanceBadge: {
    fontSize: 11,
    color: '#0284c7',
    fontWeight: '700',
    backgroundColor: '#e0f2fe',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 6,
  },
  changeLocBtn: {
    borderWidth: 1,
    borderColor: '#10b981',
    paddingVertical: 10,
    borderRadius: 10,
    alignItems: 'center',
    marginTop: 8,
  },
  changeLocText: { color: '#10b981', fontSize: 12, fontWeight: '800' },
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

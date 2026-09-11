import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TextInput,
  TouchableOpacity,
  SafeAreaView,
  StatusBar,
  ActivityIndicator,
  Modal,
  ScrollView,
  Platform,
} from 'react-native';
import { useApp } from '../context/AppContext';
import {
  User,
  Lock,
  Eye,
  EyeOff,
  ArrowRight,
  AlertCircle,
  Check,
  ShoppingBag,
  Mail,
  MapPin,
  X,
  Phone,
  Building,
  ChevronDown,
} from 'lucide-react-native';

export const LoginScreen: React.FC<{
  onNavigateToOtp: (mobile: string) => void;
}> = ({ onNavigateToOtp }) => {
  const { loginWithPassword, registerCustomer } = useApp();

  // Login form state
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [rememberMe, setRememberMe] = useState(true);
  const [showPass, setShowPass] = useState(false);
  const [usernameFocused, setUsernameFocused] = useState(false);
  const [passwordFocused, setPasswordFocused] = useState(false);
  const [loginError, setLoginError] = useState('');
  const [submittingLogin, setSubmittingLogin] = useState(false);

  // Register Modal state
  const [showRegisterModal, setShowRegisterModal] = useState(false);
  const [regFullName, setRegFullName] = useState('');
  const [regMobile, setRegMobile] = useState('');
  const [regEmail, setRegEmail] = useState('');
  const [regCity, setRegCity] = useState(''); // blank while adding new customer register
  const [regAddress, setRegAddress] = useState('');
  const [regPincode, setRegPincode] = useState('');
  const [regPassword, setRegPassword] = useState('');
  const [regConfirmPassword, setRegConfirmPassword] = useState('');
  const [showRegPass, setShowRegPass] = useState(false);
  const [showRegConfirmPass, setShowRegConfirmPass] = useState(false);
  const [registerError, setRegisterError] = useState('');
  const [submittingRegister, setSubmittingRegister] = useState(false);

  useEffect(() => {
    setLoginError('');
  }, []);

  const openRegisterModal = () => {
    setRegFullName('');
    setRegMobile('');
    setRegEmail('');
    setRegCity(''); // blank while adding new customer register
    setRegAddress('');
    setRegPincode('');
    setRegPassword('');
    setRegConfirmPassword('');
    setShowRegPass(false);
    setShowRegConfirmPass(false);
    setRegisterError('');
    setShowRegisterModal(true);
  };

  const handleLogin = async () => {
    if (!username.trim() || !password.trim()) {
      setLoginError('Please enter both mobile/email and password');
      return;
    }
    setLoginError('');
    setSubmittingLogin(true);
    const result = await loginWithPassword(username.trim(), password.trim());
    setSubmittingLogin(false);
    if (!result.success) {
      setLoginError(result.message || 'Invalid mobile/email or password');
    }
  };

  const handleRegister = async () => {
    if (!regFullName.trim()) {
      setRegisterError('Customer Full Name is required');
      return;
    }
    const cleanMobile = regMobile.replace(/\D/g, '');
    if (!cleanMobile || cleanMobile.length !== 10) {
      setRegisterError('Please enter a valid 10-digit mobile number');
      return;
    }
    if (!regEmail.trim()) {
      setRegisterError('Email Address is required');
      return;
    }
    if (!regCity.trim()) {
      setRegisterError('City is required');
      return;
    }
    if (!regAddress.trim()) {
      setRegisterError('Saved Delivery Address is required');
      return;
    }
    if (!regPassword.trim()) {
      setRegisterError('Password is required');
      return;
    }
    if (regPassword.length < 4) {
      setRegisterError('Password must be at least 4 characters');
      return;
    }
    if (regPassword !== regConfirmPassword) {
      setRegisterError('Password and Confirm Password do not match');
      return;
    }

    setRegisterError('');
    setSubmittingRegister(true);

    const result = await registerCustomer({
      name: regFullName.trim(),
      mobile: cleanMobile,
      email: regEmail.trim(),
      address: regAddress.trim(),
      city: regCity.trim(),
      pincode: regPincode.trim() || '415001',
      password: regPassword.trim(),
    });

    setSubmittingRegister(false);

    if (result.success) {
      setShowRegisterModal(false);
      // Pre-fill username on login screen
      setUsername(cleanMobile);
      // Navigate to OTP verification flow
      onNavigateToOtp(cleanMobile);
    } else {
      setRegisterError(result.message || 'Failed to register. Please try again.');
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <StatusBar barStyle="light-content" backgroundColor="#020617" />
      <View style={styles.container}>
        <View style={styles.contentWrapper}>
          {/* Main Login Card matching Admin Web Panel style */}
          <View style={styles.card}>
            {/* Header */}
            <View style={styles.headerSection}>
              <View style={styles.iconCircle}>
                <ShoppingBag size={30} color="#10b981" />
              </View>
              <Text style={styles.title}>Ecommerce App</Text>
              <Text style={styles.subtitle}>
                Enter your credentials and start ordering.
              </Text>
            </View>

            {/* Error Alert */}
            {loginError ? (
              <View style={styles.errorContainer}>
                <AlertCircle size={15} color="#f87171" style={{ marginRight: 6 }} />
                <Text style={styles.errorText}>{loginError}</Text>
              </View>
            ) : null}

            {/* Mobile / Email Input */}
            <View style={styles.formGroup}>
              <Text style={styles.label}>Mobile Number or Email</Text>
              <View style={[styles.inputRow, usernameFocused && styles.inputRowFocused]}>
                <View style={styles.iconBox}>
                  <User size={16} color={usernameFocused ? '#10b981' : '#64748b'} />
                </View>
                <TextInput
                  style={styles.input}
                  placeholder="Enter 10-digit mobile or email"
                  placeholderTextColor="#64748b"
                  autoCapitalize="none"
                  autoCorrect={false}
                  autoComplete="off"
                  value={username}
                  onFocus={() => setUsernameFocused(true)}
                  onBlur={() => setUsernameFocused(false)}
                  onChangeText={(val) => {
                    setUsername(val);
                    if (loginError) setLoginError('');
                  }}
                />
              </View>
            </View>

            {/* Password Input */}
            <View style={styles.formGroup}>
              <Text style={styles.label}>Password</Text>
              <View style={[styles.inputRow, passwordFocused && styles.inputRowFocused]}>
                <View style={styles.iconBox}>
                  <Lock size={16} color={passwordFocused ? '#10b981' : '#64748b'} />
                </View>
                <TextInput
                  style={styles.input}
                  placeholder="Enter password"
                  placeholderTextColor="#64748b"
                  secureTextEntry={!showPass}
                  autoComplete="new-password"
                  autoCorrect={false}
                  value={password}
                  onFocus={() => setPasswordFocused(true)}
                  onBlur={() => setPasswordFocused(false)}
                  onChangeText={(val) => {
                    setPassword(val);
                    if (loginError) setLoginError('');
                  }}
                />
                <TouchableOpacity
                  onPress={() => setShowPass(!showPass)}
                  style={styles.eyeBtn}
                  activeOpacity={0.7}
                >
                  {showPass ? (
                    <EyeOff size={16} color="#64748b" />
                  ) : (
                    <Eye size={16} color="#64748b" />
                  )}
                </TouchableOpacity>
              </View>
            </View>

            {/* Remember this device & Forgot pass */}
            <View style={styles.optionsRow}>
              <TouchableOpacity
                style={styles.rememberOption}
                onPress={() => setRememberMe(!rememberMe)}
                activeOpacity={0.7}
              >
                <View style={[styles.checkbox, rememberMe && styles.checkboxActive]}>
                  {rememberMe && <Check size={12} color="#ffffff" strokeWidth={3} />}
                </View>
                <Text style={styles.rememberText}>Remember this device</Text>
              </TouchableOpacity>
            </View>

            {/* Sign In Button */}
            <TouchableOpacity
              style={[styles.loginBtn, submittingLogin && { opacity: 0.7 }]}
              onPress={handleLogin}
              disabled={submittingLogin}
              activeOpacity={0.85}
            >
              {submittingLogin ? (
                <ActivityIndicator size="small" color="#ffffff" style={{ marginRight: 8 }} />
              ) : (
                <>
                  <Text style={styles.loginBtnText}>SIGN IN</Text>
                  <ArrowRight size={16} color="#ffffff" style={{ marginLeft: 8 }} />
                </>
              )}
            </TouchableOpacity>

            {/* Don't have an account? Register link */}
            <View style={styles.registerPromptContainer}>
              <Text style={styles.registerPromptText}>Don't have an account? </Text>
              <TouchableOpacity
                onPress={openRegisterModal}
                activeOpacity={0.7}
              >
                <Text style={styles.registerLinkText}>Register</Text>
              </TouchableOpacity>
            </View>
          </View>

          {/* Footer note */}
          <Text style={styles.footerText}>
            © 2026 Customer App • Hyperlocal Fresh Delivery
          </Text>
        </View>
      </View>

      {/* REGISTER CUSTOMER MODAL - Reference: Admin Web Customer Management Modal */}
      <Modal
        visible={showRegisterModal}
        transparent
        animationType="fade"
        onRequestClose={() => setShowRegisterModal(false)}
      >
        <View style={styles.modalBackdrop}>
          <View style={styles.registerCard}>
            {/* Modal Header */}
            <View style={styles.modalHeader}>
              <View style={styles.modalTitleRow}>
                <View style={styles.registerHeaderIcon}>
                  <User size={18} color="#10b981" />
                </View>
                <Text style={styles.modalTitle}>Register Customer</Text>
              </View>
              <TouchableOpacity
                onPress={() => setShowRegisterModal(false)}
                style={styles.closeBtn}
                activeOpacity={0.7}
              >
                <X size={18} color="#64748b" />
              </TouchableOpacity>
            </View>

            <ScrollView
              showsVerticalScrollIndicator={false}
              style={{ maxHeight: 520 }}
              contentContainerStyle={{ paddingVertical: 14 }}
            >
              {/* Error in modal */}
              {registerError ? (
                <View style={styles.modalErrorContainer}>
                  <AlertCircle size={14} color="#dc2626" style={{ marginRight: 6 }} />
                  <Text style={styles.modalErrorText}>{registerError}</Text>
                </View>
              ) : null}

              {/* Sequence 1: Full Name * & Mobile Number * */}
              <View style={styles.twoColRow}>
                <View style={[styles.modalFormGroup, { flex: 1, marginRight: 8 }]}>
                  <Text style={styles.modalLabel}>
                    Customer Full Name <Text style={styles.requiredStar}>*</Text>
                  </Text>
                  <TextInput
                    style={styles.modalInput}
                    placeholder="Enter name"
                    placeholderTextColor="#94a3b8"
                    value={regFullName}
                    onChangeText={(t) => {
                      setRegFullName(t);
                      if (registerError) setRegisterError('');
                    }}
                  />
                </View>

                <View style={[styles.modalFormGroup, { flex: 1, marginLeft: 8 }]}>
                  <Text style={styles.modalLabel}>
                    Mobile Number <Text style={styles.requiredStar}>*</Text>
                  </Text>
                  <TextInput
                    style={styles.modalInput}
                    placeholder="10-digits number"
                    placeholderTextColor="#94a3b8"
                    keyboardType="number-pad"
                    maxLength={10}
                    value={regMobile}
                    onChangeText={(t) => {
                      setRegMobile(t);
                      if (registerError) setRegisterError('');
                    }}
                  />
                </View>
              </View>

              {/* Sequence 2: Email (add red astric sign) & City (blank while adding new customer register) */}
              <View style={styles.twoColRow}>
                <View style={[styles.modalFormGroup, { flex: 1, marginRight: 8 }]}>
                  <Text style={styles.modalLabel}>
                    Email Address <Text style={styles.requiredStar}>*</Text>
                  </Text>
                  <TextInput
                    style={styles.modalInput}
                    placeholder="customer@example.com"
                    placeholderTextColor="#94a3b8"
                    keyboardType="email-address"
                    autoCapitalize="none"
                    value={regEmail}
                    onChangeText={(t) => {
                      setRegEmail(t);
                      if (registerError) setRegisterError('');
                    }}
                  />
                </View>

                <View style={[styles.modalFormGroup, { flex: 1, marginLeft: 8 }]}>
                  <Text style={styles.modalLabel}>
                    City <Text style={styles.requiredStar}>*</Text>
                  </Text>
                  <TextInput
                    style={styles.modalInput}
                    placeholder="Enter city"
                    placeholderTextColor="#94a3b8"
                    value={regCity}
                    onChangeText={(t) => {
                      setRegCity(t);
                      if (registerError) setRegisterError('');
                    }}
                  />
                </View>
              </View>

              {/* Sequence 3: Saved Delivery Address * & Pincode */}
              <View style={styles.twoColRow}>
                <View style={[styles.modalFormGroup, { flex: 1.4, marginRight: 8 }]}>
                  <Text style={styles.modalLabel}>
                    Saved Delivery Address <Text style={styles.requiredStar}>*</Text>
                  </Text>
                  <TextInput
                    style={styles.modalInput}
                    placeholder="Flat, Building, Street, Landmark..."
                    placeholderTextColor="#94a3b8"
                    value={regAddress}
                    onChangeText={(t) => {
                      setRegAddress(t);
                      if (registerError) setRegisterError('');
                    }}
                  />
                </View>

                <View style={[styles.modalFormGroup, { flex: 0.8, marginLeft: 8 }]}>
                  <Text style={styles.modalLabel}>Pincode</Text>
                  <TextInput
                    style={styles.modalInput}
                    placeholder="415001"
                    placeholderTextColor="#94a3b8"
                    keyboardType="number-pad"
                    maxLength={6}
                    value={regPincode}
                    onChangeText={(t) => {
                      setRegPincode(t);
                      if (registerError) setRegisterError('');
                    }}
                  />
                </View>
              </View>

              {/* Sequence 4: Password (eye icon) & Confirm Password (eye icon) */}
              <View style={styles.twoColRow}>
                <View style={[styles.modalFormGroup, { flex: 1, marginRight: 8 }]}>
                  <Text style={styles.modalLabel}>
                    Password <Text style={styles.requiredStar}>*</Text>
                  </Text>
                  <View style={styles.modalPassInputWrapper}>
                    <TextInput
                      style={styles.modalPassInput}
                      placeholder="Enter password"
                      placeholderTextColor="#94a3b8"
                      secureTextEntry={!showRegPass}
                      autoCapitalize="none"
                      value={regPassword}
                      onChangeText={(t) => {
                        setRegPassword(t);
                        if (registerError) setRegisterError('');
                      }}
                    />
                    <TouchableOpacity
                      style={styles.modalEyeBtn}
                      onPress={() => setShowRegPass(!showRegPass)}
                      activeOpacity={0.7}
                    >
                      {showRegPass ? (
                        <EyeOff size={15} color="#64748b" />
                      ) : (
                        <Eye size={15} color="#64748b" />
                      )}
                    </TouchableOpacity>
                  </View>
                </View>

                <View style={[styles.modalFormGroup, { flex: 1, marginLeft: 8 }]}>
                  <Text style={styles.modalLabel}>
                    Confirm Password <Text style={styles.requiredStar}>*</Text>
                  </Text>
                  <View style={styles.modalPassInputWrapper}>
                    <TextInput
                      style={styles.modalPassInput}
                      placeholder="Confirm password"
                      placeholderTextColor="#94a3b8"
                      secureTextEntry={!showRegConfirmPass}
                      autoCapitalize="none"
                      value={regConfirmPassword}
                      onChangeText={(t) => {
                        setRegConfirmPassword(t);
                        if (registerError) setRegisterError('');
                      }}
                    />
                    <TouchableOpacity
                      style={styles.modalEyeBtn}
                      onPress={() => setShowRegConfirmPass(!showRegConfirmPass)}
                      activeOpacity={0.7}
                    >
                      {showRegConfirmPass ? (
                        <EyeOff size={15} color="#64748b" />
                      ) : (
                        <Eye size={15} color="#64748b" />
                      )}
                    </TouchableOpacity>
                  </View>
                </View>
              </View>
            </ScrollView>

            {/* Modal Buttons: Cancel and Submit */}
            <View style={styles.modalActions}>
              <TouchableOpacity
                style={styles.modalCancelBtn}
                onPress={() => setShowRegisterModal(false)}
                activeOpacity={0.7}
              >
                <Text style={styles.modalCancelBtnText}>Cancel</Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={[styles.modalSaveBtn, submittingRegister && { opacity: 0.7 }]}
                onPress={handleRegister}
                disabled={submittingRegister}
                activeOpacity={0.85}
              >
                {submittingRegister ? (
                  <ActivityIndicator size="small" color="#ffffff" />
                ) : (
                  <Text style={styles.modalSaveBtnText}>Submit</Text>
                )}
              </TouchableOpacity>
            </View>

            {/* Already have an account? Login */}
            <View style={styles.modalLoginRow}>
              <Text style={styles.modalLoginPromptText}>Already have an account? </Text>
              <TouchableOpacity
                onPress={() => setShowRegisterModal(false)}
                activeOpacity={0.7}
              >
                <Text style={styles.modalLoginLink}>Login</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#020617', // slate-950
  },
  container: {
    flex: 1,
    padding: 16,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#0b1120',
  },
  contentWrapper: {
    width: '100%',
    maxWidth: 448, // max-w-md exact admin panel web size
    alignSelf: 'center',
  },
  card: {
    backgroundColor: '#0f172a', // slate-900/90
    borderRadius: 24, // rounded-3xl
    borderWidth: 1,
    borderColor: '#1e293b', // slate-800
    padding: 32,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 16 },
    shadowOpacity: 0.45,
    shadowRadius: 32,
    elevation: 12,
    width: '100%',
  },
  headerSection: {
    marginBottom: 24,
    alignItems: 'center',
  },
  iconCircle: {
    width: 58,
    height: 58,
    borderRadius: 29,
    backgroundColor: 'rgba(16, 185, 129, 0.12)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 16,
    borderWidth: 1,
    borderColor: 'rgba(16, 185, 129, 0.3)',
    shadowColor: '#10b981',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 12,
    elevation: 4,
  },
  title: {
    fontSize: 20,
    fontWeight: '700',
    color: '#ffffff',
    textAlign: 'center',
    letterSpacing: -0.3,
  },
  subtitle: {
    fontSize: 12,
    color: '#94a3b8',
    marginTop: 6,
    textAlign: 'center',
    lineHeight: 18,
  },
  errorContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(239, 68, 68, 0.1)',
    borderWidth: 1,
    borderColor: 'rgba(239, 68, 68, 0.25)',
    borderRadius: 12,
    paddingHorizontal: 12,
    paddingVertical: 10,
    marginBottom: 16,
  },
  errorText: {
    color: '#f87171',
    fontSize: 12,
    fontWeight: '600',
    flex: 1,
  },
  formGroup: {
    marginBottom: 16,
  },
  label: {
    fontSize: 12,
    color: '#cbd5e1',
    fontWeight: '600',
    marginBottom: 6,
  },
  inputRow: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#020617',
    borderWidth: 1,
    borderColor: '#1e293b',
    borderRadius: 12,
    paddingHorizontal: 12,
    height: 46,
  },
  inputRowFocused: {
    borderColor: '#10b981',
    borderWidth: 1.5,
  },
  iconBox: {
    width: 24,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 6,
  },
  input: {
    flex: 1,
    fontSize: 12,
    color: '#ffffff',
    height: '100%',
    ...Platform.select({
      web: { outlineStyle: 'none' } as any,
    }),
  },
  eyeBtn: {
    padding: 6,
    justifyContent: 'center',
    alignItems: 'center',
  },
  optionsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 4,
    marginBottom: 16,
  },
  rememberOption: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  checkbox: {
    width: 16,
    height: 16,
    borderRadius: 4,
    borderWidth: 1,
    borderColor: '#334155',
    backgroundColor: '#020617',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 8,
  },
  checkboxActive: {
    backgroundColor: '#059669',
    borderColor: '#059669',
  },
  rememberText: {
    fontSize: 12,
    color: '#94a3b8',
    fontWeight: '500',
  },
  loginBtn: {
    backgroundColor: '#059669',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    height: 46,
    borderRadius: 12,
    shadowColor: '#059669',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 10,
    elevation: 6,
  },
  loginBtnText: {
    color: '#ffffff',
    fontWeight: '700',
    fontSize: 12,
    letterSpacing: 0.8,
  },
  registerPromptContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 18,
  },
  registerPromptText: {
    fontSize: 12,
    color: '#94a3b8',
  },
  registerLinkText: {
    fontSize: 12,
    color: '#10b981',
    fontWeight: '700',
  },
  footerText: {
    textAlign: 'center',
    fontSize: 11,
    color: '#64748b',
    marginTop: 24,
  },

  /* Register Modal Styles matching Image 2 */
  modalBackdrop: {
    flex: 1,
    backgroundColor: 'rgba(2, 6, 23, 0.75)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 16,
  },
  registerCard: {
    backgroundColor: '#ffffff',
    borderRadius: 20,
    width: '100%',
    maxWidth: 520,
    padding: 24,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 12 },
    shadowOpacity: 0.25,
    shadowRadius: 24,
    elevation: 16,
  },
  modalHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
    paddingBottom: 14,
  },
  modalTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  registerHeaderIcon: {
    marginRight: 8,
  },
  modalTitle: {
    fontSize: 17,
    fontWeight: '700',
    color: '#0f172a',
  },
  closeBtn: {
    padding: 4,
  },
  modalErrorContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#fef2f2',
    borderWidth: 1,
    borderColor: '#fecaca',
    borderRadius: 8,
    paddingHorizontal: 10,
    paddingVertical: 8,
    marginBottom: 12,
  },
  modalErrorText: {
    color: '#dc2626',
    fontSize: 11,
    fontWeight: '600',
    flex: 1,
  },
  modalFormGroup: {
    marginBottom: 12,
  },
  twoColRow: {
    flexDirection: 'row',
  },
  modalLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: '#334155',
    marginBottom: 5,
  },
  requiredStar: {
    color: '#ef4444',
  },
  modalInput: {
    backgroundColor: '#ffffff',
    borderWidth: 1,
    borderColor: '#cbd5e1',
    borderRadius: 8,
    paddingHorizontal: 12,
    height: 40,
    fontSize: 12,
    color: '#0f172a',
    ...Platform.select({
      web: { outlineStyle: 'none' } as any,
    }),
  },
  modalTextArea: {
    height: 65,
    paddingTop: 8,
    textAlignVertical: 'top',
  },
  modalPassInputWrapper: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderWidth: 1,
    borderColor: '#cbd5e1',
    borderRadius: 8,
    paddingHorizontal: 10,
    height: 40,
  },
  modalPassInput: {
    flex: 1,
    height: '100%',
    fontSize: 12,
    color: '#0f172a',
    paddingVertical: 0,
    ...Platform.select({
      web: { outlineStyle: 'none' } as any,
    }),
  },
  modalEyeBtn: {
    padding: 6,
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalActions: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'flex-end',
    borderTopWidth: 1,
    borderTopColor: '#f1f5f9',
    paddingTop: 16,
    marginTop: 8,
  },
  modalCancelBtn: {
    backgroundColor: '#f1f5f9',
    paddingVertical: 10,
    paddingHorizontal: 18,
    borderRadius: 8,
    marginRight: 10,
  },
  modalCancelBtnText: {
    color: '#475569',
    fontSize: 12,
    fontWeight: '600',
  },
  modalSaveBtn: {
    backgroundColor: '#059669',
    paddingVertical: 10,
    paddingHorizontal: 22,
    borderRadius: 8,
    shadowColor: '#059669',
    shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.25,
    shadowRadius: 6,
    elevation: 4,
  },
  modalSaveBtnText: {
    color: '#ffffff',
    fontSize: 12,
    fontWeight: '700',
  },
  modalLoginRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 14,
    paddingTop: 4,
  },
  modalLoginPromptText: {
    fontSize: 12,
    color: '#64748b',
  },
  modalLoginLink: {
    fontSize: 12,
    color: '#10b981',
    fontWeight: '700',
  },
});

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
} from 'react-native';
import { useDeliveryApp } from '../context/DeliveryAppContext';
import { User, Lock, Eye, EyeOff, ArrowRight, AlertCircle, Check, Bike } from 'lucide-react-native';

export const LoginScreen: React.FC = () => {
  const { login } = useDeliveryApp();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [rememberMe, setRememberMe] = useState(false);
  const [showPass, setShowPass] = useState(false);
  const [usernameFocused, setUsernameFocused] = useState(false);
  const [passwordFocused, setPasswordFocused] = useState(false);
  const [error, setError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  // Ensure fields are reset to empty on screen mount
  useEffect(() => {
    setUsername('');
    setPassword('');
    setError('');
  }, []);

  const handleLogin = async () => {
    if (!username.trim() || !password.trim()) {
      setError('Please enter both username and password');
      return;
    }
    setError('');
    setSubmitting(true);
    const result = await login(username.trim(), password.trim());
    setSubmitting(false);
    if (!result.success) {
      setError(result.message || 'Invalid username or password. Contact Admin.');
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <StatusBar barStyle="light-content" backgroundColor="#020617" />
      <View style={styles.container}>
        <View style={styles.contentWrapper}>
          {/* Login Card matching Admin Web Panel */}
          <View style={styles.card}>
            {/* Header */}
            <View style={styles.headerSection}>
              <View style={styles.iconCircle}>
                <Bike size={32} color="#10b981" />
              </View>
              <Text style={styles.title}>Delivery Partner Panel</Text>
              <Text style={styles.subtitle}>
                Enter your credentials to access live management console.
              </Text>
            </View>

            {/* Error Alert */}
            {error ? (
              <View style={styles.errorContainer}>
                <AlertCircle size={15} color="#f87171" style={{ marginRight: 6 }} />
                <Text style={styles.errorText}>{error}</Text>
              </View>
            ) : null}

            {/* Username Input */}
            <View style={styles.formGroup}>
              <Text style={styles.label}>Username</Text>
              <View style={[styles.inputRow, usernameFocused && styles.inputRowFocused]}>
                <View style={styles.iconBox}>
                  <User size={16} color={usernameFocused ? '#10b981' : '#64748b'} />
                </View>
                <TextInput
                  style={styles.input}
                  placeholder="Username"
                  placeholderTextColor="#64748b"
                  autoCapitalize="none"
                  autoCorrect={false}
                  autoComplete="off"
                  textContentType="none"
                  value={username}
                  onFocus={() => setUsernameFocused(true)}
                  onBlur={() => setUsernameFocused(false)}
                  onChangeText={(val) => {
                    setUsername(val);
                    if (error) setError('');
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
                  placeholder="Password"
                  placeholderTextColor="#64748b"
                  secureTextEntry={!showPass}
                  autoComplete="new-password"
                  textContentType="none"
                  autoCorrect={false}
                  value={password}
                  onFocus={() => setPasswordFocused(true)}
                  onBlur={() => setPasswordFocused(false)}
                  onChangeText={(val) => {
                    setPassword(val);
                    if (error) setError('');
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

            {/* Remember this device */}
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
              style={[styles.loginBtn, submitting && { opacity: 0.7 }]}
              onPress={handleLogin}
              disabled={submitting}
              activeOpacity={0.85}
            >
              {submitting ? (
                <ActivityIndicator size="small" color="#ffffff" style={{ marginRight: 8 }} />
              ) : (
                <>
                  <Text style={styles.loginBtnText}>SIGN IN</Text>
                  <ArrowRight size={16} color="#ffffff" style={{ marginLeft: 8 }} />
                </>
              )}
            </TouchableOpacity>
          </View>

          {/* Footer note matching Admin Web Panel */}
          <Text style={styles.footerText}>
            © 2026 Delivery Partner Panel • Hyperlocal System
          </Text>
        </View>
      </View>
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
    backgroundColor: '#0b1120', // deep slate navy gradient base
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
    padding: 32, // p-8
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
    color: '#94a3b8', // slate-400
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
    color: '#cbd5e1', // slate-300
    fontWeight: '600',
    marginBottom: 6,
  },
  inputRow: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#020617', // slate-950
    borderWidth: 1,
    borderColor: '#1e293b', // slate-800
    borderRadius: 12, // rounded-xl
    paddingHorizontal: 12,
    height: 46,
  },
  inputRowFocused: {
    borderColor: '#10b981', // emerald-500
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
    ...({ outlineStyle: 'none', outlineWidth: 0 } as any),
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
    backgroundColor: '#059669', // emerald-600
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    height: 46,
    borderRadius: 12, // rounded-xl
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
  footerText: {
    textAlign: 'center',
    fontSize: 11,
    color: '#64748b', // slate-500
    marginTop: 24,
  },
});


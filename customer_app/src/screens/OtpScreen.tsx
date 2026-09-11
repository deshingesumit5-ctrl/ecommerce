import React, { useState, useEffect, useRef, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  SafeAreaView,
  StatusBar,
  ActivityIndicator,
  TextInput,
  Platform,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useApp } from '../context/AppContext';
import {
  Smartphone,
  CheckCircle2,
  AlertCircle,
  ArrowRight,
  Check,
  ChevronLeft,
  ShieldCheck,
  Delete,
} from 'lucide-react-native';

const OTP_STORAGE_KEY = 'customer_active_otp_session';

interface OtpStorageSession {
  mobile: string;
  step: 1 | 2 | 3;
  targetExpiry: number; // Wall-clock timestamp when 30s resend timer expires
  digits: string[];
}

export const OtpScreen: React.FC<{
  mobile: string;
  initialOtp?: string;
  onBackToLogin: () => void;
  onVerificationSuccess: () => void;
}> = ({ mobile: initialMobile, onBackToLogin, onVerificationSuccess }) => {
  const { verifyOtp, resendOtp, sendOtp } = useApp();

  const cleanInitial = (initialMobile || '').replace(/\D/g, '');

  // Step state: 1 = Enter Mobile Number, 2 = Enter Verification Code, 3 = Successfully
  const [step, setStep] = useState<1 | 2 | 3>(cleanInitial ? 2 : 1);
  const [currentMobile, setCurrentMobile] = useState<string>(cleanInitial);
  const [mobileInputFocused, setMobileInputFocused] = useState(false);
  const [step1Error, setStep1Error] = useState('');
  const [submittingStep1, setSubmittingStep1] = useState(false);

  // Step 2 OTP state
  const [digits, setDigits] = useState<string[]>(['', '', '', '']);
  const [activeBoxIndex, setActiveBoxIndex] = useState(0);
  const [step2Error, setStep2Error] = useState('');
  const [submittingStep2, setSubmittingStep2] = useState(false);
  const [targetExpiry, setTargetExpiry] = useState<number>(Date.now() + 30000);
  const [resendTimer, setResendTimer] = useState<number>(30);
  const [canResend, setCanResend] = useState<boolean>(false);
  const [resending, setResending] = useState(false);
  const [resendMessage, setResendMessage] = useState('');

  const inputRefs = [
    useRef<TextInput>(null),
    useRef<TextInput>(null),
    useRef<TextInput>(null),
    useRef<TextInput>(null),
  ];

  // Helper to persist in-progress OTP session
  const saveSession = useCallback(async (sessionData: OtpStorageSession) => {
    try {
      const json = JSON.stringify(sessionData);
      await AsyncStorage.setItem(OTP_STORAGE_KEY, json);
      if (Platform.OS === 'web' && typeof window !== 'undefined' && window.sessionStorage) {
        window.sessionStorage.setItem(OTP_STORAGE_KEY, json);
      }
    } catch (e) {}
  }, []);

  // Helper to clear saved session
  const clearSession = useCallback(async () => {
    try {
      await AsyncStorage.removeItem(OTP_STORAGE_KEY);
      if (Platform.OS === 'web' && typeof window !== 'undefined' && window.sessionStorage) {
        window.sessionStorage.removeItem(OTP_STORAGE_KEY);
      }
    } catch (e) {}
  }, []);

  // 1. Restore persistent session on mount
  useEffect(() => {
    const restoreSession = async () => {
      try {
        let saved = await AsyncStorage.getItem(OTP_STORAGE_KEY);
        if (!saved && Platform.OS === 'web' && typeof window !== 'undefined' && window.sessionStorage) {
          saved = window.sessionStorage.getItem(OTP_STORAGE_KEY);
        }

        if (saved) {
          const parsed: OtpStorageSession = JSON.parse(saved);
          const activePhone = cleanInitial || parsed.mobile;
          if (activePhone && activePhone === parsed.mobile) {
            setCurrentMobile(parsed.mobile);
            setStep(parsed.step || (parsed.mobile ? 2 : 1));
            if (Array.isArray(parsed.digits)) {
              setDigits(parsed.digits);
            }
            if (parsed.targetExpiry) {
              setTargetExpiry(parsed.targetExpiry);
              const remaining = Math.max(0, Math.ceil((parsed.targetExpiry - Date.now()) / 1000));
              setResendTimer(remaining);
              setCanResend(remaining === 0);
            }
            return;
          }
        }

        // If no prior saved session for this phone and we're starting Step 2
        if (cleanInitial) {
          const newExpiry = Date.now() + 30000;
          setTargetExpiry(newExpiry);
          setResendTimer(30);
          setCanResend(false);
          saveSession({
            mobile: cleanInitial,
            step: 2,
            targetExpiry: newExpiry,
            digits: ['', '', '', ''],
          });
        }
      } catch (e) {}
    };

    restoreSession();
  }, [cleanInitial, saveSession]);

  // 2. Wall-clock based countdown timer (accurately calculates remaining seconds across app switch / background / refocus)
  useEffect(() => {
    if (step !== 2) return;

    const updateTimer = () => {
      const remaining = Math.max(0, Math.ceil((targetExpiry - Date.now()) / 1000));
      setResendTimer(remaining);
      if (remaining === 0) {
        setCanResend(true);
      }
    };

    updateTimer();
    const interval = setInterval(updateTimer, 1000);

    return () => clearInterval(interval);
  }, [step, targetExpiry]);

  // Handle Step 1: Send / Verify Mobile Number
  const handleStep1Submit = async () => {
    const clean = currentMobile.replace(/\D/g, '');
    if (!clean || clean.length !== 10) {
      setStep1Error('Please enter a valid 10-digit mobile number');
      return;
    }
    setStep1Error('');
    setSubmittingStep1(true);

    const result = await sendOtp(clean);
    setSubmittingStep1(false);

    if (result.success) {
      const newExpiry = Date.now() + 30000;
      setDigits(['', '', '', '']);
      setActiveBoxIndex(0);
      setTargetExpiry(newExpiry);
      setResendTimer(30);
      setCanResend(false);
      setStep(2);
      saveSession({
        mobile: clean,
        step: 2,
        targetExpiry: newExpiry,
        digits: ['', '', '', ''],
      });
    } else {
      setStep1Error(result.message || 'Failed to send verification code');
    }
  };

  // Handle Digit Change
  const handleDigitChange = (index: number, val: string) => {
    const cleaned = val.replace(/\D/g, '');
    if (!cleaned) {
      const newDigits = [...digits];
      newDigits[index] = '';
      setDigits(newDigits);
      saveSession({
        mobile: currentMobile || cleanInitial,
        step: 2,
        targetExpiry,
        digits: newDigits,
      });
      return;
    }

    if (cleaned.length > 1) {
      const pasteDigits = cleaned.slice(0, 4).split('');
      const updated = ['', '', '', ''];
      pasteDigits.forEach((d, i) => {
        if (i < 4) updated[i] = d;
      });
      setDigits(updated);
      saveSession({
        mobile: currentMobile || cleanInitial,
        step: 2,
        targetExpiry,
        digits: updated,
      });
      const nextIdx = Math.min(pasteDigits.length, 3);
      inputRefs[nextIdx]?.current?.focus();
      setActiveBoxIndex(nextIdx);
      return;
    }

    const newDigits = [...digits];
    newDigits[index] = cleaned[0];
    setDigits(newDigits);
    saveSession({
      mobile: currentMobile || cleanInitial,
      step: 2,
      targetExpiry,
      digits: newDigits,
    });

    if (step2Error) setStep2Error('');

    if (index < 3) {
      inputRefs[index + 1]?.current?.focus();
      setActiveBoxIndex(index + 1);
    }
  };

  const handleKeyPress = (index: number, e: any) => {
    if (e.nativeEvent.key === 'Backspace') {
      if (digits[index] === '' && index > 0) {
        const newDigits = [...digits];
        newDigits[index - 1] = '';
        setDigits(newDigits);
        saveSession({
          mobile: currentMobile || cleanInitial,
          step: 2,
          targetExpiry,
          digits: newDigits,
        });
        inputRefs[index - 1]?.current?.focus();
        setActiveBoxIndex(index - 1);
      }
    }
  };

  const handleKeypadPress = (num: string) => {
    if (num === 'back') {
      const filledIndex = digits.map((d, i) => (d ? i : -1)).filter((i) => i >= 0);
      if (filledIndex.length > 0) {
        const lastIdx = filledIndex[filledIndex.length - 1];
        const newDigits = [...digits];
        newDigits[lastIdx] = '';
        setDigits(newDigits);
        saveSession({
          mobile: currentMobile || cleanInitial,
          step: 2,
          targetExpiry,
          digits: newDigits,
        });
        setActiveBoxIndex(lastIdx);
      }
      return;
    }

    const firstEmptyIndex = digits.findIndex((d) => d === '');
    if (firstEmptyIndex !== -1) {
      const newDigits = [...digits];
      newDigits[firstEmptyIndex] = num;
      setDigits(newDigits);
      saveSession({
        mobile: currentMobile || cleanInitial,
        step: 2,
        targetExpiry,
        digits: newDigits,
      });
      setActiveBoxIndex(Math.min(firstEmptyIndex + 1, 3));
      if (step2Error) setStep2Error('');
    }
  };

  const handleVerifyOtp = async () => {
    const fullOtp = digits.join('');
    if (fullOtp.length !== 4) {
      setStep2Error('Please enter complete 4-digit verification code');
      return;
    }

    setStep2Error('');
    setSubmittingStep2(true);
    const clean = currentMobile.replace(/\D/g, '') || cleanInitial;
    const result = await verifyOtp(clean, fullOtp);
    setSubmittingStep2(false);

    if (result.success) {
      clearSession();
      setStep(3);
    } else {
      setStep2Error(result.message || 'Invalid verification code. Please check and enter again.');
    }
  };

  // Only requested on explicit user click of "Resend code"
  const handleResend = async () => {
    if (!canResend || resending) return;

    setResending(true);
    setStep2Error('');
    const clean = currentMobile.replace(/\D/g, '') || cleanInitial;
    const result = await resendOtp(clean);
    setResending(false);

    if (result.success) {
      const newExpiry = Date.now() + 30000;
      setDigits(['', '', '', '']);
      setActiveBoxIndex(0);
      setTargetExpiry(newExpiry);
      setResendTimer(30);
      setCanResend(false);
      setResendMessage('New OTP sent to +91 ' + clean);
      saveSession({
        mobile: clean,
        step: 2,
        targetExpiry: newExpiry,
        digits: ['', '', '', ''],
      });
      setTimeout(() => setResendMessage(''), 3000);
    } else {
      setStep2Error(result.message || 'Failed to resend verification code');
    }
  };

  const handleChangeNumber = () => {
    clearSession();
    setDigits(['', '', '', '']);
    setStep2Error('');
    setStep(1);
  };

  // ==========================================
  // PAGE 3: Successfully UI (Registration Complete)
  // ==========================================
  if (step === 3) {
    return (
      <SafeAreaView style={styles.safeArea}>
        <StatusBar barStyle="light-content" backgroundColor="#020617" />
        <View style={styles.container}>
          <View style={styles.contentWrapper}>
            <View style={styles.card}>
              {/* Illustration Section */}
              <View style={styles.successIllustrationSection}>
                <View style={styles.successOuterGlow}>
                  <View style={styles.successInnerCircle}>
                    <Check size={52} color="#ffffff" strokeWidth={3.5} />
                  </View>
                </View>
                {/* Decorative particles */}
                <View style={[styles.particle, { top: 10, left: 40, width: 8, height: 8 }]} />
                <View style={[styles.particle, { top: 30, right: 35, width: 6, height: 6 }]} />
                <View style={[styles.particle, { bottom: 20, left: 30, width: 10, height: 10 }]} />
                <View style={[styles.particle, { bottom: 15, right: 40, width: 7, height: 7 }]} />
              </View>

              <Text style={styles.successTitle}>Successfully</Text>
              <Text style={styles.successSubtitle}>
                Successfully verified. You can login now to access ecommerce app
              </Text>

              {/* Ok Button */}
              <TouchableOpacity
                style={styles.primaryActionBtn}
                onPress={() => {
                  clearSession();
                  onVerificationSuccess();
                }}
                activeOpacity={0.85}
              >
                <Text style={styles.primaryActionBtnText}>Ok</Text>
              </TouchableOpacity>
            </View>

            {/* Footer note */}
            <Text style={styles.footerText}>
              © 2026 Customer App • Hyperlocal Fresh Delivery
            </Text>
          </View>
        </View>
      </SafeAreaView>
    );
  }

  // ==========================================
  // PAGE 1: Enter your Mobile Number
  // ==========================================
  if (step === 1) {
    return (
      <SafeAreaView style={styles.safeArea}>
        <StatusBar barStyle="light-content" backgroundColor="#020617" />
        <View style={styles.container}>
          <View style={styles.contentWrapper}>
            <View style={styles.card}>
              {/* Top Navigation */}
              <TouchableOpacity
                style={styles.backBtn}
                onPress={() => {
                  clearSession();
                  onBackToLogin();
                }}
                activeOpacity={0.7}
              >
                <ChevronLeft size={18} color="#94a3b8" />
                <Text style={styles.backBtnText}>Back to Login</Text>
              </TouchableOpacity>

              {/* Illustration / Graphic */}
              <View style={styles.illustrationSection}>
                <View style={styles.graphicCircle}>
                  <Smartphone size={38} color="#10b981" />
                </View>
                <Text style={styles.title}>Enter your Mobile Number</Text>
                <Text style={styles.subtitle}>
                  Enter your 10-digit mobile number to receive your 4-digit verification code.
                </Text>
              </View>

              {/* Error Alert */}
              {step1Error ? (
                <View style={styles.errorContainer}>
                  <AlertCircle size={14} color="#f87171" style={{ marginRight: 6 }} />
                  <Text style={styles.errorText}>{step1Error}</Text>
                </View>
              ) : null}

              {/* Mobile Number Input with Flag */}
              <View style={styles.formGroup}>
                <Text style={styles.label}>Mobile Number</Text>
                <View style={[styles.phoneInputRow, mobileInputFocused && styles.inputRowFocused]}>
                  <View style={styles.countryCodeBox}>
                    <Text style={styles.flagEmoji}>🇮🇳</Text>
                    <Text style={styles.countryCodeText}>+91</Text>
                  </View>
                  <View style={styles.dividerVertical} />
                  <TextInput
                    style={styles.phoneInput}
                    placeholder="Enter 10-digit mobile"
                    placeholderTextColor="#64748b"
                    keyboardType="number-pad"
                    maxLength={10}
                    value={currentMobile}
                    onFocus={() => setMobileInputFocused(true)}
                    onBlur={() => setMobileInputFocused(false)}
                    onChangeText={(val) => {
                      setCurrentMobile(val.replace(/\D/g, ''));
                      if (step1Error) setStep1Error('');
                    }}
                  />
                </View>
              </View>

              {/* Verify / Send Code Button */}
              <TouchableOpacity
                style={[styles.primaryActionBtn, submittingStep1 && { opacity: 0.7 }]}
                onPress={handleStep1Submit}
                disabled={submittingStep1}
                activeOpacity={0.85}
              >
                {submittingStep1 ? (
                  <ActivityIndicator size="small" color="#ffffff" />
                ) : (
                  <>
                    <Text style={styles.primaryActionBtnText}>Verify</Text>
                    <ArrowRight size={16} color="#ffffff" style={{ marginLeft: 8 }} />
                  </>
                )}
              </TouchableOpacity>

              {/* Back to Login Prompt */}
              <View style={styles.promptRow}>
                <Text style={styles.promptText}>Already registered? </Text>
                <TouchableOpacity
                  onPress={() => {
                    clearSession();
                    onBackToLogin();
                  }}
                  activeOpacity={0.7}
                >
                  <Text style={styles.promptLink}>Sign In</Text>
                </TouchableOpacity>
              </View>
            </View>

            {/* Footer note */}
            <Text style={styles.footerText}>
              © 2026 Customer App • Hyperlocal Fresh Delivery
            </Text>
          </View>
        </View>
      </SafeAreaView>
    );
  }

  // ==========================================
  // PAGE 2: Enter Verification code
  // ==========================================
  return (
    <SafeAreaView style={styles.safeArea}>
      <StatusBar barStyle="light-content" backgroundColor="#020617" />
      <View style={styles.container}>
        <View style={styles.contentWrapper}>
          {/* Verification Card */}
          <View style={styles.card}>
            {/* Top Navigation */}
            <TouchableOpacity
              style={styles.backBtn}
              onPress={handleChangeNumber}
              activeOpacity={0.7}
            >
              <ChevronLeft size={18} color="#94a3b8" />
              <Text style={styles.backBtnText}>Change Number</Text>
            </TouchableOpacity>

            {/* Illustration / Graphic */}
            <View style={styles.illustrationSection}>
              <View style={styles.graphicCircle}>
                <ShieldCheck size={38} color="#10b981" />
              </View>
              <Text style={styles.title}>Enter Verification code</Text>
              <Text style={styles.subtitle}>
                We have sent a 4-digit code to{' '}
                <Text style={{ fontWeight: '700', color: '#cbd5e1' }}>
                  +91 {currentMobile || cleanInitial || 'XXXXXXXXXX'}
                </Text>
              </Text>
            </View>

            {/* Error / Resend Messages */}
            {step2Error ? (
              <View style={styles.errorContainer}>
                <AlertCircle size={14} color="#f87171" style={{ marginRight: 6 }} />
                <Text style={styles.errorText}>{step2Error}</Text>
              </View>
            ) : null}

            {resendMessage ? (
              <View style={styles.successNotice}>
                <CheckCircle2 size={14} color="#34d399" style={{ marginRight: 6 }} />
                <Text style={styles.successNoticeText}>{resendMessage}</Text>
              </View>
            ) : null}

            {/* 4 OTP Digit Boxes */}
            <View style={styles.otpBoxesRow}>
              {digits.map((digit, idx) => (
                <View
                  key={idx}
                  style={[
                    styles.otpBox,
                    activeBoxIndex === idx && styles.otpBoxActive,
                    digit !== '' && styles.otpBoxFilled,
                  ]}
                >
                  <TextInput
                    ref={inputRefs[idx]}
                    style={styles.otpBoxInput}
                    keyboardType="number-pad"
                    maxLength={1}
                    value={digit}
                    onFocus={() => setActiveBoxIndex(idx)}
                    onChangeText={(val) => handleDigitChange(idx, val)}
                    onKeyPress={(e) => handleKeyPress(idx, e)}
                    selectTextOnFocus
                  />
                </View>
              ))}
            </View>

            {/* Resend Row */}
            <View style={styles.resendRow}>
              <Text style={styles.resendPrompt}>Didn't receive code? </Text>
              <TouchableOpacity
                onPress={handleResend}
                disabled={!canResend || resending}
                activeOpacity={0.7}
              >
                <Text
                  style={[
                    styles.resendLink,
                    (!canResend || resending) && styles.resendLinkDisabled,
                  ]}
                >
                  {resending
                    ? 'Resending...'
                    : canResend
                    ? 'Resend code'
                    : `Resend in ${resendTimer}s`}
                </Text>
              </TouchableOpacity>
            </View>

            {/* Verify Button */}
            <TouchableOpacity
              style={[styles.primaryActionBtn, submittingStep2 && { opacity: 0.7 }]}
              onPress={handleVerifyOtp}
              disabled={submittingStep2}
              activeOpacity={0.85}
            >
              {submittingStep2 ? (
                <ActivityIndicator size="small" color="#ffffff" />
              ) : (
                <Text style={styles.primaryActionBtnText}>VERIFY</Text>
              )}
            </TouchableOpacity>

            {/* On-screen Numeric Keypad */}
            <View style={styles.keypad}>
              {[
                ['1', '2', '3'],
                ['4', '5', '6'],
                ['7', '8', '9'],
                ['', '0', 'back'],
              ].map((row, rIdx) => (
                <View key={rIdx} style={styles.keypadRow}>
                  {row.map((btn, bIdx) => {
                    if (btn === '') {
                      return <View key={bIdx} style={styles.keypadKeyEmpty} />;
                    }
                    if (btn === 'back') {
                      return (
                        <TouchableOpacity
                          key={bIdx}
                          style={styles.keypadKey}
                          onPress={() => handleKeypadPress('back')}
                          activeOpacity={0.7}
                        >
                          <Delete size={20} color="#94a3b8" />
                        </TouchableOpacity>
                      );
                    }
                    return (
                      <TouchableOpacity
                        key={bIdx}
                        style={styles.keypadKey}
                        onPress={() => handleKeypadPress(btn)}
                        activeOpacity={0.7}
                      >
                        <Text style={styles.keypadKeyText}>{btn}</Text>
                      </TouchableOpacity>
                    );
                  })}
                </View>
              ))}
            </View>
          </View>

          {/* Footer note */}
          <Text style={styles.footerText}>
            © 2026 Customer App • Hyperlocal Fresh Delivery
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
    backgroundColor: '#0b1120',
  },
  contentWrapper: {
    width: '100%',
    maxWidth: 448, // max-w-md matching Login card and Admin Web
    alignSelf: 'center',
  },
  card: {
    backgroundColor: '#0f172a',
    borderRadius: 24,
    borderWidth: 1,
    borderColor: '#1e293b',
    padding: 28,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 16 },
    shadowOpacity: 0.45,
    shadowRadius: 32,
    elevation: 12,
    width: '100%',
  },

  /* Top Navigation */
  backBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 16,
    alignSelf: 'flex-start',
  },
  backBtnText: {
    color: '#94a3b8',
    fontSize: 12,
    fontWeight: '600',
    marginLeft: 2,
  },

  /* Illustration & Header */
  illustrationSection: {
    alignItems: 'center',
    marginBottom: 20,
  },
  graphicCircle: {
    width: 72,
    height: 72,
    borderRadius: 36,
    backgroundColor: 'rgba(16, 185, 129, 0.1)',
    borderWidth: 1,
    borderColor: 'rgba(16, 185, 129, 0.25)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 14,
    shadowColor: '#10b981',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.25,
    shadowRadius: 16,
    elevation: 6,
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
    paddingHorizontal: 12,
  },

  /* Form & Inputs */
  formGroup: {
    marginBottom: 18,
  },
  label: {
    fontSize: 12,
    color: '#cbd5e1',
    fontWeight: '600',
    marginBottom: 6,
  },
  phoneInputRow: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#020617',
    borderWidth: 1,
    borderColor: '#1e293b',
    borderRadius: 12,
    paddingHorizontal: 10,
    height: 46,
  },
  inputRowFocused: {
    borderColor: '#10b981',
    backgroundColor: '#030a16',
  },
  countryCodeBox: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingRight: 8,
  },
  flagEmoji: {
    fontSize: 16,
    marginRight: 4,
  },
  countryCodeText: {
    fontSize: 13,
    color: '#cbd5e1',
    fontWeight: '700',
  },
  dividerVertical: {
    width: 1,
    height: 20,
    backgroundColor: '#334155',
    marginRight: 10,
  },
  phoneInput: {
    flex: 1,
    fontSize: 14,
    color: '#ffffff',
    fontWeight: '600',
    ...Platform.select({
      web: { outlineStyle: 'none' } as any,
    }),
  },

  /* Alerts */
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
  successNotice: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(16, 185, 129, 0.1)',
    borderWidth: 1,
    borderColor: 'rgba(16, 185, 129, 0.25)',
    borderRadius: 12,
    paddingHorizontal: 12,
    paddingVertical: 9,
    marginBottom: 16,
  },
  successNoticeText: {
    color: '#34d399',
    fontSize: 12,
    fontWeight: '600',
    flex: 1,
  },

  /* OTP Boxes */
  otpBoxesRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 18,
    gap: 10,
  },
  otpBox: {
    flex: 1,
    height: 54,
    backgroundColor: '#020617',
    borderWidth: 1.5,
    borderColor: '#1e293b',
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  otpBoxActive: {
    borderColor: '#10b981',
    backgroundColor: 'rgba(16, 185, 129, 0.06)',
    shadowColor: '#10b981',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 6,
    elevation: 3,
  },
  otpBoxFilled: {
    borderColor: '#34d399',
    backgroundColor: 'rgba(16, 185, 129, 0.1)',
  },
  otpBoxInput: {
    width: '100%',
    height: '100%',
    textAlign: 'center',
    fontSize: 22,
    fontWeight: '800',
    color: '#ffffff',
    ...Platform.select({
      web: { outlineStyle: 'none' } as any,
    }),
  },

  /* Resend Link */
  resendRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 20,
  },
  resendPrompt: {
    fontSize: 12,
    color: '#94a3b8',
  },
  resendLink: {
    fontSize: 12,
    color: '#10b981',
    fontWeight: '700',
  },
  resendLinkDisabled: {
    color: '#64748b',
  },

  /* Action Button */
  primaryActionBtn: {
    backgroundColor: '#059669',
    height: 48,
    borderRadius: 14,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#059669',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.35,
    shadowRadius: 14,
    elevation: 6,
  },
  primaryActionBtnText: {
    color: '#ffffff',
    fontSize: 13,
    fontWeight: '800',
    letterSpacing: 0.5,
  },

  /* Keypad */
  keypad: {
    marginTop: 20,
    borderTopWidth: 1,
    borderTopColor: '#1e293b',
    paddingTop: 16,
  },
  keypadRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 10,
  },
  keypadKey: {
    flex: 1,
    height: 42,
    backgroundColor: 'rgba(255, 255, 255, 0.03)',
    borderRadius: 10,
    marginHorizontal: 4,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: '#1e293b',
  },
  keypadKeyEmpty: {
    flex: 1,
    height: 42,
    marginHorizontal: 4,
  },
  keypadKeyText: {
    fontSize: 18,
    fontWeight: '700',
    color: '#f1f5f9',
  },

  /* Prompts */
  promptRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 18,
  },
  promptText: {
    fontSize: 12,
    color: '#94a3b8',
  },
  promptLink: {
    fontSize: 12,
    color: '#10b981',
    fontWeight: '700',
  },

  /* Success Screen Styles */
  successIllustrationSection: {
    alignItems: 'center',
    justifyContent: 'center',
    marginVertical: 24,
    position: 'relative',
    height: 120,
  },
  successOuterGlow: {
    width: 90,
    height: 90,
    borderRadius: 45,
    backgroundColor: 'rgba(16, 185, 129, 0.15)',
    borderWidth: 2,
    borderColor: 'rgba(16, 185, 129, 0.4)',
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#10b981',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.4,
    shadowRadius: 20,
    elevation: 8,
  },
  successInnerCircle: {
    width: 68,
    height: 68,
    borderRadius: 34,
    backgroundColor: '#059669',
    alignItems: 'center',
    justifyContent: 'center',
  },
  particle: {
    position: 'absolute',
    borderRadius: 999,
    backgroundColor: 'rgba(52, 211, 153, 0.6)',
  },
  successTitle: {
    fontSize: 24,
    fontWeight: '800',
    color: '#ffffff',
    textAlign: 'center',
    letterSpacing: -0.4,
    marginBottom: 8,
  },
  successSubtitle: {
    fontSize: 13,
    color: '#94a3b8',
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: 28,
    paddingHorizontal: 8,
  },

  footerText: {
    textAlign: 'center',
    fontSize: 11,
    color: '#64748b',
    marginTop: 24,
  },
});

/**
 * Validates Indian 10-digit mobile numbers
 */
export function isValidMobile(mobile: string): boolean {
  const cleaned = mobile.replace(/\D/g, '');
  return /^[6-9]\d{9}$/.test(cleaned);
}

/**
 * Format and sanitize mobile number input (Max 10 digits)
 */
export function sanitizeMobileInput(text: string): string {
  return text.replace(/\D/g, '').slice(0, 10);
}

export function isValidEmail(email: string): boolean {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * تنسيق الأرقام بدون كسور عشرية (أرقام عربية عادية)
 */
export const formatNumber = (num) => {
  if (num === null || num === undefined || num === '') return '0';
  const number = typeof num === 'string' ? parseFloat(num) : num;
  if (isNaN(number)) return '0';
  // استخدام 'en-US' للحصول على أرقام عربية عادية (0-9) بدلاً من الأرقام الهندية
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(number);
};

/**
 * تنسيق الأرقام مع كسور عشرية (للأرقام الصغيرة) - أرقام عربية عادية
 */
export const formatNumberWithDecimals = (num, decimals = 2) => {
  if (num === null || num === undefined || num === '') return '0';
  const number = typeof num === 'string' ? parseFloat(num) : num;
  if (isNaN(number)) return '0';
  // استخدام 'en-US' للحصول على أرقام عربية عادية (0-9) بدلاً من الأرقام الهندية
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
  }).format(number);
};


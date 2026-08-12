/**
 * Seed expenses_breakdown from car.note (descriptions only).
 * Amounts come from expenses / expenses_s — never summed from note text.
 *
 * @param {Record<string, any>|null|undefined} formData
 * @param {'purchase'|'sales'} mode
 * @returns {boolean}
 */
export function seedExpenseBreakdownFromLegacy(formData, mode = "purchase") {
  if (!formData) {
    return false;
  }

  if (formData._expenseBreakdownSeeded) {
    return false;
  }

  const existing = formData.expenses_breakdown;
  if (Array.isArray(existing) && existing.length > 0) {
    formData._expenseBreakdownSeeded = true;
    return false;
  }

  const purchaseTotal = Number(formData.expenses) || 0;
  const salesTotalRaw = Number(formData.expenses_s) || 0;
  const salesTotal = salesTotalRaw > 0 ? salesTotalRaw : purchaseTotal;
  const note = String(formData.note || "").trim();

  if (!Array.isArray(formData.expenses_breakdown)) {
    formData.expenses_breakdown = [];
  }

  if (!note) {
    formData._expenseBreakdownSeeded = true;
    return false;
  }

  const lines = note
    .split(/\r\n|\r|\n/)
    .map((line) => line.trim())
    .filter(Boolean);

  if (!lines.length) {
    formData._expenseBreakdownSeeded = true;
    return false;
  }

  // الوصف من الملاحظة فقط — بدون استخراج/جمع مبالغ من النص
  // قيمة المصاريف من حقل expenses / expenses_s على أول بند فقط
  formData.expenses_breakdown = lines.map((line, index) => {
    const isFirst = index === 0;
    return {
      description: line,
      purchase: isFirst ? purchaseTotal : 0,
      sales: isFirst
        ? mode === "sales"
          ? salesTotal
          : salesTotalRaw > 0
            ? salesTotalRaw
            : null
        : mode === "sales"
          ? 0
          : null,
    };
  });

  formData._expenseBreakdownSeeded = true;
  return true;
}

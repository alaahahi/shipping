/**
 * Seed expenses_breakdown from car.note (initial UI only).
 * - Uses note lines as expense descriptions
 * - Parses $ amounts from each line when present
 * - Does NOT invent a generic "مصاريف" line when note is empty
 * - Never clears car.note
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

  // بدون ملاحظة → لا ننشئ بنود وهمية
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

  const items = lines.map((line) => {
    const amount = extractDollarAmount(line);
    return {
      description: line,
      purchase: amount,
      sales: mode === "sales" ? amount : amount > 0 ? amount : null,
    };
  });

  const parsedSum = items.reduce((sum, item) => sum + (Number(item.purchase) || 0), 0);

  // إذا الملاحظة بدون مبالغ مفسَّرة، ضع مجموع المصاريف على أول بند فقط
  if (parsedSum === 0 && (purchaseTotal > 0 || salesTotal > 0)) {
    items[0].purchase = purchaseTotal;
    items[0].sales =
      mode === "sales"
        ? salesTotal
        : salesTotalRaw > 0
          ? salesTotalRaw
          : null;
  }

  formData.expenses_breakdown = items;
  formData._expenseBreakdownSeeded = true;
  return true;
}

/**
 * @param {string} line
 * @returns {number}
 */
function extractDollarAmount(line) {
  const matches = String(line).matchAll(/(\d+(?:\.\d+)?)\s*\$|\$\s*(\d+(?:\.\d+)?)/g);
  let total = 0;
  for (const match of matches) {
    total += Number(match[1] || match[2] || 0);
  }
  return Math.round(total);
}

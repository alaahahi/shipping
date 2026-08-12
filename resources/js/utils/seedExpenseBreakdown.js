/**
 * Seed expenses_breakdown from legacy expenses + note (one first line).
 * Does not overwrite existing breakdown. Safe for old cars.
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

  const purchase = Number(formData.expenses) || 0;
  const salesRaw = Number(formData.expenses_s) || 0;
  const sales = salesRaw > 0 ? salesRaw : purchase;
  const note = String(formData.note || "").trim();

  // فقط إذا في مبلغ مصاريف قديم — الملاحظة وحدها قد تكون عامة
  if (purchase <= 0 && sales <= 0) {
    if (!Array.isArray(formData.expenses_breakdown)) {
      formData.expenses_breakdown = [];
    }
    formData._expenseBreakdownSeeded = true;
    return false;
  }

  const description = note
    ? note.replace(/\r\n|\r|\n/g, " | ")
    : "مصاريف";

  formData.expenses_breakdown = [
    {
      description,
      purchase,
      sales: mode === "sales" ? sales : salesRaw > 0 ? salesRaw : null,
    },
  ];
  formData._expenseBreakdownSeeded = true;

  return true;
}

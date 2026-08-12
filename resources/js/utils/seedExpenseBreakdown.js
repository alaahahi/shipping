/**
 * Seed expenses_breakdown from legacy expenses totals only.
 * Never uses car.note — note stays a general car note.
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

  if (purchase <= 0 && sales <= 0) {
    if (!Array.isArray(formData.expenses_breakdown)) {
      formData.expenses_breakdown = [];
    }
    formData._expenseBreakdownSeeded = true;
    return false;
  }

  formData.expenses_breakdown = [
    {
      description: "مصاريف",
      purchase,
      sales: mode === "sales" ? sales : salesRaw > 0 ? salesRaw : null,
    },
  ];

  formData._expenseBreakdownSeeded = true;

  return true;
}

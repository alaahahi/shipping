<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import axios from 'axios';
import { computed, ref } from 'vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const vinInput = ref('');
const loading = ref(false);
const results = ref([]);
const noResultsVINs = ref([]);

const normalizedInputLines = computed(() =>
  vinInput.value
    .split('\n')
    .map((vin) => vin.trim())
    .filter(Boolean)
);

const totalSearched = computed(() => normalizedInputLines.value.length);
const matchedCount = computed(() => results.value.filter((item) => (item.cars?.length ?? 0) > 0).length);
const exactSingleCount = computed(() => results.value.filter((item) => (item.cars?.length ?? 0) === 1).length);
const ambiguousCount = computed(() => results.value.filter((item) => (item.cars?.length ?? 0) > 1).length);

async function searchVINs() {
  if (!normalizedInputLines.value.length) {
    toast.info('أدخل أرقام الشانصي أولاً', { timeout: 2500, position: 'bottom-right', rtl: true });
    return;
  }

  loading.value = true;
  try {
    const response = await axios.post('/api/search-vins', { vins: normalizedInputLines.value });
    results.value = response.data.results ?? [];
    noResultsVINs.value = response.data.noResultsVINs ?? [];
  } catch (error) {
    console.error('خطأ في البحث:', error);
    toast.error('تعذر تنفيذ البحث', { timeout: 3000, position: 'bottom-right', rtl: true });
  } finally {
    loading.value = false;
  }
}

function getImageUrl(name) {
  return `/public/uploadsResized/${name}`;
}

function getDownloadUrl(name) {
  return `/public/uploads/${name}`;
}

function branchLabel(ownerId) {
  return Number(ownerId) === 1 ? 'أربيل' : 'كركوك';
}

async function copyText(text, successMessage) {
  try {
    await navigator.clipboard.writeText(text);
    toast.success(successMessage, { timeout: 2500, position: 'bottom-right', rtl: true });
  } catch (error) {
    console.error(error);
    toast.error('تعذر النسخ', { timeout: 2500, position: 'bottom-right', rtl: true });
  }
}

function copyMissingVINs() {
  if (!noResultsVINs.value.length) return;
  copyText(noResultsVINs.value.join('\n'), 'تم نسخ الشواصي غير الموجودة');
}

function replaceLineWithApprovedVin(originalVin, approvedVin) {
  const lines = vinInput.value.split('\n');
  let replaced = false;
  const updated = lines.map((line) => {
    if (!replaced && line.trim().toUpperCase() === String(originalVin).trim().toUpperCase()) {
      replaced = true;
      return approvedVin;
    }
    return line;
  });

  vinInput.value = updated.join('\n');
  return replaced;
}

function isExactVinMatch(inputVin, carVin) {
  return String(inputVin ?? '').trim().toUpperCase() === String(carVin ?? '').trim().toUpperCase();
}

async function approveFullVin(resultSet, approvedCar) {
  const approvedVin = String(resultSet?.vin ?? '').trim().toUpperCase();
  if (!approvedVin || !approvedCar?.id) return;

  try {
    await axios.post('/api/approve-searched-vin', {
      car_id: approvedCar.id,
      approved_vin: approvedVin,
    });

    replaceLineWithApprovedVin(resultSet.vin, approvedVin);
    resultSet.approvedVin = approvedVin;
    approvedCar.vin = approvedVin;

    toast.success('تم تحديث الشانصي فعلياً واعتماده', {
      timeout: 2200,
      position: 'bottom-right',
      rtl: true,
    });
  } catch (error) {
    console.error(error);
    const msg = error?.response?.data?.error || 'تعذر تحديث الشانصي';
    toast.error(msg, { timeout: 3000, position: 'bottom-right', rtl: true });
  }
}
</script>

<template>
  <Head title="فحص الشانصي" />

  <AuthenticatedLayout>
    <div class="py-6">
      <div class="w-full max-w-[calc(100vw-2rem)] mx-auto px-2 sm:px-4 lg:px-6">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 shadow-sm overflow-hidden">
          <div class="border-b border-slate-200 dark:border-slate-700 bg-gradient-to-l from-sky-50 to-white dark:from-slate-900 dark:to-slate-950 px-6 py-5">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">فحص الشانصي</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
              الصق الشواصي، شغّل البحث، ثم حدّث الشانصي الفعلي مباشرة على السيارة المختارة.
            </p>
          </div>

          <div class="p-6 space-y-6">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
              <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 shadow-sm dark:shadow-none">
                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">عدد المدخلات</div>
                <div class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ totalSearched }}</div>
              </div>
              <div class="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 p-4 shadow-sm dark:shadow-none">
                <div class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">مطابقات</div>
                <div class="mt-1 text-2xl font-bold text-emerald-800 dark:text-emerald-200">{{ matchedCount }}</div>
              </div>
              <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/40 p-4 shadow-sm dark:shadow-none">
                <div class="text-xs font-semibold text-amber-700 dark:text-amber-300">نتائج متعددة</div>
                <div class="mt-1 text-2xl font-bold text-amber-800 dark:text-amber-200">{{ ambiguousCount }}</div>
              </div>
              <div class="rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/40 p-4 shadow-sm dark:shadow-none">
                <div class="text-xs font-semibold text-rose-700 dark:text-rose-300">غير موجود</div>
                <div class="mt-1 text-2xl font-bold text-rose-800 dark:text-rose-200">{{ noResultsVINs.length }}</div>
              </div>
            </div>

            <div class="grid grid-cols-1 2xl:grid-cols-[440px,minmax(0,1fr)] xl:grid-cols-[400px,minmax(0,1fr)] gap-6 items-start">
              <section class="order-1 xl:order-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 shadow-sm dark:shadow-none">
                <div class="flex items-center justify-between gap-3 mb-3">
                  <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">قائمة الشواصي</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">كل سطر = شانصي واحد</p>
                  </div>
                  <button
                    type="button"
                    class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700 disabled:opacity-60"
                    :disabled="loading"
                    @click="searchVINs"
                  >
                    {{ loading ? 'جاري البحث...' : 'تشغيل البحث' }}
                  </button>
                </div>

                <textarea
                  v-model="vinInput"
                  class="vin-textarea min-h-[520px] w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-950 px-4 py-3 text-sm leading-6 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20"
                  placeholder="ألصق الشواصي هنا، كل رقم في سطر مستقل"
                  spellcheck="false"
                  dir="ltr"
                  autocapitalize="off"
                  autocomplete="off"
                  autocorrect="off"
                />
              </section>

              <section class="order-2 xl:order-1 space-y-4">
                <div
                  v-if="noResultsVINs.length"
                  class="rounded-2xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/20 p-4 shadow-sm dark:shadow-none"
                >
                  <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                    <div>
                      <h2 class="text-base font-bold text-rose-800 dark:text-rose-200">الشواصي غير الموجودة</h2>
                      <p class="text-xs text-rose-700/80 dark:text-rose-300/80">العرض مرقّم، والنسخ بدون ترقيم لسهولة اللصق في الإكسل.</p>
                    </div>
                    <button
                      type="button"
                      class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700"
                      @click="copyMissingVINs"
                    >
                      نسخ الشواصي غير الموجودة
                    </button>
                  </div>

                  <div class="grid sm:grid-cols-2 gap-2">
                    <div
                      v-for="(vin, index) in noResultsVINs"
                      :key="`${vin}-${index}`"
                      class="rounded-lg border border-rose-200 dark:border-rose-800 bg-white/90 dark:bg-slate-950 px-3 py-2 text-sm text-slate-800 dark:text-slate-100"
                    >
                      <span class="font-bold text-rose-700 dark:text-rose-300">{{ index + 1 }}.</span>
                      <span class="font-mono ms-2">{{ vin }}</span>
                    </div>
                  </div>
                </div>

                <div v-if="results.length" class="space-y-4">
                  <div
                    v-for="(resultSet, index) in results"
                    :key="`${resultSet.vin}-${index}`"
                    class="rounded-2xl border shadow-sm overflow-hidden"
                    :class="resultSet.cars?.length > 1
                      ? 'border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/20'
                      : resultSet.cars?.length === 1
                        ? 'border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/20'
                        : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900'"
                  >
                    <div class="px-4 py-3 border-b border-inherit flex flex-wrap items-center justify-between gap-3">
                      <div>
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">بحث {{ index + 1 }}</div>
                        <div class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                          <span class="font-semibold">المدخل:</span>
                          <span class="font-mono">{{ resultSet.vin || 'غير معروف' }}</span>
                        </div>
                        <div v-if="resultSet.approvedVin" class="mt-1 text-xs font-semibold text-sky-700 dark:text-sky-300">
                          تم اعتماد: <span class="font-mono">{{ resultSet.approvedVin }}</span>
                        </div>
                      </div>

                      <span
                        class="rounded-full px-3 py-1 text-xs font-bold"
                        :class="resultSet.cars?.length > 1
                          ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200'
                          : resultSet.cars?.length === 1
                            ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200'
                            : 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200'"
                      >
                        {{
                          resultSet.cars?.length > 1
                            ? `${resultSet.cars.length} نتائج - اختر السيارة الصحيحة`
                            : resultSet.cars?.length === 1
                              ? 'مطابقة واحدة'
                              : 'لا توجد نتيجة'
                        }}
                      </span>
                    </div>

                    <div v-if="resultSet.cars && resultSet.cars.length" class="overflow-x-auto">
                      <table class="min-w-full text-sm text-center text-slate-800 dark:text-slate-100">
                        <thead class="bg-slate-900 text-white dark:bg-slate-800">
                          <tr>
                            <th class="px-3 py-3">الإجراء</th>
                            <th class="px-3 py-3">التاريخ</th>
                            <th class="px-3 py-3">المالك</th>
                            <th class="px-3 py-3">النوع</th>
                            <th class="px-3 py-3">السنة</th>
                            <th class="px-3 py-3">اللون</th>
                            <th class="px-3 py-3">الشانصي الكامل</th>
                            <th class="px-3 py-3">الكاتي</th>
                            <th class="px-3 py-3">الملاحظة</th>
                            <th class="px-3 py-3">الفرع</th>
                            <th class="px-3 py-3">التخزين</th>
                          </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                          <tr
                            v-for="car in resultSet.cars"
                            :key="car.id"
                            class="bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800"
                          >
                            <td class="px-3 py-3">
                              <button
                                v-if="!isExactVinMatch(resultSet.vin, car.vin)"
                                type="button"
                                class="rounded-lg bg-sky-600 px-3 py-2 text-xs font-bold text-white hover:bg-sky-700"
                                @click="approveFullVin(resultSet, car)"
                              >
                                اعتماد وتحديث
                              </button>
                              <span
                                v-else
                                class="inline-flex rounded-lg bg-emerald-100 dark:bg-emerald-900/50 px-3 py-2 text-xs font-bold text-emerald-800 dark:text-emerald-200"
                              >
                                مطابق
                              </span>
                            </td>
                            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">{{ car.date }}</td>
                            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">{{ car.client?.name || '-' }}</td>
                            <td class="px-3 py-3 font-semibold text-slate-800 dark:text-slate-100">{{ car.car_type }}</td>
                            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">{{ car.year || '-' }}</td>
                            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">{{ car.car_color || '-' }}</td>
                            <td class="px-3 py-3 font-mono text-slate-900 dark:text-white">{{ car.vin }}</td>
                            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">{{ car.car_number || '-' }}</td>
                            <td class="px-3 py-3 text-right text-slate-600 dark:text-slate-300 min-w-[240px]">{{ car.note || '-' }}</td>
                            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">{{ branchLabel(car.owner_id) }}</td>
                            <td class="px-3 py-3">
                              <div class="flex items-center justify-center gap-2 flex-wrap">
                                <a
                                  v-for="image in car.car_images || []"
                                  :key="image.id || image.name"
                                  :href="getDownloadUrl(image.name)"
                                  target="_blank"
                                  class="inline-flex"
                                >
                                  <img
                                    :src="getImageUrl(image.name)"
                                    alt="صورة تخزين"
                                    class="h-12 w-20 rounded-md border border-slate-200 dark:border-slate-700 object-cover"
                                  />
                                </a>
                                <span
                                  v-if="!(car.car_images && car.car_images.length)"
                                  class="text-xs text-slate-400 dark:text-slate-500"
                                >
                                  لا يوجد
                                </span>
                              </div>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>

                    <div v-else class="px-4 py-5 text-sm text-rose-700 dark:text-rose-300">
                      لا توجد نتائج لهذا الرقم:
                      <span class="font-mono font-bold">{{ resultSet.vin || 'غير معروف' }}</span>
                    </div>
                  </div>
                </div>

                <div
                  v-else-if="!loading"
                  class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-6 py-16 text-center shadow-sm dark:shadow-none"
                >
                  <h3 class="text-lg font-bold text-slate-900 dark:text-white">لا توجد نتائج بعد</h3>
                  <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    الصق الشواصي في الحقل ثم اضغط تشغيل البحث.
                  </p>
                </div>
              </section>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.vin-textarea,
.vin-textarea:focus,
.vin-textarea:active,
.vin-textarea:hover {
  color: #0f172a !important;
  background: #ffffff !important;
  caret-color: #0f172a !important;
}

.vin-textarea::placeholder {
  color: #94a3b8 !important;
}

:global(.dark) .vin-textarea,
:global(.dark) .vin-textarea:focus,
:global(.dark) .vin-textarea:active,
:global(.dark) .vin-textarea:hover {
  color: #f8fafc !important;
  background: #020617 !important;
  caret-color: #f8fafc !important;
  color-scheme: dark;
}

:global(.dark) .vin-textarea::placeholder {
  color: #64748b !important;
}
</style>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const activeTab = ref('search');
const vinInput = ref('');
const loading = ref(false);
const archiveLoading = ref(false);
const results = ref([]);
const noResultsVINs = ref([]);
const archives = ref([]);
const selectedYear = ref(String(new Date().getFullYear()));

const normalizedInputLines = computed(() =>
  vinInput.value
    .split('\n')
    .map((vin) => vin.trim())
    .filter(Boolean)
);

const totalSearched = computed(() => normalizedInputLines.value.length);
const matchedCount = computed(() => results.value.filter((item) => (item.cars?.length ?? 0) > 0).length);
const ambiguousCount = computed(() => results.value.filter((item) => (item.cars?.length ?? 0) > 1).length);
const currentYear = new Date().getFullYear();
const yearOptions = computed(() => {
  const years = [];
  for (let year = currentYear; year >= 2018; year -= 1) {
    years.push(String(year));
  }
  return years;
});

async function searchVINs() {
  if (!normalizedInputLines.value.length) {
    toast.info('أدخل أرقام الشانصي أولاً', { timeout: 2500, position: 'bottom-right', rtl: true });
    return;
  }

  loading.value = true;
  try {
    const response = await axios.post('/api/search-vins', {
      vins: normalizedInputLines.value,
      year: selectedYear.value,
    });
    results.value = response.data.results ?? [];
    noResultsVINs.value = response.data.noResultsVINs ?? [];
    await loadArchives();
  } catch (error) {
    console.error('خطأ في البحث:', error);
    toast.error('تعذر تنفيذ البحث', { timeout: 3000, position: 'bottom-right', rtl: true });
  } finally {
    loading.value = false;
  }
}

async function loadArchives() {
  archiveLoading.value = true;
  try {
    const response = await axios.get('/api/vin-search-archives');
    archives.value = response.data ?? [];
  } catch (error) {
    console.error(error);
    toast.error('تعذر تحميل الأرشيف', { timeout: 2500, position: 'bottom-right', rtl: true });
  } finally {
    archiveLoading.value = false;
  }
}

function openArchive(archive) {
  vinInput.value = archive.vins_text ?? '';
  results.value = archive.results_payload ?? [];
  noResultsVINs.value = archive.missing_vins ?? [];
  selectedYear.value = archive.search_year ? String(archive.search_year) : 'all';
  activeTab.value = 'search';
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

function setTab(tab) {
  activeTab.value = tab;
  if (tab === 'archive' && !archives.value.length) {
    loadArchives();
  }
}

onMounted(() => {
  loadArchives();
});
</script>

<template>
  <Head title="فحص الشانصي" />

  <AuthenticatedLayout>
    <div class="py-6 min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-black">
      <div class="w-full max-w-[calc(100vw-2rem)] mx-auto px-2 sm:px-4 lg:px-6">
        <div class="rounded-2xl border border-slate-700 bg-gradient-to-br from-slate-950 via-slate-900 to-black shadow-sm overflow-hidden">
          <div class="border-b border-slate-700 bg-gradient-to-l from-slate-900 via-slate-800 to-black px-6 py-5">
            <h1 class="text-2xl font-bold text-white">فحص الشانصي</h1>
            <p class="mt-2 text-sm leading-6 text-slate-300">
              الصق الشواصي، شغّل البحث، ثم حدّث الشانصي الفعلي مباشرة على السيارة المختارة.
            </p>
          </div>

          <div class="p-6 space-y-6">
            <div class="flex flex-wrap items-center gap-3">
              <button
                type="button"
                class="rounded-xl px-4 py-2 text-sm font-bold transition"
                :class="activeTab === 'search'
                  ? 'bg-sky-600 text-white'
                  : 'bg-slate-900 text-slate-300 border border-slate-700 hover:bg-slate-800'"
                @click="setTab('search')"
              >
                بحث جديد
              </button>
              <button
                type="button"
                class="rounded-xl px-4 py-2 text-sm font-bold transition"
                :class="activeTab === 'archive'
                  ? 'bg-violet-600 text-white'
                  : 'bg-slate-900 text-slate-300 border border-slate-700 hover:bg-slate-800'"
                @click="setTab('archive')"
              >
                أرشيف البحث
              </button>
            </div>

            <template v-if="activeTab === 'search'">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
              <div class="rounded-xl border border-slate-700 bg-gradient-to-br from-slate-900 to-slate-950 p-4 shadow-sm">
                <div class="text-xs font-semibold text-slate-400">عدد المدخلات</div>
                <div class="mt-1 text-2xl font-bold text-white">{{ totalSearched }}</div>
              </div>
              <div class="rounded-xl border border-emerald-900 bg-gradient-to-br from-emerald-950/80 to-slate-950 p-4 shadow-sm">
                <div class="text-xs font-semibold text-emerald-300">مطابقات</div>
                <div class="mt-1 text-2xl font-bold text-emerald-200">{{ matchedCount }}</div>
              </div>
              <div class="rounded-xl border border-amber-900 bg-gradient-to-br from-amber-950/80 to-slate-950 p-4 shadow-sm">
                <div class="text-xs font-semibold text-amber-300">نتائج متعددة</div>
                <div class="mt-1 text-2xl font-bold text-amber-200">{{ ambiguousCount }}</div>
              </div>
              <div class="rounded-xl border border-rose-900 bg-gradient-to-br from-rose-950/80 to-slate-950 p-4 shadow-sm">
                <div class="text-xs font-semibold text-rose-300">غير موجود</div>
                <div class="mt-1 text-2xl font-bold text-rose-200">{{ noResultsVINs.length }}</div>
              </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr),400px] 2xl:grid-cols-[minmax(0,1fr),440px] gap-6 items-start">
              <section class="order-2 xl:order-2 rounded-2xl border border-slate-700 bg-gradient-to-br from-slate-900 to-black p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3 mb-3">
                  <div>
                    <h2 class="text-base font-bold text-white">قائمة الشواصي</h2>
                    <p class="text-xs text-slate-400">كل سطر = شانصي واحد</p>
                  </div>
                  <div class="flex items-center gap-2">
                    <select
                      v-model="selectedYear"
                      class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm font-semibold text-slate-100 focus:border-sky-500 focus:outline-none"
                    >
                      <option value="all">كل السنوات</option>
                      <option v-for="year in yearOptions" :key="year" :value="year">{{ year }}</option>
                    </select>
                    <button
                      type="button"
                      class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700 disabled:opacity-60"
                      :disabled="loading"
                      @click="searchVINs"
                    >
                      {{ loading ? 'جاري البحث...' : 'تشغيل البحث' }}
                    </button>
                  </div>
                </div>

                <textarea
                  v-model="vinInput"
                  class="vin-textarea min-h-[520px] w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm leading-6 text-slate-100 placeholder-slate-500 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20"
                  placeholder="ألصق الشواصي هنا، كل رقم في سطر مستقل"
                  spellcheck="false"
                  dir="ltr"
                  autocapitalize="off"
                  autocomplete="off"
                  autocorrect="off"
                />
              </section>

              <section class="order-1 xl:order-1 space-y-4">
                <div
                  v-if="noResultsVINs.length"
                  class="rounded-2xl border border-rose-900 bg-gradient-to-br from-rose-950/50 to-slate-950 p-4 shadow-sm"
                >
                  <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                    <div>
                      <h2 class="text-base font-bold text-rose-200">الشواصي غير الموجودة</h2>
                      <p class="text-xs text-rose-300/80">العرض مرقّم، والنسخ بدون ترقيم لسهولة اللصق في الإكسل.</p>
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
                      class="rounded-lg border border-rose-900 bg-slate-950 px-3 py-2 text-sm text-slate-100"
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
                      ? 'border-amber-900 bg-gradient-to-br from-amber-950/40 to-slate-950'
                      : resultSet.cars?.length === 1
                        ? 'border-emerald-900 bg-gradient-to-br from-emerald-950/40 to-slate-950'
                        : 'border-slate-700 bg-gradient-to-br from-slate-900 to-black'"
                  >
                    <div class="px-4 py-3 border-b border-inherit flex flex-wrap items-center justify-between gap-3">
                      <div>
                        <div class="text-xs font-semibold text-slate-400">بحث {{ index + 1 }}</div>
                        <div class="mt-1 text-sm text-slate-200">
                          <span class="font-semibold">المدخل:</span>
                          <span class="font-mono">{{ resultSet.vin || 'غير معروف' }}</span>
                        </div>
                        <div v-if="resultSet.approvedVin" class="mt-1 text-xs font-semibold text-sky-300">
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
                        <thead class="bg-slate-800 text-white">
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
                        <tbody class="divide-y divide-slate-700">
                          <tr
                            v-for="car in resultSet.cars"
                            :key="car.id"
                            class="bg-slate-950 hover:bg-slate-900"
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
                            <td class="px-3 py-3 text-slate-200">{{ car.date }}</td>
                            <td class="px-3 py-3 text-slate-200">{{ car.client?.name || '-' }}</td>
                            <td class="px-3 py-3 font-semibold text-white">{{ car.car_type }}</td>
                            <td class="px-3 py-3 text-slate-200">{{ car.year || '-' }}</td>
                            <td class="px-3 py-3 text-slate-200">{{ car.car_color || '-' }}</td>
                            <td class="px-3 py-3 font-mono text-white">{{ car.vin }}</td>
                            <td class="px-3 py-3 text-slate-200">{{ car.car_number || '-' }}</td>
                            <td class="px-3 py-3 text-right text-slate-300 min-w-[240px]">{{ car.note || '-' }}</td>
                            <td class="px-3 py-3 text-slate-200">{{ branchLabel(car.owner_id) }}</td>
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
                                    class="h-12 w-20 rounded-md border border-slate-700 object-cover"
                                  />
                                </a>
                                <span
                                  v-if="!(car.car_images && car.car_images.length)"
                                  class="text-xs text-slate-500"
                                >
                                  لا يوجد
                                </span>
                              </div>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>

                    <div v-else class="px-4 py-5 text-sm text-rose-300">
                      لا توجد نتائج لهذا الرقم:
                      <span class="font-mono font-bold">{{ resultSet.vin || 'غير معروف' }}</span>
                    </div>
                  </div>
                </div>

                <div
                  v-else-if="!loading"
                  class="rounded-2xl border border-dashed border-slate-700 bg-gradient-to-br from-slate-900 to-black px-6 py-16 text-center shadow-sm min-h-[220px] flex flex-col items-center justify-center"
                >
                  <h3 class="text-lg font-bold text-white">لا توجد نتائج بعد</h3>
                  <p class="mt-2 text-sm text-slate-400">
                    الصق الشواصي في الحقل ثم اضغط تشغيل البحث.
                  </p>
                </div>
              </section>
            </div>
            </template>

            <section v-else class="space-y-4">
              <div class="rounded-2xl border border-slate-700 bg-gradient-to-br from-slate-900 to-black p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3 mb-4">
                  <div>
                    <h2 class="text-lg font-bold text-white">أرشيف البحث</h2>
                    <p class="text-sm text-slate-400">كل تشغيل للبحث يُحفظ تلقائياً مع نتائجه.</p>
                  </div>
                  <button
                    type="button"
                    class="rounded-lg border border-slate-700 bg-slate-950 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800"
                    @click="loadArchives"
                  >
                    تحديث الأرشيف
                  </button>
                </div>

                <div v-if="archiveLoading" class="rounded-xl border border-slate-700 bg-slate-950 px-4 py-8 text-center text-slate-400">
                  جاري تحميل الأرشيف...
                </div>

                <div v-else-if="archives.length" class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                  <button
                    v-for="archive in archives"
                    :key="archive.id"
                    type="button"
                    class="rounded-2xl border border-slate-700 bg-slate-950 p-4 text-right hover:border-sky-600 hover:bg-slate-900 transition"
                    @click="openArchive(archive)"
                  >
                    <div class="flex items-center justify-between gap-3">
                      <div class="text-sm font-bold text-white">بحث #{{ archive.id }}</div>
                      <div class="text-xs text-slate-400">{{ archive.created_at }}</div>
                    </div>
                    <div class="mt-2 text-xs text-slate-400">
                      السنة:
                      <span class="text-slate-200 font-semibold">{{ archive.search_year || 'الكل' }}</span>
                    </div>
                    <div class="mt-3 grid grid-cols-4 gap-2 text-center">
                      <div class="rounded-lg bg-slate-900 px-2 py-2">
                        <div class="text-[11px] text-slate-400">مدخلات</div>
                        <div class="text-sm font-bold text-white">{{ archive.vins_count }}</div>
                      </div>
                      <div class="rounded-lg bg-emerald-950/50 px-2 py-2">
                        <div class="text-[11px] text-emerald-300">مطابقات</div>
                        <div class="text-sm font-bold text-emerald-200">{{ archive.matched_count }}</div>
                      </div>
                      <div class="rounded-lg bg-amber-950/50 px-2 py-2">
                        <div class="text-[11px] text-amber-300">متعددة</div>
                        <div class="text-sm font-bold text-amber-200">{{ archive.ambiguous_count }}</div>
                      </div>
                      <div class="rounded-lg bg-rose-950/50 px-2 py-2">
                        <div class="text-[11px] text-rose-300">مفقودة</div>
                        <div class="text-sm font-bold text-rose-200">{{ archive.missing_count }}</div>
                      </div>
                    </div>
                    <div class="mt-3 text-xs text-slate-500 line-clamp-2">
                      {{ archive.vins_text }}
                    </div>
                  </button>
                </div>

                <div v-else class="rounded-xl border border-dashed border-slate-700 bg-slate-950 px-4 py-12 text-center text-slate-400">
                  لا يوجد أرشيف بحث بعد.
                </div>
              </div>
            </section>
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
  color: #f8fafc !important;
  background: #020617 !important;
  caret-color: #f8fafc !important;
}

.vin-textarea::placeholder {
  color: #64748b !important;
}
</style>

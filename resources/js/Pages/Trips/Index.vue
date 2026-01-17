<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import { TailwindPagination } from 'laravel-vue-pagination';
import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref, watch, onMounted } from 'vue';
import debounce from 'lodash/debounce';

const toast = useToast();

const laravelData = ref({});
const search = ref('');
const isLoading = ref(false);
const page = ref(1);

const getResults = async (pageNum = 1) => {
  isLoading.value = true;
  try {
    const response = await axios.get(`/getIndexTrips`, {
      params: {
        page: pageNum,
        limit: 15,
        search: search.value,
      },
    });
    laravelData.value = response.data;
  } catch (error) {
    console.error(error);
    toast.error('حدث خطأ أثناء جلب البيانات');
  } finally {
    isLoading.value = false;
  }
};

const debouncedSearch = debounce(() => {
  page.value = 1;
  getResults(1);
}, 500);

watch(search, () => {
  debouncedSearch();
});

onMounted(() => {
  getResults();
});

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('ar-EG');
};

const formatCurrency = (amount, currency = 'dollar') => {
  if (!amount) return '0';
  const formatted = parseFloat(amount).toLocaleString('en-US', { maximumFractionDigits: 0 });
  return formatted + ' ' + (currency === 'dollar' ? '$' : 'دينار');
};
</script>

<template>
  <Head title="الرحلات" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          الرحلات
        </h2>
        <Link
          :href="route('trips.create')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 w-full sm:w-auto justify-center"
        >
          <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          رحلة جديدة
        </Link>
      </div>
    </template>

    <div class="py-6 sm:py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Search Bar -->
        <div class="mb-6">
          <input
            v-model="search"
            type="text"
            placeholder="بحث: اسم السفينة، رقم الرحلة، الموانئ..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
          />
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="mt-2 text-gray-600 dark:text-gray-400">جاري التحميل...</p>
        </div>

        <!-- Trips Grid - Responsive -->
        <div v-else-if="laravelData.data && laravelData.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
          <div
            v-for="trip in laravelData.data"
            :key="trip.id"
            class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 overflow-hidden"
          >
            <Link :href="route('trips.show', trip.id)" class="block p-4 sm:p-6">
              <!-- Header -->
              <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                    {{ trip.ship_name }}
                  </h3>
                  <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ trip.voy_no || 'بدون رقم رحلة' }}
                  </p>
                </div>
                <div class="flex-shrink-0 ml-2">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    {{ trip.total_cars || 0 }} سيارة
                  </span>
                </div>
              </div>

              <!-- Details -->
              <div class="space-y-2 text-sm">
                <div class="flex items-center text-gray-700 dark:text-gray-300">
                  <svg class="w-4 h-4 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span class="truncate">{{ formatDate(trip.sailing_date) }}</span>
                </div>

                <div class="flex items-center text-gray-700 dark:text-gray-300">
                  <svg class="w-4 h-4 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span class="truncate">{{ trip.pol }} → {{ trip.pod }}</span>
                </div>

                <div v-if="trip.captain" class="flex items-center text-gray-700 dark:text-gray-300">
                  <svg class="w-4 h-4 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <span class="truncate">{{ trip.captain }}</span>
                </div>

                <div v-if="trip.total_expenses > 0" class="flex items-center text-gray-700 dark:text-gray-300 pt-2 border-t border-gray-200 dark:border-gray-700">
                  <svg class="w-4 h-4 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="font-medium">{{ formatCurrency(trip.total_expenses, trip.expenses_currency) }}</span>
                </div>
              </div>

              <!-- Stats Footer -->
              <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                <span>{{ trip.total_companies || 0 }} شركة</span>
                <span>{{ trip.total_consignees || 0 }} عميل</span>
              </div>
            </Link>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">لا توجد رحلات</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">ابدأ بإنشاء رحلة جديدة</p>
          <div class="mt-6">
            <Link
              :href="route('trips.create')"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
            >
              <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              رحلة جديدة
            </Link>
          </div>
        </div>

        <!-- Pagination - Hidden on Mobile, Visible on Desktop -->
        <div v-if="laravelData.data && laravelData.data.length > 0" class="mt-6 hidden sm:block">
          <TailwindPagination
            :data="laravelData"
            @pagination-change-page="getResults"
            class="justify-center"
          />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

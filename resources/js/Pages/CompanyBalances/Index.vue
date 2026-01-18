<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  companies: Array,
});

const searchQuery = ref('');

// Filter companies
const filteredCompanies = computed(() => {
  if (!searchQuery.value) return props.companies;
  
  const query = searchQuery.value.toLowerCase();
  return props.companies.filter(company => 
    company.name.toLowerCase().includes(query) ||
    (company.phone && company.phone.includes(query))
  );
});

// Format currency
const formatCurrency = (amount) => {
  if (!amount) return '0';
  return parseFloat(amount).toLocaleString('en-US', { maximumFractionDigits: 0 });
};
</script>

<template>
  <Head title="حسابات الشركات" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        حسابات شركات الشحن
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Search -->
        <div class="mb-6">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="🔍 البحث عن شركة..."
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
          />
        </div>

        <!-- Companies Grid -->
        <div v-if="filteredCompanies.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <Link
            v-for="company in filteredCompanies"
            :key="company.id"
            :href="`/company-balances/${company.id}`"
            class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-xl transition-all duration-200 overflow-hidden border-2 border-transparent hover:border-blue-500"
          >
            <div class="p-6">
              <!-- Company Header -->
              <div class="flex items-start justify-between mb-4">
                <div>
                  <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">
                    {{ company.name }}
                  </h3>
                  <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ company.phone || 'لا يوجد رقم' }}
                  </p>
                </div>
                <div class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100 px-3 py-1 rounded-full text-sm font-semibold">
                  {{ company.total_shipments }} شحنة
                </div>
              </div>

              <!-- Stats -->
              <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                  <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">عدد السيارات</div>
                  <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ company.total_cars }}
                  </div>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg p-3">
                  <div class="text-xs text-green-700 dark:text-green-300 mb-1">الإجمالي</div>
                  <div class="text-xl font-bold text-green-900 dark:text-green-100">
                    {{ formatCurrency(company.total_amount) }} $
                  </div>
                </div>
              </div>

              <!-- View Details Button -->
              <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                <span class="text-blue-600 dark:text-blue-400 font-semibold">عرض التفاصيل</span>
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </div>
            </div>
          </Link>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">لا توجد شركات</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ searchQuery ? 'لم يتم العثور على نتائج للبحث' : 'لم يتم إضافة أي شركات بعد' }}
          </p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

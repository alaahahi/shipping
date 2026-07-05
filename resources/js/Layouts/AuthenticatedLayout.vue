<script setup>
import { ref, computed } from "vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import NavMoreMenu from "@/Components/NavMoreMenu.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link } from "@inertiajs/inertia-vue3";
import { useI18n } from "vue-i18n";
import DarkModeToggle from '@/Components/DarkToggle.vue';
import SyncStatusBadge from '@/Components/SyncStatusBadge.vue';
import OnlineUsersIndicator from '@/Components/OnlineUsersIndicator.vue';

const showingNavigationDropdown = ref(false);
const i18n = useI18n();
const locale = ref("en"); // Default locale
const switchLocale = (locale) => {
  i18n.locale.value = locale;
  localStorage.setItem('lang', locale);
};

const moreMenuActive = computed(() => {
  return [
    'dashboard.statistics',
    'sync.monitor',
    'online_contracts',
    'car_check',
    'damage_report.index',
    'hunter',
    'systemSettings',
    'logViewer',
  ].some((name) => route().current(name));
});
</script>

<template>
  <div>
    <!-- مؤشر حالة المزامنة المختصر -->
    <SyncStatusBadge />
    
    <div class="min-h-screen bg-gray-100 dark:bg-gray-800">
      <nav class="bg-white border-gray-100 dark:bg-gray-900 print:hidden overflow-visible">
        <!-- Primary Navigation Menu -->
        <div class="max-w-8xl mx-auto px-4 sm:px-2  ">
          <div class="flex justify-between h-16 gap-2 overflow-visible">
            <div class="flex min-w-0 flex-1 items-center overflow-visible">
              <!-- Logo -->
              <div class="shrink-0 flex items-center pe-2">
                <Link :href="route('dashboard')" class="flex items-center">
                  <img src="/img/logo.png" alt="Logo" class="h-9 w-auto object-contain" onerror="this.src='/img/logo.jpg'; this.onerror=null;" />
                </Link>
              </div>

              <div class="hidden sm:flex sm:items-center sm:gap-6 sm:ml-6 min-w-0 flex-1 overflow-x-auto nav-scroll">
              <NavLink
                :href="route('dashboard')"
                :active="route().current('dashboard')"
                class="shrink-0"
              >
                {{ $t("home") }}
              </NavLink>
              <NavLink
                v-if="$page.props.auth.user.type_id==1||$page.props.auth.user.type_id==6"
                :href="route('purchases')"
                :active="route().current('purchases')"
                class="shrink-0"
              >
                {{ $t("purchases") }}
              </NavLink>
              <NavLink
                v-if="$page.props.auth.user.type_id==1||$page.props.auth.user.type_id==6"
                :href="route('sales')"
                :active="route().current('sales')"
                class="shrink-0"
              >
                {{ $t("sales") }}
              </NavLink>
              <NavLink
                v-if="$page.props.auth.user.type_id==1||$page.props.auth.user.type_id==6"
                :href="route('clients')"
                :active="route().current('clients')"
                class="shrink-0"
              >
                {{ $t("clients") }}
              </NavLink>
              <NavLink
                v-if="$page.props.auth.user.type_id==1||$page.props.auth.user.type_id==6"
                :href="route('accounting')"
                :active="route().current('accounting')"
                class="shrink-0"
              >
                المحاسبة
              </NavLink>
              <NavLink
                :href="route('annual_information')"
                :active="route().current('annual_information')"
                class="shrink-0"
              >
                معلومات السنوية
              </NavLink>
              <NavLink
                :href="route('car_expenses')"
                :active="route().current('car_expenses')"
                class="shrink-0"
              >
                تسجيل السيارات
              </NavLink>
              <NavMoreMenu :active="moreMenuActive" />

              <NavLink
                v-if="$page.props.auth.user && ($page.props.auth.user.type_id==8||$page.props.auth.user.type_id==10)"
                :href="route('contract')"
                :active="route().current('contract')"
                class="shrink-0"
              >
                {{ $t("newContract") }}
              </NavLink>
              <NavLink
                v-if="$page.props.auth.user && ($page.props.auth.user.type_id==8||$page.props.auth.user.type_id==10)"
                :href="route('car_contract')"
                :active="route().current('car_contract')"
                class="shrink-0"
              >
                {{ $t("SalesContracts") }}
              </NavLink>
              <NavLink
                v-if="$page.props.auth.user && $page.props.auth.user.type_id==8"
                :href="route('company_treasury')"
                :active="route().current('company_treasury')"
                class="shrink-0"
              >
                {{ $t("CompanyTreasury") }}
              </NavLink>
              <NavLink
                v-if="$page.props.auth.user && $page.props.auth.user.type_id==8"
                :href="route('contract_account')"
                :active="route().current('contract_account')"
                class="shrink-0"
              >
                {{ $t("CompanyExpenses") }}
              </NavLink>
              <NavLink
                v-if="$page.props.auth.user && ($page.props.auth.user.type_id==15)"
                :href="route('trips')"
                :active="route().current('trips') || route().current('trips.create') || route().current('trips.show')"
                class="shrink-0"
              >
                🚢 الرحلات
              </NavLink>
              <NavLink
                v-if="$page.props.auth.user && ($page.props.auth.user.type_id==15)"
                :href="route('consigneeBalances.index')"
                :active="route().current('consigneeBalances.index')"
                class="shrink-0"
              >
                💰 أرصدة الزبائن
              </NavLink>
              <NavLink
                v-if="$page.props.auth.user && ($page.props.auth.user.type_id==15)"
                :href="route('companyBalances.index')"
                :active="route().current('companyBalances.index') || route().current('companyBalances.show')"
                class="shrink-0"
              >
                🚢 حسابات الشركات
              </NavLink>
              </div>
              <!-- <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                <NavLink
                  :href="route('dubai')"
                  :active="route().current('dubai')"
                >
                {{ $t("dubai") }}
                </NavLink>
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                <NavLink
                  :href="route('iran')"
                  :active="route().current('iran')"
                >
                {{ $t("iran") }}
                </NavLink>
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                <NavLink
                  :href="route('border')"
                  :active="route().current('border')"
                >
                {{ $t("border") }}
                </NavLink>
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                <NavLink
                  :href="route('coc')"
                  :active="route().current('coc')"
                >
                {{ $t("shipping_coc") }}
                </NavLink>
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                <NavLink
                  :href="route('howler')"
                  :active="route().current('howler')"
                >
                {{ $t("howler") }}

                  
                </NavLink>
              </div> -->
            </div>
            <div class="hidden sm:flex sm:items-center sm:ml-6 gap-6 shrink-0">
              <div class="ml-3 relative">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="dark:bg-gray-800  dark:text-gray-300 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                      >
                        {{ $t("lang") }}
                        <svg
                          class="ml-2 -mr-0.5 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>
                  <template #content>
                    <DropdownLink @click="switchLocale('ar')" as="button">
                      عربي
                    </DropdownLink>
                    <DropdownLink @click="switchLocale('en')" as="button">
                      انكليزي
                    </DropdownLink>
                    <DropdownLink @click="switchLocale('kr')" as="button">
                      كردي
                    </DropdownLink>
                    <!-- <DropdownLink  @click="switchLocale('en')"  as="button">
                                            English
                                        </DropdownLink> -->
                  </template>
                </Dropdown>
              </div>
              <!-- Settings Dropdown -->
              <div class="ml-3 relative">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="dark:bg-gray-800  dark:text-gray-300 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                      >
                        {{ $page.props.auth.user?.name || 'غير مسجل' }}

                        <svg
                          class="ml-2 -mr-0.5 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <DropdownLink
                      :href="route('logout')"
                      method="post"
                      as="button"
                    >
                      {{ $t("logout") }}
                    </DropdownLink>
                  </template>
                </Dropdown>
              </div>
              <OnlineUsersIndicator :user="$page.props.auth?.user" />
              <DarkModeToggle></DarkModeToggle>
            </div>

            <!-- Hamburger + مؤشر المتصلين على الهواتف -->
            <div class="-mr-2 flex items-center gap-2 sm:hidden">
              <OnlineUsersIndicator :user="$page.props.auth?.user" />
              <DarkModeToggle />
              <button
                @click="showingNavigationDropdown = !showingNavigationDropdown"
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500  focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
              >
                <svg
                  class="h-6 w-6"
                  stroke="currentColor"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <path
                    :class="{
                      hidden: showingNavigationDropdown,
                      'inline-flex': !showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                  <path
                    :class="{
                      hidden: !showingNavigationDropdown,
                      'inline-flex': showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div
          :class="{
            block: showingNavigationDropdown,
            hidden: !showingNavigationDropdown,
          }"
          class="sm:hidden"
        >
          <div class="pt-2 pb-3 space-y-1">
            <ResponsiveNavLink
              :href="route('dashboard')"
              :active="route().current('dashboard')"
            >
              {{ $t("dashboard") }}
            </ResponsiveNavLink>
          </div>

          <!-- Responsive Settings Options -->
          <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
              <div class="font-medium text-base text-gray-800">
                {{ $page.props.auth.user?.name || 'غير مسجل' }}
              </div>
              <div class="font-medium text-sm text-gray-500">
                {{ $page.props.auth.user?.email || '' }}
              </div>
            </div>
            <div class="mt-3 space-y-1">
              <ResponsiveNavLink
                :href="route('dashboard')"
                :active="route().current('dashboard')"
              >
                {{ $t("home") }}
              </ResponsiveNavLink>

              <ResponsiveNavLink
                :href="route('purchases')"
                :active="route().current('purchases')"
                v-if="$page.props.auth.user && $page.props.auth.user.type_id == 1"
              >
              {{ $t("purchases") }}
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('sales')"
                :active="route().current('sales')"
                v-if="$page.props.auth.user && $page.props.auth.user.type_id == 1"
              >
              {{ $t("sales") }}
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('accounting')"
                :active="route().current('accounting')"
                v-if="$page.props.auth.user && ($page.props.auth.user.type_id == 1||$page.props.auth.user.type_id==6)"
              >
              المحاسبة  
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('trips')"
                :active="route().current('trips') || route().current('trips.create') || route().current('trips.show')"
                v-if="$page.props.auth.user && ($page.props.auth.user.type_id==15)"
              >
              🚢 الرحلات
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('consigneeBalances.index')"
                :active="route().current('consigneeBalances.index')"
                v-if="$page.props.auth.user && ($page.props.auth.user.type_id==15)"
              >
              💰 أرصدة الزبائن
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('companyBalances.index')"
                :active="route().current('companyBalances.index') || route().current('companyBalances.show')"
                v-if="$page.props.auth.user && ($page.props.auth.user.type_id==15)"
              >
              🚢 حسابات الشركات
              </ResponsiveNavLink>
              
              <ResponsiveNavLink
                :href="route('annual_information')"
                :active="route().current('annual_information')"
              >
              معلومات السنوية
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('car_expenses')"
                :active="route().current('car_expenses')"
              >
              تسجيل السيارات
              </ResponsiveNavLink>

              <!-- المزيد — قائمة الهاتف -->
              <div
                class="px-4 pt-2 pb-1 text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500"
              >
                المزيد
              </div>
              <ResponsiveNavLink
                :href="route('dashboard.statistics')"
                :active="route().current('dashboard.statistics')"
                v-if="$page.props.auth.user.type_id!=8 && $page.props.auth.user.type_id!=10"
              >
              احصائات
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('sync.monitor')"
                :active="route().current('sync.monitor')"
                v-if="$page.props.auth.user.type_id!=8 && $page.props.auth.user.type_id!=10"
              >
              🔄 المزامنة
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('car_check')"
                :active="route().current('car_check')"
                v-if="$page.props.auth.user.type_id!=8 && $page.props.auth.user.type_id!=10"
              >
              مراجعة السيارات
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('online_contracts')"
                :active="route().current('online_contracts')"
                v-if="$page.props.auth.user.type_id!=8 && $page.props.auth.user.type_id!=10"
              >
              {{ $t("online_contracts") }}
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('damage_report.index')"
                :active="route().current('damage_report.index')"
                v-if="$page.props.auth.user.type_id!=8 && $page.props.auth.user.type_id!=10"
              >
              تقارير الضرر
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('contract')"
                :active="route().current('contract')"
                v-if="$page.props.auth.user.type_id==8||$page.props.auth.user.type_id==10"
              >
              عقد جديد   
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('car_contract')"
                :active="route().current('car_contract')"
                v-if="$page.props.auth.user.type_id==8||$page.props.auth.user.type_id==10"
              >
              عقود البيع  
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('company_treasury')"
                :active="route().current('company_treasury')"
                v-if="$page.props.auth.user && $page.props.auth.user.type_id==8"
              >
              قاصة الشركة
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('contract_account')"
                :active="route().current('contract_account')"
                v-if="$page.props.auth.user && $page.props.auth.user.type_id==8"
              >
              محاسبة عقود
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('hunter')"
                :active="route().current('hunter')"
                v-if="$page.props.auth.user.type_id!=8 && $page.props.auth.user.type_id!=10"
              >
              عاطل 
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('systemSettings')"
                :active="route().current('systemSettings')"
              >
              إعدادات النظام
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('logViewer')"
                :active="route().current('logViewer')"
              >
              📋 لوغ الأخطاء
              </ResponsiveNavLink>

              <ResponsiveNavLink
                :href="route('logout')"
                method="post"
                as="button"
              >
                {{ $t("logout") }}
              </ResponsiveNavLink>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Heading -->
      <header class="bg-white shadow dark:bg-gray-900 dark:text-gray-200" v-if="$slots.header">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <!-- Page Content -->
      <main class="dark:bg-gray-800">
        <slot />
      </main>
    </div>
  </div>
</template>
<style>
.max-w-8xl{
  max-width: 95rem
}
.nav-scroll {
  scrollbar-width: thin;
}
.nav-scroll::-webkit-scrollbar {
  height: 4px;
}
.nav-scroll::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 999px;
}
nav,
nav > div {
  overflow: visible !important;
}
</style>
<script setup>
import { ref } from "vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link } from "@inertiajs/inertia-vue3";
import { useI18n } from "vue-i18n";
import DarkModeToggle from '@/Components/DarkToggle.vue';
import SyncStatusBar from '@/Components/SyncStatusBar.vue';
import WiFiIcon from '@/Components/WiFiIcon.vue';
import OnlineUsersIndicator from '@/Components/OnlineUsersIndicator.vue';

const showingNavigationDropdown = ref(false);
const i18n = useI18n();
const locale = ref("en"); // Default locale
const switchLocale = (locale) => {
  i18n.locale.value = locale;
  localStorage.setItem('lang', locale);
};
</script>

<template>
  <div>
    <!-- شريط حالة المزامنة -->
    <SyncStatusBar />
    
    <!-- أيقونة WiFi للتبديل السريع -->
    <WiFiIcon />
    
    <div class="min-h-screen bg-gray-100 dark:bg-gray-800">
      <nav class="bg-white border-gray-100 dark:bg-gray-900  print:hidden">
        <!-- Primary Navigation Menu -->
        <div class="max-w-8xl mx-auto px-4 sm:px-2  ">
          <div class="flex justify-between h-16">
            <div class="flex">
              <!-- Logo -->
              <div class="shrink-0 flex items-center">
                <Link :href="route('dashboard')" class="flex items-center">
                  <img src="/img/logo.png" alt="Logo" class="h-9 w-auto object-contain" onerror="this.src='/img/logo.jpg'; this.onerror=null;" />
                </Link>
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                <NavLink
                  :href="route('dashboard')"
                  :active="route().current('dashboard')"
                >
                  {{ $t("home") }}
                </NavLink>
                
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex" v-if="$page.props.auth.user.type_id==1||$page.props.auth.user.type_id==6">
                <NavLink
                  :href="route('purchases')"
                  :active="route().current('purchases')"
                >
                  {{ $t("purchases") }}
                </NavLink>
                
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex" v-if="$page.props.auth.user.type_id==1||$page.props.auth.user.type_id==6">
                <NavLink
                  :href="route('sales')"
                  :active="route().current('sales')"
                >
                  {{ $t("sales") }}
                </NavLink>
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"  v-if="$page.props.auth.user.type_id==1||$page.props.auth.user.type_id==6">
                <NavLink
                  :href="route('clients')"
                  :active="route().current('clients')"
                >
                  {{ $t("clients") }}
                </NavLink>
              </div>


              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"  v-if="$page.props.auth.user.type_id==1||$page.props.auth.user.type_id==6">
                <NavLink
                  :href="route('accounting')"
                  :active="route().current('accounting')"
                >
                 المحاسبة
                </NavLink>
              </div>
              
              <!-- صفحة مراقبة المزامنة -->
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"  v-if="$page.props.auth.user.type_id==1">
                <NavLink
                  :href="route('sync.monitor')"
                  :active="route().current('sync.monitor')"
                >
                  🔄 المزامنة
                </NavLink>
              </div>
              
              <!-- صفحة البحث Offline -->
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"  v-if="false">
                <NavLink
                  :href="route('offline.car.search')"
                  :active="route().current('offline.car.search')"
                >
                  🔍 بحث Offline
                </NavLink>
              </div>
              
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"  v-if="$page.props.auth.user.type_id==1||$page.props.auth.user.type_id==6">
                <NavLink
                  :href="route('online_contracts')"
                  :active="route().current('online_contracts')"
                >
                  {{ $t("online_contracts") }}
                </NavLink>
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"  v-if="$page.props.auth.user.type_id==1">
                <NavLink
                  :href="route('annual_information')"
                  :active="route().current('annual_information')"

                >
                  معلومات السنوية
                </NavLink>

                
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"  v-if="$page.props.auth.user && ($page.props.auth.user.type_id==1||$page.props.auth.user.type_id==7)">
                <NavLink
                  :href="route('car_check')"
                  :active="route().current('car_check')"

                >
                    مراجعة السيارات
                </NavLink>
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"  v-if="$page.props.auth.user && ($page.props.auth.user.type_id==1||$page.props.auth.user.type_id==6||$page.props.auth.user.type_id==7)">
                <NavLink
                  :href="route('damage_report.index')"
                  :active="route().current('damage_report.index')"
                >
                  تقارير الضرر
                </NavLink>
              </div>
              <!-- <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"  v-if="$page.props.auth.user.type_id==1||$page.props.auth.user.type_id==7">
                <NavLink
                  :href="route('car_expenses')"
                  :active="route().current('car_expenses')"

                >
                   مصاريف السيارات
                </NavLink>
              </div> -->
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"  v-if="$page.props.auth.user && ($page.props.auth.user.type_id==8||$page.props.auth.user.type_id==10)">
                <NavLink
                  :href="route('contract')"
                  :active="route().current('contract')"

                >
                {{ $t("newContract") }}
                </NavLink>
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"  v-if="$page.props.auth.user && ($page.props.auth.user.type_id==8||$page.props.auth.user.type_id==10)">
                <NavLink
                  :href="route('car_contract')"
                  :active="route().current('car_contract')"

                >
                {{ $t("SalesContracts") }}
                </NavLink>
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"  v-if="$page.props.auth.user && $page.props.auth.user.type_id==8">
                <NavLink
                  :href="route('contract_account')"
                  :active="route().current('contract_account')"

                >
                {{ $t("CompanyExpenses") }}
                </NavLink>
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"  v-if="$page.props.auth.user && ($page.props.auth.user.type_id==15)">
                <NavLink
                  :href="route('trips')"
                  :active="route().current('trips') || route().current('trips.create') || route().current('trips.show')"
                >
                  🚢 الرحلات
                </NavLink>
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"  v-if="$page.props.auth.user && ($page.props.auth.user.type_id==15)">
                <NavLink
                  :href="route('consigneeBalances.index')"
                  :active="route().current('consigneeBalances.index')"
                >
                  💰 أرصدة الزبائن
                </NavLink>
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"  v-if="$page.props.auth.user && ($page.props.auth.user.type_id==15)">
                <NavLink
                  :href="route('companyBalances.index')"
                  :active="route().current('companyBalances.index') || route().current('companyBalances.show')"
                >
                  🚢 حسابات الشركات
                </NavLink>
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"  v-if="$page.props.auth.user.type_id==1">
                <NavLink
                  :href="route('hunter')"
                  :active="route().current('hunter')"

                >
                عاطل
                </NavLink>
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"  v-if="$page.props.auth.user && $page.props.auth.user.owner_id==1">
                <NavLink
                  :href="route('systemSettings')"
                  :active="route().current('systemSettings')"
                >
                  إعدادات النظام
                </NavLink>
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"  v-if="$page.props.auth.user && $page.props.auth.user.owner_id==1">
                <NavLink
                  :href="route('logViewer')"
                  :active="route().current('logViewer')"
                >
                  📋 لوغ الأخطاء
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
            <div class="hidden sm:flex sm:items-center sm:ml-6 gap-4">
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
              <OnlineUsersIndicator />
              <DarkModeToggle></DarkModeToggle>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
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
              
              <!-- صفحة مراقبة المزامنة للهواتف -->
              <ResponsiveNavLink
                :href="route('sync.monitor')"
                :active="route().current('sync.monitor')"
                v-if="$page.props.auth.user && $page.props.auth.user.type_id == 1"
              >
              🔄 المزامنة
              </ResponsiveNavLink>
              
              <!-- صفحة البحث Offline للهواتف -->
              <ResponsiveNavLink
                :href="route('offline.car.search')"
                :active="route().current('offline.car.search')"
                v-if="$page.props.auth.user && ($page.props.auth.user.type_id == 1||$page.props.auth.user.type_id==6) && false"
              >
              🔍 بحث Offline
              </ResponsiveNavLink>
              
              <ResponsiveNavLink
                :href="route('online_contracts')"
                :active="route().current('online_contracts')"
                v-if="$page.props.auth.user && ($page.props.auth.user.type_id == 1||$page.props.auth.user.type_id==6)"
              >
              {{ $t("online_contracts") }}
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('annual_information')"
                :active="route().current('annual_information')"
                v-if="$page.props.auth.user && ($page.props.auth.user.type_id == 1||$page.props.auth.user.type_id==6)"
              >
              معلومات السنوية
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('car_expenses')"
                :active="route().current('car_expenses')"
                v-if="$page.props.auth.user && ($page.props.auth.user.type_id == 1||$page.props.auth.user.type_id==7)"
              >
              مصاريف السيارات 
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('damage_report.index')"
                :active="route().current('damage_report.index')"
                v-if="$page.props.auth.user.type_id == 1||$page.props.auth.user.type_id==6||$page.props.auth.user.type_id==7"
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
                :href="route('contract_account')"
                :active="route().current('contract_account')"
                v-if="$page.props.auth.user && $page.props.auth.user.type_id==8"
              >
              محاسبة عقود
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('hunter')"
                :active="route().current('hunter')"
                v-if="$page.props.auth.user && ($page.props.auth.user.type_id == 1||$page.props.auth.user.type_id==6)"
              >
              عاطل 
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('systemSettings')"
                :active="route().current('systemSettings')"
                v-if="$page.props.auth.user && $page.props.auth.user.owner_id==1"
              >
              إعدادات النظام
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('logViewer')"
                :active="route().current('logViewer')"
                v-if="$page.props.auth.user && $page.props.auth.user.owner_id==1"
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
</style>
<script setup>
import { ref, computed } from "vue";
import { usePage } from "@inertiajs/inertia-vue3";
import { mainNavPages, moreNavPages, navPageLabel } from "@/utils/navAccess";
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
const page = usePage();
const mainPages = computed(() => mainNavPages(page.props.value.auth?.navPages || []));
const morePages = computed(() => moreNavPages(page.props.value.auth?.navPages || []));
const i18n = useI18n();
const { t, te } = i18n;
const switchLocale = (locale) => {
  i18n.locale.value = locale;
  localStorage.setItem('lang', locale);
};

const moreMenuActive = computed(() => {
  return morePages.value.some((navPage) => isPageActive(navPage));
});

function pageHref(navPage) {
  if (navPage.route_name) {
    try {
      if (typeof route().has === 'function' && !route().has(navPage.route_name)) {
        return navPage.path || '#';
      }
      return route(navPage.route_name);
    } catch (e) {
      return navPage.path || '#';
    }
  }
  return navPage.path || '#';
}

function onSystemLogoError(event) {
  const src = String(event?.target?.src || '');
  // لا نستبدل شعار الكونفيغ المرفوع — فقط الافتراضي عند فشله
  if (src.includes('/media/system/') || src.includes('/uploads/system/') || src.includes('/img/system/') || src.includes('/storage/system/')) {
    return;
  }
  event.target.onerror = null;
  event.target.src = '/img/logo.jpg';
}

function isPageActive(navPage) {
  if (!navPage.route_name) {
    return false;
  }
  return route().current(navPage.route_name);
}
</script>

<template>
  <div>
    <!-- مؤشر حالة المزامنة المختصر -->
    <SyncStatusBadge />
    
    <div class="min-h-screen bg-gray-100 dark:bg-gray-800">
      <nav class="bg-white border-b border-gray-200 dark:border-gray-700 dark:bg-gray-900 print:hidden overflow-visible">
        <!-- Primary Navigation Menu -->
        <div class="max-w-8xl mx-auto px-4 sm:px-2  ">
          <div class="flex justify-between h-16 gap-2 overflow-visible">
            <div class="flex min-w-0 flex-1 items-center overflow-visible">
              <!-- Logo -->
              <div class="shrink-0 flex items-center pe-2">
                <Link :href="route('dashboard')" class="flex items-center">
                  <img
                    :src="$page.props.systemLogo || '/img/logo.png'"
                    alt="Logo"
                    class="h-9 w-auto object-contain"
                    @error="onSystemLogoError"
                  />
                </Link>
              </div>

              <div class="hidden sm:flex sm:items-center sm:gap-4 sm:ml-4 min-w-0 flex-1 overflow-x-auto nav-scroll">
              <NavLink
                v-for="navPage in mainPages"
                :key="navPage.slug"
                :href="pageHref(navPage)"
                :active="isPageActive(navPage)"
                class="shrink-0"
              >
                {{ navPageLabel(navPage, t, te) }}
              </NavLink>
              <NavMoreMenu v-if="morePages.length" :active="moreMenuActive" :pages="morePages" />
              <NavLink
                v-if="$page.props.auth.canManagePermissions && !mainPages.length && !morePages.length"
                :href="route('pagePermissions')"
                :active="route().current('pagePermissions')"
                class="shrink-0"
              >
                {{ t('nav.page_permissions') }}
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
            <div class="hidden sm:flex sm:items-center sm:ml-4 gap-4 shrink-0">
              <div class="ml-3 relative">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="dark:bg-gray-800 dark:text-gray-100 dark:hover:text-white inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
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
                        class="dark:bg-gray-800 dark:text-gray-100 dark:hover:text-white inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
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
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 focus:text-gray-700 dark:focus:text-white transition duration-150 ease-in-out"
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
          class="sm:hidden dark:bg-gray-900"
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
          <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700">
            <div class="px-4">
              <div class="font-medium text-base text-gray-800 dark:text-gray-100">
                {{ $page.props.auth.user?.name || 'غير مسجل' }}
              </div>
              <div class="font-medium text-sm text-gray-500 dark:text-gray-400">
                {{ $page.props.auth.user?.email || '' }}
              </div>
            </div>
            <div class="mt-3 space-y-1">
              <ResponsiveNavLink
                v-for="navPage in mainPages"
                :key="'m-' + navPage.slug"
                :href="pageHref(navPage)"
                :active="isPageActive(navPage)"
              >
                {{ navPageLabel(navPage, t, te) }}
              </ResponsiveNavLink>

              <div
                v-if="morePages.length"
                class="px-4 pt-2 pb-1 text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-300"
              >
                {{ t('nav.more') }}
              </div>
              <ResponsiveNavLink
                v-for="navPage in morePages"
                :key="'mm-' + navPage.slug"
                :href="pageHref(navPage)"
                :active="isPageActive(navPage)"
              >
                {{ navPageLabel(navPage, t, te) }}
              </ResponsiveNavLink>

              <ResponsiveNavLink
                v-if="$page.props.auth.canManagePermissions && !mainPages.length && !morePages.length"
                :href="route('pagePermissions')"
                :active="route().current('pagePermissions')"
              >
                {{ t('nav.page_permissions') }}
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
.dark .nav-scroll::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.45);
}
.dark .nav-scroll {
  scrollbar-color: rgba(255, 255, 255, 0.45) transparent;
}
nav,
nav > div {
  overflow: visible !important;
}
</style>
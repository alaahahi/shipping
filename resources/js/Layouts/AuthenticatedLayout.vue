<script setup>
import { ref, computed, onMounted } from "vue";
import { usePage, Link } from "@inertiajs/inertia-vue3";
import { mainNavPages, moreNavPages, navPageLabel } from "@/utils/navAccess";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import NavMoreMenu from "@/Components/NavMoreMenu.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { useI18n } from "vue-i18n";
import DarkModeToggle from "@/Components/DarkToggle.vue";
import SyncStatusBadge from "@/Components/SyncStatusBadge.vue";
import OnlineUsersIndicator from "@/Components/OnlineUsersIndicator.vue";

const showingNavigationDropdown = ref(false);
const page = usePage();
const user = computed(() => page.props.value.auth?.user ?? {});
const mainPages = computed(() => mainNavPages(page.props.value.auth?.navPages || []));
const morePages = computed(() => moreNavPages(page.props.value.auth?.navPages || []));
const i18n = useI18n();
const { t, te } = i18n;

const switchLocale = (locale) => {
  i18n.locale.value = locale;
  localStorage.setItem("lang", locale);
};

onMounted(() => {
  const stored = localStorage.getItem("lang");
  if (stored) {
    i18n.locale.value = stored;
  }
});

const moreMenuActive = computed(() => morePages.value.some((navPage) => isPageActive(navPage)));

function pageHref(navPage) {
  if (navPage.route_name) {
    try {
      if (typeof route().has === "function" && !route().has(navPage.route_name)) {
        return navPage.path || "#";
      }
      return route(navPage.route_name);
    } catch (e) {
      return navPage.path || "#";
    }
  }
  return navPage.path || "#";
}

function onSystemLogoError(event) {
  const src = String(event?.target?.src || "");
  if (
    src.includes("/media/system/") ||
    src.includes("/uploads/system/") ||
    src.includes("/img/system/") ||
    src.includes("/storage/system/")
  ) {
    return;
  }
  event.target.onerror = null;
  event.target.src = "/img/logo.jpg";
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
    <SyncStatusBadge />

    <div class="min-h-screen bg-slate-100 text-slate-900 dark:bg-[#0b1220] dark:text-slate-100">
      <nav
        class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 shadow-sm backdrop-blur supports-[backdrop-filter]:bg-white/85 dark:border-slate-800 dark:bg-slate-900/95 print:hidden overflow-visible"
      >
        <div class="max-w-8xl mx-auto px-4 sm:px-4 lg:px-6">
          <div class="flex min-h-[72px] items-center justify-between gap-4 overflow-visible">
            <div class="flex min-w-0 flex-1 items-center gap-3 lg:gap-5 overflow-visible">
              <Link
                :href="route('dashboard')"
                class="inline-flex min-h-[44px] shrink-0 items-center gap-3 rounded-2xl px-2 py-2 text-right transition hover:bg-slate-100 dark:hover:bg-slate-800/80"
              >
                <img
                  :src="$page.props.systemLogo || '/img/logo.png'"
                  alt="Logo"
                  class="h-10 w-auto object-contain"
                  @error="onSystemLogoError"
                />
              </Link>

              <div class="hidden min-w-0 flex-1 items-center gap-1 overflow-x-auto nav-scroll lg:flex xl:gap-2">
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
                  {{ t("nav.page_permissions") }}
                </NavLink>
              </div>
            </div>

            <div class="hidden shrink-0 items-center gap-2 sm:flex lg:gap-3">
              <OnlineUsersIndicator :user="$page.props.auth?.user" />

              <div class="rounded-2xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <DarkModeToggle />
              </div>

              <div class="relative">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <button
                      type="button"
                      class="inline-flex min-h-[44px] items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                      {{ $t("lang") }}
                      <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                      </svg>
                    </button>
                  </template>
                  <template #content>
                    <DropdownLink @click="switchLocale('ar')" as="button">عربي</DropdownLink>
                    <DropdownLink @click="switchLocale('en')" as="button">English</DropdownLink>
                    <DropdownLink @click="switchLocale('kr')" as="button">كردي</DropdownLink>
                  </template>
                </Dropdown>
              </div>

              <div class="relative">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <button
                      type="button"
                      class="inline-flex min-h-[44px] items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-right text-sm shadow-sm transition hover:border-slate-300 hover:bg-slate-50 focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800"
                    >
                      <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-200">
                        {{ (user.name || "U").slice(0, 1) }}
                      </span>
                      <span class="hidden flex-col leading-tight xl:flex">
                        <span class="font-semibold text-slate-800 dark:text-white">
                          {{ user.name || "غير مسجل" }}
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                          {{ user.email || "" }}
                        </span>
                      </span>
                      <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                      </svg>
                    </button>
                  </template>
                  <template #content>
                    <DropdownLink :href="route('logout')" method="post" as="button">
                      {{ $t("logout") }}
                    </DropdownLink>
                  </template>
                </Dropdown>
              </div>
            </div>

            <div class="flex items-center gap-2 sm:hidden">
              <OnlineUsersIndicator :user="$page.props.auth?.user" />
              <div class="rounded-2xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <DarkModeToggle />
              </div>
              <button
                @click="showingNavigationDropdown = !showingNavigationDropdown"
                type="button"
                class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                :aria-expanded="showingNavigationDropdown ? 'true' : 'false'"
                aria-label="فتح أو إغلاق قائمة التنقل"
              >
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                  <path
                    :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                  <path
                    :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
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

        <div
          v-show="showingNavigationDropdown"
          class="border-t border-slate-200 bg-white/95 px-4 pb-4 pt-4 dark:border-slate-800 dark:bg-slate-900/95 sm:hidden"
        >
          <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-3">
              <span class="flex h-11 w-11 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-200">
                {{ (user.name || "U").slice(0, 1) }}
              </span>
              <div class="min-w-0">
                <div class="truncate text-sm font-bold text-slate-900 dark:text-white">
                  {{ user.name || "غير مسجل" }}
                </div>
                <div class="truncate text-xs text-slate-500 dark:text-slate-400">
                  {{ user.email || "" }}
                </div>
              </div>
            </div>

            <div class="mt-4 space-y-4">
              <div>
                <div class="mb-2 px-1 text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">
                  Main
                </div>
                <div class="space-y-2">
                  <ResponsiveNavLink
                    v-for="navPage in mainPages"
                    :key="'m-' + navPage.slug"
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
                    {{ t("nav.page_permissions") }}
                  </ResponsiveNavLink>
                </div>
              </div>

              <div v-if="morePages.length" class="border-t border-slate-200 pt-4 dark:border-slate-800">
                <div class="mb-2 px-1 text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">
                  {{ t("nav.more") }}
                </div>
                <div class="space-y-2">
                  <ResponsiveNavLink
                    v-for="navPage in morePages"
                    :key="'mm-' + navPage.slug"
                    :href="pageHref(navPage)"
                    :active="isPageActive(navPage)"
                  >
                    {{ navPageLabel(navPage, t, te) }}
                  </ResponsiveNavLink>
                </div>
              </div>

              <div class="border-t border-slate-200 pt-4 dark:border-slate-800">
                <div class="mb-3 px-1 text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">
                  Preferences
                </div>
                <div class="grid grid-cols-3 gap-2">
                  <button type="button" class="rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" @click="switchLocale('ar')">AR</button>
                  <button type="button" class="rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" @click="switchLocale('en')">EN</button>
                  <button type="button" class="rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" @click="switchLocale('kr')">KR</button>
                </div>
                <div class="mt-3 space-y-2">
                  <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                    {{ $t("logout") }}
                  </ResponsiveNavLink>
                </div>
              </div>
            </div>
          </div>
        </div>
      </nav>

      <header
        v-if="$slots.header"
        class="border-b border-slate-200/70 bg-white/80 shadow-sm dark:border-slate-800 dark:bg-slate-900/70 dark:text-slate-200"
      >
        <div class="max-w-7xl mx-auto px-4 py-5 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <main class="min-h-[calc(100vh-4.5rem)] bg-slate-100 dark:bg-[#0b1220]">
        <slot />
      </main>
    </div>
  </div>
</template>

<style>
.max-w-8xl {
  max-width: 95rem;
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

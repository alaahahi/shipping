<script setup>
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const now = ref(new Date());
let clockTimer = null;

const weatherTemp = ref(null);
const weatherCity = ref('');
const WEATHER_LS_KEY = 'dashboard-weather-cache';
const WEATHER_TTL_MS = 60 * 60 * 1000;

const fxSell = ref(null);
const fxBuy = ref(null);
const fxSource = ref('');
const fxUpdatedAt = ref(null);
const FX_LS_KEY = 'dashboard-exchange-rates-cache-v3';
const FX_TTL_MS = 60 * 60 * 1000;

/** dawn | day | sunset | night — local browser hour */
const dayPhase = computed(() => {
  const h = now.value.getHours();
  if (h >= 5 && h < 10) return 'dawn';
  if (h >= 10 && h < 16) return 'day';
  if (h >= 16 && h < 19) return 'sunset';
  return 'night';
});

const dayPhaseLabelKey = computed(() => {
  const map = {
    dawn: 'dashboard_phase_dawn',
    day: 'dashboard_phase_day',
    sunset: 'dashboard_phase_sunset',
    night: 'dashboard_phase_night',
  };
  return map[dayPhase.value] || 'dashboard_phase_day';
});

/** Sky band gradient class by local hour phase */
const skyPanelClass = computed(() => {
  const map = {
    dawn: 'dash-sky-dawn',
    day: 'dash-sky-day',
    sunset: 'dash-sky-sunset',
    night: 'dash-sky-night',
  };
  return map[dayPhase.value] || 'dash-sky-day';
});

const weatherTempLabel = computed(() => {
  if (weatherTemp.value === null || weatherTemp.value === undefined) return null;
  const n = Number(weatherTemp.value);
  if (Number.isNaN(n)) return null;
  return `${n.toFixed(n % 1 === 0 ? 0 : 1)}°C`;
});

const formatFxIqd = (value) => {
  if (value === null || value === undefined) return null;
  const n = Number(value);
  if (Number.isNaN(n)) return null;
  return new Intl.NumberFormat('en-US', {
    maximumFractionDigits: n % 1 === 0 ? 0 : 2,
  }).format(n);
};

const fxHasRates = computed(
  () => formatFxIqd(fxSell.value) !== null && formatFxIqd(fxBuy.value) !== null
);

const clockTime = computed(() =>
  new Intl.DateTimeFormat('ar-IQ', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false,
    numberingSystem: 'latn',
  }).format(now.value)
);

const gregorianDate = computed(() =>
  new Intl.DateTimeFormat('ar-IQ', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    numberingSystem: 'latn',
  }).format(now.value)
);

const hijriDate = computed(() => {
  try {
    return new Intl.DateTimeFormat('ar-SA-u-ca-islamic-umalqura', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      numberingSystem: 'latn',
    }).format(now.value);
  } catch {
    return new Intl.DateTimeFormat('ar-SA-u-ca-islamic', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      numberingSystem: 'latn',
    }).format(now.value);
  }
});

function readWeatherLocalCache({ allowStale = false } = {}) {
  try {
    const raw = localStorage.getItem(WEATHER_LS_KEY);
    if (!raw) return null;
    const parsed = JSON.parse(raw);
    if (!parsed || typeof parsed !== 'object' || !parsed.ts) return null;
    const fresh = Date.now() - Number(parsed.ts) <= WEATHER_TTL_MS;
    if (!fresh && !allowStale) return null;
    return { ...parsed, fresh };
  } catch {
    return null;
  }
}

function writeWeatherLocalCache(data) {
  try {
    localStorage.setItem(
      WEATHER_LS_KEY,
      JSON.stringify({
        ts: Date.now(),
        temperature: data?.temperature ?? null,
        city: data?.city ?? '',
      })
    );
  } catch {
    /* ignore quota / private mode */
  }
}

const loadWeather = async () => {
  const cached = readWeatherLocalCache();
  if (cached?.fresh && cached.temperature != null) {
    weatherTemp.value = cached.temperature;
    weatherCity.value = cached.city || '';
    return;
  }

  const stale = readWeatherLocalCache({ allowStale: true });
  if (stale?.temperature != null) {
    weatherTemp.value = stale.temperature;
    weatherCity.value = stale.city || '';
  }

  try {
    const response = await axios.get('/api/dashboardWeather');
    const data = response.data?.data || {};
    if (data.temperature != null && !Number.isNaN(Number(data.temperature))) {
      weatherTemp.value = Number(data.temperature);
      weatherCity.value = data.city || '';
      writeWeatherLocalCache(data);
    }
  } catch (error) {
    console.error(error);
    /* keep stale temp if any — never block the clock */
  }
};

function readFxLocalCache({ allowStale = false } = {}) {
  try {
    const raw = localStorage.getItem(FX_LS_KEY);
    if (!raw) return null;
    const parsed = JSON.parse(raw);
    if (!parsed || typeof parsed !== 'object' || !parsed.ts) return null;
    const fresh = Date.now() - Number(parsed.ts) <= FX_TTL_MS;
    if (!fresh && !allowStale) return null;
    return { ...parsed, fresh };
  } catch {
    return null;
  }
}

function writeFxLocalCache(data) {
  try {
    localStorage.setItem(
      FX_LS_KEY,
      JSON.stringify({
        ts: Date.now(),
        usd_to_iqd_sell: data?.usd_to_iqd_sell ?? null,
        usd_to_iqd_buy: data?.usd_to_iqd_buy ?? null,
        source: data?.source || '',
        updated_at: data?.updated_at || null,
      })
    );
  } catch {
    /* ignore quota / private mode */
  }
}

function applyFxData(data) {
  if (!data || data.usd_to_iqd_sell == null || data.usd_to_iqd_buy == null) return false;
  const sell = Number(data.usd_to_iqd_sell);
  const buy = Number(data.usd_to_iqd_buy);
  if (Number.isNaN(sell) || Number.isNaN(buy)) return false;
  fxSell.value = sell;
  fxBuy.value = buy;
  fxSource.value = data.source || '';
  fxUpdatedAt.value = data.updated_at || null;
  return true;
}

const loadExchangeRates = async () => {
  const cached = readFxLocalCache();
  if (cached?.fresh && applyFxData(cached)) {
    return;
  }

  const stale = readFxLocalCache({ allowStale: true });
  if (stale) {
    applyFxData(stale);
  }

  try {
    const response = await axios.get('/api/dashboardExchangeRates');
    const data = response.data?.data || {};
    if (applyFxData(data)) {
      writeFxLocalCache(data);
    }
  } catch (error) {
    console.error(error);
    /* keep stale rates if any */
  }
};

onMounted(() => {
  clockTimer = window.setInterval(() => {
    now.value = new Date();
  }, 1000);
  loadWeather().catch((e) => console.error(e));
  loadExchangeRates().catch((e) => console.error(e));
});

onUnmounted(() => {
  if (clockTimer) {
    window.clearInterval(clockTimer);
  }
});
</script>

<template>
  <section>
    <div class="grid grid-cols-1 gap-3 lg:grid-cols-3 sm:gap-4">
      <!-- Col 1: Live clock + sky / weather -->
      <div
        class="relative min-w-0 overflow-hidden rounded-2xl border border-white/15 p-4 shadow-sm sm:p-5"
        :class="skyPanelClass"
        :aria-label="weatherTempLabel || $t(dayPhaseLabelKey)"
      >
        <!-- Night stars (full card) -->
        <div
          v-if="dayPhase === 'night'"
          class="pointer-events-none absolute inset-0 z-0"
          aria-hidden="true"
        >
          <span class="dash-star absolute start-[8%] top-[10%] h-1.5 w-1.5 rounded-full bg-white" />
          <span class="dash-star dash-star-delay absolute start-[18%] top-[42%] h-1 w-1 rounded-full bg-white" />
          <span class="dash-star absolute start-[28%] top-[78%] h-[3px] w-[3px] rounded-full bg-sky-100" />
          <span class="dash-star dash-star-delay-2 absolute start-[38%] top-[16%] h-1.5 w-1.5 rounded-full bg-white" />
          <span class="dash-star absolute start-[48%] top-[55%] h-1 w-1 rounded-full bg-slate-100" />
          <span class="dash-star dash-star-delay absolute start-[58%] top-[8%] h-[3px] w-[3px] rounded-full bg-white" />
          <span class="dash-star absolute start-[68%] top-[36%] h-1.5 w-1.5 rounded-full bg-sky-50" />
          <span class="dash-star dash-star-delay-2 absolute start-[78%] top-[68%] h-1 w-1 rounded-full bg-white" />
          <span class="dash-star absolute end-[10%] top-[14%] h-1.5 w-1.5 rounded-full bg-white" />
          <span class="dash-star dash-star-delay absolute end-[18%] top-[48%] h-[3px] w-[3px] rounded-full bg-sky-100" />
          <span class="dash-star absolute end-[6%] top-[82%] h-1 w-1 rounded-full bg-white" />
          <span class="dash-star dash-star-delay-2 absolute end-[30%] top-[28%] h-1.5 w-1.5 rounded-full bg-white" />
          <span class="dash-star absolute start-[12%] top-[62%] h-[2px] w-[2px] rounded-full bg-white" />
          <span class="dash-star dash-star-delay absolute start-[88%] top-[58%] h-[2px] w-[2px] rounded-full bg-sky-50" />
        </div>

        <div class="relative z-10">
          <p class="text-xs font-semibold uppercase tracking-wide text-white/85">
            {{ $t('dashboard_clock') }}
          </p>

          <!-- Top row: dates ↔ large clock (RTL: dates start / clock end) -->
          <div class="mt-2 flex items-start justify-between gap-3">
            <div class="min-w-0 space-y-0.5">
              <p class="text-sm leading-snug text-white">
                <span class="text-xs text-white/75">{{ $t('dashboard_gregorian_date') }}:</span>
                {{ gregorianDate }}
              </p>
              <p class="text-sm leading-snug text-white">
                <span class="text-xs text-white/75">{{ $t('dashboard_hijri_date') }}:</span>
                {{ hijriDate }}
              </p>
            </div>

            <p
              class="shrink-0 self-center font-mono text-3xl font-bold tabular-nums tracking-tight text-white sm:text-4xl"
              dir="ltr"
            >
              {{ clockTime }}
            </p>
          </div>

          <!-- Weather + celestial (breathe scale animation) -->
          <div class="mt-4 flex items-center justify-between gap-3">
            <div class="min-w-0">
              <p class="truncate text-[11px] font-semibold uppercase tracking-wider text-white/90">
                {{ $t(dayPhaseLabelKey) }}
              </p>
              <p
                v-if="weatherCity"
                class="mt-0.5 truncate text-[11px] font-medium text-white/80"
                :title="weatherCity"
              >
                {{ weatherCity }}
              </p>
              <p
                v-if="weatherTempLabel"
                class="mt-1.5 font-mono text-2xl font-bold tabular-nums tracking-tight text-white sm:text-3xl"
                dir="ltr"
              >
                {{ weatherTempLabel }}
              </p>
            </div>

            <div class="dash-celestial shrink-0" :class="`dash-celestial--${dayPhase}`">
              <!-- Dawn / soft sun -->
              <svg
                v-if="dayPhase === 'dawn'"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 48 48"
                class="h-11 w-11"
                aria-hidden="true"
              >
                <defs>
                  <radialGradient id="dashDawnGlow" cx="50%" cy="55%" r="50%">
                    <stop offset="0%" stop-color="#fde68a" stop-opacity="0.95" />
                    <stop offset="100%" stop-color="#fbbf24" stop-opacity="0" />
                  </radialGradient>
                </defs>
                <circle cx="24" cy="26" r="16" fill="url(#dashDawnGlow)" />
                <circle cx="24" cy="26" r="8" fill="#fbbf24" />
                <path d="M8 34h32" stroke="#fcd34d" stroke-width="2" stroke-linecap="round" opacity="0.7" />
                <path d="M12 38h24" stroke="#fde68a" stroke-width="1.5" stroke-linecap="round" opacity="0.5" />
              </svg>

              <!-- Bright day sun -->
              <svg
                v-else-if="dayPhase === 'day'"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 48 48"
                class="h-11 w-11"
                aria-hidden="true"
              >
                <g stroke="#fde047" stroke-width="2.2" stroke-linecap="round">
                  <path d="M24 6v4M24 38v4M6 24h4M38 24h4M11 11l2.8 2.8M34.2 34.2L37 37M37 11l-2.8 2.8M11 37l2.8-2.8" />
                </g>
                <circle cx="24" cy="24" r="9" fill="#fde047" stroke="#facc15" stroke-width="1.5" />
              </svg>

              <!-- Sunset -->
              <svg
                v-else-if="dayPhase === 'sunset'"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 48 48"
                class="h-11 w-11"
                aria-hidden="true"
              >
                <defs>
                  <linearGradient id="dashSunsetSky" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#fdba74" stop-opacity="0.45" />
                    <stop offset="100%" stop-color="#ea580c" stop-opacity="0" />
                  </linearGradient>
                </defs>
                <rect x="4" y="8" width="40" height="22" rx="4" fill="url(#dashSunsetSky)" />
                <circle cx="24" cy="28" r="9" fill="#fb923c" />
                <path d="M6 34c6-4 12-4 18 0s12 4 18 0" fill="none" stroke="#fdba74" stroke-width="2" stroke-linecap="round" />
                <path d="M4 38h40" stroke="#f97316" stroke-width="2.5" stroke-linecap="round" opacity="0.9" />
              </svg>

              <!-- Night moon -->
              <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 48 48"
                class="h-11 w-11"
                aria-hidden="true"
              >
                <defs>
                  <radialGradient id="dashMoonGlow" cx="42%" cy="38%" r="55%">
                    <stop offset="0%" stop-color="#f1f5f9" stop-opacity="1" />
                    <stop offset="55%" stop-color="#cbd5e1" stop-opacity="0.95" />
                    <stop offset="100%" stop-color="#64748b" stop-opacity="0.35" />
                  </radialGradient>
                </defs>
                <path
                  d="M30 8.5a14 14 0 1 0 9.5 24.2A12 12 0 1 1 30 8.5Z"
                  fill="url(#dashMoonGlow)"
                />
                <circle cx="36" cy="12" r="1.2" fill="#f8fafc" opacity="0.85" />
                <circle cx="40" cy="18" r="0.8" fill="#e2e8f0" opacity="0.7" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Exchange rates (USD → IQD, CAD → USD) -->
      <div
        class="min-w-0 rounded-2xl border border-slate-600 bg-gradient-to-br from-slate-900 via-slate-900 to-emerald-950/40 p-4 shadow-sm sm:p-5"
        :aria-label="$t('dashboard_exchange_rate')"
      >
        <div class="flex items-start justify-between gap-2">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-200">
              {{ $t('dashboard_exchange_rate') }}
            </p>
            <p class="mt-0.5 text-[11px] font-medium text-emerald-300/90">
              {{ $t('dashboard_exchange_usd_to_iqd') }}
            </p>
          </div>
          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-emerald-500/40 bg-emerald-500/15 text-emerald-300"
            aria-hidden="true"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10M7 12h6M7 17h10" />
              <circle cx="17" cy="12" r="2.2" />
              <path stroke-linecap="round" d="M4 4.5v15" />
            </svg>
          </div>
        </div>

        <template v-if="fxHasRates">
          <div class="mt-3 space-y-2.5">
            <!-- USA / USD -->
            <div class="rounded-xl border border-slate-600 bg-slate-800/90 px-3 py-2.5">
              <div class="flex items-center gap-2">
                <span
                  class="inline-flex h-6 w-6 shrink-0 overflow-hidden rounded-full border border-slate-500 shadow-sm"
                  :title="$t('dashboard_exchange_usa')"
                  aria-hidden="true"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 60" class="h-full w-full">
                    <rect width="60" height="60" fill="#b22234" />
                    <rect y="4.6" width="60" height="4.6" fill="#fff" />
                    <rect y="13.8" width="60" height="4.6" fill="#fff" />
                    <rect y="23" width="60" height="4.6" fill="#fff" />
                    <rect y="32.2" width="60" height="4.6" fill="#fff" />
                    <rect y="41.4" width="60" height="4.6" fill="#fff" />
                    <rect y="50.6" width="60" height="4.6" fill="#fff" />
                    <rect width="28" height="27.6" fill="#3c3b6e" />
                  </svg>
                </span>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-300">
                  {{ $t('dashboard_exchange_usa') }} · {{ $t('dashboard_exchange_usd_to_iqd') }}
                </p>
              </div>
              <div class="mt-2 grid grid-cols-2 gap-2">
                <div>
                  <p class="text-[11px] text-slate-200">
                    {{ $t('dashboard_exchange_sell') }}
                  </p>
                  <p class="mt-0.5 font-mono text-lg font-bold tabular-nums text-white" dir="ltr">
                    {{ formatFxIqd(fxSell) }}
                  </p>
                </div>
                <div>
                  <p class="text-[11px] text-slate-200">
                    {{ $t('dashboard_exchange_buy') }}
                  </p>
                  <p class="mt-0.5 font-mono text-lg font-bold tabular-nums text-emerald-300" dir="ltr">
                    {{ formatFxIqd(fxBuy) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </template>
        <p
          v-else
          class="mt-4 text-sm font-medium text-slate-300"
        >
          {{ $t('dashboard_exchange_unavailable') }}
        </p>

        <p
          v-if="fxSource"
          class="mt-3 truncate text-[11px] text-slate-400"
          :title="fxSource"
        >
          {{ $t('dashboard_exchange_source') }}: {{ fxSource }}
        </p>
      </div>

      <!-- Col 3: treasury / debt summary (slot) -->
      <div class="min-w-0 flex flex-col gap-3">
        <slot />
      </div>
    </div>
  </section>
</template>

<style>
@media (prefers-reduced-motion: reduce) {
  .dash-star,
  .dash-celestial {
    transition: none !important;
    animation: none !important;
  }
}

/* Full-card sky gradients (dark-safe, high-contrast white text) */
.dash-sky-dawn {
  background: linear-gradient(145deg, #312e81 0%, #4338ca 45%, #b45309 100%);
}

.dash-sky-day {
  background: linear-gradient(145deg, #0369a1 0%, #0284c7 48%, #0891b2 100%);
}

.dash-sky-sunset {
  background: linear-gradient(145deg, #c2410c 0%, #be123c 55%, #9f1239 100%);
}

.dash-sky-night {
  background: linear-gradient(155deg, #1e1b4b 0%, #0f172a 55%, #020617 100%);
}

.dash-celestial {
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 9999px;
  padding: 0.2rem;
  transform-origin: center;
  animation: dash-celestial-breathe 2.5s ease-in-out infinite;
}

.dash-celestial--dawn {
  filter: drop-shadow(0 0 10px rgba(251, 191, 36, 0.55));
}

.dash-celestial--day {
  filter: drop-shadow(0 0 12px rgba(253, 224, 71, 0.55));
}

.dash-celestial--sunset {
  filter: drop-shadow(0 0 12px rgba(251, 146, 60, 0.55));
}

.dash-celestial--night {
  filter: drop-shadow(0 0 14px rgba(186, 230, 253, 0.35))
    drop-shadow(0 0 6px rgba(226, 232, 240, 0.45));
}

@keyframes dash-celestial-breathe {
  0%,
  100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.15);
  }
}

.dash-star {
  opacity: 0.95;
  animation: dash-twinkle 2.4s ease-in-out infinite;
  box-shadow:
    0 0 4px 1px rgba(255, 255, 255, 0.95),
    0 0 10px 2px rgba(186, 230, 253, 0.75),
    0 0 18px 3px rgba(255, 255, 255, 0.35);
}

.dash-star-delay {
  animation-delay: 0.9s;
}

.dash-star-delay-2 {
  animation-delay: 1.7s;
}

@keyframes dash-twinkle {
  0%,
  100% {
    opacity: 0.7;
    transform: scale(0.95);
  }
  50% {
    opacity: 1;
    transform: scale(1.35);
  }
}
</style>

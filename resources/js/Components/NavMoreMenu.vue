<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Link } from '@inertiajs/inertia-vue3';
import { useI18n } from 'vue-i18n';
import { navPageLabel } from '@/utils/navAccess';

const { t, te } = useI18n();

const props = defineProps({
    active: {
        type: Boolean,
        default: false,
    },
    pages: {
        type: Array,
        default: () => [],
    },
});

const open = ref(false);
const triggerRef = ref(null);
const panelStyle = ref({ top: '0px', left: '0px' });

const items = computed(() => {
    return props.pages.map((page) => ({
        ...page,
        href: pageHref(page),
    }));
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

function isCurrent(routeName) {
    if (!routeName) return false;
    return route().current(routeName);
}

function updatePosition() {
    const el = triggerRef.value;
    if (!el) return;

    const rect = el.getBoundingClientRect();
    const panelWidth = 208;
    let left = rect.right - panelWidth;
    left = Math.max(8, Math.min(left, window.innerWidth - panelWidth - 8));

    panelStyle.value = {
        top: `${rect.bottom + 6}px`,
        left: `${left}px`,
    };
}

function toggle() {
    open.value = !open.value;
}

function close() {
    open.value = false;
}

function onDocumentClick(e) {
    if (!open.value) return;
    const el = triggerRef.value;
    if (el && !el.contains(e.target)) {
        close();
    }
}

function onEscape(e) {
    if (e.key === 'Escape') close();
}

watch(open, async (isOpen) => {
    if (isOpen) {
        await nextTick();
        updatePosition();
    }
});

onMounted(() => {
    document.addEventListener('click', onDocumentClick);
    document.addEventListener('keydown', onEscape);
    window.addEventListener('resize', close);
    window.addEventListener('scroll', close, true);
});

onUnmounted(() => {
    document.removeEventListener('click', onDocumentClick);
    document.removeEventListener('keydown', onEscape);
    window.removeEventListener('resize', close);
    window.removeEventListener('scroll', close, true);
});
</script>

<template>
    <div ref="triggerRef" class="relative shrink-0">
        <button
            type="button"
            @click.stop="toggle"
            :class="[
                'inline-flex min-h-[44px] items-center justify-center gap-1.5 whitespace-nowrap rounded-xl border px-3 py-2 text-sm font-semibold outline-none transition duration-200 ease-out xl:px-4 focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900',
                active
                    ? 'border-indigo-600 bg-indigo-600 text-white shadow-sm dark:border-white dark:bg-white dark:text-slate-900'
                    : 'border-transparent text-slate-600 hover:border-slate-200 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:border-slate-700 dark:hover:bg-slate-800/80 dark:hover:text-white',
            ]"
        >
            {{ t('nav.more') }}
            <svg
                class="mr-1 h-4 w-4 transition-transform"
                :class="{ 'rotate-180': open }"
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

        <Teleport to="body">
            <div v-if="open" class="fixed inset-0 z-[9998]" @click="close"></div>
            <div
                v-if="open"
                class="fixed z-[9999] min-w-[13rem] rounded-2xl border border-slate-200 bg-white/95 py-1 shadow-xl backdrop-blur dark:border-slate-700 dark:bg-slate-900/95 dark:shadow-black/40"
                :style="panelStyle"
                @click.stop
            >
                <Link
                    v-for="item in items"
                    :key="item.slug"
                    :href="item.href"
                    :class="[
                        'block w-full px-4 py-2 text-right text-sm leading-5 transition duration-150 ease-in-out',
                        isCurrent(item.route_name)
                            ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white'
                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-700 dark:hover:text-white',
                    ]"
                    @click="close"
                >
                    {{ navPageLabel(item, t, te) }}
                </Link>
            </div>
        </Teleport>
    </div>
</template>

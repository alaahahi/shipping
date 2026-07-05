<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Link } from '@inertiajs/inertia-vue3';

defineProps({
    active: {
        type: Boolean,
        default: false,
    },
});

const open = ref(false);
const triggerRef = ref(null);
const panelStyle = ref({ top: '0px', left: '0px' });

const items = computed(() => [
    { href: route('sync.monitor'), label: '🔄 المزامنة', name: 'sync.monitor' },
    { href: route('online_contracts'), label: 'العقود الالكترونية', name: 'online_contracts' },
    { href: route('car_check'), label: 'مراجعة السيارات', name: 'car_check' },
    { href: route('damage_report.index'), label: 'تقارير الضرر', name: 'damage_report.index' },
    { href: route('hunter'), label: 'عاطل', name: 'hunter' },
    { href: route('systemSettings'), label: 'إعدادات النظام', name: 'systemSettings' },
    { href: route('logViewer'), label: '📋 لوغ الأخطاء', name: 'logViewer' },
]);

function isCurrent(name) {
    return route().current(name);
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
                'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out select-none',
                active
                    ? 'border-indigo-400 text-gray-900 dark:text-gray-200'
                    : 'border-transparent text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-100 hover:border-gray-300',
            ]"
        >
            المزيد
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
                class="fixed z-[9999] min-w-[13rem] rounded-md border border-gray-200 bg-white py-1 shadow-xl dark:border-gray-700 dark:bg-gray-800"
                :style="panelStyle"
                @click.stop
            >
                <Link
                    v-for="item in items"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        'block w-full px-4 py-2 text-right text-sm leading-5 transition duration-150 ease-in-out',
                        isCurrent(item.name)
                            ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-100'
                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700',
                    ]"
                    @click="close"
                >
                    {{ item.label }}
                </Link>
            </div>
        </Teleport>
    </div>
</template>

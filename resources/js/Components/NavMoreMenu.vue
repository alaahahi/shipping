<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/inertia-vue3';

defineProps({
    active: {
        type: Boolean,
        default: false,
    },
});

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
</script>

<template>
    <details class="nav-more group relative shrink-0">
        <summary
            :class="[
                'inline-flex cursor-pointer list-none items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out select-none',
                active
                    ? 'border-indigo-400 text-gray-900 dark:text-gray-200'
                    : 'border-transparent text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-100 hover:border-gray-300',
            ]"
        >
            المزيد
            <svg
                class="mr-1 h-4 w-4 transition-transform group-open:rotate-180"
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
        </summary>

        <div
            class="nav-more-panel absolute right-0 top-full z-[9999] mt-2 min-w-[13rem] rounded-md border border-gray-200 bg-white py-1 shadow-xl dark:border-gray-700 dark:bg-gray-800"
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
            >
                {{ item.label }}
            </Link>
        </div>
    </details>
</template>

<style scoped>
.nav-more summary::-webkit-details-marker {
    display: none;
}

.nav-more:not([open]) .nav-more-panel {
    display: none;
}
</style>

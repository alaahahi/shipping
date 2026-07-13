<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/inertia-vue3';

const props = defineProps(['href', 'active']);

const classes = computed(() => props.active
    ? 'block pl-3 pr-4 py-2 border-l-4 border-indigo-400 dark:border-indigo-300 text-base font-medium text-indigo-700 dark:text-indigo-200 bg-indigo-50 dark:bg-indigo-950/50 focus:outline-none focus:text-indigo-800 dark:focus:text-indigo-100 focus:bg-indigo-100 dark:focus:bg-indigo-900/60 focus:border-indigo-700 dark:focus:border-indigo-300 transition duration-150 ease-in-out'
    : 'block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-500 focus:outline-none focus:text-gray-800 dark:focus:text-white focus:bg-gray-50 dark:focus:bg-gray-800 focus:border-gray-300 dark:focus:border-gray-500 transition duration-150 ease-in-out'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>

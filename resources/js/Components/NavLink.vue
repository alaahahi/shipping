<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/inertia-vue3';

const props = defineProps(['href', 'active']);

const classes = computed(() => props.active
    ? 'inline-flex items-center px-2 pt-1 border-b-2 border-indigo-400 dark:border-indigo-300 text-sm font-medium leading-5 text-gray-900 dark:text-white focus:outline-none focus:border-indigo-700 dark:focus:border-indigo-300 transition duration-150 ease-in-out whitespace-nowrap'
    : 'inline-flex items-center px-2 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-600 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white hover:border-gray-300 dark:hover:border-gray-400 focus:outline-none focus:text-gray-900 dark:focus:text-white focus:border-gray-300 dark:focus:border-gray-400 transition duration-150 ease-in-out whitespace-nowrap'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>

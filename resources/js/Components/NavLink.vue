<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/inertia-vue3';

const props = defineProps(['href', 'active']);

const classes = computed(() => props.active
    ? 'inline-flex items-center px-2 pt-1 border-b-2 border-indigo-400 dark:border-white text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-indigo-700 dark:focus:border-white transition duration-150 ease-in-out whitespace-nowrap'
    : 'inline-flex items-center px-2 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-100 hover:border-gray-300 dark:hover:border-white focus:outline-none focus:text-gray-700 dark:focus:text-gray-100 focus:border-gray-300 dark:focus:border-white transition duration-150 ease-in-out whitespace-nowrap'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>

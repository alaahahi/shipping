<script setup>
import { Link } from '@inertiajs/inertia-vue3';

defineProps({
    href: {
        type: String,
        default: '#',
    },
    method: {
        type: String,
        default: 'get',
    },
    as: {
        type: String,
        default: 'a',
    },
});

const linkClass =
    'block w-full px-4 py-2 text-right text-sm leading-5 text-gray-700 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 transition duration-150 ease-in-out';
</script>

<template>
    <button
        v-if="as === 'button' && (!href || href === '#')"
        type="button"
        :class="linkClass"
    >
        <slot />
    </button>
    <Link
        v-else
        :href="href"
        :method="method"
        :as="as"
        :class="linkClass"
    >
        <slot />
    </Link>
</template>

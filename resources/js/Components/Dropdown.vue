<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    align: {
        default: 'right'
    },
    width: {
        default: '48'
    },
    contentClasses: {
        default: () => ['py-1', 'bg-white']
    }
});

const open = ref(false);

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const widthClass = computed(() => {
    return {
        '48': 'w-48',
    }[props.width.toString()];
});

const alignmentClasses = computed(() => {
    if (props.align === 'left') {
        return 'origin-top-left left-0';
    } else if (props.align === 'right') {
        return 'origin-top-right right-0';
    }
    return 'origin-top';
});

function toggle() {
    open.value = !open.value;
}

function close() {
    open.value = false;
}
</script>

<template>
    <div class="relative">
        <div @click.stop="toggle">
            <slot name="trigger" />
        </div>

        <div v-if="open" class="fixed inset-0 z-40" @click="close"></div>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="open"
                class="absolute z-50 mt-2 rounded-md shadow-lg"
                :class="[widthClass, alignmentClasses]"
                @click.stop
            >
                <div class="rounded-md ring-1 ring-black ring-opacity-5 dark:ring-gray-600" :class="contentClasses">
                    <slot name="content" />
                </div>
            </div>
        </Transition>
    </div>
</template>

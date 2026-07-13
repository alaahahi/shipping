<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    align: {
        default: 'right'
    },
    width: {
        default: '48'
    },
    contentClasses: {
        default: () => ['py-1', 'bg-white', 'dark:bg-gray-800']
    }
});

const open = ref(false);
const triggerRef = ref(null);
const panelStyle = ref({ top: '0px', left: '0px' });

const widthClass = computed(() => {
    return {
        '48': 'w-48',
    }[props.width.toString()];
});

const panelWidthPx = computed(() => {
    return {
        '48': 192,
    }[props.width.toString()] ?? 192;
});

function updatePosition() {
    const el = triggerRef.value;
    if (!el) {
        return;
    }

    const rect = el.getBoundingClientRect();
    let left = rect.left;

    if (props.align === 'right') {
        left = rect.right - panelWidthPx.value;
    }

    const maxLeft = window.innerWidth - panelWidthPx.value - 8;
    left = Math.max(8, Math.min(left, maxLeft));

    panelStyle.value = {
        top: `${rect.bottom + 8}px`,
        left: `${left}px`,
    };
}

function toggle() {
    open.value = !open.value;
}

function close() {
    open.value = false;
}

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        close();
    }
};

const closeOnScrollOrResize = () => {
    if (open.value) {
        close();
    }
};

watch(open, async (isOpen) => {
    if (isOpen) {
        await nextTick();
        updatePosition();
    }
});

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
    window.addEventListener('resize', closeOnScrollOrResize);
    window.addEventListener('scroll', closeOnScrollOrResize, true);
});

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    window.removeEventListener('resize', closeOnScrollOrResize);
    window.removeEventListener('scroll', closeOnScrollOrResize, true);
});
</script>

<template>
    <div class="relative">
        <div ref="triggerRef" @click.stop="toggle">
            <slot name="trigger" />
        </div>

        <Teleport to="body">
            <div v-if="open" class="fixed inset-0 z-[100]" @click="close"></div>

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
                    class="fixed z-[101] rounded-md shadow-lg"
                    :class="widthClass"
                    :style="panelStyle"
                    @click.stop
                >
                    <div
                        class="rounded-md ring-1 ring-black ring-opacity-5 dark:ring-gray-600"
                        :class="contentClasses"
                        @click="close"
                    >
                        <slot name="content" />
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

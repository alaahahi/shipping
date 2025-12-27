<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import { ref, onMounted, computed } from 'vue';
import { useToast } from "vue-toastification";
import axios from 'axios';

const toast = useToast();

const props = defineProps({
    license: Object,
    server: Object,
});

const licenseInfo = ref(props.license || {});
const serverInfo = ref(props.server || {});
const loading = ref(false);

onMounted(async () => {
    await refreshLicenseInfo();
});

const refreshLicenseInfo = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/license/status');
        if (response.data.success) {
            licenseInfo.value = response.data.license;
            serverInfo.value = response.data.server;
        }
    } catch (error) {
        console.error('Failed to get license status:', error);
    } finally {
        loading.value = false;
    }
};

const isActivated = computed(() => licenseInfo.value.activated === true);
const isValid = computed(() => licenseInfo.value.valid === true);
const isExpired = computed(() => {
    if (!licenseInfo.value.expires_at) return false;
    return new Date(licenseInfo.value.expires_at) < new Date();
});

const daysRemaining = computed(() => {
    if (!licenseInfo.value.days_remaining) return null;
    return licenseInfo.value.days_remaining;
});

const licenseTypeLabel = computed(() => {
    const types = {
        trial: 'تجريبي',
        standard: 'قياسي',
        premium: 'مميز'
    };
    return types[licenseInfo.value.type] || licenseInfo.value.type || 'غير محدد';
});

const statusColor = computed(() => {
    if (!isActivated.value) return 'red';
    if (isExpired.value) return 'red';
    if (daysRemaining.value !== null && daysRemaining.value < 30) return 'yellow';
    return 'green';
});

const statusText = computed(() => {
    if (!isActivated.value) return 'غير مفعل';
    if (isExpired.value) return 'منتهي الصلاحية';
    if (daysRemaining.value === null) return 'دائم';
    if (daysRemaining.value < 30) return 'ينتهي قريباً';
    return 'مفعل وصالح';
});
</script>

<template>
    <GuestLayout>
        <Head title="حالة الترخيص" />
        
        <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl w-full space-y-8">
                <div>
                    <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                        📊 حالة الترخيص
                    </h2>
                    <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                        عرض معلومات الترخيص الحالي
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-8">
                    <!-- حالة الترخيص -->
                    <div class="mb-6">
                        <div :class="[
                            'p-4 rounded-lg border-2',
                            statusColor === 'green' ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : '',
                            statusColor === 'yellow' ? 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800' : '',
                            statusColor === 'red' ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' : '',
                        ]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold" :class="[
                                        statusColor === 'green' ? 'text-green-800 dark:text-green-300' : '',
                                        statusColor === 'yellow' ? 'text-yellow-800 dark:text-yellow-300' : '',
                                        statusColor === 'red' ? 'text-red-800 dark:text-red-300' : '',
                                    ]">
                                        {{ statusText }}
                                    </h3>
                                    <p class="text-sm mt-1" :class="[
                                        statusColor === 'green' ? 'text-green-600 dark:text-green-400' : '',
                                        statusColor === 'yellow' ? 'text-yellow-600 dark:text-yellow-400' : '',
                                        statusColor === 'red' ? 'text-red-600 dark:text-red-400' : '',
                                    ]">
                                        <span v-if="isActivated && isValid">✅ الترخيص مفعل وصالح</span>
                                        <span v-else-if="isActivated && isExpired">❌ الترخيص منتهي الصلاحية</span>
                                        <span v-else>⚠️ الترخيص غير مفعل</span>
                                    </p>
                                </div>
                                <div class="text-4xl">
                                    <span v-if="statusColor === 'green'">✅</span>
                                    <span v-else-if="statusColor === 'yellow'">⚠️</span>
                                    <span v-else>❌</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- معلومات الترخيص -->
                    <div v-if="isActivated" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">النوع</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ licenseTypeLabel }}
                                </p>
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">الحالة</p>
                                <p class="text-sm font-semibold" :class="[
                                    isValid ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                                ]">
                                    {{ isValid ? 'صالح' : 'غير صالح' }}
                                </p>
                            </div>
                        </div>

                        <div v-if="licenseInfo.expires_at" class="p-3 bg-gray-50 dark:bg-gray-700 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">ينتهي في</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ new Date(licenseInfo.expires_at).toLocaleDateString('ar-SA') }}
                            </p>
                        </div>

                        <div v-if="daysRemaining !== null" class="p-3 bg-gray-50 dark:bg-gray-700 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">الأيام المتبقية</p>
                            <p class="text-sm font-semibold" :class="[
                                daysRemaining < 30 ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-900 dark:text-white'
                            ]">
                                {{ daysRemaining }} يوم
                            </p>
                        </div>

                        <div v-if="licenseInfo.activated_at" class="p-3 bg-gray-50 dark:bg-gray-700 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">مفعل منذ</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ new Date(licenseInfo.activated_at).toLocaleDateString('ar-SA') }}
                            </p>
                        </div>

                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Domain</p>
                            <p class="text-sm font-mono text-gray-900 dark:text-white">
                                {{ licenseInfo.domain || serverInfo.domain }}
                            </p>
                        </div>
                    </div>

                    <!-- رسالة عدم التفعيل -->
                    <div v-else class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg text-center">
                        <p class="text-gray-600 dark:text-gray-400">
                            ⚠️ الترخيص غير مفعل. يرجى تفعيل الترخيص للاستمرار.
                        </p>
                    </div>

                    <!-- الأزرار -->
                    <div class="mt-6 flex items-center justify-between gap-4">
                        <Link
                            :href="route('license.activate')"
                            class="flex-1"
                        >
                            <PrimaryButton class="w-full">
                                {{ isActivated ? 'تجديد الترخيص' : 'تفعيل الترخيص' }}
                            </PrimaryButton>
                        </Link>

                        <PrimaryButton
                            @click="refreshLicenseInfo"
                            :disabled="loading"
                            class="flex-1"
                        >
                            <span v-if="loading">جاري التحديث...</span>
                            <span v-else>🔄 تحديث</span>
                        </PrimaryButton>
                    </div>

                    <!-- معلومات السيرفر -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                            معلومات السيرفر
                        </h4>
                        <div class="space-y-2 text-xs text-gray-600 dark:text-gray-400">
                            <p><strong>Domain:</strong> {{ serverInfo.domain }}</p>
                            <p><strong>Fingerprint:</strong> <code class="font-mono">{{ serverInfo.fingerprint?.substring(0, 20) }}...</code></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>


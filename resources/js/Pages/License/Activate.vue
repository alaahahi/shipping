<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/inertia-vue3';
import { ref, onMounted } from 'vue';
import { useToast } from "vue-toastification";
import axios from 'axios';

const toast = useToast();

const props = defineProps({
    license: Object,
    server: Object,
});

const form = useForm({
    license_key: '',
    domain: props.server?.domain || '',
    fingerprint: props.server?.fingerprint || '',
});

const loading = ref(false);
const serverInfo = ref({
    domain: props.server?.domain || '',
    fingerprint: props.server?.fingerprint || '',
});

onMounted(async () => {
    // الحصول على معلومات السيرفر إذا لم تكن موجودة
    if (!serverInfo.value.domain || !serverInfo.value.fingerprint) {
        try {
            const response = await axios.get('/api/license/server-info');
            if (response.data.success) {
                serverInfo.value = {
                    domain: response.data.domain,
                    fingerprint: response.data.fingerprint,
                };
                form.domain = response.data.domain;
                form.fingerprint = response.data.fingerprint;
            }
        } catch (error) {
            console.error('Failed to get server info:', error);
        }
    }
});

const submit = async () => {
    loading.value = true;
    
    form.post(route('license.activate.post'), {
        onSuccess: (page) => {
            toast.success('تم تفعيل الترخيص بنجاح!');
            // إعادة تحميل الصفحة بعد ثانية
            setTimeout(() => {
                window.location.href = route('dashboard');
            }, 1500);
        },
        onError: (errors) => {
            toast.error(errors.license_key || errors.message || 'فشل تفعيل الترخيص');
            loading.value = false;
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text).then(() => {
        toast.success('تم النسخ!');
    }).catch(() => {
        toast.error('فشل النسخ');
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="تفعيل الترخيص" />
        
        <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl w-full space-y-8">
                <div>
                    <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                        🔑 تفعيل المنتج
                    </h2>
                    <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                        أدخل مفتاح الترخيص لتفعيل المنتج
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-8">
                    <!-- معلومات السيرفر -->
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">
                            معلومات السيرفر
                        </h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Domain:</span>
                                <div class="flex items-center gap-2">
                                    <code class="text-sm font-mono bg-gray-200 dark:bg-gray-600 px-2 py-1 rounded">
                                        {{ serverInfo.domain }}
                                    </code>
                                    <button
                                        @click="copyToClipboard(serverInfo.domain)"
                                        class="text-blue-500 hover:text-blue-700 text-sm"
                                        title="نسخ"
                                    >
                                        📋
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Fingerprint:</span>
                                <div class="flex items-center gap-2">
                                    <code class="text-sm font-mono bg-gray-200 dark:bg-gray-600 px-2 py-1 rounded max-w-xs truncate">
                                        {{ serverInfo.fingerprint }}
                                    </code>
                                    <button
                                        @click="copyToClipboard(serverInfo.fingerprint)"
                                        class="text-blue-500 hover:text-blue-700 text-sm"
                                        title="نسخ"
                                    >
                                        📋
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- حالة الترخيص الحالية -->
                    <div v-if="license?.activated" class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                        <div class="flex items-center">
                            <span class="text-yellow-600 dark:text-yellow-400">⚠️</span>
                            <p class="ml-2 text-sm text-yellow-800 dark:text-yellow-300">
                                يوجد ترخيص مفعل حالياً. إدخال مفتاح جديد سيستبدل الترخيص الحالي.
                            </p>
                        </div>
                    </div>

                    <!-- نموذج التفعيل -->
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="license_key" value="مفتاح الترخيص" />
                            <TextInput
                                id="license_key"
                                type="text"
                                class="mt-1 block w-full font-mono text-sm"
                                v-model="form.license_key"
                                placeholder="أدخل مفتاح الترخيص هنا..."
                                required
                                autofocus
                                :disabled="loading"
                            />
                            <InputError class="mt-2" :message="form.errors.license_key" />
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                💡 احصل على مفتاح الترخيص من المطور أو المسؤول
                            </p>
                        </div>

                        <div class="flex items-center justify-between">
                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing || loading }"
                                :disabled="form.processing || loading"
                            >
                                <span v-if="loading">جاري التفعيل...</span>
                                <span v-else>تفعيل الآن</span>
                            </PrimaryButton>

                            <a
                                :href="route('license.status')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >
                                عرض حالة الترخيص
                            </a>
                        </div>
                    </form>

                    <!-- معلومات إضافية -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                            💡 للمساعدة، يرجى التواصل مع الدعم الفني
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>


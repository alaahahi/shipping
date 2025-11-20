<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/inertia-vue3';
import { ref, onMounted, computed } from 'vue';
import { useToast } from "vue-toastification";
import axios from 'axios';

const toast = useToast();

const props = defineProps({
    licenses: Array,
});

const licenses = ref(props.licenses || []);
const loading = ref(false);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDetailsModal = ref(false);
const showLicenseKeyModal = ref(false);
const newLicenseKey = ref('');
const selectedLicense = ref(null);
const statistics = ref({
    total: 0,
    active: 0,
    inactive: 0,
    expired: 0,
    trial: 0,
    standard: 0,
    premium: 0,
});

const createForm = useForm({
    domain: '',
    type: 'standard',
    expires_at: '',
    max_installations: 1,
});

const editForm = useForm({
    type: 'standard',
    expires_at: '',
    is_active: true,
    notes: '',
});

const licenseTypeLabel = (type) => {
    const types = {
        trial: 'تجريبي',
        standard: 'قياسي',
        premium: 'مميز'
    };
    return types[type] || type;
};

const getStatusColor = (license) => {
    if (!license.is_active) return 'bg-gray-500';
    if (license.expires_at && new Date(license.expires_at) < new Date()) return 'bg-red-500';
    if (license.days_remaining !== null && license.days_remaining < 30) return 'bg-yellow-500';
    return 'bg-green-500';
};

const loadStatistics = async () => {
    try {
        const response = await axios.get('/api/admin/licenses/statistics');
        if (response.data.success) {
            statistics.value = response.data.statistics;
        }
    } catch (error) {
        console.error('Failed to load statistics:', error);
    }
};

const loadLicenses = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/admin/licenses');
        if (response.data.success) {
            licenses.value = response.data.licenses.map(license => ({
                ...license,
                activated_at: license.activated_at,
                expires_at: license.expires_at,
                days_remaining: license.days_remaining,
            }));
        }
    } catch (error) {
        toast.error('فشل تحميل الترخيصات');
    } finally {
        loading.value = false;
    }
};

const createLicense = async () => {
    loading.value = true;
    try {
        const response = await axios.post('/api/admin/licenses', createForm.data());
        if (response.data.success) {
            toast.success('تم إنشاء الترخيص بنجاح!');
            newLicenseKey.value = response.data.license_key;
            showCreateModal.value = false;
            showLicenseKeyModal.value = true;
            createForm.reset();
            await loadLicenses();
            await loadStatistics();
        }
    } catch (error) {
        toast.error(error.response?.data?.message || 'فشل إنشاء الترخيص');
    } finally {
        loading.value = false;
    }
};

const copyLicenseKey = async () => {
    try {
        await navigator.clipboard.writeText(newLicenseKey.value);
        toast.success('تم نسخ مفتاح الترخيص!');
    } catch (error) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = newLicenseKey.value;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        toast.success('تم نسخ مفتاح الترخيص!');
    }
};

const toggleLicense = async (id) => {
    try {
        const response = await axios.post(`/api/admin/licenses/${id}/toggle`);
        if (response.data.success) {
            toast.success(response.data.message);
            await loadLicenses();
            await loadStatistics();
        }
    } catch (error) {
        toast.error('فشل تحديث الترخيص');
    }
};

const deleteLicense = async (id) => {
    if (!confirm('هل أنت متأكد من حذف هذا الترخيص؟')) {
        return;
    }

    try {
        const response = await axios.delete(`/api/admin/licenses/${id}`);
        if (response.data.success) {
            toast.success('تم حذف الترخيص بنجاح');
            await loadLicenses();
            await loadStatistics();
        }
    } catch (error) {
        toast.error('فشل حذف الترخيص');
    }
};

const showDetails = async (id) => {
    try {
        const response = await axios.get(`/api/admin/licenses/${id}`);
        if (response.data.success) {
            selectedLicense.value = response.data.license;
            showDetailsModal.value = true;
        }
    } catch (error) {
        toast.error('فشل تحميل التفاصيل');
    }
};


const updateLicense = async () => {
    if (!selectedLicense.value) return;
    
    loading.value = true;
    editForm.type = selectedLicense.value.type;
    editForm.expires_at = selectedLicense.value.expires_at;
    editForm.is_active = selectedLicense.value.is_active;
    editForm.notes = selectedLicense.value.notes || '';

    try {
        const response = await axios.put(`/api/admin/licenses/${selectedLicense.value.id}`, editForm.data());
        if (response.data.success) {
            toast.success('تم تحديث الترخيص بنجاح');
            showEditModal.value = false;
            await loadLicenses();
            await loadStatistics();
        }
    } catch (error) {
        toast.error(error.response?.data?.message || 'فشل تحديث الترخيص');
    } finally {
        loading.value = false;
    }
};

const openEditModal = async (id) => {
    await showDetails(id);
    showEditModal.value = true;
};

onMounted(() => {
    loadStatistics();
});
</script>

<template>
    <Head title="إدارة الترخيصات" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
                إدارة الترخيصات
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- إحصائيات -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400">إجمالي الترخيصات</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.total }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400">مفعل</div>
                        <div class="text-2xl font-bold text-green-600">{{ statistics.active }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400">معطل</div>
                        <div class="text-2xl font-bold text-gray-600">{{ statistics.inactive }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400">منتهي</div>
                        <div class="text-2xl font-bold text-red-600">{{ statistics.expired }}</div>
                    </div>
                </div>

                <!-- الأزرار -->
                <div class="mb-6 flex justify-between items-center">
                    <PrimaryButton @click="showCreateModal = true">
                        ➕ إنشاء ترخيص جديد
                    </PrimaryButton>
                    <PrimaryButton @click="loadLicenses" :disabled="loading">
                        🔄 تحديث
                    </PrimaryButton>
                </div>

                <!-- الجدول -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Domain</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">النوع</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الحالة</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ينتهي في</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">مفعل منذ</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="license in licenses" :key="license.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                            <code class="text-xs">{{ license.domain }}</code>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                            {{ licenseTypeLabel(license.type) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <span :class="['px-2 py-1 rounded text-xs', getStatusColor(license)]" class="text-white">
                                                {{ license.is_active ? 'مفعل' : 'معطل' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                            {{ license.expires_at || 'دائم' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                            {{ license.activated_at || '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <div class="flex gap-2">
                                                <button
                                                    @click="showDetails(license.id)"
                                                    class="text-blue-600 hover:text-blue-800"
                                                    title="التفاصيل"
                                                >
                                                    👁️
                                                </button>
                                                <button
                                                    @click="openEditModal(license.id)"
                                                    class="text-yellow-600 hover:text-yellow-800"
                                                    title="تعديل"
                                                >
                                                    ✏️
                                                </button>
                                                <button
                                                    @click="toggleLicense(license.id)"
                                                    :class="license.is_active ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800'"
                                                    :title="license.is_active ? 'إلغاء التفعيل' : 'تفعيل'"
                                                >
                                                    {{ license.is_active ? '⏸️' : '▶️' }}
                                                </button>
                                                <button
                                                    @click="deleteLicense(license.id)"
                                                    class="text-red-600 hover:text-red-800"
                                                    title="حذف"
                                                >
                                                    🗑️
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="licenses.length === 0">
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                            لا توجد تراخيص
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal إنشاء ترخيص -->
        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <template #header>
                <h3 class="text-lg font-semibold">إنشاء ترخيص جديد</h3>
            </template>
            <template #body>
                <form @submit.prevent class="space-y-4">
                    <div>
                        <InputLabel for="domain" value="Domain" />
                        <TextInput
                            id="domain"
                            v-model="createForm.domain"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="example.com"
                            required
                        />
                        <InputError :message="createForm.errors.domain" />
                    </div>

                    <div>
                        <InputLabel for="type" value="النوع" />
                        <select
                            id="type"
                            v-model="createForm.type"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                            required
                        >
                            <option value="trial">تجريبي</option>
                            <option value="standard">قياسي</option>
                            <option value="premium">مميز</option>
                        </select>
                    </div>

                    <div>
                        <InputLabel for="expires_at" value="ينتهي في (اختياري)" />
                        <TextInput
                            id="expires_at"
                            v-model="createForm.expires_at"
                            type="date"
                            class="mt-1 block w-full"
                        />
                    </div>

                    <div>
                        <InputLabel for="max_installations" value="عدد التثبيتات" />
                        <TextInput
                            id="max_installations"
                            v-model="createForm.max_installations"
                            type="number"
                            min="1"
                            class="mt-1 block w-full"
                        />
                    </div>

                </form>
            </template>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <PrimaryButton type="button" @click="showCreateModal = false">
                        إلغاء
                    </PrimaryButton>
                    <PrimaryButton type="button" @click="createLicense" :disabled="loading">
                        إنشاء
                    </PrimaryButton>
                </div>
            </template>
        </Modal>

        <!-- Modal تعديل ترخيص -->
        <Modal :show="showEditModal" @close="showEditModal = false" v-if="selectedLicense">
            <template #header>
                <h3 class="text-lg font-semibold">تعديل الترخيص</h3>
            </template>
            <template #body>
                <form @submit.prevent class="space-y-4">
                    <div>
                        <InputLabel for="edit_type" value="النوع" />
                        <select
                            id="edit_type"
                            v-model="selectedLicense.type"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >
                            <option value="trial">تجريبي</option>
                            <option value="standard">قياسي</option>
                            <option value="premium">مميز</option>
                        </select>
                    </div>

                    <div>
                        <InputLabel for="edit_expires_at" value="ينتهي في" />
                        <TextInput
                            id="edit_expires_at"
                            v-model="selectedLicense.expires_at"
                            type="datetime-local"
                            class="mt-1 block w-full"
                        />
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="selectedLicense.is_active"
                                class="rounded border-gray-300"
                            />
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">مفعل</span>
                        </label>
                    </div>

                    <div>
                        <InputLabel for="notes" value="ملاحظات" />
                        <textarea
                            id="notes"
                            v-model="selectedLicense.notes"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                            rows="3"
                        ></textarea>
                    </div>

                </form>
            </template>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <PrimaryButton type="button" @click="showEditModal = false">
                        إلغاء
                    </PrimaryButton>
                    <PrimaryButton type="button" @click="updateLicense" :disabled="loading">
                        حفظ
                    </PrimaryButton>
                </div>
            </template>
        </Modal>

        <!-- Modal التفاصيل -->
        <Modal :show="showDetailsModal" @close="showDetailsModal = false" v-if="selectedLicense">
            <template #header>
                <h3 class="text-lg font-semibold">تفاصيل الترخيص</h3>
            </template>
            <template #body>
                <div class="space-y-3">
                    <div>
                        <strong>Domain:</strong> {{ selectedLicense.domain }}
                    </div>
                    <div>
                        <strong>Fingerprint:</strong> 
                        <code class="text-xs">{{ selectedLicense.fingerprint }}</code>
                    </div>
                    <div>
                        <strong>النوع:</strong> {{ licenseTypeLabel(selectedLicense.type) }}
                    </div>
                    <div>
                        <strong>الحالة:</strong> 
                        <span :class="['px-2 py-1 rounded text-xs', getStatusColor(selectedLicense)]" class="text-white">
                            {{ selectedLicense.is_active ? 'مفعل' : 'معطل' }}
                        </span>
                    </div>
                    <div>
                        <strong>ينتهي في:</strong> {{ selectedLicense.expires_at || 'دائم' }}
                    </div>
                    <div>
                        <strong>الأيام المتبقية:</strong> {{ selectedLicense.days_remaining ?? '∞' }}
                    </div>
                    <div>
                        <strong>مفعل منذ:</strong> {{ selectedLicense.activated_at || '-' }}
                    </div>
                    <div>
                        <strong>آخر تحقق:</strong> {{ selectedLicense.last_verified_at || '-' }}
                    </div>
                    <div v-if="selectedLicense.max_installations">
                        <strong>عدد التثبيتات المسموح:</strong> {{ selectedLicense.max_installations }}
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end">
                    <PrimaryButton @click="showDetailsModal = false">
                        إغلاق
                    </PrimaryButton>
                </div>
            </template>
        </Modal>

        <!-- Modal عرض مفتاح الترخيص -->
        <Modal :show="showLicenseKeyModal" @close="showLicenseKeyModal = false">
            <template #header>
                <h3 class="text-lg font-semibold">مفتاح الترخيص</h3>
            </template>
            <template #body>
                <div class="space-y-4">
                    <div>
                        <InputLabel value="انسخ هذا المفتاح واحفظه في مكان آمن" />
                        <div class="mt-2 flex gap-2">
                            <TextInput
                                :value="newLicenseKey"
                                type="text"
                                readonly
                                class="flex-1 font-mono text-sm"
                            />
                            <PrimaryButton @click="copyLicenseKey" type="button">
                                📋 نسخ
                            </PrimaryButton>
                        </div>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            ⚠️ لن تتمكن من رؤية هذا المفتاح مرة أخرى بعد إغلاق هذه النافذة
                        </p>
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end">
                    <PrimaryButton @click="showLicenseKeyModal = false">
                        تم
                    </PrimaryButton>
                </div>
            </template>
        </Modal>
    </AuthenticatedLayout>
</template>


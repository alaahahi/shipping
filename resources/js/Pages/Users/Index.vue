<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import { computed, onMounted, ref, watch } from 'vue';
import { TailwindPagination } from 'laravel-vue-pagination';
import { useToast } from 'vue-toastification';
import axios from 'axios';

const toast = useToast();

const props = defineProps({
    url: String,
    usersType: { type: Array, default: () => [] },
    typeLabels: { type: Object, default: () => ({}) },
});

const laravelData = ref({ data: [], links: [], total: 0 });
const loading = ref(false);
const search = ref('');
const filterTypeId = ref('');
const showEditModal = ref(false);
const saving = ref(false);
const editErrors = ref({});

const editForm = ref({
    id: null,
    name: '',
    email: '',
    phone: '',
    organizer_name: '',
    type_id: '',
    password: '',
    password_confirmation: '',
});

let searchTimer = null;

const typeLabel = (name) => props.typeLabels?.[name] || name || '—';

const canEditUser = (user) => user?.email !== 'admin@admin.com';

const getResults = async (page = 1) => {
    loading.value = true;
    try {
        const params = new URLSearchParams({ page: String(page) });
        if (search.value.trim()) params.set('q', search.value.trim());
        if (filterTypeId.value) params.set('type_id', String(filterTypeId.value));
        const response = await fetch(`/getIndex?${params.toString()}`);
        laravelData.value = await response.json();
    } catch (e) {
        toast.error('تعذر تحميل المستخدمين', { timeout: 3000, position: 'bottom-right', rtl: true });
    } finally {
        loading.value = false;
    }
};

watch([filterTypeId], () => getResults(1));

watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => getResults(1), 350);
});

onMounted(() => getResults());

function openEdit(user) {
    if (!canEditUser(user)) {
        toast.error('لا يمكن تعديل حساب المدير الرئيسي من هنا', { timeout: 3000, position: 'bottom-right', rtl: true });
        return;
    }
    editErrors.value = {};
    editForm.value = {
        id: user.id,
        name: user.name || '',
        email: user.email || '',
        phone: user.phone || '',
        organizer_name: user.organizer_name || '',
        type_id: user.type_id || user.user_type?.id || '',
        password: '',
        password_confirmation: '',
    };
    showEditModal.value = true;
}

function closeEdit() {
    showEditModal.value = false;
    editErrors.value = {};
}

async function saveEdit() {
    if (!editForm.value.id) return;

    if (editForm.value.password && editForm.value.password !== editForm.value.password_confirmation) {
        editErrors.value = { password: 'تأكيد كلمة المرور غير مطابق' };
        return;
    }

    saving.value = true;
    editErrors.value = {};

    const payload = {
        name: editForm.value.name,
        email: editForm.value.email,
        phone: editForm.value.phone || null,
        organizer_name: editForm.value.organizer_name || null,
        type_id: Number(editForm.value.type_id),
    };

    if (editForm.value.password) {
        payload.password = editForm.value.password;
    }

    try {
        await axios.put(route('users.update', editForm.value.id), payload);
        toast.success('تم حفظ التعديلات', { timeout: 2500, position: 'bottom-right', rtl: true });
        closeEdit();
        await getResults(laravelData.value.current_page || 1);
    } catch (error) {
        const errors = error?.response?.data?.errors;
        if (errors) {
            editErrors.value = Object.fromEntries(
                Object.entries(errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
            );
        } else {
            toast.error(error?.response?.data?.message || 'فشل حفظ التعديلات', {
                timeout: 3000,
                position: 'bottom-right',
                rtl: true,
            });
        }
    } finally {
        saving.value = false;
    }
}

async function ban(id) {
    if (!confirm('تأكيد تقييد هذا المستخدم؟')) return;
    try {
        await axios.get(route('ban', id));
        toast.success('تم تقييد المستخدم', { timeout: 2500, position: 'bottom-right', rtl: true });
        await getResults(laravelData.value.current_page || 1);
    } catch (e) {
        toast.error('تعذر تقييد المستخدم', { timeout: 3000, position: 'bottom-right', rtl: true });
    }
}

async function unban(id) {
    try {
        await axios.get(route('unban', id));
        toast.success('تم إلغاء التقييد', { timeout: 2500, position: 'bottom-right', rtl: true });
        await getResults(laravelData.value.current_page || 1);
    } catch (e) {
        toast.error('تعذر إلغاء التقييد', { timeout: 3000, position: 'bottom-right', rtl: true });
    }
}

const usersCount = computed(() => laravelData.value?.total ?? laravelData.value?.data?.length ?? 0);
</script>

<template>
    <Head title="إدارة المستخدمين" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
                        إدارة المستخدمين
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        موظفو النظام فقط — الزبائن تُدار من صفحة الزبائن
                    </p>
                </div>
                <Link
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 rounded-md transition"
                    :href="route('users.create')"
                >
                    + مستخدم جديد
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="md:col-span-2">
                            <InputLabel for="user-search" value="بحث" />
                            <TextInput
                                id="user-search"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="search"
                                placeholder="الاسم / اسم المستخدم / الهاتف"
                            />
                        </div>
                        <div>
                            <InputLabel for="type-filter" value="الصلاحية" />
                            <select
                                id="type-filter"
                                v-model="filterTypeId"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm"
                            >
                                <option value="">الكل</option>
                                <option
                                    v-for="t in usersType"
                                    :key="t.id"
                                    :value="t.id"
                                >
                                    {{ typeLabel(t.name) }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        عدد النتائج: {{ usersCount }}
                        <span v-if="loading" class="ms-2 text-rose-500">جاري التحميل...</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-center">
                            <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-3 py-3 border-b dark:border-gray-700">#</th>
                                    <th class="px-3 py-3 border-b dark:border-gray-700">الاسم</th>
                                    <th class="px-3 py-3 border-b dark:border-gray-700">اسم المستخدم</th>
                                    <th class="px-3 py-3 border-b dark:border-gray-700">الصلاحية</th>
                                    <th class="px-3 py-3 border-b dark:border-gray-700">الهاتف</th>
                                    <th class="px-3 py-3 border-b dark:border-gray-700">الرصيد</th>
                                    <th class="px-3 py-3 border-b dark:border-gray-700">الحالة</th>
                                    <th class="px-3 py-3 border-b dark:border-gray-700">تنفيذ</th>
                                </tr>
                            </thead>
                            <tbody class="dark:text-gray-200">
                                <tr v-if="!loading && !(laravelData.data || []).length">
                                    <td colspan="8" class="px-4 py-10 text-gray-500">لا يوجد مستخدمون</td>
                                </tr>
                                <tr
                                    v-for="user in laravelData.data"
                                    :key="user.id"
                                    class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                >
                                    <td class="px-3 py-3">{{ user.id }}</td>
                                    <td class="px-3 py-3 font-medium">{{ user.name }}</td>
                                    <td class="px-3 py-3">
                                        <div>{{ user.email }}</div>
                                        <div v-if="user.device" class="text-xs text-emerald-500 mt-0.5">{{ user.device }}</div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs bg-slate-100 dark:bg-slate-700">
                                            {{ typeLabel(user.user_type?.name) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3">{{ user.phone || '—' }}</td>
                                    <td class="px-3 py-3 tabular-nums">{{ user.wallet?.balance ?? 0 }}</td>
                                    <td class="px-3 py-3">
                                        <span
                                            v-if="user.is_band"
                                            class="inline-flex px-2 py-0.5 rounded text-xs bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200"
                                        >
                                            مقيّد
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200"
                                        >
                                            نشط
                                        </span>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex flex-wrap items-center justify-center gap-1">
                                            <button
                                                v-if="canEditUser(user)"
                                                type="button"
                                                class="px-2.5 py-1 text-xs text-white bg-slate-600 hover:bg-slate-700 rounded"
                                                @click="openEdit(user)"
                                            >
                                                تعديل / صلاحية
                                            </button>
                                            <button
                                                v-if="canEditUser(user) && !user.is_band"
                                                type="button"
                                                class="px-2.5 py-1 text-xs text-white bg-amber-500 hover:bg-amber-600 rounded"
                                                @click="ban(user.id)"
                                            >
                                                تقييد
                                            </button>
                                            <button
                                                v-if="canEditUser(user) && user.is_band"
                                                type="button"
                                                class="px-2.5 py-1 text-xs text-white bg-emerald-600 hover:bg-emerald-700 rounded"
                                                @click="unban(user.id)"
                                            >
                                                إلغاء التقييد
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 text-center" style="direction: ltr;">
                        <TailwindPagination
                            :data="laravelData"
                            @pagination-change-page="getResults"
                            :limit="10"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit modal: profile + password + role -->
        <Teleport to="body">
            <div
                v-if="showEditModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
                @click.self="closeEdit"
            >
                <div class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-lg shadow-xl max-h-[90vh] overflow-y-auto">
                    <div class="px-5 py-4 border-b dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">تعديل مستخدم</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-600" @click="closeEdit">✕</button>
                    </div>

                    <form class="p-5 space-y-4" @submit.prevent="saveEdit">
                        <div>
                            <InputLabel for="edit-name" value="الاسم" />
                            <TextInput id="edit-name" type="text" class="mt-1 block w-full" v-model="editForm.name" required />
                            <p v-if="editErrors.name" class="mt-1 text-sm text-red-600">{{ editErrors.name }}</p>
                        </div>

                        <div>
                            <InputLabel for="edit-email" value="اسم المستخدم" />
                            <TextInput id="edit-email" type="text" class="mt-1 block w-full" v-model="editForm.email" required />
                            <p v-if="editErrors.email" class="mt-1 text-sm text-red-600">{{ editErrors.email }}</p>
                        </div>

                        <div>
                            <InputLabel for="edit-phone" value="رقم الهاتف" />
                            <TextInput id="edit-phone" type="text" class="mt-1 block w-full" v-model="editForm.phone" />
                            <p v-if="editErrors.phone" class="mt-1 text-sm text-red-600">{{ editErrors.phone }}</p>
                        </div>

                        <div>
                            <InputLabel for="edit-organizer" value="اسم منظم العقد" />
                            <TextInput
                                id="edit-organizer"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="editForm.organizer_name"
                                placeholder="يظهر في توقيع العقود"
                            />
                        </div>

                        <div>
                            <InputLabel for="edit-type" value="الصلاحية" />
                            <select
                                id="edit-type"
                                v-model="editForm.type_id"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm"
                            >
                                <option disabled value="">اختر الصلاحية</option>
                                <option v-for="t in usersType" :key="t.id" :value="t.id">
                                    {{ typeLabel(t.name) }}
                                </option>
                            </select>
                            <p v-if="editErrors.type_id || editErrors.userType" class="mt-1 text-sm text-red-600">
                                {{ editErrors.type_id || editErrors.userType }}
                            </p>
                        </div>

                        <div class="rounded-md border border-dashed border-gray-300 dark:border-gray-600 p-3 space-y-3">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                تغيير كلمة المرور (اتركه فارغاً للإبقاء على الحالية)
                            </p>
                            <div>
                                <InputLabel for="edit-password" value="كلمة المرور الجديدة" />
                                <TextInput
                                    id="edit-password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="editForm.password"
                                    autocomplete="new-password"
                                />
                                <p v-if="editErrors.password" class="mt-1 text-sm text-red-600">{{ editErrors.password }}</p>
                            </div>
                            <div>
                                <InputLabel for="edit-password-confirm" value="تأكيد كلمة المرور" />
                                <TextInput
                                    id="edit-password-confirm"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="editForm.password_confirmation"
                                    autocomplete="new-password"
                                />
                            </div>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button
                                type="button"
                                class="flex-1 px-4 py-2 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100"
                                @click="closeEdit"
                            >
                                إلغاء
                            </button>
                            <button
                                type="submit"
                                class="flex-1 px-4 py-2 rounded-md bg-rose-600 hover:bg-rose-700 text-white font-medium disabled:opacity-60"
                                :disabled="saving"
                            >
                                {{ saving ? 'جاري الحفظ...' : 'حفظ' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style>
.sr-only {
    display: none;
}
</style>

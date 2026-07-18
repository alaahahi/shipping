<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';

const props = defineProps({
    usersType: { type: Array, default: () => [] },
    typeLabels: { type: Object, default: () => ({}) },
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    userType: '',
    phone: '',
    organizer_name: '',
});

const typeLabel = (name) => props.typeLabels?.[name] || name || '—';

const submit = () => {
    form.post(route('users.store'));
};
</script>

<template>
    <Head title="إنشاء مستخدم" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
                إنشاء مستخدم
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <Link
                                class="px-4 py-2 text-white bg-gray-500 hover:bg-gray-600 rounded-md"
                                :href="route('users.index')"
                            >
                                العودة
                            </Link>
                        </div>

                        <form class="space-y-4" @submit.prevent="submit">
                            <div>
                                <InputLabel for="name" value="الاسم" />
                                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" autofocus />
                                <span class="text-red-600 text-sm" v-if="form.errors.name">{{ form.errors.name }}</span>
                            </div>

                            <div>
                                <InputLabel for="email" value="اسم المستخدم" />
                                <TextInput id="email" type="text" class="mt-1 block w-full" v-model="form.email" />
                                <span class="text-red-600 text-sm" v-if="form.errors.email">{{ form.errors.email }}</span>
                            </div>

                            <div>
                                <InputLabel for="password" value="كلمة المرور" />
                                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" autocomplete="new-password" />
                                <span class="text-red-600 text-sm" v-if="form.errors.password">{{ form.errors.password }}</span>
                            </div>

                            <div>
                                <InputLabel for="userType" value="الصلاحية" />
                                <select
                                    id="userType"
                                    v-model="form.userType"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm"
                                >
                                    <option disabled value="">اختر الصلاحية</option>
                                    <option v-for="t in usersType" :key="t.id" :value="t.id">
                                        {{ typeLabel(t.name) }}
                                    </option>
                                </select>
                                <span class="text-red-600 text-sm" v-if="form.errors.userType">{{ form.errors.userType }}</span>
                            </div>

                            <div>
                                <InputLabel for="phone" value="رقم الهاتف" />
                                <TextInput id="phone" type="text" class="mt-1 block w-full" v-model="form.phone" />
                            </div>

                            <div>
                                <InputLabel for="organizer_name" value="اسم منظم العقد" />
                                <TextInput
                                    id="organizer_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.organizer_name"
                                    placeholder="اختياري — يظهر في توقيع العقود"
                                />
                            </div>

                            <button
                                type="submit"
                                class="px-6 py-2 font-bold text-white bg-rose-600 hover:bg-rose-700 rounded disabled:opacity-60"
                                :disabled="form.processing"
                            >
                                حفظ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

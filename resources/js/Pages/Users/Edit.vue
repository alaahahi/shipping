<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';

const props = defineProps({
    user: Object,
    usersType: { type: Array, default: () => [] },
    typeLabels: { type: Object, default: () => ({}) },
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone ?? '',
    password: '',
    type_id: props.user.type_id,
    organizer_name: props.user.organizer_name ?? '',
});

const typeLabel = (name) => props.typeLabels?.[name] || name || '—';

const submit = () => {
    form.put(route('users.update', props.user.id));
};
</script>

<template>
    <Head title="تعديل مستخدم" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
                تعديل معلومات المستخدم
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
                                <InputLabel for="phone" value="رقم الهاتف" />
                                <TextInput id="phone" type="text" class="mt-1 block w-full" v-model="form.phone" />
                                <span class="text-red-600 text-sm" v-if="form.errors.phone">{{ form.errors.phone }}</span>
                            </div>

                            <div>
                                <InputLabel for="organizer_name" value="اسم منظم العقد" />
                                <TextInput
                                    id="organizer_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.organizer_name"
                                    placeholder="يظهر في توقيع العقود"
                                />
                            </div>

                            <div>
                                <InputLabel for="type_id" value="الصلاحية" />
                                <select
                                    id="type_id"
                                    v-model="form.type_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm"
                                >
                                    <option v-for="t in usersType" :key="t.id" :value="t.id">
                                        {{ typeLabel(t.name) }}
                                    </option>
                                </select>
                                <span class="text-red-600 text-sm" v-if="form.errors.type_id">{{ form.errors.type_id }}</span>
                            </div>

                            <div class="rounded-md border border-dashed border-gray-300 dark:border-gray-600 p-3">
                                <InputLabel for="password" value="كلمة المرور الجديدة (اختياري)" />
                                <TextInput
                                    id="password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="form.password"
                                    autocomplete="new-password"
                                    placeholder="اتركه فارغاً للإبقاء على الحالية"
                                />
                                <span class="text-red-600 text-sm" v-if="form.errors.password">{{ form.errors.password }}</span>
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

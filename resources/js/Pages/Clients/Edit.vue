<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Dropdown from '@/Components/Dropdown.vue';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';


const props = defineProps({
    user: Object,
    url:String,
    usersType: Array,
    userSeles:String,
    userHospital:String,
    userDoctor:String,
});

const form = useForm({
    name: props.user.name,
    phone: props.user.phone,
});

const submit = () => {
    form.put(route('users.update', props.user.id));
};

</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout v-if="$page.props.auth.user.type_id==1">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
                تعديل معلومات الزبون
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6  dark:bg-gray-900">

                        <div className="flex items-center justify-between mb-6">
                            <Link
                                className="px-6 py-2 text-white bg-gray-500 rounded-md focus:outline-none"
                                :href="route('users.index')"
                            >
                                العودة
                            </Link>
                        </div>

                        <form name="createForm" @submit.prevent="submit">
                                <div className="flex flex-col">
                                    <div className="mb-4">

                                    <InputLabel for="name" value="الأسم" />

                                    <TextInput 
                                        id="name" 
                                        type="text" 
                                        class="mt-1 block w-full" 
                                        v-model="form.name" 
                                        autofocus />

                                    <span className="text-red-600" v-if="form.errors.name">
                                        {{ form.errors.name }}
                                    </span>
                                    </div>
         
                                    <div class="mb-4">
                                    <InputLabel for="phone" :value="$t('phone')" />
                                    <TextInput 
                                        id="phone" 
                                        type="number" 
                                        class="mt-1 block w-full" 
                                        v-model="form.phone" 
                                    />
                                    </div>
                                </div>
  
                                <div className="mt-4">
                                    <button
                                        type="submit"
                                        className="px-6 py-2 font-bold text-white bg-rose-500 rounded"
                                    >
                                        Save
                                    </button>
                                </div>
                            </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';
import { ref } from 'vue';
import { TailwindPagination } from 'laravel-vue-pagination';
import ModalAddCardUser from "@/Components/ModalAddCardUser.vue";

const laravelData = ref({});
const getResults = async (page = 1) => {
    const response = await fetch(`/getIndexClients?page=${page}`);
    laravelData.value = await response.json();
}
getResults();


const props = defineProps({
    url:String,
    cards:Array
});

const form = useForm();

function destroy(id) {
        form.delete(route('users.destroy', id));
        window.location.reload();
 
}
function ban(id) {

        form.get(route('ban', id));
        window.location.reload();
   
}
function unban(id) {
   
        form.get(route('unban', id));
        window.location.reload();
}
let showModal = ref(false);
let user_id = ref(0);
function confirm(V) {
    let card_id = V.card_id
    let card = V.card
    fetch(`/addUserCard/${card_id}/${card}/${ user_id.value}`).then(() => {
        showModal.value = false;
        user_id.value=0
        window.location.reload();
    })
    .catch((error) => {
        showModal.value = false;
        user_id.value=0
    });
    
}
function open(id) {
    user_id.value=id
    showModal.value = true;
}
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
               {{$t('Customer_management')}}
            </h2>
        </template>
            <ModalAddCardUser
            :show="showModal ? true : false"
            :data="cards"
            @a="confirm($event)"
            @close="showModal = false"
            >
      <template #header>
ْ      </template>
        </ModalAddCardUser>
            <div class="py-12">
                <div class=" mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6  dark:bg-gray-900">
                            <div className="flex items-center justify-between mb-6">
                                <Link
                                    className="px-6 py-2 text-white bg-rose-500 rounded-md focus:outline-none"
                                    :href="route('addClients')">
                                    {{ $t('addCustomer') }}
                                </Link>
                            </div>
                            <div class="overflow-x-auto shadow-md ">
                            <table class="w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center" >
                                    <tr>
                                        <th className="px-1 py-3 text-base">{{ $t('number') }}</th>
                                        <th className="px-1 py-3 text-base">{{ $t('name') }}</th>
                                        <th className="px-1 py-3 text-base">{{ $t('phoneNumber') }}</th>
                                        <th className="px-1 py-3 text-base">مجموع السيارات غير مكتمل</th>
                                        <th className="px-1 py-3 text-base">مجموع السيارات مكتمل</th>
                                        <th className="px-1 py-3 text-base">{{ $t('debt') }}</th>
                                        <th className="px-1 py-3 text-base">{{ $t('execute') }}</th>       
                                    </tr>
                                </thead>
                                <tbody class="flex-1 sm:flex-none dark:bg-gray-700 dark:text-gray-200">
                                
                                    <tr v-for="user in laravelData.data" :key="user.id"  class="text-center dark:text-gray-200mb-2 sm:mb-0 "  :class="user.wallet['balance'] <= 0 ?'bg-green-100 dark:bg-green-900':'bg-red-100 dark:bg-red-900'" >
                                        <td className="border dark:border-gray-800 text-center px-4 py-2 text-base"> {{user.id }}</td>
                                        <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ user.name }}</td>
                                        <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ user.phone }}</td>
                                        <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{user.car_total_uncomplete}}</td>
                                        <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{user.car_total_complete}}</td>
                                        <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ user.wallet ? '$'+user.wallet['balance']:0   }}</td>
                                        <td className="border dark:border-gray-800 text-center px-4 py-2 text-base"  style="min-height: 42px;">
                                            <Link
                                                tabIndex="1"
                                                className="px-2 py-1 text-sm text-white bg-slate-500 rounded"
                                                :href="route('showClients', user.id)"
                                                v-if="user.email!='admin@admin.com'">
                                                عرض
                                            </Link>

                                            <!-- <button
                                                @click="destroy(user.id)"
                                                tabIndex="-1"
                                                type="button"
                                                className="mx-1 px-2 py-1 text-sm text-white bg-rose-500 rounded"
                                                v-if="user.email!='admin@admin.com'"
                                            >
                                                حذف
                                            </button> -->
                                            
                                            <!-- <button 
                                                @click="ban(user.id)"
                                                tabIndex="-1"
                                                type="button"
                                                className="mx-1 px-2 py-1 text-sm text-white bg-orange-500 rounded"
                                                v-if="!user.is_band && user.email!='admin@admin.com'">
                                                تقيد
                                            </button>
                                            <button 
                                                @click="unban(user.id)"
                                                tabIndex="-1"
                                                type="button"
                                                className="mx-1 px-2 py-1 text-sm text-white bg-orange-500 rounded"
                                                v-if="user.is_band && user.email!='admin@admin.com'">
                                                إلغاء التقيد 
                                            </button> -->
           
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                            <div class="mt-3 text-center" style="direction: ltr;">
                                    <TailwindPagination
                                    :data="laravelData"
                                    @pagination-change-page="getResults"
                                    :limit ="10"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
</template>
<style>
.sr-only{
    display: none;
}
</style>
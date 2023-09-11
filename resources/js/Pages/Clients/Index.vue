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
const getResultSearch = async (q) => {
    const response = await fetch(`/getIndexClientsSearch?q=${q}`);
    laravelData.value = await response.json();
}

const props = defineProps({
    url:String,
});


let showModal = ref(false);
let user_id = ref(0);

</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
               {{$t('Customer_management')}}
            </h2>
        </template>

            <div class="py-12">
                <div class=" mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6  dark:bg-gray-900">
                            <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-5 gap-2 lg:gap-1">
                        <div>
                          <form class="flex items-center max-w-5xl">
                            <label  class="dark:text-gray-200" for="simple-search"  ></label>
                            <div class="relative w-full">
                              <div
                                class="
                                  absolute
                                  inset-y-0
                                  left-0
                                  flex
                                  items-center
                                  pl-3
                                  pointer-events-none
                                "
                              >
                                <svg
                                  aria-hidden="true"
                                  class="w-5 h-5 text-gray-500 dark:text-gray-200 dark:text-gray-400"
                                  fill="currentColor"
                                  viewBox="0 0 20 20"
                                  xmlns="http://www.w3.org/2000/svg"
                                >
                                  <path
                                    fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd"
                                  ></path>
                                </svg>
                              </div>
                              <input
                                v-model="searchTerm"
                                @input="getResultSearch(searchTerm)"
                                type="text"
                                id="simple-search"
                                class="
                                  bg-gray-50
                                  border border-gray-300
                                  text-gray-900 text-sm
                                  rounded-lg
                                  focus:ring-blue-500 focus:border-blue-500
                                  block
                                  w-full
                                  pl-10
                                  p-2.5
                                  dark:bg-gray-700
                                  dark:border-gray-600
                                  dark:placeholder-gray-400
                                  dark:text-white
                                  dark:focus:ring-blue-500
                                  dark:focus:border-blue-500
                                "
                                placeholder="بحث"
                                required
                              />
                            </div>
                          </form>
                        </div>
                        <!-- <div>
                          <button
                            type="button"
                            @click="openAddGenExpenses()"
                            style="min-width:150px;"
                            className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-red-500 rounded">
                               {{ $t('genExpenses') }}
                          </button>
                        </div> -->
                        <!-- <div>
                          <button
                            type="button"
                            @click="openAddCar()"
                            style="min-width:150px;"
                            className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-green-500 rounded">
                            {{ $t('addCar') }} 
                          </button>
                        </div> -->
                        <!-- <div>
                          <a
                            type="button"
                            :href="route('FormRegistrationCompleted')"
                            style="min-width:150px;"
                            className="px-6 mb-12 text-center mx-2 py-2 font-bold text-white bg-blue-600 rounded">
                            {{ $t('allCars') }}
                          </a>
                        </div> -->
                        <!-- <div>
                          <button
                            type="button"
                            @click="openAddTransfers()"
                            style="min-width:150px;"
                            className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-indigo-600 rounded">
                            {{ $t('transfers') }} 
                          </button>
                        </div> -->
                        <div>
                            <select @change="getResultSearch(user_id)" v-model="user_id" id="default" class="pr-8 bg-gray-50 border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                              <option value="undefined" disabled> {{ $t("selectCustomer") }}</option>
                              <option value="">{{ $t("allOwners") }}</option>
                              <option value="debit">يوجد دين</option>
                            </select>
                        </div>
                        <div class="text-center">
                            <Link
                                    style="display: inline-block"
                                    className="px-6 py-2 font-bold text-white bg-red-600 rounded "
                                    :href="route('addClients')">
                                    {{ $t('addCustomer') }}
                            </Link>
                        </div>


                      </div>

                 
                            <div class="overflow-x-auto shadow-md ">
                            <table class="w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center" >
                                    <tr>
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
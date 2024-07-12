<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref, watch } from 'vue'; // Import ref and watch from Vue
import { useI18n } from "vue-i18n";
import { Link } from "@inertiajs/inertia-vue3";

const props = defineProps({
  auth: Object,
  apiKey: String
});

const {t} = useI18n();
let userType = ref(props.auth.user.type_id)

function selectUser(v){
  if(v==1 || v==6){
    return 'getIndexClients'
  }
  if(v==8){
    return 'getIndexClientsContract'
  }
}

let data = ref({});
 

const laravelData = ref({});
const laravelData1 = ref({});
const laravelData2 = ref({});
let  controller = new AbortController(); // Create a new AbortController

const getResults = async (page = 1) => {
  axios.get(`api/${selectUser(userType.value)}?page=${page}&q=debit`)
  .then(response => {
    if(userType.value==1 || userType.value==6){
      try {
      laravelData.value =  response.data.data
    } catch (error) {
      laravelData.value =  response.data.data

    }
  }
  if(userType.value==8){
    laravelData1.value =  response.data.data1
      laravelData2.value =  response.data.data2
  }



  })
  .catch(error => {
    console.error(error);
  })
}
getResults();

let expenses_type_id = ref(0);

const toast = useToast();

let selectedUserIds = ref([]);

let showModal = ref(false);

function sendWhatsAppMessage(phoneNumber) {
  if(phoneNumber){
    phoneNumber= '964'+phoneNumber;
    const message = 'السلام عليكم: شركة سلام جلال ايوب - أربيل ,يرجى الأخذ بالعلم تسديد المبلغ المستحق عليكم في أقرب وقت ممكن. شكرا لتعاونكم  ..........   سڵاوی خواتان لێبێت: کۆمپانیای سلام جلال ايوب - تکایە ئاگاداربن بە زووترین کات ئەو بڕە پارەیەی کە قەرزارن بیدەن. سوپاس بۆ هەماهەنگیت';

    // Construct the WhatsApp message URL
    const whatsappURL = `https://api.whatsapp.com/send?phone=${phoneNumber}&text=${encodeURIComponent(message)}`;

    // Open the WhatsApp app with the pre-filled message
    window.open(whatsappURL);
  }

}

  const sendWhatsAppMessageArray = (array) => {
    const phoneNumbers = array;
    const baseUrl = 'https://api.textmebot.com/send.php';
    const apiKey = props.apiKey;
    const textMessage = 'السلام عليكم: شركة سلام جلال ايوب - أربيل ,يرجى الأخذ بالعلم تسديد المبلغ المستحق عليكم في أقرب وقت ممكن. شكرا لتعاونكم  ..........   سڵاوی خواتان لێبێت: کۆمپانیای سلام جلال ايوب - تکایە ئاگاداربن بە زووترین کات ئەو بڕە پارەیەی کە قەرزارن بیدەن. سوپاس بۆ هەماهەنگیت';
    
    const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

    let promise = Promise.resolve(); // Start with a resolved promise

    phoneNumbers.forEach((phoneNumber) => {
      if (phoneNumber) {
        promise = promise.then(() => {
          const url = `${baseUrl}?recipient=+964${phoneNumber}&apikey=${apiKey}&text=${encodeURIComponent(textMessage)}&json=yes`;
          return axios.get(url)
          
            .then(() => {
              if (index !== -1) {
                phoneNumbers.splice(index, 1);
              }
              toast.success("تم الارسال بنجاح", {
                timeout: 2000,
                position: "bottom-right",
                rtl: true,
              });
     
            })
            .catch((error) => {
              const index = phoneNumbers.indexOf(phoneNumber);
              if (index !== -1) {
                phoneNumbers.splice(index, 1);
              }
              if (error.code === 'ERR_NETWORK') {
                toast.success("تم الارسال بنجاح", {
                timeout: 2000,
                position: "bottom-right",
                rtl: true,
              });
              } else {
                toast.error("خطأ في الارسال", {
                  timeout: 2000,
                  position: "bottom-right",
                  rtl: true,
                });
              }
            })
            .then(() => delay(5000)); // Wait for 5 seconds before sending the next message
        });
      }
    });
  };
 
let searchTerm = ref('');
let mainAccount= ref(0)
let onlineContracts= ref(0)
let howler= ref(0)
let shippingCoc= ref(0)
let border= ref(0)
let iran= ref(0)
let dubai= ref(0)
let debtOnlineContracts= ref(0)
let onlineContractsDinar= ref(0)
let debtOnlineContractsDinar= ref(0)
let purchasesCost = ref(0)
let clientPaid = ref(0)
let clientDebit  = ref(0)
let mainBoxDollar = ref(0)
let mainBoxDinar = ref(0)

let allCars= ref(0)

function openModal() {
  showModal.value = true;
}
const formData = ref({});
const car = ref([]);



const handleCheckboxChange = (userId) => {
  const index = selectedUserIds.value.indexOf(userId);
  if (index > -1) {
    selectedUserIds.value.splice(index, 1);
  } else {
    selectedUserIds.value.push(userId);
  }
  console.log(selectedUserIds.value);
};

import { debounce } from 'lodash'; // Import debounce function from Lodash


const debouncedGetResultsCarSearch = debounce(async (q = '', page = 1) => {
  if(!q){
    q='debit'
  }
    try {
        const response = await axios.get(`api/${selectUser(userType.value)}?page=${page}&q=${q}`,{      signal: controller.signal // Pass the signal to abort the request if needed
});
        laravelData.value = response.data.data
    } catch (error) {
        console.error(error);
    }
}, 300); 

const getResultsCarSearch = (q = '', page = 1) => {
    debouncedGetResultsCarSearch(q, page);
}
//
const getcountTotalInfo = async () => {
  axios.get('/api/totalInfo', {
    headers: {
        Authorization: 'Bearer ' + props.auth.accessToken,
    },
})
  .then(response => {
    mainAccount.value = response.data.data.mainAccount;
    onlineContracts.value=  response.data.data.onlineContracts
    howler.value=  response.data.data.howler
    shippingCoc.value=  response.data.data.shippingCoc
    border.value=  response.data.data.border
    iran.value=  response.data.data.iran
    dubai.value=  response.data.data.dubai
    debtOnlineContracts.value=  response.data.data.debtOnlineContracts
    onlineContractsDinar.value =response.data.data.onlineContractsDinar
    debtOnlineContractsDinar.value = response.data.data.debtOnlineContractsDinar
    allCars.value =response.data.data.allCars;
    purchasesCost.value =response.data.data.purchasesCost
    clientPaid.value =response.data.data.clientPaid
    clientDebit.value =response.data.data.clientDebit
    mainBoxDollar.value =response.data.data.mainBoxDollar
    mainBoxDinar.value =response.data.data.mainBoxDinar

  })
  .catch(error => {
    console.error(error);
  })
  
    
}
const abortRequest = () => {
  if (controller) {
    controller.abort(); // Abort previous request if it exists
  }
  controller = new AbortController(); // Create a new AbortController
};

watch([searchTerm], () => {
  abortRequest(); // Abort previous request
  debouncedGetResultsCarSearch(); // Call debounced function to fetch new results
});

getcountTotalInfo()
function changeColor(total){

  if(total >= 30000){
    return 'bg-red-600  dark:bg-red-600'

  }
  if(total >= 25000){
    return 'bg-pink-600  dark:bg-pink-600'

  }
  if(total >= 20000){
    return 'bg-purple-600  dark:bg-purple-600'
  }
  if(total >= 15000){
    return 'bg-indigo-600  dark:bg-indigo-600'

  }
  if(total >= 10000){
    return 'bg-cyan-600  dark:bg-cyan-600'

  }
  if(total >= 5000){
    return 'bg-blue-800  dark:bg-blue-800'

  }
  
  if(total >= 1000){
    return 'bg-teal-500  dark:bg-teal-500'

  }
  if(total >= 0){
    return 'bg-yellow-600  dark:bg-yellow-600'

  }
  if(total < 0){
    return 'bg-green-600  dark:bg-green-600'

  }
}
function updateResults(input) {
  // Ensure the input is a number
  if (typeof input !== 'number') {
    // Try converting the input to a number
    input = parseFloat(input) || 0;
  }
  
  // Use toLocaleString to format the number with commas
  return input.toLocaleString();
}
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout >
        <div class="py-2"  v-if="$page.props.auth.user.type_id==1||$page.props.auth.user.type_id==6">
        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8 ">
            <div class="bg-white overflow-hidden shadow-sm ">
                <div class="p-6  dark:bg-gray-900">
                    <div class="flex flex-col">
         
                      <div>
                    
                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg" v-if="false">
                
                        </div>
             
                      </div>
                      <div>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7"> 
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
                                @input="getResultsCarSearch(searchTerm)"
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
        
                 
                        </div>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7">     
                          
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg"  v-if="$page.props.auth.user.type_id==1">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                              </svg>
                            </div>
                            <div class="mr-4" >
                              <h2 class="font-semibold ">{{ $t('capital') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ updateResults(mainAccount) }}</p>
                            </div>
                          </div>
                 
                         
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                              </svg>
                            </div>
                            <div class="mr-4" >
                              <h2 class="font-semibold ">دين التجار</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{  updateResults(clientDebit) }} دولار</p>
                            </div>
                          </div>

                      

                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                              </svg>
                            </div>
                            <div class="mr-4" >
                              <h2 class="font-semibold ">الصندوق</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{  updateResults(mainBoxDollar) }} دولار</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                              </svg>
                            </div>
                            <div class="mr-4" >
                              <h2 class="font-semibold ">الصندوق</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{  updateResults(mainBoxDinar) }} دينار</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                              </svg>
                            </div>
                            <div class="mr-4" >
                              <h2 class="font-semibold ">{{ $t('online_contracts') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ updateResults(onlineContracts) }} دولار</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                              </svg>
                            </div>
                            <div class="mr-4" >
                              <h2 class="font-semibold ">{{ $t('debtOnlineContracts') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ updateResults(debtOnlineContracts) }} دولار</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                              </svg>
                            </div>
                            <div class="mr-4" >
                              <h2 class="font-semibold ">{{ $t('online_contracts') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ updateResults(onlineContractsDinar) }} دينار</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                              </svg>
                            </div>
                            <div class="mr-4" >
                              <h2 class="font-semibold ">{{ $t('debtOnlineContracts') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ updateResults(debtOnlineContractsDinar) }} دينار</p>
                            </div>
                          </div>
                          <button @click="sendWhatsAppMessageArray(selectedUserIds)" v-if="selectedUserIds.length" type="button" class="text-white bg-gradient-to-r from-green-400 via-green-500 to-green-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-green-300 dark:focus:ring-green-800 shadow-lg shadow-green-500/50 dark:shadow-lg dark:shadow-green-800/80 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">
                            
                            ارسال رسالة تذكير
                          
                            <span class="inline-flex items-center justify-center w-4 h-4 ms-2 text-xs font-semibold text-blue-800 bg-blue-200 rounded-full">
                            {{selectedUserIds.length}}
                            </span>
                          </button>


                          </div>
                          <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7">
            
                          
                          <div v-for="(user,i) in laravelData" :key="i">
                            <input type="checkbox" :id="'e'+user.id" :value="user.phone" class="hidden peer" @change="handleCheckboxChange(user.phone)">
                            <label :for="'e'+user.id" class="inline-flex items-center justify-between w-full     border-4 border-gray-200  cursor-pointer  dark:border-gray-700 peer-checked:border-rose-700   dark:peer-checked:text-gray-300 peer-checked:text-gray-600" style="border-radius: 4rem;">
                            <div   class="flex items-start  text-gray-200  dark:text-gray-300  p-4 shadow-lg w-full" style="border-radius: 4rem;"    :class="changeColor(user.balance)">
                            <div   class="mr-4"  v-if="selectedUserIds.length">
                            <div   style="display: block;" class="font-semibold">{{ user.name}}</div>
                              <div   style="display: block;" class="mt-2 text-sm text-gray-200  dark:text-gray-200">{{ '$'+updateResults(user.balance) }}
                                <span class="inline-flex items-center justify-center w-4 h-4 ml-2 text-xs font-semibold text-blue-800 bg-blue-200 rounded-full">
                                {{ user.car_total_un_pay}}
                              </span>
                              </div>
                            </div>
                            <div   class="mr-4"  v-if="!selectedUserIds.length">
                              <Link :href="route('showClients', { id: user.id, q: searchTerm })" style="display: block;" class="font-semibold">{{ user.name}}</Link>
                              <Link :href="route('showClients', { id: user.id, q: searchTerm })" style="display: block;" class="mt-2 text-sm text-gray-200  dark:text-gray-200">{{ '$'+updateResults(user.balance) }}
                                <span class="inline-flex items-center justify-center w-4 h-4 ml-2 text-xs font-semibold text-blue-800 bg-blue-200 rounded-full">
                                {{ user.car_total_un_pay}}
                              </span>
                              </Link>
                            </div>
                            </div>
                         
                            </label>
                          </div>
                        </div>
                      </div>
                      </div>
                    </div>
                    </div>
                </div>
        </div>
        <div v-if="$page.props.auth.user.type_id==8">
          <div class="max-w-9xl mx-auto sm:px-6 lg:px-8 ">
            <div class="bg-white overflow-hidden shadow-sm ">
                <div class="p-6  dark:bg-gray-900">
                    <div class="flex flex-col">
                      <div>
                          <h2 class="my-3 dark:text-white" v-if="laravelData1">دين البائع</h2>
                          <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7">
                          <Link @dblclick="sendWhatsAppMessage(user.phone)"  v-for="(user,i) in laravelData1" :key="i" class="flex items-start rounded-xl text-gray-200  dark:text-gray-300  p-4 shadow-lg"  :href="route('car_contract', {   q:  user.name_seller })"   :class="changeColor(user.tex_seller -  user.tex_seller_paid)">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ user.name_seller}}</h2>
                              <p class="mt-2 text-sm text-gray-200  dark:text-gray-200"> ${{ user.tex_seller -  user.tex_seller_paid }}
                                - IQD {{ user.tex_seller_dinar -  user.tex_seller_dinar_paid }}
                              </p>
                            
                            </div>
                          </Link>

 
                        </div>
                        <h2 class="my-3 dark:text-white" v-if="laravelData2">دين المشتري</h2>
                          <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7">
                          <Link @dblclick="sendWhatsAppMessage(user.phone)"  v-for="(user,i) in laravelData2" :key="i" class="flex items-start rounded-xl text-gray-200  dark:text-gray-300  p-4 shadow-lg"  :href="route('car_contract', {q:  user.name_buyer })"   :class="changeColor(user.tex_buyer -  user.tex_buyer_paid)">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ user.name_buyer}}</h2>
                              <p class="mt-2 text-sm text-gray-200  dark:text-gray-200">${{ user.tex_buyer -  user.tex_buyer_paid }}
                                - IQD {{ user.tex_buyer_dinar -  user.tex_buyer_dinar_paid }}
                              </p>
                            
                            </div>
                          </Link>

 
                        </div>
                      </div>
                      </div>
                    </div>
                    </div>
                </div>
            </div>
        <div >
         
      </div>   
    </AuthenticatedLayout>
</template>
<style>
.Vue-Toastification__container {
width: unset !important;
}
.duet-date__dialog {
  direction: ltr;
    right: 0;
    top: 44px;
}
.header-rgRow{
  text-align: center;
}
.rgRow > div {
  text-align: center !important;
}
.rgCell.disabled {
    background-color: unset !important;
}
.rgCell{
  padding-top: 7px !important;
}

body::-webkit-scrollbar {
  width: 12px;
}

body::-webkit-scrollbar-track {
  background: #f1f1f1;
}

body::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 6px;
}

/* Style the scrollbars for Firefox */
body {
  scrollbar-width: thin;
  scrollbar-color: #888 #f1f1f1;
}

.border-4 {
    border-width: 6px;
}
</style>

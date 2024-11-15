<script setup>
import { reactive, toRefs } from 'vue';

let props = defineProps({
  show: Boolean,
  formDriving: Object,
});


</script>

<template>
  <Transition name="modal">
    <div v-if="show" class="modal-mask">
      <div class="modal-wrapper max-h-[80vh]">
        <div class="modal-container dark:bg-gray-900 overflow-auto max-h-[80vh]">
          <div class="modal-header text-center py-4 dark:text-gray-300"> 
            تخويل قيادة {{ formDriving.car_typeDriving }} {{ formDriving.yearDriving }} {{ formDriving.car_colorDriving }} رقم شانصى {{ formDriving.vinDriving }}
            <slot name="header"></slot>
          </div>

          <div class="modal-body">
            <div class="grid grid-cols-2 gap-3 lg:gap-2">
              <div class="mb-4 mx-5">
                <label class="dark:text-gray-200" for="amountTotal">الاسم كامل</label>
                <input
                  id="amountTotal"
                  type="text"
                  
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formDriving.nameDriving"
                />
              </div>
              <div class="mb-4 mx-5">
                <label class="dark:text-gray-200" for="carType">السيارة</label>
                <input
                  id="carType"
                  type="text"
                  
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formDriving.car_typeDriving"
                />
              </div>

              <div class="mb-4 mx-5">
                <label class="dark:text-gray-200" for="vin">رقم الشانصي</label>
                <input
                  id="vin"
                  type="text"
                  
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formDriving.vinDriving"
                />
              </div>
              <div class="mb-4 mx-5">
                <label class="dark:text-gray-200" for="carNumber">رقم السيارة</label>
                <input
                  id="carNumber"
                  type="text"
                  
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formDriving.car_numberDriving"
                />
              </div>

              <div class="mb-4 mx-5">
                <label class="dark:text-gray-200" for="color">اللون</label>
                <input
                  id="color"
                  type="text"
                  
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formDriving.car_colorDriving"
                />
              </div>
              <div class="mb-4 mx-5">
                <label class="dark:text-gray-200" for="model">موديل</label>
                <input
                  id="model"
                  type="text"
                  
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formDriving.yearDriving"
                />
              </div>

         

              <div class="mb-4 mx-5">
                <label class="dark:text-gray-200" for="exitDate">تاريخ التخويل</label>
                <input
                  id="exitDate"
                  type="date"
                  
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formDriving.createdDriving"
                />
              </div>
            </div>
            <div class="mb-4 mx-5">
                <label class="dark:text-gray-200" for="note">تخويل القيادة</label>
                <textarea
                  id="note"
                  type="text"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formDriving.noteDriving"
                />
            </div>
          </div>

          <div class="modal-footer my-2">
            <div class="flex flex-row">
              <div class="basis-1/2 px-4">
                <button class="modal-default-button py-3 bg-gray-500 rounded" @click="$emit('close');">
                  {{ $t('cancel') }}
                </button>
              </div>
              <div class="basis-1/2 px-4">
                <button
                  class="modal-default-button py-3 bg-rose-500 rounded col-6"
                  @click="$emit('a', formDriving)"
                  :disabled="!(formDriving.noteDriving)"
                >
                  {{ $t('yes') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style>
/* existing CSS remains unchanged */
</style>

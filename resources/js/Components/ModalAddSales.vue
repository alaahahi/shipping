<script setup>
import { ref, watch } from 'vue';



const props = defineProps({
  show: Boolean,
  data: Array,
  accounts: Array,
  tagOptions: { type: Array, default: () => [] },
  showExtendedFields: { type: Boolean, default: true },
});
const form = ref({
  date: getTodayDate(),
  cars_count: '',
  cmr: '',
  driver_name: '',
  entry_date: '',
  tag: '',
});
function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}
const restform = () => {
  form.value = {
    date: getTodayDate(),
    cars_count: '',
    cmr: '',
    driver_name: '',
    entry_date: '',
    tag: '',
  };
}

</script>
  
  <template>
    <Transition name="modal">
      <div v-if="show" class="modal-mask ">
        <div class="modal-wrapper  max-h-[80vh]">
          <div class="modal-container bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
            <div class="modal-header text-gray-900 dark:text-gray-100">
              <slot name="header"></slot>
            </div>
            <div class="modal-body text-gray-800 dark:text-gray-200">
                        <h2 class="text-center pb-5">
                         وصل قبض
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-2 gap-3 lg:gap-3">

   

                        <div className="mb-4 mx-5">
                        <label for="card" class="dark:text-gray-200">المبلغ بالدولار</label>
                        <input
                          id="card"
                          type="number"
                          class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm"
                          v-model="form.amountDollar"   />
                        </div>

                        <div className="mb-4 mx-5">
                        <label for="card" class="dark:text-gray-200">المبلغ بالدينار</label>
                        <input
                          id="card"
                          type="number"
                          class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm"
                          v-model="form.amountDinar"   />
                        </div>
                     
                        <div className="mb-4 mx-5">
                        <label for="card" class="dark:text-gray-200">ملاحظة</label>
                        <input
                          id="card"
                          type="text"
                          class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm"
                          v-model="form.amountNote"   />
                        </div>

                        <div className="mb-4 mx-5">
                          <label for="card" >التاريخ</label>
                          <input
                          id="card"
                          type="date"
                          class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                          v-model="form.date"   />
                        </div>

                        <template v-if="showExtendedFields">
                          <div className="mb-4 mx-5">
                            <label for="cars_count" class="dark:text-gray-200">عدد السيارات</label>
                            <input id="cars_count" type="number" min="0" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm" v-model="form.cars_count" />
                          </div>
                          <div className="mb-4 mx-5">
                            <label for="cmr" class="dark:text-gray-200">رقم CMR</label>
                            <input id="cmr" type="text" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm" v-model="form.cmr" />
                          </div>
                          <div className="mb-4 mx-5">
                            <label for="driver_name" class="dark:text-gray-200">اسم السائق</label>
                            <input id="driver_name" type="text" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm" v-model="form.driver_name" />
                          </div>
                          <div className="mb-4 mx-5">
                            <label for="entry_date" class="dark:text-gray-200">تاريخ الدخول</label>
                            <input id="entry_date" type="date" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm" v-model="form.entry_date" />
                          </div>
                          <div className="mb-4 mx-5 lg:col-span-2">
                            <label for="tag" class="dark:text-gray-200">التاغ</label>
                            <input v-if="!tagOptions.length" id="tag" type="text" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm" v-model="form.tag" placeholder="اختياري" />
                            <select v-else id="tag" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm" v-model="form.tag">
                              <option value="">— بدون تاغ —</option>
                              <option v-for="t in tagOptions" :key="t.id" :value="t.name">{{ t.name }}</option>
                            </select>
                          </div>
                        </template>

                        </div>

            </div>
  
            <div class="modal-footer my-2">
              <div class="flex flex-row">
                <div class="basis-1/2 px-4"> 
                  <button class="modal-default-button py-3  bg-gray-500 rounded"
                    @click="$emit('close');">تراجع</button>
                  </div>
              <div class="basis-1/2 px-4">
                <button class="modal-default-button py-3  bg-rose-500 rounded col-6"  @click="$emit('a',form);restform();" :disabled="!form.amountDollar && !form.amountDinar">نعم</button>
                </div>

            </div>
  
     
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </template>
  
  <style>
  .modal-mask {
    position: fixed;
    z-index: 9998;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: table;
    transition: opacity 0.3s ease;
  }
  
  .modal-wrapper {
    display: table-cell;
    vertical-align: middle;
  }
  
  .modal-container {
    width: 50%;
    min-width: 350px;
    margin: 0px auto;
    padding: 20px  30px;
    padding-bottom: 60px;
    background-color: #fff;
    border-radius: 2px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
    transition: all 0.3s ease;
    border-radius: 10px;
  }
  
  .modal-header h3 {
    margin-top: 0;
    color: #42b983;
  }
  
  .modal-body {
    margin: 20px 0;
  }
  
  .modal-default-button {
    float: right;
    width: 100%;
    color: #fff;
  }
  
  /*
   * The following styles are auto-applied to elements with
   * transition="modal" when their visibility is toggled
   * by Vue.js.
   *
   * You can easily play with the modal transition by editing
   * these styles.
   */
  
  .modal-enter-from {
    opacity: 0;
  }
  
  .modal-leave-to {
    opacity: 0;
  }
  
  .modal-enter-from .modal-container,
  .modal-leave-to .modal-container {
    -webkit-transform: scale(1.1);
    transform: scale(1.1);
  }
  </style>
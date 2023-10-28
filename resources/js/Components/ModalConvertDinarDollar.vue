<script setup>
import { ref } from 'vue';

import { useToast } from "vue-toastification";
let toast = useToast();

const props = defineProps({
  show: Boolean,
  boxes: Array,
});
const form = ref({
  user: {
    percentage:0,
  },
  date:getTodayDate(),
  amount: 0,
  exchangeRate:1

});
function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}
const restform =()=>{
  form.value = {
  user: {
    percentage:0,
  },
  date:getTodayDate(),
  amount: 0,

};
}

function calculateAmountDollarDinar (){
  if(form.value.amountDinar<=form.value.user.wallet?.balance_dinar){
    form.value.amountResultDollar = form.value.amountDinar/(form.value.exchangeRate/100)
    form.value.newBalanceDinar =form.value.user.wallet?.balance_dinar -form.value.amountDinar
  form.value.newBalanceDollar =form.value.user.wallet?.balance +form.value.amountResultDollar
  }
  else{
    form.value.amountDinar=form.value.user.wallet?.balance_dinar
    form.value.amountResultDollar = form.value.amountDinar/(form.value.exchangeRate/100)
  form.value.newBalanceDinar =form.value.user.wallet?.balance_dinar -form.value.amountDinar
  form.value.newBalanceDollar =form.value.user.wallet?.balance +-form.value.amountResultDollar
  
    toast.info(" المبلغ اكبر من  الموجود في الصندوق"+" "+form.value.user.wallet?.balance_dinar, {
        timeout: 4000,
        position: "bottom-right",
        rtl: true,
      });
  }

}

</script>
  
  <template>  
    <Transition name="modal">
      <div v-if="show" class="modal-mask ">
        <div class="modal-wrapper  max-h-[80vh]">
          <div class="modal-container">
            <div class="modal-header">
              <slot name="header"></slot>
            </div>
            <div class="modal-body">
                        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-3 lg:gap-3">
                        <div className="mb-4 mx-5">
                          <label for="card" >التاريخ</label>
                          <input
                          id="card"
                          type="date"
                          class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                          v-model="form.date"   />
                        </div>
                        <div className="mb-4 mx-5">
                          <label for="user_id" >الحساب</label>
                          <select
                            v-model="form.user"
                            id="user_id"
                            class="pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option selected disabled>تحديد الحساب</option>
                            <option v-for="(user, index) in boxes" :key="index" :value="user">{{ user.name }}</option>
                          </select>
                        </div>
                        <div className="mb-4 mx-5">
                        <label for="balance" >الرصيد الحالي بالدولار</label>
                        <input
                          id="balance"
                          type="number"
                          disabled
                          :value="form.user.wallet?.balance"
                          class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            />
                        </div>
                        <div className="mb-4 mx-5">
                        <label for="balance" >الرصيد الحالي بالدينار العراقي</label>
                        <input
                          id="balance"
                          type="number"
                          disabled
                          :value="form.user.wallet?.balance_dinar"
                          class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            />
                        </div>
                        <hr />
                        <hr />

                        <div className="mb-4 mx-5">
                        <label for="balance" >الرصيد بعد التحويل بالدولار</label>
                        <input
                          id="balance"
                          type="number"
                          disabled
                          :value="form.newBalanceDollar"
                          class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            />
                        </div>
                        <div className="mb-4 mx-5">
                        <label for="balance" >الرصيد  بعد التحويل  بالدينار العراقي</label>
                        <input
                          id="balance"
                          type="number"
                          disabled
                          :value="form.newBalanceDinar"
                          class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            />
                        </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-3 lg:gap-3">
                        <div className="mb-4 mx-5">
                        <label for="amountDinar" >المبلغ بالدينار العراقي</label>
                        <input
                          id="amountDinar"
                          type="number"
                          @input="calculateAmountDollarDinar()"
                          class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                          v-model="form.amountDinar" />
                        </div>
                        <div className="mb-4 mx-5">
                        <label for="amountDinar" >سعر الصرف 100$</label>
                        <input
                          id="amountDinar"
                          type="number"
                          @input="calculateAmountDollarDinar()"
                          class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                          v-model="form.exchangeRate" />
                        </div>
                        <div className="mb-4 mx-5">
                        <label for="amountDinar" >المبلغ  بالدولار</label>
                        <input
                          id="amountDinar"
                          type="number"
                          class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                          v-model="form.amountResultDollar" />
                        </div>
                        <div className="mb-4 mx-5">
                        <label for="note" >ملاحظة</label>
                        <input
                          id="note"
                          type="text"
                          class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                          v-model="form.note" />
                        </div>
                        </div>
            </div>
  
            <div class="modal-footer my-2">
              <div class="flex flex-row">
                <div class="basis-1/2 px-4"> 
                  <button class="modal-default-button py-3  bg-gray-500 rounded"
                    @click="$emit('close');">تراجع</button>
                  </div>
              <div class="basis-1/2 px-4">
                <button class="modal-default-button py-3  bg-rose-500 rounded col-6"  @click="$emit('a',form);restform();"  >نعم</button>
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
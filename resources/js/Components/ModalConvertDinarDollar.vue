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
  amount: 0,
  exchangeRate:1

});

const restform =()=>{
  form.value = {
  user: {
    percentage:0,
  },
  amount: 0,

};
}
let exchangeRateError= ref(false);
function validateExchangeRate() {
      const input = form.value.exchangeRate;
      if (/^\d{6}$/.test(input)) {
        exchangeRateError.value = false;
      } else {
        exchangeRateError.value = true;
      }
    }
function calculateAmountDollarDinar (){
  validateExchangeRate()
    form.value.amountResultDollar = Math.floor(form.value.amountDinar/(form.value.exchangeRate/100))



}

</script>
  
  <template>  
    <Transition name="modal">
      <div v-if="show" class="modal-mask ">
        <div class="modal-wrapper  max-h-[80vh]">
          <div class="modal-container dark:bg-gray-800 dark:text-gray-100">
            <div class="modal-header">
              <slot name="header"></slot>
            </div>
            <div class="modal-body">
                        <div>
                        <div className="my-4 mx-5">
                        <label for="exchangeRateIqdUsd" class="modal-field-label">سعر الصرف 100$</label>
                        <input
                          id="exchangeRateIqdUsd"
                          type="number"
                          @input="calculateAmountDollarDinar()"
                          class="modal-field-input"
                          v-model="form.exchangeRate" />
                          <div v-if="exchangeRateError" class="modal-field-error">
                          مطلوب رقم من 6 خانة فقط
                          </div>
                        </div>
                        
                        <div className="my-4 mx-5">
                        <label for="amountDinarWithdraw" class="modal-field-label">المبلغ بالدينار العراقي
                          (المبلغ المسحوب من الصندوق بالدينار العراقي)
                        </label>
                        <input
                          id="amountDinarWithdraw"
                          type="number"
                          @input="calculateAmountDollarDinar()"
                          class="modal-field-input"
                          v-model="form.amountDinar" />
                        </div>
                  
                        <div className="mb-y mx-5">
                        <label for="amountResultDollar" class="modal-field-label">المبلغ  بالدولار
                          (المبلغ المضاف للصندوق بالدولار)
                        </label>
                        <input
                          id="amountResultDollar"
                          type="number"
                          class="modal-field-input"
                          v-model="form.amountResultDollar" />
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
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
    transition: all 0.3s ease;
  }

  .dark .modal-container {
    background-color: #1f2937;
    color: #f3f4f6;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.45);
  }

  .modal-field-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.25rem;
    line-height: 1.4;
  }

  .dark .modal-field-label {
    color: #e5e7eb;
  }

  .modal-field-input {
    margin-top: 0.25rem;
    display: block;
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    padding: 0.5rem 0.75rem;
    background-color: #fff;
    color: #111827;
  }

  .dark .modal-field-input {
    background-color: #374151;
    border-color: #4b5563;
    color: #f9fafb;
  }

  .modal-field-input:focus {
    outline: none;
    border-color: #818cf8;
    box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.25);
  }

  .modal-field-error {
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #ef4444;
  }

  .dark .modal-field-error {
    color: #fca5a5;
  }
  
  .modal-header h3 {
    margin-top: 0;
    color: #111827;
    font-weight: 700;
  }

  .dark .modal-header h3 {
    color: #f9fafb;
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
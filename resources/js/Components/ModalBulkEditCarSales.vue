<script setup>
import { ref, watch, computed } from "vue";

const props = defineProps({
  show: Boolean,
  baseData: {
    type: Object,
    default: () => ({}),
  },
  selectedCount: {
    type: Number,
    default: 0,
  },
});
const emit = defineEmits(["close", "confirm"]);

const formData = ref({});
const exchangeRateError = ref(false);

function cloneBaseData() {
  const data = {
    ...props.baseData,
  };
  delete data.vin;
  delete data.car_number;
  delete data.id;
  delete data.created_at;
  delete data.updated_at;
  delete data.client;
  delete data.contract;
  delete data.exitcar;
  delete data.car_images;
  delete data.CarImages;
  delete data.car_type;
  delete data.car_color;
  delete data.year;
  delete data.dinar_s;
  return data;
}

watch(
  () => props.show,
  (val) => {
    if (val) {
      formData.value = cloneBaseData();
      if (!formData.value.dinar_s && props.baseData?.dinar_s) {
        formData.value.dinar_s = props.baseData.dinar_s;
      }
      if (!formData.value.expenses_s && props.baseData?.expenses_s) {
        formData.value.expenses_s = props.baseData.expenses_s;
      }
      exchangeRateError.value = false;
    } else {
      formData.value = {};
    }
  }
);

function validateExchangeRate() {
  const input = formData.value?.dolar_price_s ?? "";
  if (!input) {
    exchangeRateError.value = false;
    return;
  }
  exchangeRateError.value = !/^\d{6}$/.test(String(input));
}

const isSubmitDisabled = computed(() => exchangeRateError.value);

function handleSubmit() {
  if (isSubmitDisabled.value) {
    return;
  }
  emit("confirm", { ...formData.value });
}
</script>

<template>
  <Transition name="modal">
    <div v-if="show" class="modal-mask">
      <div class="modal-wrapper max-h-[80vh]">
        <div class="modal-container dark:bg-gray-900 overflow-auto max-h-[80vh]">
          <div class="modal-header">
            <slot name="header">
              <h2 class="text-center dark:text-gray-200">
                تعديل جماعي للمبيعات ({{ selectedCount }})
              </h2>
            </slot>
          </div>
          <div class="modal-body">
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
              سيتم تطبيق التعديلات على جميع السيارات المحددة. رقم الشاصي ورقم السيارة لن يتغيرا.
            </p>
            <div
              class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1 lg:gap-2"
            >
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="car_owner">
                  {{ $t("car_owner") }}
                </label>
                <input
                  id="car_owner"
                  type="text"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.car_owner"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="dolar_price_s">
                  {{ $t("dolar_price") }}
                </label>
                <input
                  id="dolar_price_s"
                  @change="validateExchangeRate"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.dolar_price_s"
                />
                <div v-if="exchangeRateError" class="text-red-500">
                  مطلوب رقم من 6 خانة فقط
                </div>
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="shipping_dolar_s">
                  {{ $t("shipping_dolar") }}
                </label>
                <input
                  id="shipping_dolar_s"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.shipping_dolar_s"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="coc_dolar_s">
                  {{ $t("coc_dolar") }}
                </label>
                <input
                  id="coc_dolar_s"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.coc_dolar_s"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="checkout_s">
                  {{ $t("checkout") }}
                </label>
                <input
                  id="checkout_s"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.checkout_s"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="expenses_s">
                  {{ $t("expenses") }}
                </label>
                <input
                  id="expenses_s"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.expenses_s"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="land_shipping_s">
                  نقل بري
                </label>
                <input
                  id="land_shipping_s"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.land_shipping_s"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="land_shipping_dinar_s">
                  نقل وتخليص بالدينار
                </label>
                <input
                  id="land_shipping_dinar_s"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.land_shipping_dinar_s"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="date">
                  {{ $t("date") }}
                </label>
                <input
                  id="date"
                  type="date"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.date"
                />
              </div>
            </div>

            <div className="mb-4 mx-1">
              <label class="dark:text-gray-200" for="note">
                {{ $t("note") }}
              </label>
              <input
                id="note"
                type="text"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                v-model="formData.note"
              />
            </div>
          </div>

          <div class="modal-footer my-2">
            <div class="flex flex-row">
              <div class="basis-1/2 px-4">
                <button
                  class="modal-default-button py-3 bg-gray-500 rounded"
                  @click="$emit('close')"
                >
                  {{ $t("cancel") }}
                </button>
              </div>
              <div class="basis-1/2 px-4">
                <button
                  class="modal-default-button py-3 bg-rose-500 rounded col-6"
                  @click="handleSubmit"
                  :disabled="isSubmitDisabled"
                >
                  {{ $t("yes") }}
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
  padding: 20px 30px;
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


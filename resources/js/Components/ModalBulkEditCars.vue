<script setup>
import { ref, watch, computed } from "vue";

const props = defineProps({
  show: Boolean,
  baseData: {
    type: Object,
    default: () => ({}),
  },
  client: {
    type: Array,
    default: () => [],
  },
  selectedCount: {
    type: Number,
    default: 0,
  },
});
const emit = defineEmits(["close", "confirm"]);

const formData = ref({});
const exchangeRateError = ref(false);
const includedFields = ref({});

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
  delete data.CarImages;
  delete data.car_type;
  delete data.car_color;
  delete data.year;
  delete data.dinar;
  delete data.client_id;
  delete data.client_name;
  delete data.client_phone;
  return data;
}

watch(
  () => props.show,
  (val) => {
    if (val) {
      formData.value = cloneBaseData();
      exchangeRateError.value = false;
      // Initialize all fields as included by default
      const fields = ['car_owner', 'dolar_price', 'shipping_dolar', 'coc_dolar', 'checkout', 'expenses', 'land_shipping', 'land_shipping_dinar', 'date', 'note'];
      includedFields.value = {};
      fields.forEach(field => {
        includedFields.value[field] = true;
      });
    } else {
      formData.value = {};
      includedFields.value = {};
    }
  }
);

function validateExchangeRate() {
  const input = formData.value?.dolar_price ?? "";
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
  // Only include fields that are checked
  const dataToSubmit = {};
  Object.keys(formData.value).forEach(key => {
    if (includedFields.value[key]) {
      dataToSubmit[key] = formData.value[key];
    }
  });
  emit("confirm", dataToSubmit);
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
                تعديل جماعي ({{ selectedCount }})
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
                <div class="flex items-center gap-2 mb-1">
                  <input
                    type="checkbox"
                    id="include_car_owner"
                    v-model="includedFields.car_owner"
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                  />
                  <label class="dark:text-gray-200" for="car_owner">
                    {{ $t("car_owner") }}
                  </label>
                </div>
                <input
                  id="car_owner"
                  type="text"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.car_owner"
                  :disabled="!includedFields.car_owner"
                />
              </div>
              <div className="mb-4 mx-1">
                <div class="flex items-center gap-2 mb-1">
                  <input
                    type="checkbox"
                    id="include_dolar_price"
                    v-model="includedFields.dolar_price"
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                  />
                  <label class="dark:text-gray-200" for="dolar_price">
                    {{ $t("dolar_price") }}
                  </label>
                </div>
                <input
                  id="dolar_price"
                  @change="validateExchangeRate"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.dolar_price"
                  :disabled="!includedFields.dolar_price"
                />
                <div v-if="exchangeRateError" class="text-red-500">
                  مطلوب رقم من 6 خانة فقط
                </div>
              </div>
              <div className="mb-4 mx-1">
                <div class="flex items-center gap-2 mb-1">
                  <input
                    type="checkbox"
                    id="include_shipping_dolar"
                    v-model="includedFields.shipping_dolar"
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                  />
                  <label class="dark:text-gray-200" for="shipping_dolar">
                    {{ $t("shipping_dolar") }}
                  </label>
                </div>
                <input
                  id="shipping_dolar"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.shipping_dolar"
                  :disabled="!includedFields.shipping_dolar"
                />
              </div>
              <div className="mb-4 mx-1">
                <div class="flex items-center gap-2 mb-1">
                  <input
                    type="checkbox"
                    id="include_coc_dolar"
                    v-model="includedFields.coc_dolar"
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                  />
                  <label class="dark:text-gray-200" for="coc_dolar">
                    {{ $t("coc_dolar") }}
                  </label>
                </div>
                <input
                  id="coc_dolar"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.coc_dolar"
                  :disabled="!includedFields.coc_dolar"
                />
              </div>
              <div className="mb-4 mx-1">
                <div class="flex items-center gap-2 mb-1">
                  <input
                    type="checkbox"
                    id="include_checkout"
                    v-model="includedFields.checkout"
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                  />
                  <label class="dark:text-gray-200" for="checkout">
                    {{ $t("checkout") }}
                  </label>
                </div>
                <input
                  id="checkout"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.checkout"
                  :disabled="!includedFields.checkout"
                />
              </div>
              <div className="mb-4 mx-1">
                <div class="flex items-center gap-2 mb-1">
                  <input
                    type="checkbox"
                    id="include_expenses"
                    v-model="includedFields.expenses"
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                  />
                  <label class="dark:text-gray-200" for="expenses">
                    {{ $t("expenses") }}
                  </label>
                </div>
                <input
                  id="expenses"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.expenses"
                  :disabled="!includedFields.expenses"
                />
              </div>
              <div className="mb-4 mx-1">
                <div class="flex items-center gap-2 mb-1">
                  <input
                    type="checkbox"
                    id="include_land_shipping"
                    v-model="includedFields.land_shipping"
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                  />
                  <label class="dark:text-gray-200" for="land_shipping">
                    نقل بري
                  </label>
                </div>
                <input
                  id="land_shipping"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.land_shipping"
                  :disabled="!includedFields.land_shipping"
                />
              </div>
              <div className="mb-4 mx-1">
                <div class="flex items-center gap-2 mb-1">
                  <input
                    type="checkbox"
                    id="include_land_shipping_dinar"
                    v-model="includedFields.land_shipping_dinar"
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                  />
                  <label class="dark:text-gray-200" for="land_shipping_dinar">
                    نقل وتخليص بالدينار
                  </label>
                </div>
                <input
                  id="land_shipping_dinar"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.land_shipping_dinar"
                  :disabled="!includedFields.land_shipping_dinar"
                />
              </div>
              <div className="mb-4 mx-1">
                <div class="flex items-center gap-2 mb-1">
                  <input
                    type="checkbox"
                    id="include_date"
                    v-model="includedFields.date"
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                  />
                  <label class="dark:text-gray-200" for="date">
                    {{ $t("date") }}
                  </label>
                </div>
                <input
                  id="date"
                  type="date"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.date"
                  :disabled="!includedFields.date"
                />
              </div>
            </div>

            <div className="mb-4 mx-1">
              <div class="flex items-center gap-2 mb-1">
                <input
                  type="checkbox"
                  id="include_note"
                  v-model="includedFields.note"
                  class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                />
                <label class="dark:text-gray-200" for="note">
                  {{ $t("note") }}
                </label>
              </div>
              <input
                id="note"
                type="text"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                v-model="formData.note"
                :disabled="!includedFields.note"
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


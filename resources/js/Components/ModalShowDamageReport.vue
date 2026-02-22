<script setup>
import { ref, watch } from 'vue';

let props = defineProps({
  show: Boolean,
  formDamage: Object,
});

const emit = defineEmits(['close', 'save']);

// نسخ عميق لتفادي تعديل الـ props مباشرة
function deepCopyCarsInfo(cars) {
  if (!Array.isArray(cars) || cars.length === 0) return [];
  return cars.map((car) => ({
    car: car.car ?? '',
    vin: car.vin ?? '',
    model: car.model ?? '',
    color: car.color ?? '',
    damage: car.damage ?? '',
  }));
}

const carsInfo = ref([]);
const totalDamage = ref(0);

// حساب مجموع الضرر
const calculateTotal = () => {
  totalDamage.value = carsInfo.value.reduce((sum, car) => {
    const damage = parseFloat(String(car.damage || 0).replace('$', '').trim()) || 0;
    return sum + damage;
  }, 0);
};

// إضافة سيارة جديدة
const addCar = () => {
  carsInfo.value.push({
    car: '',
    vin: '',
    model: '',
    color: '',
    damage: '',
  });
};

// حذف سيارة
const removeCar = (index) => {
  carsInfo.value.splice(index, 1);
  calculateTotal();
};

// مراقبة التغييرات في قائمة السيارات لتحديث المجموع فقط (لا نعدّل الـ props هنا)
watch(carsInfo, () => {
  calculateTotal();
}, { deep: true });

// عند فتح النافذة: تهيئة carsInfo من نسخة من formDamage
watch(
  () => props.show,
  (isShow) => {
    if (!isShow || !props.formDamage) return;
    const source = props.formDamage.cars_info;
    carsInfo.value = deepCopyCarsInfo(source);
    if (carsInfo.value.length === 0) {
      addCar();
    }
    calculateTotal();
  },
  { immediate: true }
);

// عند الحفظ: إرسال formDamage مع cars_info الحالية
function doSave() {
  const payload = {
    ...props.formDamage,
    cars_count: carsInfo.value.length,
    total_damage: totalDamage.value,
    cars_info: deepCopyCarsInfo(carsInfo.value),
  };
  emit('save', payload);
}
</script>

<template>
  <Transition name="modal">
    <div v-if="show" class="modal-mask">
      <div class="modal-wrapper max-h-[90vh]">
        <div class="modal-container dark:bg-gray-900 overflow-auto max-h-[90vh]">
          <div class="modal-header text-center py-4 dark:text-gray-300"> 
            تقرير ضرر السيارات
            <slot name="header"></slot>
          </div>

          <div class="modal-body">
            <div class="grid grid-cols-2 gap-3 lg:gap-2">
              <div class="mb-4 mx-5">
                <label class="dark:text-gray-200" for="driverName">اسم السائق</label>
                <input
                  id="driverName"
                  type="text"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formDamage.driver_name"
                />
              </div>
              <div class="mb-4 mx-5">
                <label class="dark:text-gray-200" for="cmrNumber">رقم CMR</label>
                <input
                  id="cmrNumber"
                  type="text"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formDamage.cmr_number"
                />
              </div>

              <div class="mb-4 mx-5">
                <label class="dark:text-gray-200" for="carsCount">عدد السيارات</label>
                <input
                  id="carsCount"
                  type="number"
                  readonly
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  :value="carsInfo.length"
                />
              </div>
              <div class="mb-4 mx-5">
                <label class="dark:text-gray-200" for="totalDamage">مجموع الضرر ($)</label>
                <input
                  id="totalDamage"
                  type="text"
                  readonly
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  :value="totalDamage.toFixed(2) + '$'"
                />
              </div>

              <div class="mb-4 mx-5">
                <label class="dark:text-gray-200" for="createdDate">التاريخ</label>
                <input
                  id="createdDate"
                  type="date"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formDamage.created"
                />
              </div>
            </div>

            <!-- قائمة السيارات -->
            <div class="mb-4 mx-5">
              <div class="flex justify-between items-center mb-2">
                <label class="dark:text-gray-200 font-bold">معلومات السيارات</label>
                <button
                  @click="addCar"
                  class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                >
                  إضافة سيارة
                </button>
              </div>
              
              <div v-for="(car, index) in carsInfo" :key="index" class="mb-4 p-4 border border-gray-300 dark:border-gray-700 rounded">
                <div class="flex justify-between items-center mb-2">
                  <h4 class="dark:text-gray-200 font-semibold">السيارة {{ index + 1 }}</h4>
                  <button
                    v-if="carsInfo.length > 1"
                    @click="removeCar(index)"
                    class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm"
                  >
                    حذف
                  </button>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="dark:text-gray-200 text-sm">السيارة</label>
                    <input
                      type="text"
                      class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                      v-model="car.car"
                      placeholder="مثال: TOYOTA COROLLA"
                    />
                  </div>
                  <div>
                    <label class="dark:text-gray-200 text-sm">رقم الشانصي (VIN)</label>
                    <input
                      type="text"
                      class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                      v-model="car.vin"
                      placeholder="مثال: LFMAAA0C9S0664914"
                    />
                  </div>
                  <div>
                    <label class="dark:text-gray-200 text-sm">الموديل</label>
                    <input
                      type="text"
                      class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                      v-model="car.model"
                      placeholder="مثال: 2025"
                    />
                  </div>
                  <div>
                    <label class="dark:text-gray-200 text-sm">اللون</label>
                    <input
                      type="text"
                      class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                      v-model="car.color"
                      placeholder="مثال: WHITE"
                    />
                  </div>
                  <div class="col-span-2">
                    <label class="dark:text-gray-200 text-sm">الضرر ($)</label>
                    <input
                      type="text"
                      class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                      v-model="car.damage"
                      placeholder="مثال: 200$"
                      @input="calculateTotal"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer my-2">
            <div class="flex flex-row">
              <div class="basis-1/2 px-4">
                <button class="modal-default-button py-3 bg-gray-500 rounded" @click="$emit('close');">
                  إلغاء
                </button>
              </div>
              <div class="basis-1/2 px-4">
                <button
                  class="modal-default-button py-3 bg-rose-500 rounded col-6"
                  @click="doSave"
                  :disabled="!formDamage.driver_name || !formDamage.cmr_number || carsInfo.length === 0"
                >
                  حفظ
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


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
        <div class="modal-container max-h-[90vh] overflow-auto !bg-slate-800 !text-slate-100 border border-slate-700 shadow-xl">
          <div class="modal-header py-4 text-center text-lg font-bold text-white">
            تقرير ضرر السيارات
            <slot name="header"></slot>
          </div>

          <div class="modal-body">
            <div class="grid grid-cols-2 gap-3 lg:gap-2">
              <div class="mx-5 mb-4">
                <label class="text-sm font-medium text-slate-200" for="driverName">اسم السائق</label>
                <input
                  id="driverName"
                  type="text"
                  class="mt-1.5 block w-full rounded-xl border border-slate-600 bg-slate-900 text-white placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-500/30"
                  v-model="formDamage.driver_name"
                />
              </div>
              <div class="mx-5 mb-4">
                <label class="text-sm font-medium text-slate-200" for="cmrNumber">رقم CMR</label>
                <input
                  id="cmrNumber"
                  type="text"
                  class="mt-1.5 block w-full rounded-xl border border-slate-600 bg-slate-900 text-white placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-500/30"
                  v-model="formDamage.cmr_number"
                />
              </div>

              <div class="mx-5 mb-4">
                <label class="text-sm font-medium text-slate-200" for="carsCount">عدد السيارات</label>
                <input
                  id="carsCount"
                  type="number"
                  readonly
                  class="mt-1.5 block w-full rounded-xl border border-slate-600 bg-slate-900 text-white shadow-sm"
                  :value="carsInfo.length"
                />
              </div>
              <div class="mx-5 mb-4">
                <label class="text-sm font-medium text-slate-200" for="totalDamage">مجموع الضرر ($)</label>
                <input
                  id="totalDamage"
                  type="text"
                  readonly
                  class="mt-1.5 block w-full rounded-xl border border-slate-600 bg-slate-900 font-bold text-rose-300 shadow-sm"
                  :value="totalDamage.toFixed(2) + '$'"
                />
              </div>

              <div class="mx-5 mb-4">
                <label class="text-sm font-medium text-slate-200" for="createdDate">التاريخ</label>
                <input
                  id="createdDate"
                  type="date"
                  class="mt-1.5 block w-full rounded-xl border border-slate-600 bg-slate-900 text-white shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-500/30"
                  v-model="formDamage.created"
                />
              </div>
            </div>

            <!-- قائمة السيارات -->
            <div class="mx-5 mb-4">
              <div class="mb-3 flex items-center justify-between gap-3">
                <label class="font-bold text-white">معلومات السيارات</label>
                <button
                  type="button"
                  @click="addCar"
                  class="rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-500"
                >
                  إضافة سيارة
                </button>
              </div>
              
              <div v-for="(car, index) in carsInfo" :key="index" class="mb-4 rounded-2xl border border-slate-600 bg-slate-900/60 p-4">
                <div class="mb-3 flex items-center justify-between gap-2">
                  <h4 class="font-semibold text-white">السيارة {{ index + 1 }}</h4>
                  <button
                    v-if="carsInfo.length > 1"
                    type="button"
                    @click="removeCar(index)"
                    class="rounded-lg bg-rose-600 px-3 py-1 text-sm font-semibold text-white transition hover:bg-rose-500"
                  >
                    حذف
                  </button>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="text-sm text-slate-200">السيارة</label>
                    <input
                      type="text"
                      class="mt-1.5 block w-full rounded-xl border border-slate-600 bg-slate-900 text-white placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-500/30"
                      v-model="car.car"
                      placeholder="مثال: TOYOTA COROLLA"
                    />
                  </div>
                  <div>
                    <label class="text-sm text-slate-200">رقم الشانصي (VIN)</label>
                    <input
                      type="text"
                      class="mt-1.5 block w-full rounded-xl border border-slate-600 bg-slate-900 text-white placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-500/30"
                      v-model="car.vin"
                      placeholder="مثال: LFMAAA0C9S0664914"
                    />
                  </div>
                  <div>
                    <label class="text-sm text-slate-200">الموديل</label>
                    <input
                      type="text"
                      class="mt-1.5 block w-full rounded-xl border border-slate-600 bg-slate-900 text-white placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-500/30"
                      v-model="car.model"
                      placeholder="مثال: 2025"
                    />
                  </div>
                  <div>
                    <label class="text-sm text-slate-200">اللون</label>
                    <input
                      type="text"
                      class="mt-1.5 block w-full rounded-xl border border-slate-600 bg-slate-900 text-white placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-500/30"
                      v-model="car.color"
                      placeholder="مثال: WHITE"
                    />
                  </div>
                  <div class="col-span-2">
                    <label class="text-sm text-slate-200">الضرر ($)</label>
                    <input
                      type="text"
                      class="mt-1.5 block w-full rounded-xl border border-slate-600 bg-slate-900 text-white placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-500/30"
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
                <button type="button" class="modal-default-button rounded-xl bg-slate-600 py-3 font-semibold text-white transition hover:bg-slate-500" @click="$emit('close');">
                  إلغاء
                </button>
              </div>
              <div class="basis-1/2 px-4">
                <button
                  type="button"
                  class="modal-default-button col-6 rounded-xl bg-emerald-600 py-3 font-bold text-white transition hover:bg-emerald-500 disabled:opacity-50"
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


<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/inertia-vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useToast } from "vue-toastification";

const toast = useToast();

const form = useForm({
  sailing_date: '',
  captain: '',
  pol: '',
  pod: '',
  flag: '',
  ship_name: '',
  voy_no: '',
  note: '',
});

const submit = () => {
  form.post(route('trips.store'), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('تم إنشاء الرحلة بنجاح');
    },
    onError: (errors) => {
      toast.error('حدث خطأ أثناء إنشاء الرحلة');
    },
  });
};
</script>

<template>
  <Head title="إنشاء رحلة جديدة" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          إنشاء رحلة جديدة
        </h2>
        <Link
          :href="route('trips')"
          class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 w-full sm:w-auto justify-center"
        >
          العودة للقائمة
        </Link>
      </div>
    </template>

    <div class="py-6 sm:py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
          <form @submit.prevent="submit" class="p-4 sm:p-6 lg:p-8 space-y-6">
            <!-- SAILING DATE -->
            <div>
              <InputLabel for="sailing_date" value="تاريخ الإبحار (SAILING DATE) *" />
              <TextInput
                id="sailing_date"
                v-model="form.sailing_date"
                type="date"
                class="mt-1 block w-full"
                required
                autofocus
              />
              <span v-if="form.errors.sailing_date" class="mt-2 text-sm text-red-600">
                {{ form.errors.sailing_date }}
              </span>
            </div>

            <!-- Ship Name & Voyage Number - Responsive Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
              <div>
                <InputLabel for="ship_name" value="اسم السفينة (SHIP NAME) *" />
                <TextInput
                  id="ship_name"
                  v-model="form.ship_name"
                  type="text"
                  class="mt-1 block w-full"
                  placeholder="مثال: NOOR 91"
                  required
                />
                <span v-if="form.errors.ship_name" class="mt-2 text-sm text-red-600">
                  {{ form.errors.ship_name }}
                </span>
              </div>

              <div>
                <InputLabel for="voy_no" value="رقم الرحلة (VOY. NO)" />
                <TextInput
                  id="voy_no"
                  v-model="form.voy_no"
                  type="text"
                  class="mt-1 block w-full"
                  placeholder="مثال: 113574"
                />
                <span v-if="form.errors.voy_no" class="mt-2 text-sm text-red-600">
                  {{ form.errors.voy_no }}
                </span>
              </div>
            </div>

            <!-- Captain & Flag - Responsive Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
              <div>
                <InputLabel for="captain" value="اسم القبطان (CAPTAIN)" />
                <TextInput
                  id="captain"
                  v-model="form.captain"
                  type="text"
                  class="mt-1 block w-full"
                  placeholder="مثال: EMRAN ABDOLRAHMAN ZAREI"
                />
                <span v-if="form.errors.captain" class="mt-2 text-sm text-red-600">
                  {{ form.errors.captain }}
                </span>
              </div>

              <div>
                <InputLabel for="flag" value="علم السفينة (FLAG)" />
                <TextInput
                  id="flag"
                  v-model="form.flag"
                  type="text"
                  class="mt-1 block w-full"
                />
                <span v-if="form.errors.flag" class="mt-2 text-sm text-red-600">
                  {{ form.errors.flag }}
                </span>
              </div>
            </div>

            <!-- P.O.L & P.O.D - Responsive Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
              <div>
                <InputLabel for="pol" value="ميناء التحميل (P.O.L) *" />
                <TextInput
                  id="pol"
                  v-model="form.pol"
                  type="text"
                  class="mt-1 block w-full"
                  placeholder="مثال: PORT RASHID DUBAI"
                  required
                />
                <span v-if="form.errors.pol" class="mt-2 text-sm text-red-600">
                  {{ form.errors.pol }}
                </span>
              </div>

              <div>
                <InputLabel for="pod" value="ميناء التفريغ (P.O.D) *" />
                <TextInput
                  id="pod"
                  v-model="form.pod"
                  type="text"
                  class="mt-1 block w-full"
                  placeholder="مثال: BANDAR SIRIK"
                  required
                />
                <span v-if="form.errors.pod" class="mt-2 text-sm text-red-600">
                  {{ form.errors.pod }}
                </span>
              </div>
            </div>

            <!-- Note -->
            <div>
              <InputLabel for="note" value="ملاحظات (NOTE)" />
              <textarea
                id="note"
                v-model="form.note"
                rows="4"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="أي ملاحظات إضافية..."
              ></textarea>
              <span v-if="form.errors.note" class="mt-2 text-sm text-red-600">
                {{ form.errors.note }}
              </span>
            </div>

            <!-- Submit Buttons - Responsive -->
            <div class="flex flex-col sm:flex-row justify-end gap-3 sm:gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
              <Link
                :href="route('trips')"
                class="inline-flex justify-center items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150"
              >
                إلغاء
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
              >
                <span v-if="form.processing" class="ml-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ form.processing ? 'جاري الحفظ...' : 'إنشاء الرحلة' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

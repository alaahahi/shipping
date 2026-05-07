<script setup>
import { ref, computed, watch } from "vue";
import axios from 'axios';
import { ModelListSelect } from "vue-search-select"
import * as XLSX from "xlsx";
  // Import everythModelSelecting
import "vue-search-select/dist/VueSearchSelect.css"
const props = defineProps({
  show: Boolean,
  formData: Object,
  client: Array,
  tagOptions: {
    type: Array,
    default: () => [],
  },
});
const emit = defineEmits(["close", "a"]);
const tagInput = ref("");
const selectedTagToAdd = ref("");
const tagActionLoading = ref(false);

const carEntries = ref([]);
let carEntryUid = 0;

function createCarEntry(data = {}) {
  carEntryUid += 1;
  return {
    id: carEntryUid,
    vin: data.vin ?? "",
    car_number: data.car_number ?? "",
    car_type: data.car_type ?? "",
    car_color: data.car_color ?? "",
    expenses: data.expenses ?? "",
    error: false,
  };
}

function initializeCars() {
  const baseEntries = Array.isArray(props.formData?.cars) && props.formData.cars.length
    ? props.formData.cars
    : [{
        vin: props.formData?.vin ?? "",
        car_number: props.formData?.car_number ?? "",
      }];

  const prepared = baseEntries
    .map((entry) => createCarEntry(entry))
    .filter((entry, index, self) => {
      // keep duplicates for now, will handle via validation, but ensure at least one entry
      return entry;
    });

  carEntries.value = prepared.length ? prepared : [createCarEntry()];
}

watch(
  () => props.show,
  (show) => {
    if (show) {
      initializeCars();
      if (props.formData) {
        const currentTags = Array.isArray(props.formData.tags) ? props.formData.tags : [];
        props.formData.tags = currentTags.map((t) => (typeof t === "object" ? t.id : t)).filter(Boolean);
      }
      if (props.formData?.client_id) {
        showClient.value = false;
      }
    } else {
      carEntries.value = [];
    }
  }
);

watch(
  carEntries,
  (entries) => {
    if (!props.formData) {
      return;
    }
    props.formData.cars = entries.map(({ vin, car_number, car_type, car_color, expenses }) => ({
      vin: vin ?? "",
      car_number: car_number ?? "",
      car_type: car_type ?? "",
      car_color: car_color ?? "",
      expenses: expenses ?? "",
    }));
    props.formData.vin = entries[0]?.vin ?? "";
    props.formData.car_number = entries[0]?.car_number ?? "";
  },
  { deep: true }
);
    
function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0") ;
  if(props.formData.days){
    return `${year}-${month}-${day-props.formData.days}`;
  }
  else{
    return `${year}-${month}-${day}`;

  }
}
function VinApi (v){
  props.formData.car_type=''
    props.formData.year=''
    axios.get(`https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvalues/${v}?format=json`)
  .then(response => {
    props.formData.car_type=(response.data.Results[0].Make ? response.data.Results[0].Make:response.data.Results[0].Manufacturer)+' '+response.data.Results[0].Model
    props.formData.year=response.data.Results[0].ModelYear

  })
  .catch(error => {
    console.error(error);
  })
}
let showClient = ref(false);
let exchangeRateError= ref(false);
function validateExchangeRate(v) {
      const input = props.formData.dolar_price;
      if (/^\d{6}$/.test(input)) {
        exchangeRateError.value = false;
      } else {
        exchangeRateError.value = true;
      }
    }
function onSelect (items, lastSelectItem) {
    items.value = items

    lastSelectItem.value = lastSelectItem
    }
function addCarEntry() {
  carEntries.value.push(createCarEntry());
}
function removeCarEntry(index) {
  if (carEntries.value.length === 1) {
    carEntries.value[0].vin = "";
    carEntries.value[0].car_number = "";
    carEntries.value[0].error = false;
    return;
  }
  carEntries.value.splice(index, 1);
}
function checkVin(entry) {
  const vin = entry.vin ? entry.vin.trim() : "";
  if (!vin) {
    entry.error = false;
    return;
  }
  axios
    .get(`/api/check_vin?car_vin=${vin}`)
    .then((response) => {
      entry.error = response.data || false;
    })
    .catch((error) => {
      console.error(error);
      entry.error = {
        serverError: true,
      };
    });
}
const duplicateVinMap = computed(() => {
  const map = {};
  carEntries.value.forEach(({ vin }) => {
    const key = vin ? vin.trim().toUpperCase() : "";
    if (!key) {
      return;
    }
    map[key] = (map[key] || 0) + 1;
  });
  return map;
});
function isDuplicateVin(entry) {
  const key = entry.vin ? entry.vin.trim().toUpperCase() : "";
  if (!key) {
    return false;
  }
  return (duplicateVinMap.value[key] || 0) > 1;
}
const invalidEntries = computed(() =>
  carEntries.value.filter((entry) => {
    const hasVin = entry.vin && entry.vin.trim();
    if (!hasVin) {
      return false;
    }
    if (isDuplicateVin(entry)) {
      return true;
    }
    return !!entry.error;
  })
);
const hasAtLeastOneVin = computed(() =>
  carEntries.value.some((entry) => entry.vin && entry.vin.trim())
);
const isSubmitDisabled = computed(() => {
  const missingClient =
    (!props.formData?.client_id || props.formData.client_id === "") &&
    (!props.formData?.client_name || props.formData.client_name === "");
  return (
    missingClient ||
    exchangeRateError.value ||
    !hasAtLeastOneVin.value ||
    invalidEntries.value.length > 0
  );
});
function prepareCarsPayload() {
  return carEntries.value
    .map(({ vin, car_number, car_type, car_color, expenses }) => ({
      vin: vin ? vin.trim() : "",
      car_number: car_number ? String(car_number).trim() : null,
      car_type: car_type ? String(car_type).trim() : null,
      car_color: car_color ? String(car_color).trim() : null,
      expenses: expenses !== "" && expenses !== null && expenses !== undefined ? Number(expenses) : null,
    }))
    .filter((entry) => entry.vin);
}

const excelLoading = ref(false);

function normalizeHeader(value) {
  return String(value || "")
    .trim()
    .toLowerCase()
    .replace(/[\s_\-]+/g, "");
}

function getMappedValue(row, keys = []) {
  const map = {};
  Object.keys(row || {}).forEach((key) => {
    map[normalizeHeader(key)] = row[key];
  });
  for (const key of keys) {
    const value = map[normalizeHeader(key)];
    if (value !== undefined && value !== null && String(value).trim() !== "") {
      return value;
    }
  }
  return null;
}

function importCarsFromExcel(event) {
  const file = event?.target?.files?.[0];
  if (!file) return;

  excelLoading.value = true;
  const reader = new FileReader();
  reader.onload = (e) => {
    try {
      const data = e.target.result;
      const workbook = XLSX.read(data, { type: "array" });
      const sheetName = workbook.SheetNames[0];
      const worksheet = workbook.Sheets[sheetName];
      const rows = XLSX.utils.sheet_to_json(worksheet, { defval: "" });

      const imported = rows
        .map((row) => {
          const vin = getMappedValue(row, ["vin", "chassis", "chassisno", "رقمالشاصي", "الشاصي"]);
          const carType = getMappedValue(row, ["car_type", "cartype", "name", "model", "اسم السيارة", "السيارة"]);
          const carColor = getMappedValue(row, ["car_color", "carcolor", "color", "اللون"]);
          const expenses = getMappedValue(row, ["expenses", "expense", "مصاريف", "المصاريف"]);
          const carNumber = getMappedValue(row, ["car_number", "carnumber", "number", "رقمالسيارة"]);
          if (!vin || !String(vin).trim()) return null;
          return createCarEntry({
            vin: String(vin).trim(),
            car_number: carNumber ? String(carNumber).trim() : "",
            car_type: carType ? String(carType).trim() : "",
            car_color: carColor ? String(carColor).trim() : "",
            expenses: expenses !== "" && expenses !== null && expenses !== undefined ? Number(expenses) : "",
          });
        })
        .filter(Boolean);

      if (!imported.length) {
        alert("لم يتم العثور على صفوف صالحة. تأكد من وجود عمود VIN/رقم الشاصي.");
      } else {
        carEntries.value = imported;
      }
    } catch (error) {
      console.error(error);
      alert("تعذر قراءة ملف Excel");
    } finally {
      excelLoading.value = false;
      event.target.value = "";
    }
  };
  reader.readAsArrayBuffer(file);
}

function downloadExcelTemplate() {
  const templateRows = [
    {
      vin: "1HGCM82633A123456",
      name: "Camry",
      color: "White",
      expenses: 120,
      car_number: "45",
    },
    {
      vin: "JH4KA9650MC012345",
      name: "Sonata",
      color: "Black",
      expenses: 95,
      car_number: "77",
    },
  ];
  const worksheet = XLSX.utils.json_to_sheet(templateRows);
  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, "cars_template");
  XLSX.writeFile(workbook, "cars_import_template.xlsx");
}
function handleSubmit() {
  props.formData.date = props.formData.date
    ? props.formData.date
    : getTodayDate();
  const carsPayload = prepareCarsPayload();
  const payload = {
    ...props.formData,
    cars: carsPayload,
    vin: carsPayload[0]?.vin ?? "",
    car_number: carsPayload[0]?.car_number ?? "",
  };
  emit("a", payload);
}

function addTagFromInput() {
  const name = tagInput.value ? tagInput.value.trim() : "";
  if (!name || !props.formData) return;
  if (props.formData.id) {
    addTagToCar({ tagName: name });
  } else {
    if (!Array.isArray(props.formData.tags)) {
      props.formData.tags = [];
    }
    const exists = props.formData.tags.some((t) => String(t).toLowerCase() === name.toLowerCase());
    if (!exists) {
      props.formData.tags.push(name);
    }
    tagInput.value = "";
  }
}

function ensureTagsArray() {
  if (!Array.isArray(props.formData.tags)) {
    props.formData.tags = [];
  }
}

function addSelectedTag() {
  if (!selectedTagToAdd.value || !props.formData) return;
  if (props.formData.id) {
    addTagToCar({ tagId: selectedTagToAdd.value });
  } else {
    ensureTagsArray();
    const idVal = Number(selectedTagToAdd.value) || selectedTagToAdd.value;
    if (!props.formData.tags.includes(idVal)) {
      props.formData.tags.push(idVal);
    }
    selectedTagToAdd.value = "";
  }
}

async function removeTag(tagValue) {
  if (!props.formData) return;
  const label = getTagLabel(tagValue);
  if (!confirm(`هل تريد حذف التاغ "${label}"؟`)) {
    return;
  }
  if (props.formData.id) {
    await removeTagFromCar(tagValue);
    return;
  }
  ensureTagsArray();
  props.formData.tags = props.formData.tags.filter((t) => t !== tagValue);
}

function getTagLabel(tagValue) {
  const idVal = Number(tagValue);
  const found = props.tagOptions.find((tag) => Number(tag.id) === idVal);
  return found ? found.name : tagValue;
}

function syncTagsFromCarResponse(car) {
  if (!car || !Array.isArray(car.tags) || !props.formData) return;
  props.formData.tags = car.tags.map((tag) => tag.id).filter(Boolean);
}

function extractApiError(error, fallback) {
  return error?.response?.data?.message || fallback;
}

async function addTagToCar({ tagId = null, tagName = "" } = {}) {
  if (!props.formData?.id || tagActionLoading.value) return;
  tagActionLoading.value = true;
  try {
    const payload = { car_id: props.formData.id };
    if (tagId) {
      payload.tag_id = Number(tagId);
    } else if (tagName) {
      payload.tag_name = tagName;
    }
    const response = await axios.post("/api/addTagToCar", payload);
    syncTagsFromCarResponse(response?.data?.car);
    tagInput.value = "";
    selectedTagToAdd.value = "";
  } catch (error) {
    alert(extractApiError(error, "تعذر إضافة التاغ الآن"));
  } finally {
    tagActionLoading.value = false;
  }
}

async function removeTagFromCar(tagValue) {
  if (!props.formData?.id || tagActionLoading.value) return;
  const tagId = Number(tagValue);
  if (!tagId) {
    alert("لا يمكن حذف تاغ غير محفوظ من السيارة الحالية");
    return;
  }
  tagActionLoading.value = true;
  try {
    const response = await axios.post("/api/removeTagFromCar", {
      car_id: props.formData.id,
      tag_id: tagId,
    });
    syncTagsFromCarResponse(response?.data?.car);
  } catch (error) {
    alert(extractApiError(error, "تعذر حذف التاغ الآن"));
  } finally {
    tagActionLoading.value = false;
  }
}
</script>
  <template>
  <Transition name="modal">
    <div v-if="show" class="modal-mask">
      <div class="modal-wrapper  max-h-[80vh]">
        <div class="modal-container dark:bg-gray-900 overflow-auto  max-h-[80vh]">
          <div class="modal-header">
            <slot name="header">
              <h2 class="text-center dark:text-gray-200">
                {{ $t("add_car") }}
              </h2>
            </slot>
          </div>
          <div class="modal-body">

            <div
              class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-1 lg:gap-2"
              v-if="!formData.id"
            >

              <div class="mb-4 mx-1">
                <label class="dark:text-gray-200" for="color_id">{{
                  $t("car_owner")
                }}</label>
                <div class="relative">
                <ModelListSelect
                  v-if="!showClient"
                  optionValue="id"
                  optionText="name"
                  v-model="formData.client_id"
                  :list="client"
                  :placeholder="$t('selectCustomer')">
                </ModelListSelect>
                  <button
                    type="button"
                    @click="
                      showClient = true;
                      formData.client_name = '';
                      formData.client_id='';
                    "
                    v-if="!showClient"
                    class="absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-green-500 rounded-tl-lg rounded-bl-lg"
                  >
                    {{ $t("addCustomer") }}
                  </button>
                </div>
                <div class="relative">
                  <input
                    id="note"
                    v-if="showClient"
                    type="text"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                    v-model="formData.client_name"
                  />
                  <button
                    type="button"
                    @click="
                      showClient = false;
                      formData.client = '';
                    "
                    v-if="showClient"
                    class="absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-pink-500 rounded-tl-lg rounded-bl-lg"
                  >
                    {{ $t("selectCustomer") }}
                  </button>
                </div>
              </div>
              <div className="mb-4 mx-1" v-if="showClient">
                <label class="dark:text-gray-200" for="number">
                {{ $t("phoneNumber") }}
              </label>
                <input
                  id="number"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.client_phone"
                />
              </div>
            </div>
            <div
              class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1 lg:gap-2"
            >  

  


 
              <div className="mb-4 mx-1 col-span-2 lg:col-span-4">
                <div class="flex items-center justify-between">
                  <label class="dark:text-gray-200" for="vin">
                    {{ $t("vin") }}
                  </label>
                  <div class="flex items-center gap-2">
                    <button
                      type="button"
                      class="px-2 py-1 text-white bg-slate-600 rounded-md text-sm font-semibold"
                      @click="downloadExcelTemplate"
                    >
                      تحميل قالب
                    </button>
                    <label class="px-2 py-1 text-white bg-indigo-600 rounded-md text-sm font-semibold cursor-pointer">
                      {{ excelLoading ? "جاري القراءة..." : "استيراد Excel" }}
                      <input
                        type="file"
                        class="hidden"
                        accept=".xlsx,.xls"
                        :disabled="excelLoading"
                        @change="importCarsFromExcel"
                      />
                    </label>
                    <button
                      type="button"
                      class="px-2 py-1 text-white bg-green-600 rounded-md text-sm font-semibold"
                      @click="addCarEntry"
                    >
                      +
                    </button>
                  </div>
                </div>
                <div
                  v-for="(entry, index) in carEntries"
                  :key="entry.id"
                  class="mt-3 border border-dashed border-gray-300 rounded-lg p-3 dark:border-gray-600"
                >
                  <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold dark:text-gray-100">
                      {{ $t("car") }} {{ index + 1 }}
                    </h3>
                    <button
                      type="button"
                      class="px-2 py-1 text-sm font-semibold text-white bg-rose-500 rounded-md"
                      @click="removeCarEntry(index)"
                    >
                      &minus;
                    </button>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-2">
                    <div>
                      <label class="dark:text-gray-200 block text-sm" :for="`vin_${entry.id}`">
                        {{ $t("vin") }}
                      </label>
                      <input
                        :id="`vin_${entry.id}`"
                        type="text"
                        @change="checkVin(entry)"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                        v-model="entry.vin"
                      />
                      <div
                        class="text-red-700 text-sm mt-2"
                        v-if="entry.error && entry.error.client_id"
                      >
                        رقم الشاصي مستخدم - {{ entry.error.car_type }} -
                        {{ entry.error.year }} - بتاريخ
                        <br />
                        {{ entry.error.date }}
                      </div>
                      <div
                        class="text-red-700 text-sm mt-2"
                        v-else-if="entry.error && entry.error.price_s"
                      >
                        رقم الشاصي عاطل - {{ entry.error.car_type }} -
                        {{ entry.error.year }}
                        <br />
                        سعر المشتريات {{ entry.error.price_p }}
                        و سعر المبيعات {{ entry.error.price_s }}
                        -
                        بتاريخ
                        {{ entry.error.date }}
                      </div>
                      <div
                        class="text-red-700 text-sm mt-2"
                        v-else-if="entry.error && entry.error.serverError"
                      >
                        حدث خطأ أثناء التحقق من رقم الشاصي، حاول مرة أخرى.
                      </div>
                      <div
                        class="text-red-700 text-sm mt-2"
                        v-else-if="isDuplicateVin(entry)"
                      >
                        رقم الشاصي مكرر في القائمة الحالية.
                      </div>
                    </div>
                    <div>
                      <label class="dark:text-gray-200 block text-sm" :for="`car_number_${entry.id}`">
                        {{ $t("car_number") }}
                      </label>
                      <input
                        :id="`car_number_${entry.id}`"
                        type="text"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                        v-model="entry.car_number"
                      />
                    </div>
                    <div>
                      <label class="dark:text-gray-200 block text-sm" :for="`car_type_${entry.id}`">اسم السيارة</label>
                      <input
                        :id="`car_type_${entry.id}`"
                        type="text"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                        v-model="entry.car_type"
                      />
                    </div>
                    <div>
                      <label class="dark:text-gray-200 block text-sm" :for="`car_color_${entry.id}`">اللون</label>
                      <input
                        :id="`car_color_${entry.id}`"
                        type="text"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                        v-model="entry.car_color"
                      />
                    </div>
                    <div>
                      <label class="dark:text-gray-200 block text-sm" :for="`expenses_${entry.id}`">مصاريف الصف</label>
                      <input
                        :id="`expenses_${entry.id}`"
                        type="number"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                        v-model="entry.expenses"
                      />
                    </div>
                  </div>
                </div>
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="pin">
                  {{ $t("car_type") }}</label
                >
                <input
                  id="car_type"
                  type="text"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.car_type"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="pin">
                  {{ $t("year") }}</label
                >
                <input
                  id="year"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.year"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="pin">
                  {{ $t("color") }}</label
                >
                <input
                  id="car_color"
                  type="text"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.car_color"
                />
              </div>

       
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="dinar">
                  {{ $t("dinar") }}</label
                >
                <input
                  id="dinar"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.dinar"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="dolar_price">
                  {{ $t("dolar_price") }}</label
                >
                <input
                  id="dolar_price"
                  @change="validateExchangeRate"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.dolar_price"
                />
                <div v-if="exchangeRateError" class="text-red-500">
                          مطلوب رقم من 6 خانة فقط
                </div>
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="shipping_dolar">
                  {{ $t("shipping_dolar") }}</label
                >
                <input
                  id="shipping_dolar"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.shipping_dolar"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="coc_dolar">
                  {{ $t("coc_dolar") }}</label
                >
                <input
                  id="coc_dolar"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.coc_dolar"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="checkout">
                  {{ $t("checkout") }}</label
                >
                <input
                  id="checkout"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.checkout"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="expenses">
                  {{ $t("expenses") }}</label
                >
                <input
                  id="expenses"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.expenses"
                />
              </div>

              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="expenses">
                 نقل بري
                  </label
                >
                <input
                  id="expenses"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.land_shipping"
                />
              </div>
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="expenses">
                  نقل وتخليص بالدينار
                  </label
                >
                <input
                  id="expenses"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.land_shipping_dinar"
                />
                </div>
              <!-- <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="paid">
                  {{ $t("paid") }}</label
                >
                <input
                  id="paid"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.paid"
                />
              </div> -->
              <div className="mb-4 mx-1">
                <label class="dark:text-gray-200" for="date">
                  {{ $t("date") }}</label
                >
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
                {{$t("note")}}
              </label>
              <input
                id="note"
                type="text"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                v-model="formData.note"
              />
            </div>
            <div className="mb-4 mx-1">
              <label class="dark:text-gray-200" for="car_tags">
                تاغات السيارة
              </label>
              <div class="mt-2 flex gap-2">
                <select
                  id="car_tags"
                  v-model="selectedTagToAdd"
                  class="block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                >
                  <option value="">اختر تاغ من القائمة</option>
                  <option
                    v-for="tag in tagOptions.filter((opt) => !(formData.tags || []).includes(opt.id))"
                    :key="tag.id"
                    :value="tag.id"
                  >
                    {{ tag.name }}
                  </option>
                </select>
                <button type="button" class="px-3 py-1 bg-indigo-600 text-white rounded" @click="addSelectedTag">إضافة</button>
              </div>
              <div class="mt-3 flex flex-wrap gap-2">
                <span
                  v-for="tag in (formData.tags || [])"
                  :key="String(tag)"
                  class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-100"
                >
                  {{ getTagLabel(tag) }}
                  <button type="button" class="text-red-600 font-bold" @click="removeTag(tag)">×</button>
                </span>
              </div>
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
                  :disabled="isSubmitDisabled">
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
  .ui.fluid.search.selection.dropdown{
    justify-content: revert;
    display: flex;
    min-height: 40px;
  }
  .ui.dropdown .menu .selected.item{
    background-color: #e012035d;
  }
  .ui.dropdown .menu>.item {
    text-align: right;
  }
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
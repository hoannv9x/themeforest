<!-- components/FilterSidebar.vue -->
<template>
  <div class="p-4 bg-white rounded-xl shadow space-y-4">
    <h2 class="font-semibold text-lg">Filters</h2>

    <!-- Keyword -->
    <input
      v-model="filters.keyword"
      type="text"
      placeholder="Search..."
      class="w-full border rounded-lg px-3 py-2"
    />

    <!-- City Filter -->
    <div class="mb-4">
      <label for="city" class="block text-gray-700 font-medium mb-2">City/Province</label>
      <select
        id="city"
        v-model="filters.city_id"
        @change="onCityChange"
        class="w-full p-2 border rounded-md"
      >
        <option :value="null">All Cities</option>
        <option v-for="city in cities" :key="city.id" :value="city.id">
          {{ city.name }}
        </option>
      </select>
    </div>

    <!-- District Filter -->
    <div class="mb-4">
      <label for="district" class="block text-gray-700 font-medium mb-2">District</label>
      <select
        id="district"
        v-model="filters.district_id"
        :disabled="!filters.city_id"
        class="w-full p-2 border rounded-md disabled:bg-gray-200"
      >
        <option :value="null">All Districts</option>
        <option v-for="district in districts" :key="district.id" :value="district.id">
          {{ district.name }}
        </option>
      </select>
    </div>

    <!-- Property Type Filter -->
    <div class="mb-4">
      <label for="property-type" class="block text-gray-700 font-medium mb-2"
        >Property Type</label
      >
      <select
        id="property-type"
        v-model="filters.property_type"
        class="w-full p-2 border rounded-md"
      >
        <option :value="null">All Types</option>
        <option v-for="type in propertyTypes" :key="type.id" :value="type.id">
          {{ type.name }}
        </option>
      </select>
    </div>

    <!-- Bedrooms Filter -->
    <div class="mb-4">
      <label for="bedrooms" class="block text-gray-700 font-medium mb-2">Bedrooms</label>
      <select
        id="bedrooms"
        v-model.number="filters.bedrooms"
        class="w-full p-2 border rounded-md"
      >
        <option :value="null">Any</option>
        <option v-for="n in 5" :key="n" :value="n">{{ n }}+ Beds</option>
      </select>
    </div>

    <!-- Bathrooms Filter -->
    <div class="mb-6">
      <label for="bathrooms" class="block text-gray-700 font-medium mb-2"
        >Bathrooms</label
      >
      <select
        id="bathrooms"
        v-model.number="filters.bathrooms"
        class="w-full p-2 border rounded-md"
      >
        <option :value="null">Any</option>
        <option v-for="n in 5" :key="n" :value="n">{{ n }}+ Baths</option>
      </select>
    </div>

    <!-- Price -->
    <div>
      <label class="text-sm mt-3">Khoảng giá</label>
      <div class="flex flex-wrap gap-2 mt-3">
        <button
          v-for="opt in priceOptions"
          :key="opt.label"
          @click="selectPrice(opt)"
          :class="[
            'px-3 py-1 text-sm border rounded-full',
            minValue == opt.min && maxValue == opt.max
              ? 'bg-blue-500 text-white'
              : 'hover:bg-blue-50',
          ]"
        >
          {{ opt.label }}
        </button>
      </div>
    </div>

    <!-- Action buttons -->
    <div class="flex gap-2">
      <button @click="apply" class="flex-1 bg-blue-500 text-white py-2 rounded-lg">
        Apply
      </button>
      <button @click="reset" class="flex-1 bg-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-400">
        Reset
      </button>
    </div>
  </div>
</template>

<script setup>
/* ========== Props & Emits ========== */
const props = defineProps({
  cities: {
    type: Array,
    default: () => [],
  },
  districts: {
    type: Array,
    default: () => [],
  },
  propertyTypes: {
    type: Array,
    default: () => [],
  },
  filtersDefault: {
    type: Object,
    default: () => ({}),
  },
});
const emit = defineEmits(["apply-filters", "city-change"]);

/* ========== Constants ========== */
const priceOptions = [
  { label: "Tất cả", min: 0, max: 5000000000 },
  { label: "Dưới 100 triệu", min: 0, max: 100000000 },
  { label: "100 - 500 triệu", min: 100000000, max: 500000000 },
  { label: "500 triệu - 1 tỷ", min: 500000000, max: 1000000000 },
  { label: "1 - 3 tỷ", min: 1000000000, max: 3000000000 },
  { label: "Trên 3 tỷ", min: 3000000000, max: 5000000000 },
];

const gap = 100000000;

/* ========== Reactive State ========== */
const minValue = ref(0);
const maxValue = ref(5000000000);

const filters = reactive({
  keyword: "",
  bedrooms: null,
  bathrooms: null,
  property_type: null,
  city_id: null,
  district_id: null,
});

/* ========== Watchers ========== */
watch(minValue, () => {
  if (maxValue.value - minValue.value < gap) {
    minValue.value = maxValue.value - gap;
  }
});

watch(maxValue, () => {
  if (maxValue.value - minValue.value < gap) {
    maxValue.value = minValue.value + gap;
  }
});

watch(
  () => props.filtersDefault,
  (newVal) => {
    Object.assign(filters, newVal);
  }
);

/* ========== Methods ========== */
function selectPrice(opt) {
  minValue.value = opt.min;
  maxValue.value = opt.max;
}

function onCityChange() {
  filters.district_id = null;
  emit("city-change", filters.city_id);
}

function apply() {
  emit("apply-filters", {
    ...filters,
    min_price: minValue.value,
    max_price: maxValue.value,
  });
}

function reset() {
  filters.keyword = "";
  filters.bedrooms = null;
  filters.bathrooms = null;
  filters.property_type = null;
  filters.city_id = null;
  filters.district_id = null;
  minValue.value = 0;
  maxValue.value = 5000000000;
  emit("city-change", null);
  emit("apply-filters", {});
}
</script>

<style scoped></style>

<!-- components/FavoriteButton.vue -->
<template>
  <button
    @click="toggle"
    class="p-2 rounded-full transition"
    :class="isFav ? 'bg-red-100 text-red-500' : 'bg-gray-100 text-gray-400'"
  >
    <svg
      xmlns="http://www.w3.org/2000/svg"
      fill="currentColor"
      viewBox="0 0 24 24"
      class="w-5 h-5"
    >
      <path
        d="M12 21s-6.716-4.35-9.193-7.193C.84 11.84.5 10.5.5 9.5A5.5 5.5 0 0 1 6 4c1.74 0 3.41.81 4.5 2.09A6.03 6.03 0 0 1 15 4a5.5 5.5 0 0 1 5.5 5.5c0 1-.34 2.34-2.307 4.307C18.716 16.65 12 21 12 21z"
      />
    </svg>
  </button>
</template>

<script setup>
const props = defineProps({
  modelValue: Boolean,
});

const emit = defineEmits(["update:modelValue"]);

const isFav = ref(props.modelValue);

const toggle = () => {
  isFav.value = !isFav.value;
  emit("update:modelValue", isFav.value);
};
</script>

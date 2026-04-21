<template>
  <div ref="wrapper" class="relative inline-block w-full">
    <!-- Trigger -->
    <div @click="toggle">
      <slot name="trigger" />
    </div>

    <!-- Popover -->
    <Transition name="fade">
      <div
        v-if="open"
        ref="popover"
        :style="{ transform: `translateX(calc(-50% + ${position.left}px))` }"
        class="absolute left-1/2 -translate-x-1/2 z-50 mt-2 w-56 max-sm:w-32 rounded-2xl shadow-lg bg-white border border-gray-200 p-4 max-sm:p-2"
      >
        <slot />
        <div
          :style="{ left: `calc(50% - ${position.left}px)` }"
          class="absolute -top-2 left-1/2 -translate-x-1/2 w-3 h-3 bg-white border-l border-t rotate-45"
        ></div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from "vue";

const open = ref(false);
const wrapper = ref(null);
const popover = ref(null);
const position = ref({ left: 0 });

const adjustPosition = () => {
  const el = popover.value;
  if (!el) return;

  const rect = el.getBoundingClientRect();

  const padding = 8;

  let shift = 0;

  // tràn bên trái
  if (rect.left < padding) {
    shift = padding - rect.left;
  }

  // tràn bên phải
  if (rect.right > window.innerWidth - padding) {
    shift = (window.innerWidth - padding - rect.right) * 2;
  }

  position.value.left = shift;
};

const toggle = async () => {
  open.value = !open.value;
  if (open.value) {
    await nextTick();
    adjustPosition();
  }
};

const close = () => {
  open.value = false;
};

// click outside
const handleClickOutside = (e) => {
  if (!wrapper.value) return;
  if (!wrapper.value.contains(e.target)) {
    close();
  }
};

// esc key
const handleEsc = (e) => {
  if (e.key === "Escape") close();
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
  document.addEventListener("keydown", handleEsc);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
  document.removeEventListener("keydown", handleEsc);
});
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(4px);
}
</style>

<script setup>
const props = defineProps({
  text: {
    type: String,
    default: "",
  },
  label: {
    type: String,
    default: "Copy",
  },
  copiedLabel: {
    type: String,
    default: "Đã copy",
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const copied = ref(false);
const copying = ref(false);

const copy = async () => {
  if (props.disabled || copying.value) return;
  const value = String(props.text || "");
  if (!value) return;

  copying.value = true;
  try {
    if (navigator?.clipboard?.writeText) {
      await navigator.clipboard.writeText(value);
    } else {
      const el = document.createElement("textarea");
      el.value = value;
      el.setAttribute("readonly", "readonly");
      el.style.position = "absolute";
      el.style.left = "-9999px";
      document.body.appendChild(el);
      el.select();
      document.execCommand("copy");
      document.body.removeChild(el);
    }

    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 1200);
  } finally {
    copying.value = false;
  }
};
</script>

<template>
  <button
    type="button"
    class="inline-flex items-center justify-center border rounded px-2 py-1 text-xs font-semibold disabled:opacity-50 hover:bg-gray-50"
    :disabled="disabled || !text"
    @click="copy"
  >
    {{ copied ? copiedLabel : label }}
  </button>
</template>


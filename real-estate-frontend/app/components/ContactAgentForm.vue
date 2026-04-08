<!-- components/ContactAgentForm.vue -->
<template>
  <div class="bg-white p-6 rounded-2xl shadow space-y-4">
    <h2 class="text-lg font-semibold">Contact Agent</h2>

    <!-- Name -->
    <div>
      <input
        v-model="form.name"
        type="text"
        placeholder="Your name"
        class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
    </div>

    <!-- Email -->
    <div>
      <input
        v-model="form.email"
        type="email"
        placeholder="Your email"
        class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
    </div>

    <!-- Phone -->
    <div>
      <input
        v-model="form.phone"
        type="text"
        placeholder="Phone number"
        class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
    </div>

    <!-- Message -->
    <div>
      <textarea
        v-model="form.message"
        rows="4"
        placeholder="I'm interested in this property..."
        class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
      ></textarea>
    </div>

    <!-- Error -->
    <div v-if="error" class="text-red-500 text-sm">
      {{ error }}
    </div>

    <!-- Success -->
    <div v-if="success" class="text-green-600 text-sm">Message sent successfully!</div>

    <!-- Submit -->
    <button
      @click="submit"
      :disabled="loading"
      class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition disabled:opacity-50"
    >
      <span v-if="loading">Sending...</span>
      <span v-else>Send Message</span>
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  propertyId: [Number, String], // optional
  agentId: [Number, String], // optional
});

const form = reactive({
  name: "",
  email: "",
  phone: "",
  message: "",
});

const loading = ref(false);
const error = ref("");
const success = ref(false);

const { $api } = useNuxtApp();

const validate = () => {
  if (!form.name || !form.email || !form.message) {
    return "Please fill required fields";
  }
  return "";
};

const submit = async () => {
  error.value = "";
  success.value = false;

  const err = validate();
  if (err) {
    error.value = err;
    return;
  }

  try {
    loading.value = true;

    await $api.post("/contact-agent", {
      ...form,
      property_id: props.propertyId,
      agent_id: props.agentId,
    });

    success.value = true;

    // reset form
    form.name = "";
    form.email = "";
    form.phone = "";
    form.message = "";
  } catch (e) {
    error.value = e?.response?.data?.message || "Something went wrong";
  } finally {
    loading.value = false;
  }
};
</script>

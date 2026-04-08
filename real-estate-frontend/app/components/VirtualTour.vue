<script setup>
import { ref, watch, nextTick } from "vue";

const viewerRef = ref(null);
let viewerInstance = null;

const props = defineProps({
  rooms: {
    type: Array,
    default: () => [],
  },
});

const buildScenes = (rooms) => {
  const scenes = {};
  let firstScene = null;

  rooms.forEach((room) => {
    if (!firstScene) firstScene = room.slug;

    scenes[room.slug] = {
      title: room.name,
      type: "equirectangular",
      panorama: room.image?.panorama_url || "",
      hotSpots: (room.hotspots || []).map((h) => ({
        pitch: h.pitch,
        yaw: h.yaw,
        text: h.text,
        type: "scene",
        sceneId: h.to_room_slug,
      })),
    };
  });

  return { scenes, firstScene };
};

watch(
  () => props.rooms,
  async (rooms) => {
    if (!process.client || !window.pannellum) return;
    if (!rooms || rooms.length === 0) return;

    await nextTick(); // ensure DOM is ready

    if (!viewerRef.value) return;

    // destroy old instance if it exists
    if (viewerInstance) {
      viewerInstance.destroy();
      viewerInstance = null;
    }

    const { scenes, firstScene } = buildScenes(rooms);

    viewerInstance = window.pannellum.viewer(viewerRef.value, {
      default: {
        firstScene,
        autoLoad: true,
        showControls: false,
        sceneFadeDuration: 800,
      },
      scenes,
    });
  },
  { immediate: true }
);

// Control functions
const panUp = () => viewerInstance?.setPitch(viewerInstance.getPitch() + 10);
const panDown = () => viewerInstance?.setPitch(viewerInstance.getPitch() - 10);
const panLeft = () => viewerInstance?.setYaw(viewerInstance.getYaw() - 10);
const panRight = () => viewerInstance?.setYaw(viewerInstance.getYaw() + 10);
const zoomIn = () => viewerInstance?.setHfov(viewerInstance.getHfov() - 10);
const zoomOut = () => viewerInstance?.setHfov(viewerInstance.getHfov() + 10);
const toggleFullscreen = () => viewerInstance?.toggleFullscreen();
</script>

<template>
  <div class="relative w-full h-96">
    <div ref="viewerRef" class="w-full h-full rounded-xl"></div>
    <div
      id="controls"
      class="absolute bottom-2 left-1/2 -translate-x-1/2 z-10 flex items-center justify-center space-x-1 bg-black bg-opacity-50 rounded-lg p-1"
    >
      <button @click="panUp" class="ctrl-btn" aria-label="Pan Up">&#9650;</button>
      <button @click="panDown" class="ctrl-btn" aria-label="Pan Down">&#9660;</button>
      <button @click="panLeft" class="ctrl-btn" aria-label="Pan Left">&#9664;</button>
      <button @click="panRight" class="ctrl-btn" aria-label="Pan Right">&#9654;</button>
      <button @click="zoomIn" class="ctrl-btn" aria-label="Zoom In">&plus;</button>
      <button @click="zoomOut" class="ctrl-btn" aria-label="Zoom Out">&minus;</button>
      <button @click="toggleFullscreen" class="ctrl-btn" aria-label="Toggle Fullscreen">
        &#x2922;
      </button>
    </div>
  </div>
</template>

<style scoped>
.ctrl-btn {
  @apply w-10 h-10 text-white text-lg flex items-center justify-center bg-gray-600 bg-opacity-75 rounded-md hover:bg-opacity-100 transition-colors duration-200;
}
</style>

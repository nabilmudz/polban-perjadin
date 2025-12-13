<!-- resources/js/Components/ModalLaporan.vue -->
<template>
  <transition name="fade">
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
    >
      <!-- Klik area gelap untuk close -->
      <div
        class="absolute inset-0"
        @click="emit('close')"
      ></div>

      <!-- Isi modal -->
      <div
        class="relative max-h-[95vh] w-full max-w-5xl overflow-auto rounded-l"
      >
        <!-- Tombol close -->
        <button
          type="button"
          class="fixed top-3 right-3 px-2 py-1 rounded text-sm text-white hover:text-gray-400"
          @click="emit('close')"
        >
          ✕
        </button>

        <!-- Konten yang dikirim dari parent (LaporanSurat) -->
        <slot />
      </div>
    </div>
  </transition>
</template>

<script setup>
const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close'])
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

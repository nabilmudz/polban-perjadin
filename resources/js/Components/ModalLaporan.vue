<template>
  <teleport to="body">
    <div
      v-if="show"
      class="fixed inset-0 bg-black/30 backdrop-blur-[2px] flex items-center justify-center z-[9999]"
    >
      <div
        class="rounded-lg shadow-lg w-[100vw] h-[100vh] overflow-y-auto relative"
      >
        <button
          @click="$emit('close')"
          class="fixed top-6 right-8 z-[10000] text-white hover:text-gray-400 text-2xl font-bold"
        >
          ✕
        </button>
        <slot />
      </div>
    </div>
  </teleport>
</template>

<script setup>
defineProps({ show: Boolean })
defineEmits(['close'])
</script>

<template>
  <div v-if="show" class="fixed inset-0 bg-black/40 flex items-center justify-center z-[9999]">
    <div class="bg-white w-[450px] max-h-[70vh] overflow-y-auto p-6 rounded-lg shadow-lg">
      <h2 class="text-xl font-bold mb-3 text-yellow-600">Beberapa Data Dilewati</h2>
      <p class="text-sm mb-3 text-gray-600">
        Beberapa NIP sudah ada di database, jadi data tersebut tidak dimasukkan:
      </p>
      <ul class="list-disc ml-5 space-y-1 text-sm">
        <li v-for="(err, i) in errors" :key="i">{{ err }}</li>
      </ul>
      <div class="mt-5 text-right">
        <button class="bg-yellow-600 text-white px-4 py-2 rounded" @click="$emit('close')">
          Oke, Mengerti
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean,
  errors: { type: Array, default: () => [] }
})
defineEmits(['close'])
</script>

<template>
  <div v-if="error" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-lg shadow p-6 w-[420px]">
      <h2 class="text-lg font-bold mb-2">{{ error.title }}</h2>
      <p class="text-gray-700 whitespace-pre-line mb-4">
        {{ error.message }}
      </p>

      <div class="text-right">
        <button
          class="px-4 py-2 bg-blue-600 text-white rounded"
          @click="close"
        >
          OK
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const error = computed(() => page.props.error ?? null)

function close() {
  page.props.error = null
}
</script>

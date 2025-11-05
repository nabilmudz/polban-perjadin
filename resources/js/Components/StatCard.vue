<!-- components/StatCard.vue -->
<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { defineProps } from 'vue'

const props = defineProps({
  title: { type: String, required: true },
  icon: { type: String, required: true },
  count: { type: [Number, String], required: true },
  color: { type: String, default: 'blue' }
})
</script>

<template>
  <div class="p-6 bg-white rounded-lg flex flex-col justify-left gap-4 w-80 border-gray-100 shadow border-2 ">
    <div>
        <p class="text-lg font-medium">{{ title }}</p>
    </div>
    <div class="flex flex-row items-center justify-left gap-4">
        <FontAwesomeIcon :icon="['far', icon]" class="text-2xl"/>
      <h3 class="text-2xl font-semibold">{{ count }}</h3>
    </div>
  </div>
</template>

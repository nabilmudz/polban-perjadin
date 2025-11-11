<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { defineProps, computed } from 'vue'

const props = defineProps({
  title: { type: String, required: true },
  icon: { type: String, required: true },
  count: { type: [Number, String], required: true },
  color: { type: String, default: 'blue' }
})

const colorClasses = {
  blue: 'text-blue-500',
  red: 'text-red-500',
  green: 'text-green-500',
  yellow: 'text-yellow-500',
  purple: 'text-purple-500'
}

const iconColor = computed(() => colorClasses[props.color] || colorClasses.blue)
</script>

<template>
  <div class="p-6 bg-white rounded-lg flex flex-col justify-left gap-4 w-70 border-gray-100 shadow border-2">
    <div>
      <p class="text-lg font-medium" :class="iconColor">{{ title }}</p>
    </div>
    <div class="flex flex-row items-center justify-left gap-4">
      <FontAwesomeIcon :icon="['far', icon]" class="text-2xl" :class="iconColor" />
      <h3 class="text-2xl font-semibold" :class="iconColor">{{ count }}</h3>
    </div>
  </div>
</template>

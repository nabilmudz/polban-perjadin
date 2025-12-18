<script setup>
import { usePage } from '@inertiajs/vue3'
import { watch, ref } from 'vue'
import Sidebar from '@/Components/Sidebar.vue'
import GlobalErrorModal from '@/Components/GlobalErrorModal.vue'

const page = usePage()
const modalRef = ref(null)

watch(
  () => page.props.error,
  (err) => {
    if (err?.title && err?.message) {
      modalRef.value?.open(err)
    }
  },
  { immediate: true }
)
</script>

<template>
  <div class="flex h-screen overflow-hidden">
    <Sidebar />

    <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
      <slot />
    </main>

    <GlobalErrorModal ref="modalRef" />
  </div>
</template>

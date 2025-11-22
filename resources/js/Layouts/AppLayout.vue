<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import Sidebar from '@/Components/Sidebar.vue'

const { props } = usePage()
const user = props.auth.user
</script>

<template>
  <div class="flex h-screen overflow-hidden">
    <Sidebar />
    <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
      <slot />
    </main>
  </div>
</template>

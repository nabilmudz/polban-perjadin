<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import Sidebar from '@/Components/Sidebar.vue'

const { props } = usePage()
const user = props.auth.user
</script>

<template>
  <Head :title="user.role" />

  <div class="flex min-h-screen bg-gray-100 text-gray-800">
    <Sidebar />
    <main class="flex-1 p-6 overflow-y-auto">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { usePage, router, Link } from '@inertiajs/vue3'

const { props } = usePage()
const user = props.auth.user

const dropdownOpen = ref(false)

const logout = () => router.post(route('logout'))

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
}
</script>

<template>
  <header class="flex justify-end items-center p-4 bg-white shadow relative">
    <div class="relative">
      <button
        @click="toggleDropdown"
        class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 focus:outline-none"
      >
        <span class="hidden sm:inline">{{ user.name }}</span>
        <svg
          class="w-4 h-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>

      <div
        v-show="dropdownOpen"
        @click.outside="dropdownOpen = false"
        class="absolute right-0 mt-2 w-48 bg-white border rounded shadow-lg z-50"
      >
      <Link
        href="/user/password" 
        class="block px-4 py-2 hover:bg-gray-100"
      >
        Change Password
      </Link>
        <button
          @click="logout"
          class="w-full text-left px-4 py-2 hover:bg-red-50 text-red-600 font-semibold"
        >
          Logout
        </button>
      </div>
    </div>
  </header>
</template>

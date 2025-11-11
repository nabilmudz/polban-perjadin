<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import getSidebarLinks from '@/utils/getSidebarLinks.js'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { route } from 'ziggy-js'

const { props, url } = usePage()
const user = props.auth.user
const Ziggy = props.ziggy

const links = getSidebarLinks(user.role).map(link => ({
  ...link,
  href: route(link.route, {}, false, Ziggy)
}))

const currentPath = computed(() => new URL(url, window.location.origin).pathname)

const logout = () => router.post(route('logout', {}, false, Ziggy))
const isActive = (link) => currentPath.value.startsWith(link.href)
</script>

<template>
  <aside class="flex flex-col justify-between w-72 bg-white min-h-screen p-4">
    <div>
      <div class="flex items-center gap-4 p-4 mb-4">
        <img src="/images/polban2.png" alt="Logo Polban" class="w-12" />
        <div class="flex flex-col">
          <h2 class="font-medium text-xl capitalize">Aplikasi SPPD</h2>
          <p>Polban</p>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="space-y-2">
        <Link
          v-for="link in links"
          :key="link.name"
          :href="link.href"
          :class="[
            'flex items-center gap-2 p-3 rounded-md hover:bg-gray-100 border-l-4',
            isActive(link)
              ? 'bg-[#ffa17f37] text-primary-default font-semibold border-primary-dark'
              : 'text-gray-700 border-transparent'
          ]"
        >
          <FontAwesomeIcon :icon="['far', link.icon]" />
          <span class="text-lg">{{ link.name }}</span>
        </Link>
      </nav>
    </div>

    <!-- Logout -->
    <div>
      <form @submit.prevent="logout">
        <button
          type="submit"
          class="w-full flex items-center gap-2 p-2 rounded text-red-600 hover:bg-red-50"
        >
          <FontAwesomeIcon :icon="['far', 'share-from-square']" />
          <span>Logout</span>
        </button>
      </form>
    </div>
  </aside>
</template>

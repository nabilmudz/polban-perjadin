<template>
  <div class="bg-white shadow rounded-md p-6">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
      <div class="flex items-center gap-3 flex-wrap">
        <input
          v-model="search"
          type="text"
          placeholder="Search..."
          class="border rounded-md px-3 py-2 text-sm"
          @input="onFilterChange"
        />
        <select
          v-model="status"
          class="border rounded-md px-3 py-2 text-sm"
          @change="onFilterChange"
        >
          <option value="">All Status</option>
          <option v-for="s in statusOptions" :key="s" :value="s">
            {{ s }}
          </option>
        </select>
        <input
          type="date"
          v-model="startDate"
          class="border rounded-md px-3 py-2 text-sm"
          @change="onFilterChange"
        />
        <input
          type="date"
          v-model="endDate"
          class="border rounded-md px-3 py-2 text-sm"
          @change="onFilterChange"
        />
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm text-left">
        <thead class="bg-gray-100 border-b text-gray-600 uppercase text-xs">
          <tr>
            <th v-for="col in columns" :key="col.key" class="px-4 py-2 font-medium">
              {{ col.label }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in data"
            :key="row.id"
            class="border-b hover:bg-gray-50 transition"
          >
            <td v-for="col in columns" :key="col.key" class="px-4 py-2">
              <slot :name="col.key" :row="row">
                {{ row[col.key] }}
              </slot>
            </td>
          </tr>
          <tr v-if="!data.length">
            <td :colspan="columns.length" class="text-center py-4 text-gray-500">
              No data found
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex justify-between items-center">
      <div class="text-sm text-gray-500">
        Showing {{ meta.from }}–{{ meta.to }} of {{ meta.total }}
      </div>
      <div class="flex items-center gap-2">
        <button
          :disabled="!links.prev"
          @click="changePage(meta.current_page - 1)"
          class="px-3 py-1 border rounded-md text-sm disabled:opacity-50"
        >
          Prev
        </button>
        <button
          :disabled="!links.next"
          @click="changePage(meta.current_page + 1)"
          class="px-3 py-1 border rounded-md text-sm disabled:opacity-50"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  columns: { type: Array, default: () => [] },
  data: { type: Array, default: () => [] },
  meta: { type: Object, default: () => ({}) },
  links: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
  statusOptions: { type: Array, default: () => [] },
  routeName: { type: String, required: true },
})

const search = ref(props.filters?.search || '')
const status = ref(props.filters?.status || '')
const startDate = ref(props.filters?.from || '')
const endDate = ref(props.filters?.to || '')

const onFilterChange = () => {
  router.get(route(props.routeName), {
    search: search.value,
    status: status.value,
    from: startDate.value,
    to: endDate.value,
  }, { preserveState: true, replace: true })
}

const changePage = (page) => {
  router.get(route(props.routeName), {
    ...props.filters,
    page,
  }, { preserveState: true, replace: true })
}
</script>

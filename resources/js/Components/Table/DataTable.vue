<template>
  <div class="bg-white shadow rounded-md p-6">
    <!-- Filters -->
    <div v-if="showFilters" class="mb-4">

      <!-- Row 1 -->
      <div class="flex flex-wrap items-center gap-3">

        <!-- Search -->
        <input
          v-if="enableSearch && filters.search !== undefined"
          v-model="filters.search"
          type="text"
          placeholder="Search..."
          class="border rounded-md px-3 py-2 text-sm min-w-[200px]"
          @input="onFilterChange"
        />

        <!-- Status -->
        <select
          v-if="enableStatus && statusOptions.length && filters.status !== undefined"
          v-model="filters.status"
          class="border rounded-md px-3 py-2 text-sm min-w-[180px]"
          @change="onFilterChange"
        >
          <option value="">Semua Status</option>
          <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>

        <!-- From Date -->
        <input
          v-if="enableDate && filters.from !== undefined"
          type="date"
          v-model="filters.from"
          class="border rounded-md px-3 py-2 text-sm min-w-[150px]"
          @change="onFilterChange"
        />

        <!-- To Date -->
        <input
          v-if="enableDate && filters.to !== undefined"
          type="date"
          v-model="filters.to"
          class="border rounded-md px-3 py-2 text-sm min-w-[150px]"
          @change="onFilterChange"
        />

        <slot name="filters-extra" />
      </div>

    </div>


    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm text-left table-fixed">
        <thead class="bg-gray-100 border-b text-gray-600 uppercase text-xs">
          <tr>
            <th class="px-4 py-2 font-medium w-16">No</th>
            <th
              v-for="col in columns"
              :key="col.key"
              class="px-4 py-2 font-medium"
            >
              {{ col.label }}
            </th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="(row, index) in data"
            :key="row.id"
            class="border-b hover:bg-gray-50 transition"
          >
            <td class="px-4 py-2">
              {{ (meta.from || 1) + index }}
            </td>
            <td v-for="col in columns" :key="col.key" class="px-4 py-2">
              <slot :name="col.key" :row="row">
                {{ row[col.key] }}
              </slot>
            </td>
          </tr>

          <tr
            v-for="n in Math.max(0, meta.per_page - data.length)"
            :key="'empty-' + n"
            class="border-b"
          >
            <td
              class="px-4 py-2 h-10"
              v-for="col in columns"
              :key="col.key"
            >&nbsp;</td>
          </tr>

          <tr v-if="!data.length">
            <td :colspan="columns.length + 1" class="text-center py-2 text-gray-500">
              No data found
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex justify-between items-center">
      <div class="text-sm text-gray-500">
        Showing {{ meta.from }}–{{ meta.to }} of {{ meta.total }}
      </div>
      <div class="flex items-center gap-2">
      <button
        :disabled="!links.prev"
        @click="changePageUrl(links.prev)"
        class="px-3 py-1 border rounded-md text-sm disabled:opacity-50"
      >
        Prev
      </button>
      <button
        :disabled="!links.next"
        @click="changePageUrl(links.next)"
        class="px-3 py-1 border rounded-md text-sm disabled:opacity-50"
      >
        Next
      </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineEmits } from 'vue'
import { router } from '@inertiajs/vue3'
const props = defineProps({
  columns: Array,
  data: Array,
  meta: { type: Object, default: () => ({ per_page: 10, total: 0, from: 0, to: 0 }) },
  links: Object,
  filters: Object,
  routeName: String,
  statusOptions: { type: Array, default: () => [] },
  showFilters: { type: Boolean, default: true },
  enableSearch: { type: Boolean, default: true },
  enableStatus: { type: Boolean, default: true },
  enableDate: { type: Boolean, default: true },
})


const filters = props.filters
const emit = defineEmits(['update:filters', 'changePage'])

const onFilterChange = () => {
  emit('update:filters', { ...filters })
}

const changePageUrl = (url) => {
  if (!url) return
  router.get(url, {}, { preserveState: true, replace: true })
}

</script>

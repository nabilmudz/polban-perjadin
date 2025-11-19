<template>
  <AppLayout>
    <div class="bg-white w-full rounded-md shadow">
      <HeaderPage />

      <div class="p-8">

        <h1 class="text-3xl font-bold mb-2">Nomor Surat</h1>

        <div class="mt-4">
          <DataTable
            :columns="columns"
            :data="surat.data"
            :meta="surat.meta"
            :links="surat.links"
            :filters="filters"
            route-name="sekdir.nomorsurat"
            @update:filters="Object.assign(filters, $event)"
          >

            <template #no="{ index }">
              {{ index + 1 }}
            </template>

            <template #aksi="{ row }">
              <button
                class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm"
                @click="gotoReview(row.id)"
              >
                Review & Nomor
              </button>
            </template>

          </DataTable>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'

import { router, usePage } from '@inertiajs/vue3'
import { reactive, watch } from 'vue'
import debounce from 'lodash.debounce'

const { props } = usePage()
const surat = props.surat

const filters = reactive({
  search: props.filters?.search || "",
})

watch(filters,
  debounce(() => {
    router.get(route('sekdir.nomorsurat'), filters, {
      preserveState: true,
      replace: true
    })
  }, 300),
  { deep: true }
)

const gotoReview = (id) => {
  router.get(route('sekdir.review', id))
}

const columns = [
  { key: 'tanggal_pengajuan', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'nomor_surat_pengusulan', label: 'Nomor Surat Pengusulan' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'aksi', label: 'Aksi' },
]
</script>
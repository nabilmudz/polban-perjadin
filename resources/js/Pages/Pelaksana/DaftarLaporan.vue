<template>
  <Head title="Perjalanan Dinas" />

  <AppLayout>
    <div class="bg-white w-full max-w-7xl mx-auto rounded-md shadow">
      <HeaderPage />

      <div class="p-8 border-b">
        <h1 class="text-3xl font-bold mb-2">Perjalanan Dinas</h1>
        <p class="text-gray-600">Daftar perjalanan dinas yang harus dilaporkan</p>
      </div>

      <div class="p-8">
        <DataTable
          :columns="columns"
          :data="suratTugas.data"
          :meta="suratTugas.meta"
          :links="suratTugas.links"
          :filters="filters"
          @update:filters="updateFilters"
          @changePage="changePage"
        >
          <template #status_surat="{ row }">
            <StatusBadges :status="row.status_surat" />
          </template>

          <template #action="{ row }">
            <div class="flex gap-2">
              <button
                v-for="action in getRowActions(row, currentUser.role)"
                :key="action.type"
                @click="handleAction(action.type, row)"
                class="px-2 py-1 rounded shadow"
                :class="buttonClass(action.color)"
              >
                <font-awesome-icon :icon="['far', action.icon]" />
              </button>
            </div>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { getRowActions } from '@/utils/rowAction'

const page = usePage()
const currentUser = page.props.auth.user
const suratTugas = computed(() => page.props.suratTugas)
const filters = reactive({
  search: page.props.filters?.search ?? '',
  status_surat: page.props.filters?.status_surat ?? '',
  from: page.props.filters?.from ?? '',
  to: page.props.filters?.to ?? '',
  range: page.props.filters?.range ?? '',
  page: page.props.filters?.page ?? 1,
})

const columns = [
  { key: 'diusulkan_kepada', label: 'Wadir yang Memaraf' },
  { key: 'nomor_surat_tugas_resmi', label: 'No Surat Resmi' },
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Pelaksanaan' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'status_surat', label: 'Status' },
  { key: 'action', label: 'Aksi', fixedWidth: '160px' },
]

const updateFilters = (newFilters) => {
  Object.assign(filters, newFilters)

  router.get(route(route().current()), filters, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

const changePage = (pageNumber) => {
  filters.page = pageNumber

  router.get(route(route().current()), filters, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

const handleAction = (type, row) => {
  const id = row?.id

  if (!id) {
    console.warn('Row ID tidak ditemukan:', row)
    return
  }

  if (type === 'upload-bukti') {
    router.get(route('bukti.page', id))
  }

  if (type === 'view') {
    router.get(route('pelaksana.surat.show', id))
  }

  if (type === 'download') {
    router.get(route('laporan.download', id))
  }
}

const buttonClass = (color) => ({
  'bg-blue-500 text-white': color === 'blue',
  'bg-green-500 text-white': color === 'green',
  'bg-red-500 text-white': color === 'red',
  'bg-yellow-400 text-black': color === 'yellow',
  'bg-purple-500 text-white': color === 'purple',
})
</script>

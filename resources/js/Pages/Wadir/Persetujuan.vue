<template>
  <AppLayout>
    <Head title="Persetujuan Wadir" />

    <div class="bg-white w-full rounded-md shadow">
      <HeaderPage />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-4">Persetujuan Surat Tugas</h1>
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

          <template #total_dana="{ row }">
            Rp {{ Number(row.total_dana ?? 0).toLocaleString('id-ID') }}
          </template>

          <template #path_file_surat_usulan="{ row }">
            <div class="flex gap-2">
              <button
                v-for="action in getSuratUndanganAction(row, currentUser.role)"
                :key="action.type"
                @click="handleAction(action.type, row)"
                :disabled="action.disabled"
                class="px-2 py-1 rounded shadow"
                :class="buttonClass(action.color)"
              >
                <font-awesome-icon :icon="['far', action.icon]" />
              </button>
            </div>
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

import { getRowActions, getSuratUndanganAction } from '@/utils/rowAction'

const page = usePage()
const currentUser = page.props.auth.user
const suratTugas = computed(() => page.props.suratTugas)

const filters = reactive({
  search: page.props.filters?.search ?? '',
  from: page.props.filters?.from ?? '',
  to: page.props.filters?.to ?? '',
  range: page.props.filters?.range ?? '',
  page: page.props.filters?.page ?? 1,
})

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'tanggal_kembali', label: 'Tanggal Kembali' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'total_dana', label: 'Total Dana' },
  { key: 'status_surat', label: 'Status' },
  { key: 'path_file_surat_usulan', label: 'Surat Undangan' },
  { key: 'action', label: 'Aksi', fixedWidth: '180px' },
]

const applyFilter = () => {
  filters.page = 1
  router.get(route(`${currentUser.role}.persetujuan`), filters, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

const updateFilters = (newFilters) => {
  Object.assign(filters, newFilters)
  applyFilter()
}

const changePage = (pageNumber) => {
  filters.page = pageNumber
  applyFilter()
}

const resolveRowData = (row) => {
  return (
    row?.original ||
    row?.row ||
    row?.item ||
    row?.data ||
    row
  )
}

const handleAction = (type, row) => {
  const data = resolveRowData(row)
  const id = data?.id

  if (!id) {
    console.warn('Row ID tidak ditemukan:', row)
    return
  }

  switch (type) {
    case 'review':
      router.get(route(`${currentUser.role}.persetujuan.show`, id))
      break

    case 'download':
      router.get(route('laporan.download', id))
      break

    case 'lihat_surat':
      if (data.path_file_surat_usulan) {
        window.open(data.path_file_surat_usulan, '_blank')
      }
      break
  }
}

const buttonClass = (color) => ({
  'bg-blue-500 text-white': color === 'blue',
  'bg-green-500 text-white': color === 'green',
  'bg-red-500 text-white': color === 'red',
  'bg-purple-500 text-white': color === 'purple',
  'bg-yellow-400 text-black': color === 'yellow',
})
</script>
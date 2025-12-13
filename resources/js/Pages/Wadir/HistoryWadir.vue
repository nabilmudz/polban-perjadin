<template>
  <AppLayout>
    <Head title="History Wadir" />

    <div class="bg-white w-full rounded-md shadow">
      <HeaderPage />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-2">History Surat Tugas</h1>
        <p class="text-gray-600">
          Riwayat seluruh pengusulan surat tugas yang ditujukan kepada Wadir.
        </p>
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

          <template #action="{ row }">
            <div class="flex gap-2">
              <button
                v-for="action in getRowActions(row, user.role)"
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
import { Head, usePage, router } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { getRowActions } from '@/utils/rowAction'

const page = usePage()
const user = page.props.auth.user
const suratTugas = computed(() => page.props.suratTugas)
const filters = reactive({
  search: page.props.filters?.search ?? '',
  status: page.props.filters?.status ?? '',
  from: page.props.filters?.from ?? '',
  to: page.props.filters?.to ?? '',
  range: page.props.filters?.range ?? '',
  page: page.props.filters?.page ?? 1,
})

const applyFilter = () => {
  filters.page = 1

  router.get(route(`${user.role}.history`), filters, {
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

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },

  // tambahan history
  { key: 'nomor_surat_usulan_jurusan', label: 'Nomor Surat Usulan' },
  { key: 'nomor_surat_tugas_resmi', label: 'Nomor Surat Tugas' },

  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'total_dana', label: 'Total Dana' },
  { key: 'status_surat', label: 'Status' },

  { key: 'action', label: 'Aksi', fixedWidth: '160px' },
]

const handleAction = (type, row) => {
  router.get(route(`${user.role}.persetujuan.show`, row.id))
}

const buttonClass = (color) => ({
  'bg-blue-500 text-white': color === 'blue',
  'bg-green-500 text-white': color === 'green',
  'bg-red-500 text-white': color === 'red',
  'bg-purple-500 text-white': color === 'purple',
  'bg-yellow-400 text-black': color === 'yellow',
})
</script>

<template>
  <Head title="History Perjalanan Dinas" />

  <AppLayout>
    <div class="bg-white w-full rounded-md shadow">
      <HeaderPage />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-2">History Perjalanan Dinas</h1>
        <p class="text-gray-600 mb-6">Riwayat lengkap surat tugas</p>

        <DataTable
          :columns="columns"
          :data="suratTugas.data"
          :meta="suratTugas.meta"
          :links="suratTugas.links"
          :filters="filters"
          @update:filters="updateFilters"
          @changePage="changePage"
        >
          <div class="mb-4 flex items-center gap-3">
            <label class="text-sm text-gray-600">Periode:</label>
            <select v-model="filters.range" class="border rounded px-2 py-1">
              <option value="">Semua</option>
              <option value="weekly">Minggu</option>
              <option value="monthly">Bulan</option>
              <option value="yearly">Tahun</option>
            </select>
          </div>

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

      <ModalLaporan :show="showModal" @close="showModal = false">
        <LaporanSurat :surat="selectedData" />
      </ModalLaporan>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { reactive, ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import ModalLaporan from '@/Components/ModalLaporan.vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'
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
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'nomor_surat_usulan_jurusan', label: 'Nomor Surat Pengantar' },
  { key: 'nomor_surat_tugas_resmi', label: 'Nomor Surat Tugas' },
  { key: 'diusulkan_kepada', label: 'Diajukan Kepada' },
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

const showModal = ref(false)
const selectedData = ref({})

const handleAction = (type, row) => {
  if (type === 'view') {
    selectedData.value = row
    showModal.value = true
  }

  if (type === 'download') {
    router.get(route('laporan.download', row.id))
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

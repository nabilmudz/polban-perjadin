<template>
  <Head title="Dashboard Pelaksana" />

  <AppLayout>
    <div class="bg-white w-full min-h-full rounded-md shadow">
      <HeaderPage />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-6">Dashboard Pelaksana</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
          <StatCard title="Total Penugasan" icon="file" :count="totalPengusulan" />
          <StatCard title="Laporan Selesai" icon="square-check" color="green" :count="stats.selesai" />
          <StatCard title="Belum Selesai" icon="folder-closed" color="purple" :count="stats.published" />
          <StatCard title="Bertugas" icon="user" color="yellow" :count="stats.on_duty" />
          <StatCard title="Dikembalikan" icon="circle-left" color="red" :count="stats.revision_requested" />
        </div>
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

      <ModalLaporan :show="showModal" @close="showModal = false">
        <LaporanSurat :surat="selectedRow" />
      </ModalLaporan>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { reactive, ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import StatCard from '@/Components/StatCard.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import ModalLaporan from '@/Components/ModalLaporan.vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'
import { getRowActions } from '@/utils/rowAction'

const page = usePage()
const user = page.props.auth.user
const suratTugas = computed(() => page.props.suratTugas)

const filters = reactive({
  search: page.props.filters?.search ?? '',
  status_surat: page.props.filters?.status_surat ?? '',
  from: page.props.filters?.from ?? '',
  to: page.props.filters?.to ?? '',
  range: page.props.filters?.range ?? '',
  page: page.props.filters?.page ?? 1,
})

const reload = () =>
  router.get(route(route().current()), filters, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })

const updateFilters = (v) => {
  Object.assign(filters, v)
  reload()
}

const changePage = (p) => {
  filters.page = p
  reload()
}

const stats = reactive({
  selesai: page.props.stats?.selesai ?? 0,
  published: page.props.stats?.published ?? 0,
  on_duty: page.props.stats?.on_duty ?? 0,
  revision_requested: page.props.stats?.revision_requested ?? 0,
})

const totalPengusulan = computed(() =>
  Object.values(stats).reduce((a, b) => a + b, 0)
)

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'nomor_surat_usulan_jurusan', label: 'Nomor Surat Usulan' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'status_surat', label: 'Status' },
  { key: 'action', label: 'Aksi', fixedWidth: '180px' },
]

const showModal = ref(false)
const selectedRow = ref({})

const handleAction = (type, row) => {
  if (type === 'view') {
    selectedRow.value = row
    showModal.value = true
  }

  if (type === 'download') {
    router.get(route('laporan.download', row.id))
  }

  if (type === 'upload') {
    router.get(route('bukti.page', row.id))
  }
}

const buttonClass = (c) => ({
  'bg-blue-500 text-white': c === 'blue',
  'bg-green-500 text-white': c === 'green',
  'bg-red-500 text-white': c === 'red',
  'bg-purple-500 text-white': c === 'purple',
  'bg-yellow-400 text-black': c === 'yellow',
})
</script>

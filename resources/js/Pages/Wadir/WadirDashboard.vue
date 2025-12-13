<template>
  <Head title="Dashboard Wadir" />

  <AppLayout>
    <div class="bg-white w-full rounded-md shadow">
      <HeaderPage />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-4">Dashboard Wadir</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
          <StatCard title="Total Pengusulan" icon="file" :count="stats.total" />
          <StatCard title="Usulan Baru" icon="plus-square" color="green" :count="stats.baru" />
          <StatCard title="Proses Direktur" icon="clock" color="yellow" :count="stats.proses_direktur" />
          <StatCard title="Bertugas" icon="user" color="purple" :count="stats.bertugas" />
          <StatCard title="Ditolak" icon="times-circle" color="red" :count="stats.rejected" />
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

          <template #total_dana="{ row }">
            Rp {{ Number(row.total_dana).toLocaleString('id-ID') }}
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
import StatCard from '@/Components/StatCard.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { getRowActions } from '@/utils/rowAction'

const page = usePage()
const currentUser = page.props.auth.user

const suratTugas = computed(() => page.props.suratTugas)
const stats = computed(() => page.props.stats)

const filters = reactive({
  search: page.props.filters?.search ?? '',
  status: page.props.filters?.status ?? '',
  from: page.props.filters?.from ?? '',
  to: page.props.filters?.to ?? '',
  range: page.props.filters?.range ?? '',
  page: page.props.filters?.page ?? 1,
})

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'nomor_surat_usulan_jurusan', label: 'Nomor Surat' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'total_dana', label: 'Total Dana' },
  { key: 'status_surat', label: 'Status' },
  { key: 'action', label: 'Aksi', fixedWidth: '150px' },
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
  if (type === 'view') {
    router.get(route(`${currentUser.role}.persetujuan.show`, row.id))
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

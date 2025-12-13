<template>
  <AppLayout>
    <!-- Set window tab label -->
    <Head title="Dashboard Pelaksana" />

    <div class="bg-white w-full rounded-md shadow">
      <HeaderPage />

      <div class="p-8">
        <div class="flex items-center justify-between">
          <h1 class="text-3xl font-bold mb-4">Dashboard Pelaksana</h1>
          <!-- contoh tag kecil di atas page (boleh diubah styling) -->
          <span class="text-sm text-gray-500">Role: {{ currentUser.role }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <!-- StatCard sekarang bind ke meta yang datang dari server -->
          <StatCard title="Total Tugas" icon="file" :count="tugasCount" />
          <StatCard title="Tugas Selesai" icon="square-check" :count="tugasSelesai" />
          <StatCard title="Pending" icon="clock" :count="tugasPending" />
          <StatCard title="Ditolak" icon="times-circle" :count="tugasDitolak" />
        </div>
      </div>

      <div class="p-8">
        <div class="flex justify-between items-center mb-4 gap-4">
          <!-- Filters: search, status, by date (week/month/year) -->
          <div class="flex gap-2 items-center">
            <input
              v-model="filters.search"
              @input="debouncedUpdateFilters"
              type="search"
              placeholder="Cari..."
              class="px-3 py-2 border rounded"
            />

            <select v-model="filters.status" @change="updateFilters" class="px-3 py-2 border rounded">
              <option value="">Semua status</option>
              <option value="draft">Draft</option>
              <option value="pengajuan">Pengajuan</option>
              <option value="disetujui">Disetujui</option>
              <option value="ditolak">Ditolak</option>
            </select>

            <select v-model="filters.range" @change="updateFilters" class="px-3 py-2 border rounded">
              <option value="">Semua waktu</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
              <option value="year">This Year</option>
            </select>
          </div>

          <div class="flex gap-2 items-center">
            <!-- Tombol untuk buka form pengusul (semua role bisa mengusulkan) -->
            <button
              class="px-4 py-2 rounded bg-blue-600 text-white shadow"
              @click="$emit('open-pengusul-form')"
              title="Usulkan Surat"
            >
              Usulkan Surat
            </button>

            <!-- API Download laporan, sering dipakai -->
            <button class="px-4 py-2 rounded border" @click="downloadLaporan">
              Download Laporan
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <!-- DataTable: sudah dikonfigurasi untuk multiple tables & tanpa reload -->
          <DataTable
            :columns="columns"
            :data="suratTugas.data"
            :meta="suratTugas.meta"
            :links="suratTugas.links"
            :filters="filters"
            :allow-multiple-tables="true"
            download-api="/api/laporan/download"           <!-- contoh endpoint -->
            route-name="pelaksana.dashboard"
            @update:filters="onFiltersEvent"
            @changePage="onChangePage"
            @request-download="onRequestDownload"
          >
            <!-- Custom formatter untuk kolom status_surat -->
            <template #status_surat="{ row }">
              <StatusBadges :status="row.status_surat" />
            </template>

            <!-- Formatter untuk total_dana -->
            <template #total_dana="{ row }">
              <span class="font-medium">
                {{ formatCurrency(row.total_dana) }}
              </span>
            </template>

            <!-- Action buttons per row -->
            <template #action="{ row }">
              <div class="flex gap-2">
                <button
                  v-for="action in getRowActions(row, currentUser.role)"
                  :key="action.type"
                  @click="handleAction(action.type, row)"
                  :title="action.type"
                  class="px-2 py-1 rounded shadow flex items-center justify-center transition hover:brightness-90"
                  :class="actionClass(action.color)"
                >
                  <font-awesome-icon :icon="['far', action.icon]" class="text-md" />
                </button>
              </div>
            </template>

            <!-- Allow DataTable to render multiple internal tables (LaporanSurat) -->
            <template #extra_tables>
              <!-- contoh: jika LaporanSurat perlu multiple table, DataTable akan render slot ini -->
              <LaporanSurat v-if="showLaporanSurat" :multi="true" :tables="laporanTables" />
            </template>
          </DataTable>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
/* imports */
import { ref, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import StatCard from '@/Components/StatCard.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'
import { getRowActions } from '@/utils/rowAction'
import debounce from 'lodash.debounce'

/* page props from Inertia */
const { props } = usePage()
const currentUser = props.auth.user || {}
const suratTugas = ref(props.suratTugas || { data: [], meta: {}, links: [] })

/* Filters are reactive and will update the route using Inertia (no full reload) */
const filters = ref(Object.assign(
  {
    search: '',
    status: '',
    range: '', // week | month | year
    page: props.filters?.page || 1,
    per_page: props.filters?.per_page || 10,
  },
  props.filters || {}
))

/* Columns: mengikuti struktur pengusul draft + tambahan total_dana */
const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Pelaksanaan' },
  { key: 'total_dana', label: 'Total Dana' },          // NEW column
  { key: 'status_surat', label: 'Status' },
  { key: 'action', label: 'Aksi', sortable: false },
]

/* Example tables config for LaporanSurat multi-table support */
const laporanTables = ref([
  { id: 'by-department', title: 'By Department', query: { group_by: 'department' } },
  { id: 'by-type', title: 'By Type', query: { group_by: 'type' } },
])

const showLaporanSurat = ref(true)

/* Stat counts derived from meta (safe fallback) */
const tugasCount = computed(() => suratTugas.value.meta?.total || 0)
const tugasSelesai = computed(() => suratTugas.value.meta?.completed || 0)
const tugasPending = computed(() => suratTugas.value.meta?.pending || 0)
const tugasDitolak = computed(() => suratTugas.value.meta?.rejected || 0)

/* Helpers */
function actionClass(color) {
  return {
    'bg-blue-500 text-white': color === 'blue',
    'bg-green-500 text-white': color === 'green',
    'bg-red-500 text-white': color === 'red',
    'bg-yellow-400 text-black': color === 'yellow',
    'bg-purple-500 text-white': color === 'purple',
  }
}

function formatCurrency(val) {
  if (val == null) return '-'
  // gunakan util jika ada; fallback sederhana:
  try {
    const num = Number(val)
    if (Number.isNaN(num)) return val
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(num)
  } catch (e) {
    return val
  }
}

/* Row action handler */
function handleAction(type, row) {
  switch (type) {
    case 'view':
      router.get(route(`${currentUser.role}.persetujuan.show`, row.id))
      break
    case 'download':
      // panggil API download khusus
      router.get(route('laporan.download', { id: row.id }), {}, { preserveState: true })
      break
    case 'review':
      router.get(route(`${currentUser.role}.persetujuan.review`, row.id))
      break
    case 'edit':
      router.get(route(`${currentUser.role}.persetujuan.edit`, row.id))
      break
    default:
      console.warn('action not implemented:', type)
  }
}

/* Update filters triggered by DataTable or inputs.
   Use router.get with preserveState so UI updates without full reload. */
function updateFilters() {
  // reset to first page on filter change
  filters.value.page = 1

  router.get(
    route('pelaksana.dashboard'),
    { ...filters.value },
    {
      preserveState: true,
      replace: true,
      preserveScroll: true,
    }
  )
}

/* When DataTable emits update:filters with partial payload */
function onFiltersEvent(newFilters) {
  // merge incoming filters
  Object.assign(filters.value, newFilters)
  updateFilters()
}

/* Pagination handler from DataTable */
function onChangePage(page) {
  filters.value.page = page
  router.get(route('pelaksana.dashboard'), { ...filters.value }, { preserveState: true, replace: true })
}

/* Debounced search input to avoid spamming router */
const debouncedUpdateFilters = debounce(() => updateFilters(), 350)

/* Download Laporan (global API used in many pages) */
function downloadLaporan() {
  // contoh: panggil endpoint download dengan current filters
  const params = { ...filters.value }
  // jika butuh POST/GET dengan file, sesuaikan
  router.visit(route('laporan.downloadAll'), {
    method: 'get',
    data: params,
    preserveState: true,
  })
}

/* Handle DataTable internal request-download (slot event) */
function onRequestDownload(payload) {
  // payload bisa berisi { type: 'csv'|'pdf', tableId: 'by-department', query: {} }
  router.visit(route('laporan.download'), {
    method: 'get',
    data: { ...filters.value, ...payload },
    preserveState: true,
  })
}
</script>

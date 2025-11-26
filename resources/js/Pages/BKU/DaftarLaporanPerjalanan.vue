<template>
  <AppLayout>
    <div class="bg-white w-full rounded-md shadow overflow-hidden">
      <HeaderPage title="Laporan & Bukti Perjalanan Dinas" />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Daftar Laporan & Bukti</h1>

        <DataTable
          :columns="columns"
          :data="laporanData.data"
          :meta="laporanData.meta"
          :links="laporanData.links"
          :filters="filters"
          route-name="bku.daftarlaporanperjalanan"
          @update:filters="handleFilterUpdate"
          @changePage="handlePageChange"
        >
          <template #status_laporan="{ row }">
             <span v-if="row.laporan" class="px-2 py-1 rounded bg-green-100 text-green-800 text-xs font-bold">
                Sudah Upload
             </span>
             <span v-else class="px-2 py-1 rounded bg-yellow-100 text-yellow-800 text-xs font-bold">
                Belum Upload
             </span>
          </template>

          <template #actions="{ row }">
             <button 
                v-if="row.laporan"
                class="px-3 py-1.5 bg-cyan-500 text-white rounded-md text-sm font-medium hover:bg-cyan-600 shadow-sm flex items-center gap-2 transition-colors"
                @click="router.get(route('bku.verifikasi', row.id))"
             >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                Lihat Bukti
             </button>
             <span v-else class="text-gray-400 text-sm italic">Menunggu</span>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import { reactive, computed, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()

const laporanData = computed(() => {
  const raw = page.props.laporanBukti || {};
  return {
    data: raw.data || [],
    links: raw.links || [],
    meta: {
        current_page: raw.current_page || 1,
        last_page: raw.last_page || 1,
        per_page: raw.per_page || 10, 
        total: raw.total || 0,
        from: raw.from || 0,
        to: raw.to || 0
    }
  }
})

const filters = reactive({
  search: page.props.filters?.search || ''
})

let searchTimeout;
watch(
  () => filters.search,
  (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('bku.daftarlaporanperjalanan'), { search: value }, { preserveState: true, replace: true })
    }, 300);
  }
)

const handleFilterUpdate = (newFilters) => {
    Object.assign(filters, newFilters)
}

const handlePageChange = (page) => {
    router.get(route('bku.daftarlaporanperjalanan'), { ...filters, page }, { preserveState: true, replace: true })
}

const columns = [
  { key: 'nama_kegiatan', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_pelaksanaan', label: 'Tanggal Pelaksanaan' },
  { key: 'nomor_surat_resmi', label: 'Nomor Surat Tugas' },
  { key: 'status_laporan', label: 'Status Laporan', slot: 'status_laporan' },
  { key: 'actions', label: 'Aksi', slot: 'actions' }
]
</script>
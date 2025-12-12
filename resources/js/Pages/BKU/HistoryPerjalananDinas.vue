<template>
  <Head title="History" />

  <AppLayout>
    <div class="bg-white w-full rounded-md shadow overflow-hidden">
      <HeaderPage title="History Perjalanan Dinas" />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Riwayat Lengkap</h1>

        <DataTable
          :columns="columns"
          :data="history.data"
          :meta="history.meta"
          :links="history.links"
          :filters="filters"
          route-name="bku.historyperjalanandinas"
          @update:filters="handleFilterUpdate"
          @changePage="handlePageChange"
        >
           <template #no="{ index }">
              {{ (history.meta.from || 1) + index }}
           </template>

           <template #diusulkan_kepada="{ row }">
              <span class="text-gray-700">{{ row.diusulkan_kepada }}</span>
           </template>

           <template #status_surat="{ row }">
              <StatusBadges :status="row.status_surat" />
           </template>

           <template #aksi="{ row }">
            <div class="flex gap-2 justify-center">
              <button class="p-2 bg-cyan-500 text-white rounded shadow hover:bg-cyan-600 transition" title="Lihat Detail">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
              </button>
              <button v-if="row.status_surat === 'completed' || row.status_surat === 'approved'" class="p-2 bg-green-600 text-white rounded shadow hover:bg-green-700 transition" title="Download">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
              </button>
            </div>
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
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { reactive, computed, watch } from 'vue'

const page = usePage()

const history = computed(() => {
    const raw = page.props.history || {};
    return {
        data: raw.data || [],
        meta: {
            current_page: raw.current_page || 1,
            last_page: raw.last_page || 1,
            total: raw.total || 0,
            per_page: raw.per_page || 10,
            from: raw.from || 0,
            to: raw.to || 0
        },
        links: raw.links || []
    }
})

const filters = reactive({
  search: page.props.filters?.search || '',
})

let searchTimeout;
watch(
  () => filters.search,
  (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('bku.historyperjalanandinas'), { search: value }, { preserveState: true, replace: true })
    }, 300);
  }
)

const handleFilterUpdate = (newFilters) => {
    Object.assign(filters, newFilters)
}

const handlePageChange = (page) => {
    router.get(route('bku.historyperjalanandinas'), { ...filters, page }, { preserveState: true, replace: true })
}

const columns = [
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_pelaksanaan', label: 'Tanggal Berangkat' },
  { key: 'nomor_surat_usulan_jurusan', label: 'Nomor Surat Pengantar' }, 
  { key: 'nomor_surat_resmi', label: 'Nomor Surat Tugas' },
  { key: 'updated_at', label: 'Tanggal Diterbitkan' }, 
  { key: 'diusulkan_kepada', label: 'Diusulkan Kepada', slot: 'diusulkan_kepada' },
  { key: 'status_surat', label: 'Status', slot: 'status_surat' },
  { key: 'aksi', label: 'Aksi', slot: 'aksi' },
]
</script>
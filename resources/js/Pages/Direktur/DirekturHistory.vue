<template>
  <Head title="Riwayat Surat Tugas" />

  <AppLayout>
    <div class="bg-white w-full rounded-md shadow overflow-hidden">
      <HeaderPage title="History Perjalanan Dinas" />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Riwayat Surat Tugas</h1>

        <DataTable
          :columns="columns"
          :data="history.data"
          :meta="history.meta"
          :links="history.links"
          :filters="filters"
          route-name="direktur.history"
          @update:filters="handleFilterUpdate"
          @changePage="handlePageChange"
        >
           <template #no="{ index }">
              {{ (history.meta.from || 1) + index }}
           </template>

           <template #nominal_biaya="{ row }">
             {{ formatCurrency(row.nominal_biaya) }}
           </template>

           <template #status_surat="{ row }">
             <StatusBadges :status="row.status_surat" />
           </template>

           <template #aksi="{ row }">
            <div class="flex gap-2 justify-center">
              <button 
                @click="router.get(route('direktur.persetujuan.show', row.id))" 
                class="px-2 py-1 rounded shadow flex items-center justify-center transition hover:brightness-90 bg-blue-500 text-white"
                title="Lihat Detail"
              >
                 <font-awesome-icon :icon="['far', 'eye']" class="text-md" />
              </button>
              
              <button 
                v-if="['approved', 'completed'].includes(row.status_surat)" 
                class="px-2 py-1 rounded shadow flex items-center justify-center transition hover:brightness-90 bg-green-500 text-white"
                title="Download"
                @click="router.get(route('surat.download', row.id))"
              >
                 <font-awesome-icon :icon="['far', 'circle-down']" class="text-md" />
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
import debounce from 'lodash.debounce'

const page = usePage()

const history = computed(() => {
    const raw = page.props.history || {};
    return {
        data: raw.data || [],
        meta: raw.meta || {},
        links: raw.links || []
    }
})

const filters = reactive({
  search: page.props.filters?.search || '',
})

watch(
  () => filters.search,
  debounce((value) => {
    router.get(route('direktur.history'), { search: value }, { preserveState: true, replace: true })
  }, 300)
)

const handleFilterUpdate = (newFilters) => {
    Object.assign(filters, newFilters)
}

const handlePageChange = (pageNumber) => {
    router.get(route('direktur.history'), { ...filters, page: pageNumber }, { preserveState: true, replace: true })
}

const formatCurrency = (value) => {
  if (!value) return 'Rp 0';
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value);
}

const columns = [
  { key: 'nama_kegiatan', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_pelaksanaan', label: 'Tanggal Pelaksanaan' },
  { key: 'nomor_surat_resmi', label: 'Nomor Surat Tugas' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'nominal_biaya', label: 'Total Dana', slot: 'nominal_biaya' },
  { key: 'status_surat', label: 'Status', slot: 'status_surat' },
  { key: 'aksi', label: 'Aksi', slot: 'aksi' },
]
</script>
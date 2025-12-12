<template>
  <Head title="Daftar Laporan & Bukti" />

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
          <template #no="{ index }">
              {{ (laporanData.meta.from || 1) + index }}
          </template>

          <template #nominal_biaya="{ row }">
             {{ formatCurrency(row.nominal_biaya) }}
          </template>

          <template #status_surat="{ row }">
             <StatusBadges :status="row.status_surat" />
          </template>

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
                class="px-3 py-1.5 bg-blue-500 text-white rounded-md text-sm font-medium hover:bg-blue-600 shadow-sm flex items-center gap-2 transition-colors"
                @click="router.get(route('bku.verifikasi', row.id))"
             >
                <font-awesome-icon :icon="['far', 'eye']" />
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
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { Head, router, usePage } from '@inertiajs/vue3' 
import { reactive, computed, watch } from 'vue'

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
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'no_usulan_surat', label: 'No. Usulan Surat' }, 
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'nominal_biaya', label: 'Total Dana', slot: 'nominal_biaya' },
  { key: 'status_surat', label: 'Status Surat', slot: 'status_surat' },
  { key: 'status_laporan', label: 'Status Laporan', slot: 'status_laporan' },
  { key: 'actions', label: 'Aksi', slot: 'actions' }
]
</script>
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
          :status-options="statusOptions"
          route-name="bku.daftarlaporanperjalanan"
          @update:filters="Object.assign(filters, $event)"
          @changePage="(page) => router.get(route('bku.daftarlaporanperjalanan'), { ...filters, page }, { preserveState: true, replace: true })"
        >
          <template #no="{ index }">{{ (laporanData.meta.from || 1) + index }}</template>
          <template #nominal_biaya="{ row }">{{ formatCurrency(row.nominal_biaya) }}</template>
          <template #status_surat="{ row }"><StatusBadges :status="row.status_surat" /></template>
          <template #status_laporan="{ row }">
             <span v-if="row.laporan" class="px-2 py-1 rounded bg-green-100 text-green-800 text-xs font-bold">Sudah Upload</span>
             <span v-else class="px-2 py-1 rounded bg-yellow-100 text-yellow-800 text-xs font-bold">Belum Upload</span>
          </template>
          
          <template #actions="{ row }">
             <div class="flex gap-2 justify-center">
                 <button 
                    class="px-2 py-1 rounded shadow flex items-center justify-center transition hover:brightness-90 bg-blue-500 text-white" 
                    title="Lihat Detail"
                    @click="router.get(route('bku.verifikasi', row.id))"
                 >
                    <font-awesome-icon :icon="['far', 'eye']" class="text-md" />
                 </button>
                 <button v-if="['approved', 'completed'].includes(row.status_surat)" class="px-2 py-1 rounded shadow flex items-center justify-center transition hover:brightness-90 bg-green-500 text-white" title="Download" @click="router.get(route('surat.download', row.id))">
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
import { statusOptions } from '@/utils/statusOptions'
import debounce from 'lodash.debounce'

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
  search: page.props.filters?.search || '',
  status: page.props.filters?.status || '',
  from: page.props.filters?.from || '',
  to: page.props.filters?.to || '',
  range: page.props.filters?.range || '',
})

watch(
  filters,
  debounce(() => {
    router.get(route('bku.daftarlaporanperjalanan'), filters, { preserveState: true, replace: true })
  }, 300),
  { deep: true }
)

const formatCurrency = (value) => {
  if (!value) return 'Rp 0';
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
}

const columns = [
  { key: 'nama_kegiatan', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'no_usulan_surat', label: 'Nomor Surat Usulan' }, 
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'nominal_biaya', label: 'Total Dana', slot: 'nominal_biaya' },
  { key: 'status_surat', label: 'Status Surat', slot: 'status_surat' },
  { key: 'actions', label: 'Aksi', slot: 'actions' }
]
</script>
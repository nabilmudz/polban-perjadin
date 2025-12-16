<template>
  <Head title="Riwayat Perjalanan Dinas" />

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
          @update:filters="Object.assign(filters, $event)"
          @changePage="(page) => router.get(route('bku.historyperjalanandinas'), { ...filters, page }, { preserveState: true, replace: true })"
        >
          <template #created_at="{ row }">{{ formatDate(row.created_at) }}</template>
          <template #tanggal_pelaksanaan="{ row }">{{ formatDate(row.tanggal_pelaksanaan) }}</template>
          <template #diusulkan_kepada="{ row }"><span class="text-gray-700">{{ row.diusulkan_kepada }}</span></template>
          <template #nominal_biaya="{ row }">{{ formatCurrency(row.nominal_biaya) }}</template>
          <template #status_surat="{ row }"><StatusBadges :status="row.status_surat" /></template>

          <template #filters-extra>
            <button
              class="px-3 py-2 rounded shadow bg-green-600 text-white hover:brightness-90 flex items-center gap-2"
              title="Export Excel sesuai filter"
              @click="downloadExcel"
            >
              <font-awesome-icon :icon="['far', 'file-excel']" />
              Export Excel
            </button>
          </template>

          <template #aksi="{ row }">
            <div class="flex gap-2 justify-center">
              <button
                class="px-2 py-1 rounded shadow flex items-center justify-center transition hover:brightness-90 bg-blue-500 text-white"
                title="Lihat Detail"
                @click="openModal(row)"
              >
                <font-awesome-icon :icon="['far', 'eye']" class="text-md" />
              </button>
            </div>
          </template>
        </DataTable>

      </div>
    </div>

    <ModalLaporan :show="showViewModal" @close="showViewModal = false">
        <LaporanSurat v-if="selectedData" :surat="selectedData" />
    </ModalLaporan>

  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import ModalLaporan from '@/Components/ModalLaporan.vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'

import { Head, router, usePage } from '@inertiajs/vue3'
import { reactive, computed, watch, ref } from 'vue'
import debounce from 'lodash.debounce'

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
  from: page.props.filters?.from || '',
  to: page.props.filters?.to || '',
  range: page.props.filters?.range || '',
})

watch(
  filters,
  debounce(() => {
    router.get(route('bku.historyperjalanandinas'), filters, { preserveState: true, replace: true })
  }, 300),
  { deep: true }
)

const downloadExcel = () => {
  const url = route('bku.historyperjalanandinas.export', {
    search: filters.search || '',
    from: filters.from || '',
    to: filters.to || '',
    range: filters.range || '',
  })

  window.open(url, '_blank')
}

const formatDate = (dateString) => {
    if (!dateString) return '-';
    if (dateString.length === 10 && dateString.includes('-')) return dateString;
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString; 
    return date.toISOString().split('T')[0];
}

const formatCurrency = (value) => {
  if (!value) return 'Rp 0';
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
}

const showViewModal = ref(false)
const selectedData = ref(null)

const openModal = (row) => {
    selectedData.value = row
    showViewModal.value = true
}
const downloadSurat = (row) => {
  const id = row?.surat_tugas_id ?? row?.id
  if (!id) return
  window.open(route('surat.download', { suratTugas: id }), '_blank')
}

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan', slot: 'created_at' },
  { key: 'tanggal_pelaksanaan', label: 'Tanggal Berangkat', slot: 'tanggal_pelaksanaan' },
  { key: 'nomor_surat_usulan_jurusan', label: 'Nomor Surat Usulan' }, 
  { key: 'diusulkan_kepada', label: 'Diusulkan Kepada', slot: 'diusulkan_kepada' },
  { key: 'sumber_dana', label: 'Sumber Dana' }, 
  { key: 'nominal_dana', label: 'Total Dana', slot: 'nominal_biaya' },
  { key: 'status_surat', label: 'Status', slot: 'status_surat' },
  { key: 'aksi', label: 'Aksi', slot: 'aksi' },
]
</script>
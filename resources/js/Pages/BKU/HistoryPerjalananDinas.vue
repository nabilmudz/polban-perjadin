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
          :status-options="statusOptions"
          route-name="bku.historyperjalanandinas"
          @update:filters="Object.assign(filters, $event)"
          @changePage="(page) => router.get(route('bku.historyperjalanandinas'), { ...filters, page }, { preserveState: true, replace: true })"
        >
           <template #no="{ index }">{{ (history.meta.from || 1) + index }}</template>
           <template #diusulkan_kepada="{ row }"><span class="text-gray-700">{{ row.diusulkan_kepada }}</span></template>
           <template #nominal_biaya="{ row }">{{ formatCurrency(row.nominal_biaya) }}</template>
           <template #status_surat="{ row }"><StatusBadges :status="row.status_surat" /></template>
           <template #aksi="{ row }">
            <div class="flex gap-2 justify-center">
              <button 
                class="px-2 py-1 rounded shadow flex items-center justify-center transition hover:brightness-90 bg-blue-500 text-white" 
                title="Lihat Detail"
                @click="router.get(route('bku.verifikasi', row.id))"
              >
                 <font-awesome-icon :icon="['far', 'eye']" class="text-md" />
              </button>
              <button 
                v-if="['approved', 'completed', 'published', 'awaiting_proof_upload', 'under_bku_review', 'returned_for_correction'].includes(row.status_surat)" 
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
import { statusOptions } from '@/utils/statusOptions'

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
  status: page.props.filters?.status || '',
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

const formatCurrency = (value) => {
  if (!value) return 'Rp 0';
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
}

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_pelaksanaan', label: 'Tanggal Berangkat' },
  { key: 'nomor_surat_usulan_jurusan', label: 'Nomor Surat Usulan' }, 
  { key: 'diusulkan_kepada', label: 'Diusulkan Kepada', slot: 'diusulkan_kepada' },
  { key: 'sumber_dana', label: 'Sumber Dana' }, 
  { key: 'nominal_biaya', label: 'Total Dana', slot: 'nominal_biaya' },
  { key: 'status_surat', label: 'Status', slot: 'status_surat' },
  { key: 'aksi', label: 'Aksi', slot: 'aksi' },
]
</script>
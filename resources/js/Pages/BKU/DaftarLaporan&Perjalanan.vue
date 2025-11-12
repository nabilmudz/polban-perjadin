<template>
  <div class="bg-white w-full h-full rounded-md">
    <HeaderPage />

    <div class="p-8">
      <h1 class="text-3xl font-bold mb-4">Laporan & Bukti Perjalanan Dinas</h1>
    </div>

    <DataTable
      :columns="columns"
      :data="laporan.data"
      :meta="laporan.meta"
      :links="laporan.links"
      :filters="filters"
      route-name="bku.daftarlaporanperjalanan"
      @update:filters="Object.assign(filters, $event)"
      @changePage="(page) =>
        router.get(
          route('bku.daftarlaporanperjalanan'),
          { ...filters, page },
          { preserveState: true, replace: true }
        )"
    >
      <template #status_laporan="{ row }">
        <StatusBadges :status="row.status_laporan" />
      </template>
    </DataTable>
  </div>
</template>

<script setup>
import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { usePage, router } from '@inertiajs/vue3'
import { reactive, watch, computed } from 'vue'
import debounce from 'lodash.debounce'

const page = usePage()
const laporan = computed(() => page.props.laporanBukti)

const filters = reactive({
  search: page.props.filters?.search || '',
  status: page.props.filters?.status || '',
  from: page.props.filters?.from || '',
  to: page.props.filters?.to || '',
  page: page.props.filters?.page || 1,
})

watch(
  filters,
  debounce(() => {
    router.get(route('bku.daftarlaporanperjalanan'), { ...filters }, {
      preserveState: true,
      replace: true,
    })
  }, 300),
  { deep: true }
)

const columns = [
  { key: 'nama_kegiatan', label: 'Nama Kegiatan' },
  { key: 'tanggal_pengusulan', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_pelaksanaan', label: 'Tanggal Pelaksanaan' },
  { key: 'nomor_surat_tugas', label: 'Nomor Surat Tugas' },
  { key: 'status_laporan', label: 'Status' },
]
</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
}
</script>

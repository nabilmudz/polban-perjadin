<template>
  <Head title="Dashboard Pelaksana" />

  <div class="bg-white w-full h-full rounded-md shadow">
    <HeaderPage />

    <div class="p-8">
      <h1 class="text-3xl font-bold mb-4">Dashboard Pelaksana</h1>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-5">
        <StatCard title="Total Pengusulan" icon="file" :count="statusCounts.total || 0" />
        <StatCard title="Laporan Selesai" icon="square-check" :count="statusCounts.completed || 0" color="green" />
        <StatCard title="Belum Selesai" icon="folder-closed" :count="statusCounts.published || 0" color="purple" />
        <StatCard title="Bertugas" icon="user" :count="statusCounts.on_duty || 0" color="yellow" />
        <StatCard title="Dikembalikan" icon="circle-left" :count="statusCounts.revision_requested || 0" color="red" />
      </div>
    </div>

    <DataTable
      :columns="columns"
      :data="suratTugas.data"
      :meta="suratTugas.meta"
      :links="suratTugas.links"
      :filters="filters"
      route-name="pelaksana.dashboard"
      @update:filters="onUpdateFilters"
      @changePage="onChangePage"
    >
      <template #status_surat="{ row }">
        <StatusBadges :status="row.status_surat" />
      </template>

      <template #path_file_surat_usulan="{ row }">
        <button
          v-if="row.path_file_surat_usulan"
          @click="openSuratUndangan(row)"
          class="px-3 py-1 rounded bg-yellow-400 text-black shadow hover:brightness-95 flex items-center gap-2 justify-center"
          title="Lihat Surat Undangan"
        >
          <font-awesome-icon :icon="['far', 'file-lines']" />
        </button>
        <span v-else class="text-gray-400">-</span>
      </template>

      <template #action="{ row }">
        <div class="flex gap-2">
          <button
            class="px-2 py-1 rounded shadow flex items-center justify-center bg-blue-500 text-white hover:brightness-90"
            title="Lihat"
            @click="handleView(row)"
          >
            <font-awesome-icon :icon="['far', 'eye']" class="text-md" />
          </button>
        </div>
      </template>
    </DataTable>
  </div>

  <ModalLaporan :show="showViewModal" @close="showViewModal = false">
    <LaporanSurat v-if="selectedData" :surat="selectedData" />
  </ModalLaporan>
</template>

<script setup>
import { Head, usePage, router } from '@inertiajs/vue3'
import { reactive, watch, computed, ref } from 'vue'
import debounce from 'lodash.debounce'

import HeaderPage from '@/Components/HeaderPage.vue'
import StatCard from '@/Components/StatCard.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import ModalLaporan from '@/Components/ModalLaporan.vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'
import FilePreviewModal from '@/Components/FilePreviewModal.vue'
import { useFilePreview } from '@/utils/useFilePreviews.js'

const page = usePage()

const propsSafe = computed(() => page.props?.value ?? page.props ?? {})

const statusCounts = computed(() => propsSafe.value.statusCounts ?? {})

const suratTugas = computed(() => propsSafe.value.suratTugas ?? {
  data: [],
  meta: {},
  links: {},
})

const filters = reactive({
  search: propsSafe.value.filters?.search ?? '',
  from: propsSafe.value.filters?.from ?? '',
  to: propsSafe.value.filters?.to ?? '',
  page: propsSafe.value.filters?.page ?? 1,
  range: propsSafe.value.filters?.range ?? '',
})

const fetchData = debounce(() => {
  router.get(route('pelaksana.dashboard'), { ...filters }, {
    preserveState: true,
    replace: true,
  })
}, 300)

watch(filters, fetchData, { deep: true })

const onUpdateFilters = (newFilters) => Object.assign(filters, newFilters)

const onChangePage = (pageNumber) => {
  router.get(route('pelaksana.dashboard'), { ...filters, page: pageNumber }, {
    preserveState: true,
    replace: true,
  })
}

const showViewModal = ref(false)
const selectedData = ref(null)

const handleView = (row) => {
  selectedData.value = row
  showViewModal.value = true
}

const preview = useFilePreview()
const toStorageUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  if (path.startsWith('/storage/')) return path
  if (path.startsWith('/')) return path
  return `/storage/${path}`
}

const openSuratUndangan = (row) => {
  const url = toStorageUrl(row?.path_file_surat_usulan)
  if (!url) return
  preview.open({ url, name: 'Surat Undangan' })
}

const handleDownloadFinal = (row) => {
  const url = toStorageUrl(row?.path_file_surat_tugas_final)
  if (!url) return
  window.open(url, '_blank')
}

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'no_usulan_surat', label: 'Nomor Surat Usulan' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'total_dana', label: 'Total Dana' },
  { key: 'status_surat', label: 'Status' },
  { key: 'action', label: 'Aksi', sortable: false, fixedWidth: '140px' },
]
</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
}
</script>

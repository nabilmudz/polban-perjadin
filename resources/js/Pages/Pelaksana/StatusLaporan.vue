<template>
  <Head title="Status Laporan" />

  <div class="bg-white w-full max-w-7xl mx-auto rounded-md shadow">
    <HeaderPage />

    <div class="p-8 border-b">
      <h1 class="text-3xl font-bold mb-2">Status Laporan</h1>
      <p class="text-gray-600">
        Status pertanggungjawaban perjalanan dinas setelah kegiatan selesai.
      </p>
    </div>

    <div class="p-8">
      <div class="overflow-x-auto">
        <DataTable
          :columns="columns"
          :data="suratTugas.data"
          :meta="suratTugas.meta"
          :links="suratTugas.links"
          :filters="filters"
          route-name="pelaksana.status-laporan"
          @update:filters="onUpdateFilters"
          @changePage="onChangePage"
        >
          <template #status_laporan="{ row }">
            <StatusBadges :status="row.status_surat" type="laporan" />
          </template>

          <template #tanggal_berangkat="{ row }">
            {{ formatDate(row.tanggal_berangkat) }}
          </template>

          <template #action="{ row }">
            <div class="flex gap-2">
              <button
                v-if="canUpload(row)"
                class="px-2 py-1 rounded shadow flex items-center justify-center bg-yellow-400 text-black hover:brightness-90"
                title="Kelola Upload Laporan/Visum/Bukti"
                @click="goToLampiran(row)"
              >
                Kelola Upload
                <font-awesome-icon :icon="['far', 'file-arrow-up']" class="text-md ml-2" />
              </button>
            </div>
          </template>
        </DataTable>
      </div>
    </div>

    <UploadBuktiModal
      :show="isUploadModalOpen"
      :surat="selectedSurat"
      @close="closeUploadModal"
      @success="onUploadSuccess"
    />
  </div>
</template>

<script setup>
import { Head, usePage, router } from '@inertiajs/vue3'
import { computed, reactive, watch, ref } from 'vue'
import debounce from 'lodash.debounce'
import { parseISO, format } from 'date-fns'

import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import UploadBuktiModal from './Partials/UploadBuktiModal.vue'

const page = usePage()
const propsSafe = computed(() => page.props?.value ?? page.props ?? {})

const suratTugas = computed(() => propsSafe.value.suratTugas ?? { data: [], meta: {}, links: {} })

const filters = reactive({
  search: propsSafe.value.filters?.search ?? '',
  from: propsSafe.value.filters?.from ?? '',
  to: propsSafe.value.filters?.to ?? '',
  page: propsSafe.value.filters?.page ?? 1,
  range: propsSafe.value.filters?.range ?? '',
})

const fetchData = debounce(() => {
  router.get(route('pelaksana.status-laporan'), { ...filters }, {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  })
}, 300)

watch(filters, fetchData, { deep: true })

const onUpdateFilters = (newFilters) => Object.assign(filters, newFilters)
const onChangePage = (pageNumber) => { filters.page = pageNumber }

const columns = [
  { key: 'nomor_surat_tugas_resmi', label: 'No Surat Resmi' },
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Pelaksanaan' },
  { key: 'status_laporan', label: 'Status Laporan' },
  { key: 'action', label: 'Aksi', sortable: false, fixedWidth: '160px' },
]

const formatDate = (val) => {
  if (!val) return ''
  return format(parseISO(String(val)), 'dd MMM yyyy')
}

const getSuratId = (row) => row?.surat_tugas_id ?? row?.id

const canUpload = (row) => {
  const st = row?.status_surat
  return ['awaiting_proof_upload', 'published', 'returned_for_correction'].includes(st)
}

const goToLampiran = (row) => {
  const id = getSuratId(row)
  if (!id) return
  router.get(route('pelaksana.lampiran.index', { suratTugas: id }))
}

const handleView = (row) => {
  const id = getSuratId(row)
  if (!id) return
  router.get(route('pelaksana.laporan.view', { suratTugas: id }))
}

const toStorageUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  if (path.startsWith('/storage/')) return path
  if (path.startsWith('/')) return path
  return `/storage/${path}`
}

const handleDownloadFinal = (row) => {
  const url = toStorageUrl(row?.path_file_surat_tugas_final)
  if (!url) return
  window.open(url, '_blank')
}

const isUploadModalOpen = ref(false)
const selectedSurat = ref(null)

const openUploadModal = (row) => {
  selectedSurat.value = row
  isUploadModalOpen.value = true
}

const closeUploadModal = () => {
  isUploadModalOpen.value = false
  selectedSurat.value = null
}

const onUploadSuccess = () => {
  fetchData()
  closeUploadModal()
}
</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
}
</script>

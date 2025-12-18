<template>
  <Head title="Riwayat Surat Tugas" />

  <div class="bg-white w-full h-full rounded-md">
    <HeaderPage title="History Perjalanan Dinas" />

    <div class="p-8">
      <h1 class="text-3xl font-bold mb-6 text-gray-800">Riwayat Surat Tugas</h1>
    </div>

    <DataTable
      :columns="columns"
      :data="suratTugas.data"
      :meta="suratTugas.meta"
      :links="suratTugas.links"
      :filters="filters"
      :status-options="historyStatusOptions"
      route-name="direktur.history"
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

  <FilePreviewModal
    :show="preview.state.show"
    :file="preview.state.file"
    :loading="preview.state.loading"
    :error="preview.state.error"
    title="Surat Undangan"
    @close="preview.close"
  />
</template>

<script setup>
import { Head, usePage, router } from '@inertiajs/vue3'
import { reactive, watch, computed, ref } from 'vue'
import debounce from 'lodash.debounce'

import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import ModalLaporan from '@/Components/ModalLaporan.vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'

import FilePreviewModal from '@/Components/FilePreviewModal.vue'
import { useFilePreview } from '@/utils/useFilePreviews.js'

import { statusOptions } from '@/utils/statusOptions'

const page = usePage()

const suratTugas = computed(() => page.props.suratTugas ?? {
  data: [],
  meta: {},
  links: {},
})

const filters = reactive({
  search: page.props.filters?.search ?? '',
  status: page.props.filters?.status ?? '',
  from: page.props.filters?.from ?? '',
  to: page.props.filters?.to ?? '',
  page: page.props.filters?.page ?? 1,
  range: page.props.filters?.range ?? '',
})

const historyStatusOptions = computed(() => {
  const allowed = [
    'published',
    'awaiting_proof_upload',
    'under_bku_review',
    'returned_for_correction',
    'completed',
  ]
  return statusOptions.filter(opt => allowed.includes(opt.value))
})

const fetchData = debounce(() => {
  router.get(route('direktur.history'), { ...filters }, {
    preserveState: true,
    replace: true,
  })
}, 300)

watch(filters, fetchData, { deep: true })

const onUpdateFilters = (newFilters) => Object.assign(filters, newFilters)

const onChangePage = (pageNumber) => {
  router.get(route('direktur.history'), { ...filters, page: pageNumber }, {
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
  const raw = row?.path_file_surat_usulan
  const url = toStorageUrl(raw)
  if (!url) return

  preview.open({ url, name: 'Surat Undangan' })
}

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'nama_pengusul', label: 'Nama Pengusul' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'nomor_surat_tugas_resmi', label: 'Nomor Surat Resmi' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'total_dana', label: 'Total Dana' },
  { key: 'status_surat', label: 'Status', sortable: false, fixedWidth: '200px', slot: 'status_surat' },
  { key: 'path_file_surat_usulan', label: 'Surat Undangan', sortable: false, fixedWidth: '130px' },
  { key: 'action', label: 'Aksi', sortable: false, fixedWidth: '140px' },
]
</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
}
</script>

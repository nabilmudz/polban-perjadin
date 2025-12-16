<template>
  <Head title="History" />

  <AppLayout>
    <div class="bg-white w-full rounded-md shadow">
      <HeaderPage />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-6">History Surat Tugas</h1>

        <DataTable
          :columns="columns"
          :data="surat.data"
          :meta="surat.meta"
          :links="surat.links"
          :filters="filters"
          :status-options="historyStatusOptions"
          route-name="sekdir.history"
          @update:filters="onUpdateFilters"
          @changePage="onChangePage"
        >
          <template #status_surat="{ row }">
            <StatusBadges :status="row.status_surat" />
          </template>

          <template #path_file_surat_usulan="{ row }">
            <button
              v-if="row.path_file_surat_usulan"
              @click="openPreview(row.path_file_surat_usulan, 'Surat Undangan')"
              class="px-3 py-1 rounded bg-yellow-400 text-black shadow hover:brightness-95 flex items-center justify-center"
              title="Lihat Surat Undangan"
            >
              <font-awesome-icon :icon="['far', 'file-lines']" />
            </button>
            <span v-else class="text-gray-400">-</span>
          </template>

          <template #aksi="{ row }">
            <div class="flex gap-2">
              <button
                title="Lihat Detail"
                @click="handleViewDetail(row)"
                class="px-2 py-1 rounded shadow flex items-center justify-center bg-blue-500 text-white hover:brightness-90"
              >
                <font-awesome-icon :icon="['far', 'eye']" class="text-md" />
              </button>

              <button
                v-if="row.file_final"
                title="Lihat File"
                @click="openPreview(row.file_final, 'File Final')"
                class="px-2 py-1 rounded shadow flex items-center justify-center bg-purple-500 text-white hover:brightness-90"
              >
                <font-awesome-icon :icon="['far', 'file-pdf']" class="text-md" />
              </button>

              <a
                v-if="row.file_final"
                :href="toStorageUrl(row.file_final)"
                download
                class="px-2 py-1 rounded shadow flex items-center justify-center bg-green-500 text-white hover:brightness-90"
                title="Download"
              >
                <font-awesome-icon :icon="['far', 'circle-down']" class="text-md" />
              </a>
            </div>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>

  <ModalLaporan :show="showViewModal" @close="showViewModal = false">
    <LaporanSurat v-if="selectedData" :surat="selectedData" />
  </ModalLaporan>

  <FilePreviewModal
    :show="preview.state.show"
    :file="preview.state.file"
    :loading="preview.state.loading"
    :error="preview.state.error"
    :title="previewTitle"
    @close="closePreview"
  />
</template>

<script setup>
import { Head, usePage, router } from '@inertiajs/vue3'
import { reactive, watch, computed, ref } from 'vue'
import debounce from 'lodash.debounce'

import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import ModalLaporan from '@/Components/ModalLaporan.vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'

import FilePreviewModal from '@/Components/FilePreviewModal.vue'
import { useFilePreview } from '@/utils/useFilePreviews.js'
import { statusOptions } from '@/utils/statusOptions'

const page = usePage()

const surat = computed(() => page.props.surat ?? { data: [], meta: {}, links: {} })

const filters = reactive({
  search: page.props.filters?.search ?? '',
  status: page.props.filters?.status ?? '',
  from: page.props.filters?.from ?? '',
  to: page.props.filters?.to ?? '',
  page: page.props.filters?.page ?? 1,
  range: page.props.filters?.range ?? '',
})

const allowed = new Set([
  'pending_direktur_signature',
  'published',
  'awaiting_proof_upload',
  'under_bku_review',
  'returned_for_correction',
  'completed',
])

const historyStatusOptions = statusOptions.filter((x) => allowed.has(x.value))

const fetchData = debounce(() => {
  router.get(route('sekdir.history'), { ...filters }, {
    preserveState: true,
    replace: true,
  })
}, 300)

watch(filters, fetchData, { deep: true })

const onUpdateFilters = (newFilters) => Object.assign(filters, newFilters)
const onChangePage = (pageNumber) => { filters.page = pageNumber }

const showViewModal = ref(false)
const selectedData = ref(null)
const handleViewDetail = (row) => {
  selectedData.value = row
  showViewModal.value = true
}

const preview = useFilePreview()
const previewTitle = ref('Preview')

const toStorageUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  if (path.startsWith('/storage/')) return path
  if (path.startsWith('/')) return path
  return `/storage/${path}`
}

const openPreview = (rawPath, title) => {
  const url = toStorageUrl(rawPath)
  if (!url) return
  previewTitle.value = title
  preview.open({ url, name: title })
}

const closePreview = () => preview.close()

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'no_usulan_surat', label: 'Nomor Surat Usulan' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'total_dana', label: 'Total Dana' },
  { key: 'status_surat', label: 'Status' },
  { key: 'path_file_surat_usulan', label: 'Surat Undangan', sortable: false, fixedWidth: '130px' },
  { key: 'aksi', label: 'Aksi', fixedWidth: '180px' },
]
</script>

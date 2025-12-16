<template>
  <Head title="Daftar Persetujuan Direktur" />

  <AppLayout>
    <div class="bg-white w-full rounded-md shadow">
      <HeaderPage title="Daftar Persetujuan Direktur" />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-6">Menunggu Tanda Tangan</h1>

        <DataTable
          :columns="columns"
          :data="suratTugas.data"
          :meta="suratTugas.meta"
          :links="suratTugas.links"
          :filters="filters"
          route-name="direktur.daftarpersetujuan"
          @update:filters="onUpdateFilters"
          @changePage="onChangePage"
        >
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

          <template #aksi="{ row }">
            <button
              class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm"
              @click="gotoReview(row.id)"
            >
              Review
            </button>
          </template>
        </DataTable>
      </div>
    </div>

    <FilePreviewModal
      :show="preview.state.show"
      :file="preview.state.file"
      :loading="preview.state.loading"
      :error="preview.state.error"
      title="Surat Undangan"
      @close="preview.close"
    />
  </AppLayout>
</template>

<script setup>
import { Head, usePage, router } from '@inertiajs/vue3'
import { reactive, watch, computed } from 'vue'
import debounce from 'lodash.debounce'

import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import FilePreviewModal from '@/Components/FilePreviewModal.vue'
import { useFilePreview } from '@/utils/useFilePreviews.js'

const page = usePage()

const suratTugas = computed(() => page.props.suratTugas ?? { data: [], meta: {}, links: {} })

const filters = reactive({
  search: page.props.filters?.search ?? '',
  from: page.props.filters?.from ?? '',
  to: page.props.filters?.to ?? '',
  range: page.props.filters?.range ?? '',
  page: page.props.filters?.page ?? 1,
})

const fetchData = debounce(() => {
  router.get(route('direktur.daftarpersetujuan'), { ...filters }, { preserveState: true, replace: true })
}, 300)

watch(filters, fetchData, { deep: true })

const onUpdateFilters = (newFilters) => Object.assign(filters, newFilters)
const onChangePage = (pageNumber) => { filters.page = pageNumber }

const gotoReview = (id) => router.get(route('direktur.persetujuan.show', id))

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

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'nama_pengusul', label: 'Nama Pengusul' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'nomor_surat_tugas_resmi', label: 'Nomor Surat Resmi' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'total_dana', label: 'Total Dana' },
  { key: 'path_file_surat_usulan', label: 'Surat Undangan', sortable: false, fixedWidth: '130px' },
  { key: 'aksi', label: 'Aksi', sortable: false, fixedWidth: '140px' },
]
</script>

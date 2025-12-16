<template>
  <Head title="Dashboard" />

  <div class="bg-white w-full h-full rounded-md shadow">
    <HeaderPage />

    <div class="p-8">
      <h1 class="text-3xl font-bold mb-4">Dashboard Direktur</h1>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-5">
        <StatCard title="Total Pengusulan" icon="file" :count="statusCounts.total || 0" />
        <StatCard title="Laporan Selesai" icon="square-check" :count="statusCounts.completed || 0" color="green" />
        <StatCard title="Belum Selesai" icon="folder-closed" :count="statusCounts.published || 0" color="purple" />
        <StatCard title="Bertugas" icon="user" :count="statusCounts.on_duty || 0" color="yellow" />
        <StatCard title="Dikembalikan" icon="circle-left" :count="statusCounts.returned_for_correction || 0" color="red" />
      </div>
    </div>

    <DataTable
      :columns="columns"
      :data="suratTugas.data"
      :meta="suratTugas.meta"
      :links="suratTugas.links"
      :filters="filters"
      route-name="direktur.dashboard"
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
import StatCard from '@/Components/StatCard.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import ModalLaporan from '@/Components/ModalLaporan.vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'

import FilePreviewModal from '@/Components/FilePreviewModal.vue'
import { useFilePreview } from '@/utils/useFilePreviews.js'

const page = usePage()

const statusCounts = computed(() => page.props.statusCounts || {})

const suratTugas = computed(() => page.props.suratTugas ?? {
  data: [],
  meta: {},
  links: {},
})

const filters = reactive({
  search: page.props.filters?.search ?? '',
  from: page.props.filters?.from ?? '',
  to: page.props.filters?.to ?? '',
  page: page.props.filters?.page ?? 1,
  range: page.props.filters?.range ?? '',
})

const showViewModal = ref(false)
const selectedData = ref(null)

const fetchData = debounce(() => {
  router.get(route('direktur.dashboard'), { ...filters }, {
    preserveState: true,
    replace: true,
  })
}, 300)

watch(filters, fetchData, { deep: true })

const onUpdateFilters = (newFilters) => Object.assign(filters, newFilters)

const onChangePage = (pageNumber) => {
  router.get(route('direktur.dashboard'), { ...filters, page: pageNumber }, {
    preserveState: true,
    replace: true,
  })
}

const handleView = (row) => {
  selectedData.value = row
  showViewModal.value = true
}

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'nama_pengusul', label: 'Nama Pengusul' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'nomor_surat_tugas_resmi', label: 'Nomor Surat Resmi' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'total_dana', label: 'Total Dana' },
  { key: 'path_file_surat_usulan', label: 'Surat Undangan', sortable: false, fixedWidth: '130px' },
  { key: 'action', label: 'Aksi', sortable: false, fixedWidth: '120px' },
]

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

  preview.open({
    url,
    name: 'Surat Undangan',
  })
}

</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'

export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
}
</script>

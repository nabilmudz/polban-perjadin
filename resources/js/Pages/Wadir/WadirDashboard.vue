<template>
  <Head title="Dashboard"/>
  <AppLayout>
    <div class="bg-white w-full rounded-md shadow">
      <HeaderPage />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-4">Dashboard Wadir</h1>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-5">
            <StatCard
              title="Total Pengusulan"
              icon="file"
              :count="totalPengusulan"
            />
            <StatCard
              title="Laporan Selesai"
              icon="square-check"
              :count="statusCounts.completed || 0"
              color="green"
            />
            <StatCard
              title="Belum Selesai"
              icon="folder-closed"
              :count="statusCounts.published || 0"
              color="purple"
            />
            <StatCard
              title="Bertugas"
              icon="user"
              :count="statusCounts.on_duty || 0"
              color="yellow"
            />
            <StatCard
              title="Dikembalikan"
              icon="circle-left"
              :count="statusCounts.revision_requested || 0"
              color="red"
            />
          </div>
        </div>

      <div class="p-8">
        <DataTable
          :columns="columns"
          :data="suratTugas.data"
          :meta="suratTugas.meta"
          :links="suratTugas.links"
          :filters="filters"
          :route-name="dashboardRoute"
          @update:filters="onUpdateFilters"
          @changePage="onChangePage"
        >
          <template #status_surat="{ row }">
            <StatusBadges :status="row.status_surat" />
          </template>

          <template #action="{ row }">
            <button
              @click="handleView(row)"
              class="px-3 py-1 rounded bg-blue-500 text-white shadow hover:brightness-90 flex items-center gap-1"
            >
              <font-awesome-icon :icon="['far', 'eye']" /> Lihat
            </button>
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
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import StatCard from '@/Components/StatCard.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { usePage, router, Head } from '@inertiajs/vue3'
import { reactive, watch, computed } from 'vue'
import debounce from 'lodash.debounce'
import FilePreviewModal from '@/Components/FilePreviewModal.vue'
import { useFilePreview } from '@/utils/useFilePreviews.js'

const page = usePage()
const user = page.props.auth.user
const dashboardRoute = `${user.role}.dashboard`
const statusCounts = computed(() => page.props.statusCounts || {})

const totalPengusulan = computed(() =>
  Object.values(statusCounts.value).reduce((a, b) => a + (Number(b) || 0), 0)
)

const suratTugas = computed(
  () => page.props.suratTugas ?? { data: [], meta: {}, links: {} },
)

const filters = reactive({
  search: page.props.filters?.search ?? '',
  status: page.props.filters?.status ?? '',
  from: page.props.filters?.from ?? '',
  to: page.props.filters?.to ?? '',
  page: page.props.filters?.page ?? 1,
  range: page.props.filters?.range ?? '',  
})

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'no_usulan_surat', label: 'Nomor Surat Usulan' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'total_dana', label: 'Total Dana' },
  { key: 'path_file_surat_usulan', label: 'Surat Undangan', sortable: false, fixedWidth: '130px' },
  { key: 'action', label: 'Aksi', sortable: false, fixedWidth: '120px' },
]

const preview = useFilePreview()

const openSuratUndangan = (row) => {
  const path = row?.path_file_surat_usulan
  if (!path) return

  preview.open({
    url: path,
    name: 'Surat Undangan',
  })
}

watch(
  filters,
  debounce(() => {
    router.get(
      route(dashboardRoute),
      { ...filters },
      {
        preserveState: true,
        replace: true,
      },
    )
  }, 300),
  { deep: true },
)

const onUpdateFilters = (newFilters) => {
  Object.assign(filters, newFilters)
}

const onChangePage = (pageNumber) => {
  filters.page = pageNumber
}
const getSuratTugasId = (row) => row?.surat_tugas_id ?? row?.id

const handleView = (row) => {
  const id = getSuratTugasId(row)
  if (!id) return console.error('Missing suratTugasId', row)
  router.get(route(`${user.role}.persetujuan.show`, id))
}

const updateFilters = () => {
    router.get(route(dashboardRoute), filters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const debouncedUpdateFilters = debounce(() => {
    updateFilters()
}, 400)

</script>

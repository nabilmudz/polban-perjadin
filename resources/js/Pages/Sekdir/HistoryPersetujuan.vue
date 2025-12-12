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
          route-name="sekdir.history"
          @update:filters="onUpdateFilters"
          @changePage="onChangePage"
        >
          <template #status_surat="{ row }">
            <StatusBadges :status="row.status_surat" />
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
                @click="openFile(row.file_final)"
                class="px-2 py-1 rounded shadow flex items-center justify-center bg-purple-500 text-white hover:brightness-90"
              >
                <font-awesome-icon :icon="['far', 'file-pdf']" class="text-md" />
              </button>

              <a
                v-if="row.file_final"
                :href="fileUrl(row.file_final)"
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
</template>

<script setup>
import { Head, usePage, router } from '@inertiajs/vue3'
import { reactive, watch, computed, ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import ModalLaporan from '@/Components/ModalLaporan.vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'
import debounce from 'lodash.debounce'

const page = usePage()

const surat = computed(() => page.props.surat ?? {
  data: [],
  meta: {},
  links: {},
})

const filters = reactive({
  search: page.props.filters?.search ?? '',
  status: page.props.filters?.status ?? '',
  from: page.props.filters?.from ?? '',
  to: page.props.filters?.to ?? '',
  range: page.props.filters?.range ?? '', 
})

const fetchData = debounce(() => {
  router.get(route('sekdir.history'), filters, {
    preserveState: true,
    replace: true,
  })
}, 300)

watch(filters, fetchData, { deep: true })

const onUpdateFilters = (newFilters) => {
  Object.assign(filters, newFilters)
}

const onChangePage = (pageNumber) => {
  router.get(
    route('sekdir.history'),
    { ...filters, page: pageNumber },
    { preserveState: true, replace: true },
  )
}

const showViewModal = ref(false)
const selectedData = ref(null)

const handleViewDetail = (row) => {
  selectedData.value = row
  showViewModal.value = true
}

const fileUrl = (path) => `/storage/${path}`

const openFile = (path) => {
  window.open(`/storage/${path}`, '_blank')
}

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'no_usulan_surat', label: 'Nomor Surat Usulan' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'nominal_dana', label: 'Total Dana' },
  { key: 'status_surat', label: 'Status' },
  { key: 'aksi', label: 'Aksi', fixedWidth: '180px' },
]
</script>

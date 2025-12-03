<template>
  <Head title="Dashboard" />

  <div class="bg-white w-full h-full rounded-md shadow">
    <HeaderPage />

    <div class="p-8">
      <h1 class="text-3xl font-bold mb-4">Dashboard</h1>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-5">
        <StatCard
          title="Total Pengusulan"
          icon="file"
          :count="statusCounts.total || 0"
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

    <DataTable
      :columns="columns"
      :data="suratTugas.data"
      :meta="suratTugas.meta"
      :links="suratTugas.links"
      :filters="filters"
      :status-options="statusOptions"
      route-name="sekdir.dashboard"
      @update:filters="onUpdateFilters"
      @changePage="onChangePage"
    >
      <template #status_surat="{ row }">
        <StatusBadges :status="row.status_surat" />
      </template>

      <template #action="{ row }">
        <div class="flex gap-2">
          <button
            class="px-2 py-1 rounded shadow flex items-center justify-center
                   bg-blue-500 text-white hover:brightness-90"
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
import { statusOptions } from '@/utils/statusOptions'

const page = usePage()

const suratTugas = computed(() => page.props.suratTugas ?? {
  data: [],
  meta: {},
  links: {},
})

const statusCounts = computed(() => page.props.statusCounts || {})

const filters = reactive({
  search: page.props.filters?.search ?? '',
  status: page.props.filters?.status ?? '',
  from: page.props.filters?.from ?? '',
  to: page.props.filters?.to ?? '',
})

const showViewModal = ref(false)
const selectedData = ref(null)

const fetchData = debounce(() => {
  router.get(route('sekdir.dashboard'), filters, {
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
    route('sekdir.dashboard'),
    { ...filters, page: pageNumber },
    { preserveState: true, replace: true },
  )
}

const handleView = (row) => {
  selectedData.value = row
  showViewModal.value = true
}

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'no_usulan_surat', label: 'Nomor Surat Usulan' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'nominal_dana', label: 'Total Dana' },
  { key: 'status_surat', label: 'Status' },
  { key: 'action', label: 'Aksi', fixedWidth: '120px' },
]
</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'

export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
}
</script>

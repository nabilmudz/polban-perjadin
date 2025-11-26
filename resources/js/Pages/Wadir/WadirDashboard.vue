<template>
  <Head title="Dashboard"/>
  <AppLayout>
    <div class="bg-white w-full rounded-md shadow">
      <HeaderPage />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-4">Dashboard Wadir</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
          <StatCard title="Total Pengusulan" icon="file" :count="stats.total" />
          <StatCard title="Usulan Baru" icon="PlusSquare" :count="stats.baru" color="green" />
          <StatCard title="Dalam Proses (Direktur)" icon="clock" :count="stats.proses_direktur" color="yellow" />
          <StatCard title="Bertugas" icon="briefcase" :count="stats.bertugas" color="light_blue" />
          <StatCard title="Ditolak" icon="times-circle" :count="stats.rejected" color="red" />
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
        </DataTable>
      </div>
    </div>
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

const page = usePage()
const user = page.props.auth.user
const dashboardRoute = `${user.role}.dashboard`

const suratTugas = computed(
  () => page.props.suratTugas ?? { data: [], meta: {}, links: {} },
)
const stats = computed(() => page.props.stats ?? {})

const filters = reactive({
  search: page.props.filters?.search ?? '',
  status: page.props.filters?.status ?? '',
  from: page.props.filters?.from ?? '',
  to: page.props.filters?.to ?? '',
  page: page.props.filters?.page ?? 1,
})

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'status_surat', label: 'Status' },
  { key: 'action', label: 'Aksi', sortable: false },
]

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

const handleView = (row) => {
  router.get(route(`${user.role}.persetujuan.show`, row.surat_tugas_id))
}

const handleAction = (type, row) => {
  switch (type) {
    case 'view':
      router.get(route(`${user.role}.persetujuan.show`, row.surat_tugas_id))
      break
    case 'edit':
      router.get(route('pengusul.edit', row.id))
      break
    case 'delete':
      if (confirm('Are you sure?')) {
        router.delete(route('pengusul.destroy', row.id))
      }
      break
    case 'download':
      router.get(route('pengusul.download', row.id))
      break
  }
}
</script>

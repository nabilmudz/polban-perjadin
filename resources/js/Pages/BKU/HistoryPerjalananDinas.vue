<template>
  <AppLayout>
    <div class="bg-white w-full h-full rounded-md shadow">
      <HeaderPage />

      <!-- TITLE -->
      <div class="p-8">
        <h1 class="text-3xl font-bold mb-4">History Perjalanan Dinas</h1>
      </div>

      <!-- DATA TABLE -->
      <DataTable
        :columns="columns"
        :data="history.data"
        :meta="history.meta"
        :links="history.links"
        :filters="filters"
        route-name="history.perjalanan"
        @update:filters="Object.assign(filters, $event)"
        @changePage="(page) =>
          router.get(route('history.perjalanan'), { ...filters, page }, {
            preserveState: true,
            replace: true,
          })
        "
      >
        <template #status_surat="{ row }">
          <StatusBadges :status="row.status_surat" />
        </template>

        <template #aksi="{ row }">
          <div class="flex gap-2">
            <button
              @click="viewItem(row)"
              class="p-2 bg-blue-500 text-white rounded hover:brightness-90"
            >
              <font-awesome-icon :icon="['far', 'eye']" />
            </button>

            <button
              @click="downloadItem(row)"
              class="p-2 bg-green-500 text-white rounded hover:brightness-90"
            >
              <font-awesome-icon :icon="['far', 'download']" />
            </button>
          </div>
        </template>
      </DataTable>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { router, usePage } from '@inertiajs/vue3'
import { reactive, computed, watch } from 'vue'
import debounce from 'lodash.debounce'


const page = usePage()
const history = computed(() => page.props.history)

const filters = reactive({
  search: page.props.filters?.search || '',
  from: page.props.filters?.from || '',
  to: page.props.filters?.to || '',
})

watch(
  filters,
  debounce(() => {
    router.get(route('bku.historyperjalanandinas'), filters, {
      preserveState: true,
      replace: true,
    })
  }, 300),
  { deep: true }
)

const columns = [
  { key: 'no', label: 'No' },
  { key: 'tanggal_pengusulan', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'nomor_pengantar', label: 'Nomor Surat Pengantar' },
  { key: 'nomor_tugas', label: 'Nomor Surat Tugas' },
  { key: 'tanggal_diterbitkan', label: 'Tanggal Diterbitkan' },
  { key: 'diusulkan_kepada', label: 'Diusulkan Kepada' },
  { key: 'status_surat', label: 'Status', slot: 'status_surat' },
  { key: 'aksi', label: 'Aksi', slot: 'aksi' },
]

const viewItem = (row) => {
  router.get(route('history.view', row.id))
}

const downloadItem = (row) => {
  router.get(route('history.download', row.id))
}
</script>

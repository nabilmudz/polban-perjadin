<template>
  <Head title="Dashboard" />
  <div class="bg-white w-full h-full rounded-md shadow">
    <HeaderPage />
    <div class="p-8">
      <h1 class="text-3xl font-bold mb-4">Dashboard</h1>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        <StatCard title="Total Pengusulan" icon="file" :count="totalPengusulan" />
        <StatCard title="Laporan Selesai" icon="square-check" :count="statusCounts.completed || 0" color="green" />
        <StatCard title="Belum Selesai" icon="folder-closed" :count="statusCounts.published || 0" color="purple" />
        <StatCard title="Bertugas" icon="user" :count="statusCounts.on_duty || 0" color="yellow" />
        <StatCard title="Dikembalikan" icon="circle-left" :count="statusCounts.revision_requested || 0" color="red" />
      </div>
    </div>

      <DataTable
        :columns="columns"
        :data="suratTugas.data"
        :meta="suratTugas.meta"
        :links="suratTugas.links"
        :filters="filters"
        :status-options="statusOptions"
        route-name="pengusul.dashboard"
        @update:filters="Object.assign(filters, $event)"
        @changePage="(page) =>
          router.get(route('pengusul.dashboard'), { ...filters }, {
            preserveState: true,
            replace: true
          })
        "
      >
        <template #status_surat="{ row }">
          <StatusBadges :status="row.status_surat" />
        </template>
        <template #action="{ row }">
          <div class="flex gap-2">
            <button
              v-for="action in getRowActions(row, currentUser.role)"
              :key="action.type"
              @click="handleAction(action.type, row)"
              :title="action.type"
              class="px-2 py-1 rounded shadow flex items-center justify-center transition hover:brightness-90"
              :class="{
                'bg-blue-500 text-white': action.color === 'blue',
                'bg-green-500 text-white': action.color === 'green',
                'bg-red-500 text-white': action.color === 'red',
                'bg-yellow-400 text-black': action.color === 'yellow',
                'bg-purple-500 text-white': action.color === 'purple',
              }"
            >
              <font-awesome-icon :icon="['far', action.icon]" class="text-md" />
            </button>
          </div>
        </template>
      </DataTable>
    </div>
    <ModalLaporan :show="showViewModal" @close="showViewModal = false">
      <LaporanSurat :surat="selectedData" />
    </ModalLaporan>
</template>

<script setup>
import HeaderPage from '@/Components/HeaderPage.vue'
import StatCard from '@/Components/StatCard.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import ModalLaporan from '@/Components/ModalLaporan.vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'
import { usePage, router,Head } from '@inertiajs/vue3'
import { reactive, watch, ref } from 'vue'
import debounce from 'lodash.debounce'
import { computed } from 'vue'
import { getRowActions } from '@/utils/rowAction'


const page = usePage()
const currentUser = page.props.auth.user
const suratTugas = computed(() => page.props.suratTugas)

const filters = reactive({
  search: page.props.filters?.search || '',
  status: page.props.filters?.status || '',
  from: page.props.filters?.from || '',
  to: page.props.filters?.to || '',
})

const statusCounts = computed(() => page.props.statusCounts || {})
const totalPengusulan = computed(() => {
  return Object.values(statusCounts.value).reduce((a, b) => a + b, 0)
})

watch(
  filters,
  debounce(() => {
    router.get(route('pengusul.dashboard'), filters, {
      preserveState: true,
      replace: true,
    })
  }, 300),
  { deep: true }
)

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'status_surat', label: 'Status' },
  { key: 'action', label: 'Aksi', fixedWidth: '180px' }
]

const showViewModal = ref(false)
const selectedData = ref({})

const handleAction = (type, row) => {
  switch(type) {
    case 'view':
      selectedData.value = row
      showViewModal.value = true
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

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import ModalLaporan from '@/Components/ModalLaporan.vue'
import { statusOptions } from '@/utils/statusOptions'
export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
}
</script>

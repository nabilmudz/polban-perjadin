<template>
  <AppLayout>
    <div class="bg-white w-full rounded-md shadow">
      <HeaderPage />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-4">Dashboard</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <StatCard title="Total Ulasan" icon="file" :count="stats?.total_ulasan || 0" />
          <StatCard title="Bertugas" icon="user" :count="stats?.bertugas || 0" />
        </div>
      </div>

      <div class="px-6 pb-8 space-y-10">
        <DataTable
          :columns="columns"
          :data="suratTugas.data"
          :meta="suratTugas.meta"
          :links="suratTugas.links"
          :filters="filters"
          route-name="direktur.dashboard"
          @update:filters="Object.assign(filters, $event)"
          @changePage="(page) =>
            router.get(route('direktur.dashboard'), { ...filters, page }, {
              preserveState: true,
              replace: true,
            })
          "
        >
          <template #tanggal_berangkat="{ row }">
              {{ formatDate(row.tanggal_berangkat) }}
          </template>

          <template #status_surat="{ row }">
            <StatusBadges :status="row.status_surat" />
          </template>
          
          <template #actions="{ row }">
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
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import StatCard from '@/Components/StatCard.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { reactive, computed, watch } from 'vue'
import debounce from 'lodash.debounce'
import { getRowActions } from '@/utils/rowAction'

const props = defineProps({
  suratTugas: Object,
  filters: Object,
  stats: Object 
})

const page = usePage()
const currentUser = page.props.auth.user

const suratTugas = computed(() => props.suratTugas)

const filters = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  from: props.filters?.from || '',
  to: props.filters?.to || '',
})

watch(
  filters,
  debounce(() => {
    router.get(route('direktur.dashboard'), filters, {
      preserveState: true,
      replace: true,
    })
  }, 300),
  { deep: true }
)

const formatDate = (dateString) => {
    if (!dateString) return '-';
    if (dateString.length === 10 && dateString.includes('-')) return dateString;

    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString; 
    
    return date.toISOString().split('T')[0];
}

const columns = [
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat', slot: 'tanggal_berangkat' },
  { key: 'nomor_surat_tugas_resmi', label: 'Nomor Surat' },
  { key: 'perihal_tugas', label: 'Perihal Tugas' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'status_surat', label: 'Status', slot: 'status_surat' },
  { key: 'actions', label: 'Aksi', slot: 'actions' },
]


const handleAction = (type, row) => {
  switch(type) {
    case 'view':
      router.get(route('direktur.persetujuan.show', row.id)) 
      break
    case 'approve':
      router.post(route('direktur.persetujuan.approve', row.id))
      break
    case 'reject':
      router.post(route('direktur.persetujuan.reject', row.id))
      break
    default:
      console.warn(`Unhandled action type: ${type}`)
  }
}
</script>
<template>
  <AppLayout>
    <div class="bg-white w-full rounded-md shadow">
      <HeaderPage />

      <!-- PAGE TITLE & STAT CARDS -->
      <div class="p-8">
        <h1 class="text-3xl font-bold mb-4">Dashboard</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <StatCard title="Total Ulasan" icon="file" :count="10" />
          <StatCard title="Bertugas" icon="user" :count="4" />
        </div>
      </div>

      <!-- MAIN CONTENT -->
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
          <!-- Status Badge slot -->
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

const page = usePage()
const currentUser = page.props.auth.user
const suratTugas = computed(() => page.props.suratTugas)
console.log("Surat Tugas: ", suratTugas);

const filters = reactive({
  search: page.props.filters?.search || '',
  status: page.props.filters?.status || '',
  from: page.props.filters?.from || '',
  to: page.props.filters?.to || '',
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

const columns = [
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'nomor_surat_tugas_resmi', label: 'Nomor Surat' },
  { key: 'perihal_tugas', label: 'Perihal Tugas' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'status_surat', label: 'Status', slot: 'status_surat' },
  { key: 'actions', label: 'Aksi', slot: 'actions' },
]


const handleAction = (type, row) => {
  switch(type) {
    case 'view':
      router.get(route('direktur.view', row.id))
      break
    case 'approve':
      router.post(route('direktur.approve', row.id))
      break
    case 'reject':
      router.post(route('direktur.reject', row.id))
      break
    default:
      console.warn(`Unhandled action type: ${type}`)
  }
}
</script>



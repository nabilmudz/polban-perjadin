<template>
    <AppLayout>
        <div class="bg-white w-full rounded-md shadow">
            <HeaderPage />

            <div class="p-8">
                <h1 class="text-3xl font-bold mb-4">Dashboard Wadir</h1>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                    <StatCard title="Total Pengusulan" icon="file" :count="totalPengusulan" />
                    <StatCard title="Laporan Selesai" icon="square-check" :count="statusCounts.selesai || 0" color="green" />
                    <StatCard title="Belum Selesai" icon="folder-closed" :count="statusCounts.published || 0" color="purple" />
                    <StatCard title="Bertugas" icon="user" :count="statusCounts.on_duty || 0" color="yellow" />
                    <StatCard title="Dikembalikan" icon="circle-left" :count="statusCounts.revision_requested || 0" color="red" />
                </div>
            </div>

            <div class="p-8">
                <DataTable
                    :columns="columns"
                    :data="suratTugas.data"
                    :meta="suratTugas.meta"
                    :links="suratTugas.links"
                    :filters="filters"
                    :status-options="statusOptions"
                    route-name="wadir.dashboard"
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
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import StatCard from '@/Components/StatCard.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { usePage, router } from '@inertiajs/vue3'
import { ref, reactive, onMounted } from 'vue'
import debounce from 'lodash.debounce'

const { props } = usePage()
const suratTugas = props.suratTugas
const filters = ref(props.filters)
const user = usePage().props.auth.user
const dashboardRoute = `${user.role}.dashboard`

// --------------------------
// 1. Definisi stats / counts
// --------------------------
const totalPengusulan = ref(props.stats?.total_pengusulan || 0)

const statusCounts = reactive({
  selesai: props.stats?.selesai || 0,
  published: props.stats?.published || 0,
  on_duty: props.stats?.on_duty || 0,
  revision_requested: props.stats?.revision_requested || 0,
})

// --------------------------
// 2. Columns & actions
// --------------------------
const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'nomor_surat_usulan_jurusan', label: 'Nomor Surat Usulan' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  // { key: 'surat_undangan', label: 'Surat Undangan' },
  { key: 'action', label: 'Aksi', fixedWidth: '180px' }
]

const handleView = (row) => {
  router.get(route(`${user.role}.persetujuan.show`, row.surat_tugas_id))
}

const handleAction = (type, row) => {
  switch(type) {
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

// --------------------------
// 3. Filter debounce
// --------------------------
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

const onFiltersUpdate = (newFilters) => {
    Object.assign(filters.value, newFilters)
    debouncedUpdateFilters()
}
</script>

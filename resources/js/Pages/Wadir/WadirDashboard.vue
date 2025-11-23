<template>
    <AppLayout>
        <div class="bg-white w-full rounded-md shadow">
            <HeaderPage />

            <div class="p-8">
                <h1 class="text-3xl font-bold mb-4">Dashboard Wadir</h1>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <StatCard title="Total Tugas" icon="file" :count="stats.total" />
                    <StatCard title="Tugas Selesai" icon="square-check" :count="stats.approved" />
                    <StatCard title="Pending" icon="clock" :count="stats.pending" />
                    <StatCard title="Ditolak" icon="times-circle" :count="stats.rejected" />
                </div>
            </div>

            <div class="p-8">
                <DataTable
                    :columns="columns"
                    :data="suratTugas.data"
                    :meta="suratTugas.meta"
                    :links="suratTugas.links"
                    :filters="filters"
                    route-name="dashboardRoute"
                    @update:filters="Object.assign(filters, $event)"
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
import { usePage, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const { props } = usePage()
const suratTugas = props.suratTugas
const stats = props.stats
const filters = ref(props.filters)
const user = usePage().props.auth.user
const dashboardRoute = `${user.role}.dashboard`

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'status_surat', label: 'Status' },
  { key: 'action', label: 'Aksi', sortable: false },
]

const handleView = (row) => {
    router.get(route(`${user.role}.persetujuan.show`, row.id))
}

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

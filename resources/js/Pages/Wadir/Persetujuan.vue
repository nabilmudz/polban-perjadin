<template>
    <AppLayout>
        <div class="bg-white w-full h-full rounded-md shadow">
            <HeaderPage />

            <div class="p-8">
                <h1 class="text-3xl font-bold mb-4">Persetujuan</h1>
            </div>

            <div class="p-8">
                <DataTable
                    :columns="columns"
                    :data="suratTugas.data"
                    :meta="suratTugas.meta"
                    :links="suratTugas.links"
                    :filters="filters"
                    route-name="persetujuanRoute"
                    @update:filters="Object.assign(filters, $event)"
                >
                    <!-- STATUS BADGE -->
                    <template #status_surat="{ row }">
                        <StatusBadges :status="row.status_surat" />
                    </template>

                    <!-- ACTION -->
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
const dashboardRoute = `${user.role}.persetujuan`

const columns = [
  { key: 'pengusul', label: 'Pengusul' },
  { key: 'nama_kegiatan', label: 'Nama Kegiatan' },
  { key: 'tanggal_pelaksanaan', label: 'Tanggal Pelaksanaan' },
  { key: 'pembiayaan', label: 'Pembiayaan' },
  { key: 'surat_undangan', label: 'Surat Undangan' },
  { key: 'status_surat', label: 'Status' },
  { key: 'action', label: 'Aksi', sortable: false },
]

const handleView = (row) => {
  router.get(route(`${user.role}.surat.show`, row.id))
}
</script>

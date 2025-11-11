<template>
    <AppLayout>
        <div class="bg-white w-full h-full rounded-md shadow">
            <HeaderPage />
            <div class="p-8">
                <h1 class="text-3xl font-bold mb-4">Dashboard Pelaksana</h1>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <StatCard title="Total Tugas" icon="file" :count="tugas?.meta?.total || 0" />
                    <StatCard title="Tugas Selesai" icon="square-check" :count="7" />
                    <StatCard title="Pending" icon="clock" :count="6" />
                    <StatCard title="Ditolak" icon="times-circle" :count="2" />
                </div>
            </div>

            <div class="p-8">
                <DataTable
                    :columns="columns"
                    :data="tugas?.data"
                    :meta="tugas?.meta"
                    :links="tugas?.links"
                    route-name="tugas.index"
                >
                    <!-- Slot untuk kolom aksi -->
                    <template #aksi="{ row }">
                        <div class="flex gap-2">
                            <Link
                                :href="`/surat-tugas/${row.id}`"
                                class="px-3 py-1 bg-cyan-500 text-white rounded text-sm hover:bg-cyan-600"
                            >
                                Detail
                            </Link>
                            <a
                                :href="`/surat-tugas/${row.id}/pdf`"
                                target="_blank"
                                class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700"
                            >
                                PDF
                            </a>
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
import { usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const { props } = usePage()
const tugas = props.tugas
const filters = ref(props.filters)

const columns = [
    { key: 'nomor', label: 'No', sortable: false },
    { key: 'nama_kegiatan', label: 'Nama Kegiatan' },
    { key: 'tanggal_pelaksanaan', label: 'Tanggal Pelaksanaan' },
    { key: 'status', label: 'Status' },
    { key: 'aksi', label: 'Aksi', sortable: false },
]
</script>
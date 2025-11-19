<template>
    <AppLayout>
        <div class="bg-white w-full rounded-md shadow">
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
                <div class="overflow-x-auto">
                <DataTable
                    :columns="columns"
                    :data="suratTugas.data"
                    :meta="suratTugas.meta"
                    :links="suratTugas.links"
                    :filters="filters"
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
                    </template><template #action="{ row }">
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
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import StatCard from '@/Components/StatCard.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { getRowActions } from '@/utils/rowAction'
import { usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const { props } = usePage()
const currentUser = props.auth.user
const suratTugas = props.suratTugas
const filters = ref(props.filters)

const columns = [
    { key: 'nama_kegiatan', label: 'Nama Kegiatan' },
    { key: 'tanggal_pelaksanaan', label: 'Tanggal Pelaksanaan' },
    { key: 'status_surat', label: 'Status' },
    { key: 'action', label: 'Aksi', sortable: false },
]
</script>
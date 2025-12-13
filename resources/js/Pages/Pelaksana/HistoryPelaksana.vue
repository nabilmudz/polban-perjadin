<template>
    <AppLayout>
        <div class="bg-white w-full rounded-md shadow">
            <HeaderPage />

            <div class="p-8">
                <h1 class="text-3xl font-bold mb-4">History Surat Tugas</h1>

                <p class="text-gray-600 mb-3">Riwayat Lengkap</p>
                <div class="overflow-x-auto">
                    <DataTable
                        :columns="columns"
                        :data="formattedSuratTugas"
                        :meta="suratTugas.meta"
                        :links="suratTugas.links"
                        :filters="filters"
                        route-name="Pelaksana.HistoryPelaksana"
                        @update:filters="Object.assign(filters, $event)"
                    >
                        <template #status_surat="{ row }">
                            <StatusBadges :status="row.status_surat" />
                        </template>

                        <template #action="{ row }">
                            <div class="flex gap-2">
                                <button
                                    v-for="action in getRowActions(row)"
                                    :key="action.type"
                                    @click="handleAction(action.type, row)"
                                    class="px-2 py-1 rounded shadow flex items-center justify-center transition hover:brightness-90"
                                    :class="{
                                        'bg-blue-500 text-white': action.color === 'blue',
                                        'bg-green-500 text-white': action.color === 'green'
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
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { getRowActions } from '@/utils/rowAction'
import { usePage, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const { props } = usePage()
const suratTugas = props.suratTugas
const filters = ref(props.filters || { search: '' })

// =====================
// Format tanggal
// =====================
const formatDate = (dateStr) => {
    if (!dateStr) return ''
    // pastikan cuma ambil YYYY-MM-DD dari string ISO
    return dateStr.split('T')[0]
}

// computed untuk data tabel dengan tanggal sudah diformat
const formattedSuratTugas = computed(() =>
    suratTugas.data.map(row => ({
        ...row,
        created_at: formatDate(row.created_at),
        tanggal_berangkat: formatDate(row.tanggal_berangkat),
        tanggal_penomoran_sekdir: formatDate(row.tanggal_penomoran_sekdir),
    }))
)

const columns = [
    { key: 'created_at', label: 'Tanggal Pengusulan' },
    { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
    { key: 'nomor_surat_usulan_jurusan', label: 'Nomor Surat Pengantar' },
    { key: 'nomor_surat_tugas_resmi', label: 'Nomor Surat Tugas' },
    { key: 'tanggal_penomoran_sekdir', label: 'Tanggal Diterbitkan' },
    { key: 'diusulkan_kepada', label: 'Diajukan Kepada' },
    { key: 'status_surat', label: 'Status' },
    { key: 'action', label: 'Aksi', sortable: false },
]

function handleAction(type, row) {
    if (type === 'view') {
        router.get(route('pelaksana.surat.show', row.id))
    }
    if (type === 'download') {
        window.open(route('pelaksana.surat.download', row.id), '_blank')
    }
}
</script>

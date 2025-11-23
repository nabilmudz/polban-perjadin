<template>
    <AppLayout>
        <div class="bg-white w-full h-full rounded-md shadow">
            <HeaderPage />

            <div class="p-8">
                <h1 class="text-3xl font-bold mb-4">History Surat Tugas</h1>
            </div>

            <div class="p-8">
                <DataTable
                    :columns="columns"
                    :data="suratTugas.data"
                    :meta="suratTugas.meta"
                    :links="suratTugas.links"
                    :filters="filters"
                    route-name="historyWadirRoute"
                    @update:filters="Object.assign(filters, $event)"
                >
                    <!-- Status badge -->
                    <template #status_surat="{ row }">
                        <StatusBadges :status="row.status_surat" />
                    </template>
                    <template #action="{ row }">
                        <div class="flex gap-2">
                            <button
                            v-for="action in getRowActions(row, user.role)"
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
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { getRowActions } from '@/utils/rowAction'
import { usePage, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const { props } = usePage()
const suratTugas = props.suratTugas
const filters = ref(props.filters)
filters.value.status = filters.value.status ?? ''

const page = usePage()
const user = page.props.auth?.user ?? {}

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

const statusOptions = [
    { label: "Pending Wadir Review", value: "pending" },
    { label: "Diterbitkan", value: "Diterbitkan" },
    { label: "Draft", value: "Draft" },
]

const handleAction = (type, row) => {
    if (type === 'view') {
        router.get(route(`${user.role}.persetujuan.show`, row.id))
    }

    if (type === 'review') {
        router.get(route(`${user.role}.persetujuan.show`, row.id))
    }
}
</script>

<style scoped>
.flex-center {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

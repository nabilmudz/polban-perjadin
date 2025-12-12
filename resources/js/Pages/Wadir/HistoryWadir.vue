<template>
    <AppLayout>
        <Head title="History Surat Tugas - Wadir" />

        <div class="bg-white w-full rounded-md shadow">
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
                    :route-name="routeName"
                    @update:filters="updateFilters"
                >
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
import { ref, computed, watch } from 'vue'

const { props } = usePage()
const suratTugas = props.suratTugas

const user = usePage().props.auth?.user ?? { role: 'wadir' }

const routeName = computed(() => `${user.role}.history`)

const filters = ref({
    search: props.filters?.search ?? '',
    status: props.filters?.status ?? '',
    from: props.filters?.from ?? '',
    to: props.filters?.to ?? '',
})

const updateFilters = (newFilters) => {
    filters.value = { ...filters.value, ...newFilters }

    router.get(route(routeName.value), filters.value, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    })
}

const columns = [
    { key: 'perihal_tugas', label: 'Nama Kegiatan' },
    { key: 'created_at', label: 'Tanggal Pengusulan' },
    { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
    { key: 'no_usulan_surat', label: 'Nomor Surat Usulan' },
    { key: 'nomor_surat_tugas_resmi', label: 'Nomor Surat Tugas' },
    { key: 'sumber_dana', label: 'Sumber Dana' },
    { key: 'total_dana', label: 'Total Dana' },
    { key: 'status_surat', label: 'Status' },
    { key: 'action', label: 'Aksi', fixedWidth: '180px' }
]

const handleAction = (type, row) => {
    router.get(route(`${user.role}.persetujuan.show`, row.id))
}
</script>

<style scoped>
.flex-center {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

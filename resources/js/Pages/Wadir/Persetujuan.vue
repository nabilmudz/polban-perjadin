<template>
    <AppLayout>
        <div class="bg-white w-full rounded-md shadow">
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
                    :status-options="statusOptions"
                    :route-name="`${user.role}.persetujuan`"
                    @update:filters="Object.assign(filters, $event)"
                >
                    
                    <template #status_surat="{ row }">
                        <StatusBadges :status="row.status_surat" />
                    </template>
                    <template #path_file_surat_usulan="{ row }">
                        <div class="flex gap-2">
                            <button
                                v-for="action in getSuratUndanganAction(row, user.role)"
                                :key="action.type"
                                @click="handleAction(action.type, row)"
                                :disabled="action.disabled"
                                class="px-2 py-1 rounded shadow flex items-center justify-center transition hover:brightness-90"
                                :class="{
                                    'bg-blue-500 text-white': action.color === 'blue',
                                    'bg-gray-300 text-black': action.color === 'gray',
                                }"
                            >
                                <font-awesome-icon :icon="['far', action.icon]" class="text-md" />
                            </button>
                        </div>
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
import { usePage, router } from '@inertiajs/vue3'
import { getRowActions, getSuratUndanganAction } from '@/utils/rowAction'
import { ref } from 'vue'

const goToReview = () => {
  router.visit('/wadir/review')
}
const { props } = usePage()
const suratTugas = props.suratTugas ?? {
  data: [],
  meta: {},
  links: []
};


const stats = props.stats ?? {}

const filters = ref({
    ...props.filters,
    status: props.filters?.status ?? ''
})

const page = usePage()
const user = page.props.auth?.user ?? {}
const dashboardRoute = `${user.role}.persetujuan`

const columns = [
  { key: 'user_id', label: 'Pengusul' },
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'tanggal_kembali', label: 'Tanggal Kembali' },
  { key: 'sumber_dana', label: 'Pembiayaan' },
  { key: 'status_surat', label: 'Status' },
  { key: 'path_file_surat_usulan', label: 'Surat Undangan' },
  { key: 'action', label: 'Aksi' },
]

const statusOptions = [
    { label: "Menunggu Review", value: "pending" },
    { label: "Disetujui", value: "approved" },
    { label: "Ditolak", value: "rejected" },
]

const handleView = (row) => {
  router.get(route(`${user.role}.persetujuan.show`, row.surat_tugas_id))
}

const handleAction = (type, row) => {
    switch (type) {
        case 'download':
            router.visit(`/surat/download/${row.id}`)
            break
        case 'review':
            router.get(route(`${user.role}.persetujuan.show`, row.surat_tugas_id))  
            break
    }
}
</script>

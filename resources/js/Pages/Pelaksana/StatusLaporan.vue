<template>
  <AppLayout>
    <div class="bg-white w-full max-w-7xl mx-auto rounded-md shadow">
      <HeaderPage />

      <!-- TITLE -->
      <div class="p-8 border-b">
        <h1 class="text-3xl font-bold mb-4">Status Laporan</h1>
        <p class="text-gray-600">
          Status pertanggungjawaban perjalanan dinas setelah kegiatan selesai.
        </p>
      </div>

      <div class="p-8">
        <div class="overflow-x-auto">

          <DataTable
            :columns="columns"
            :data="suratTugas.data"
            :meta="suratTugas.meta"
            :links="suratTugas.links"
            :filters="filters"
            route-name="pelaksana.status-laporan"
            @update:filters="Object.assign(filters, $event)"
            @changePage="(page) =>
              router.get(route('pelaksana.status-laporan'), { ...filters }, {
                preserveState: true,
                replace: true
              })
            "
          >

            <!-- STATUS LAPORAN -->
            <template #status_laporan="{ row }">
              <StatusBadges :status="row.status_laporan" type="laporan" />
            </template>

            <!-- ACTIONS -->
            <template #action="{ row }">
              <div class="flex gap-2">
                <button
                  v-for="action in getRowActions(row, currentUser.role, 'laporan')"
                  :key="action.type"
                  @click="handleAction(action.type, row)"
                  class="px-2 py-1 rounded shadow transition hover:brightness-90 flex items-center justify-center"
                  :class="{
                    'bg-blue-500 text-white': action.color === 'blue',
                    'bg-green-500 text-white': action.color === 'green',
                    'bg-red-500 text-white': action.color === 'red',
                    'bg-yellow-400 text-black': action.color === 'yellow',
                    'bg-purple-500 text-white': action.color === 'purple',
                  }"
                >
                  <font-awesome-icon :icon="['far', action.icon]" />
                </button>
              </div>
            </template>

          </DataTable>
        </div>
      </div>

      <Modal :show="isUploadModalOpen" @close="isUploadModalOpen = false" />
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import Modal from './Partials/Modal.vue'
import { usePage, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { getRowActions } from '@/utils/rowAction'

/* MODAL */
const isUploadModalOpen = ref(false)
const openUploadModal = (row) => {
  console.log('Upload modal opened for:', row)
  isUploadModalOpen.value = true
}

/* PAGE PROPS */
const { props } = usePage()
const currentUser = props.auth.user
const suratTugas = props.suratTugas
const filters = ref(props.filters)

/* TABLE COLUMNS */
const columns = [
  { key: 'nomor_surat_tugas_resmi', label: 'No Surat Resmi' },
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Pelaksanaan' },
  { key: 'status_laporan', label: 'Status Laporan' },
  { key: 'action', label: 'Aksi' },
]

/* ACTION HANDLER */
const handleAction = (type, row) => {
  switch (type) {
    case 'upload':
      openUploadModal(row)
      break
    case 'view':
      router.get(route('pelaksana.laporan.view', row.id))
      break
    case 'download':
      router.get(route('pelaksana.laporan.download', row.id))
      break
    case 'fix':
      router.get(route('pelaksana.laporan.revision', row.id))
      break
  }
}
</script>

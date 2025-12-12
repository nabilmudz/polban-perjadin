<template>
  <AppLayout>
    <div class="bg-white w-full max-w-7xl mx-auto rounded-md shadow">
      <HeaderPage />

      <!-- TITLE UPDATED -->
      <div class="p-8 border-b">
        <h1 class="text-3xl font-bold mb-4">Perjalanan Dinas</h1>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"></div>
      </div>

      <div class="p-8">
        <div class="overflow-x-auto">

          <DataTable
            :columns="columns"
            :data="suratTugas.data"
            :meta="suratTugas.meta"
            :links="suratTugas.links"
            :enable-date="false"
            :filters="filters"
            route-name="pelaksana.daftar-laporan"
            @update:filters="Object.assign(filters, $event)"
            @changePage="(page) =>
              router.get(route('pelaksana.dashboard'), { ...filters }, {
                preserveState: true,
                replace: true
              })
            "
          >

            <!-- STATUS BADGE -->
            <template #status_surat="{ row }">
              <!-- NEW Updated Status Badges -->
              <StatusBadges :status="row.status_surat" />
            </template>

            <!-- ACTION BUTTONS -->
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

      <Modal
        :show="isUploadModalOpen"
        @close="isUploadModalOpen = false"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { usePage, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { getRowActions } from '@/utils/rowAction'
import Modal from './Partials/Modal.vue'

/* =========================
   MODAL UPLOAD
========================= */
const isUploadModalOpen = ref(false)

const openUploadModal = (row) => {
  console.log('Open modal for row:', row)
  isUploadModalOpen.value = true
}

/* =========================
   PAGE PROPS
========================= */
const { props } = usePage()
const currentUser = props.auth.user
const suratTugas = props.suratTugas
const filters = ref(props.filters)

/* =========================
   COLUMNS UPDATE
========================= */
const columns = [
  { key: 'user_id', label: 'Pengusul' },
  { key: 'diusulkan_kepada', label: 'Wadir yang Memaraf' },
  { key: 'nomor_surat_tugas_resmi', label: 'No Surat Resmi' },
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Pelaksanaan' },
  { key: 'sumber_dana', label: 'Sumber Dana' },

  /* STATUS BARU YANG MENCERMINKAN FLOW PERJALANAN DINAS */
  { key: 'status_surat', label: 'Status' },

  { key: 'action', label: 'Aksi' },
]

/* =========================
   FILTERING (optional)
========================= */
const search = ref('')
const statusFilter = ref('')

const filteredLaporan = computed(() => {
  const q = search.value.toLowerCase()

  return suratTugas.value.filter(l =>
    (!statusFilter.value || l.status_surat === statusFilter.value) &&
    (
      l.perihal_tugas.toLowerCase().includes(q) ||
      l.user_id.toLowerCase().includes(q)
    )
  )
})

</script>

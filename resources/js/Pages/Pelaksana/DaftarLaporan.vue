<template>
  <AppLayout>
    <div class="bg-white w-full max-w-7xl mx-auto rounded-md shadow">
      <HeaderPage />

      <div class="p-8 border-b">
        <h1 class="text-3xl font-bold mb-2">Daftar Laporan</h1>
        <p class="text-gray-600">
          Monitoring proses Surat Tugas sampai terbit. Upload bukti dilakukan di menu Status Laporan.
        </p>

        <div class="mt-4 flex flex-wrap gap-2">
          <button
            class="px-3 py-1 rounded border"
            :class="filters.scope === 'all' ? 'bg-primary-default text-white' : 'bg-white'"
            @click="setScope('all')"
          >
            Semua ({{ scopeCounts.all ?? 0 }})
          </button>

          <button
            class="px-3 py-1 rounded border"
            :class="filters.scope === 'processing' ? 'bg-primary-default text-white' : 'bg-white'"
            @click="setScope('processing')"
          >
            Diproses ({{ scopeCounts.processing ?? 0 }})
          </button>

          <button
            class="px-3 py-1 rounded border"
            :class="filters.scope === 'published' ? 'bg-primary-default text-white' : 'bg-white'"
            @click="setScope('published')"
          >
            Published ({{ scopeCounts.published ?? 0 }})
          </button>
        </div>
      </div>

      <div class="p-8">
        <DataTable
          :columns="columns"
          :data="suratTugas.data"
          :meta="suratTugas.meta"
          :links="suratTugas.links"
          :filters="filters"
          route-name="pelaksana.daftarlaporan"
          @update:filters="Object.assign(filters, $event)"
          @changePage="(page) => (filters.page = page)"
        >
          <template #status_surat="{ row }">
            <StatusBadges :status="row.status_surat" type="surat" />
          </template>
          <template #action="{ row }">
            <div class="flex gap-2">
              <button
                class="px-2 py-1 rounded shadow flex items-center justify-center bg-blue-500 text-white hover:brightness-90"
                title="Lihat"
                @click="handleView(row)"
              >
                <font-awesome-icon :icon="['far', 'eye']" class="text-md" />
              </button>
            </div>
          </template>

        </DataTable>
      </div>
    </div>
    <ModalLaporan :show="showViewModal" @close="showViewModal = false">
      <LaporanSurat v-if="selectedData" :surat="selectedData" />
    </ModalLaporan>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { usePage, router } from '@inertiajs/vue3'
import { computed, reactive, watch, ref } from 'vue'
import debounce from 'lodash.debounce'
import ModalLaporan from '@/Components/ModalLaporan.vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'

const page = usePage()
const propsSafe = computed(() => page.props?.value ?? page.props ?? {})
const showViewModal = ref(false)
const selectedData = ref(null)

const handleView = (row) => {
  selectedData.value = row
  showViewModal.value = true
}

const suratTugas = computed(() => propsSafe.value.suratTugas ?? { data: [], meta: {}, links: {} })
const scopeCounts = computed(() => propsSafe.value.scopeCounts ?? {})

const filters = reactive({
  search: propsSafe.value.filters?.search ?? '',
  from: propsSafe.value.filters?.from ?? '',
  to: propsSafe.value.filters?.to ?? '',
  page: propsSafe.value.filters?.page ?? 1,
  range: propsSafe.value.filters?.range ?? '',
  scope: propsSafe.value.filters?.scope ?? 'processing',
})

const fetchData = debounce(() => {
  router.get(route('pelaksana.daftarlaporan'), { ...filters }, {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  })
}, 300)

watch(filters, fetchData, { deep: true })

const setScope = (s) => {
  filters.scope = s
  filters.page = 1
}

const columns = [
  { key: 'nomor_surat_tugas_resmi', label: 'No Surat Resmi' },
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'no_usulan_surat', label: 'Nomor Surat Usulan' },
  { key: 'status_surat', label: 'Status' },
  { key: 'action', label: 'Aksi', sortable: false, fixedWidth: '180px' },
]

const toStorageUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  if (path.startsWith('/storage/')) return path
  if (path.startsWith('/')) return path
  return `/storage/${path}`
}

const openFile = (path) => {
  const url = toStorageUrl(path)
  if (!url) return
  window.open(url, '_blank')
}

const getSuratId = (row) => row?.surat_tugas_id ?? row?.id

const canManageUpload = (row) => {
  return ['published', 'awaiting_proof_upload', 'returned_for_correction'].includes(row?.status_surat)
}

const goToLampiran = (row) => {
  const id = getSuratId(row)
  if (!id) return
  router.get(route('pelaksana.lampiran.index', { suratTugas: id }))
}
</script>

<template>
  <Head title="History Surat Tugas" />

  <div class="bg-white w-full h-auto rounded-md shadow">
    <HeaderPage />

    <div class="p-8 border-b">
      <h1 class="text-3xl font-bold mb-2">History Surat Tugas</h1>
      <p class="text-gray-600">
        Riwayat pertanggungjawaban (Awaiting Proof, BKU Review, Returned, Completed).
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
          route-name="pelaksana.historypelaksana"
          @update:filters="onUpdateFilters"
          @changePage="onChangePage"
        >
          <template #status_surat="{ row }">
            <StatusBadges :status="row.status_surat" />
          </template>

          <template #action="{ row }">
            <div class="flex gap-2">
              <button
                v-for="action in getRowActions(row, currentUser.role)"
                :key="action.type"
                @click="handleAction(action.type, row)"
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
    <ModalLaporan :show="showViewModal" @close="showViewModal = false">
        <LaporanSurat v-if="selectedData" :surat="selectedData" />
    </ModalLaporan>
</template>

<script setup>
import { Head, usePage, router } from '@inertiajs/vue3'
import { computed, reactive, watch, ref } from 'vue'
import debounce from 'lodash.debounce'
import ModalLaporan from '@/Components/ModalLaporan.vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { getRowActions } from '@/utils/rowAction'
import { applyActiveTemplate } from '@/utils/suratTemplate'

const page = usePage()
const propsSafe = computed(() => page.props?.value ?? page.props ?? {})

const currentUser = computed(() => propsSafe.value.auth?.user ?? {})
const suratTugas = computed(() => propsSafe.value.suratTugas ?? { data: [], meta: {}, links: {} })

const filters = reactive({
  search: propsSafe.value.filters?.search ?? '',
  from: propsSafe.value.filters?.from ?? '',
  to: propsSafe.value.filters?.to ?? '',
  page: propsSafe.value.filters?.page ?? 1,
  range: propsSafe.value.filters?.range ?? '',
})
const activeTemplate = computed(() => propsSafe.value.activeTemplateSurat ?? null)

const showViewModal = ref(false)
const selectedData = ref(null)

const openViewModal = (row) => {
  selectedData.value = activeTemplate.value
    ? applyActiveTemplate(row, activeTemplate.value)
    : row

  showViewModal.value = true
}

const fetchData = debounce(() => {
  router.get(route('pelaksana.historypelaksana'), { ...filters }, {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  })
}, 300)

watch(filters, fetchData, { deep: true })

const onUpdateFilters = (newFilters) => Object.assign(filters, newFilters)
const onChangePage = (pageNumber) => { filters.page = pageNumber }

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'no_usulan_surat', label: 'Nomor Surat Usulan' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'total_dana', label: 'Total Dana' },
  { key: 'status_surat', label: 'Status' },
  { key: 'action', label: 'Aksi', sortable: false, fixedWidth: '140px' },
]

function handleAction(type, row) {
  switch (type) {
    case 'view':
      openViewModal(row)
      break

    case 'download':
      window.open(route('pelaksana.surat.download', row.surat_tugas_id ?? row.id), '_blank')
      break

    default:
      console.warn('action not implemented:', type)
  }
}

</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
}
</script>

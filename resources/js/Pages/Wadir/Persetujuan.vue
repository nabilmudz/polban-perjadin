<template>
  <AppLayout>
    <Head title="Persetujuan" />

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
          :route-name="dashboardRoute"
          @update:filters="onUpdateFilters"
          @changePage="onChangePage"
        >
          <template #status_surat="{ row }">
            <StatusBadges :status="row.status_surat" />
          </template>

          <template #path_file_surat_usulan="{ row }">
            <button
              v-if="row.path_file_surat_usulan"
              @click="openSuratUndangan(row)"
              class="px-3 py-1 rounded bg-yellow-400 text-black shadow hover:brightness-95 flex items-center gap-2 justify-center"
              title="Lihat Surat Undangan"
            >
              <font-awesome-icon :icon="['far', 'file-lines']" />
            </button>
            <span v-else class="text-gray-400">-</span>
          </template>

          <template #action="{ row }">
            <div class="flex gap-2">
              <button
                v-for="action in getRowActions(row, user.role)"
                :key="action.type"
                :title="action.type"
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

    <FilePreviewModal
      :show="preview.state.show"
      :file="preview.state.file"
      :loading="preview.state.loading"
      :error="preview.state.error"
      title="Surat Undangan"
      @close="preview.close"
    />
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import FilePreviewModal from '@/Components/FilePreviewModal.vue'

import { Head, usePage, router } from '@inertiajs/vue3'
import { reactive, watch, computed } from 'vue'
import debounce from 'lodash.debounce'

import { getRowActions } from '@/utils/rowAction'
import { useFilePreview } from '@/utils/useFilePreviews.js'

const page = usePage()
const user = page.props.auth?.user ?? {}
const dashboardRoute = `${user.role}.persetujuan`

const suratTugas = computed(() => page.props.suratTugas ?? {
  data: [],
  meta: {},
  links: {},
})

const filters = reactive({
  search: page.props.filters?.search ?? '',
  status: page.props.filters?.status ?? '',
  from: page.props.filters?.from ?? '',
  to: page.props.filters?.to ?? '',
  page: page.props.filters?.page ?? 1,
  range: page.props.filters?.range ?? '',
})

const columns = [
  { key: 'nama_pengusul', label: 'Pengusul' },
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'sumber_dana', label: 'Pembiayaan' },
  { key: 'total_dana', label: 'Total Dana' },
  { key: 'path_file_surat_usulan', label: 'Surat Undangan', sortable: false, fixedWidth: '140px' },
  { key: 'action', label: 'Aksi', sortable: false, fixedWidth: '180px' },
]

watch(
  filters,
  debounce(() => {
    router.get(route(dashboardRoute), { ...filters }, {
      preserveState: true,
      replace: true,
    })
  }, 300),
  { deep: true }
)

const onUpdateFilters = (newFilters) => {
  Object.assign(filters, newFilters)
}

const onChangePage = (pageNumber) => {
  filters.page = pageNumber
}

const preview = useFilePreview()

const openSuratUndangan = (row) => {
  const path = row?.path_file_surat_usulan
  if (!path) return

  preview.open({
    url: path,
    name: 'Surat Undangan',
  })
}

const getSuratTugasId = (row) => row?.surat_tugas_id ?? row?.id

const handleAction = (type, row) => {
  const id = getSuratTugasId(row)
  if (!id) return console.error('Missing surat_tugas_id', row)

  switch (type) {
    case 'review':
    case 'view':
      router.get(route(`${user.role}.persetujuan.show`, id))
      break

    case 'approve':
      router.post(route(`${user.role}.persetujuan.approve`, id), {}, { preserveScroll: true })
      break

    case 'reject':
      router.post(route(`${user.role}.persetujuan.reject`, id), {}, { preserveScroll: true })
      break
  }
}
</script>

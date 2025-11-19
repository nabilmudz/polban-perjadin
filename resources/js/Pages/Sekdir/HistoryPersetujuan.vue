<template>
  <AppLayout>
    <div class="bg-white w-full rounded-md shadow">
      <HeaderPage />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-6">History Persetujuan</h1>

        <DataTable
          :columns="columns"
          :data="surat.data"
          :meta="surat.meta"
          :links="surat.links"
          :filters="filters"
          route-name="sekdir.history"
          @update:filters="Object.assign(filters, $event)"
        >

          <template #aksi="{ row }">
            <div class="flex gap-2">
              <button
                v-if="row.file_final"
                :title="'View'"
                @click="openFile(row.file_final)"
                class="px-2 py-1 rounded shadow flex items-center justify-center bg-blue-500 text-white hover:brightness-90"
              >
                <font-awesome-icon :icon="['far', 'eye']" class="text-md" />
              </button>

              <a
                v-if="row.file_final"
                :href="fileUrl(row.file_final)"
                :download
                class="px-2 py-1 rounded shadow flex items-center justify-center bg-green-500 text-white hover:brightness-90"
                title="Download"
              >
                <font-awesome-icon :icon="['far', 'circle-down']" class="text-md" />
              </a>
            </div>
          </template>

          <template #status_surat="{ row }">
            <StatusBadges :status="row.status_surat" />
          </template>

        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import { usePage, router } from '@inertiajs/vue3'
import { reactive, watch } from 'vue'
import debounce from 'lodash.debounce'

const { props } = usePage()
const surat = props.surat

const filters = reactive({
  search: props.filters?.search || "",
  status: props.filters?.status || "",
})

watch(
  filters,
  debounce(() => {
    router.get(route('sekdir.history'), filters, {
      preserveState: true,
      replace: true,
    })
  }, 300),
  { deep: true }
)

const fileUrl = (path) => `/storage/${path}`

const openFile = (path) => {
  window.open(`/storage/${path}`, '_blank')
}

const columns = [
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'nomor_surat_pengantar', label: 'Nomor Surat Pengantar' },
  { key: 'nomor_surat_tugas', label: 'Nomor Surat Tugas' },
  { key: 'tanggal_diterbitkan', label: 'Tanggal Diterbitkan' },
  { key: 'diusulkan_kepada', label: 'Diusulkan Kepada' },
  { key: 'status_surat', label: 'Status'},
  { key: 'aksi', label: 'Aksi' },
]
</script>
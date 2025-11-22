<template>
  <Head title="Pegawai" />
  <div class="bg-white w-full h-full rounded-md">
    <HeaderPage/>
    <div class="p-8">
      <h1 class="text-3xl font-bold">Daftar Pegawai</h1>
    </div>

    <DataTable
      :columns="columns"
      :data="pegawai.data"
      :meta="pegawai.meta"
      :links="pegawai.links"
      :filters="filters"
      route-name="admin.pegawai"
      :status-options="statusOptionsArray"
      :enable-search="true"
      :enable-status="true"
      :enable-date="false"
      @update:filters="Object.assign(filters, $event)"
    >
      <template #status="{ row }">
        <div class="flex items-center gap-2">
          <label class="relative inline-flex items-center cursor-pointer">
            <input
              type="checkbox"
              class="sr-only peer"
              :checked="row.status == 1"
              @change="toggleStatus(row)"
            />
            <div
              class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-500
                  peer-focus:ring-4 peer-focus:ring-green-300
                  after:content-[''] after:absolute after:top-0.5 after:left-[2px] 
                  after:bg-white after:border after:border-gray-300 after:rounded-full
                  after:h-5 after:w-5 after:transition-all
                  peer-checked:after:translate-x-full peer-checked:after:border-white"
            ></div>
          </label>
        </div>
      </template>

      <template #filters-extra>
        <button class="bg-primary-default text-white px-3 py-2 rounded" @click="showAddModal = true">
          <font-awesome-icon :icon="['far', 'file-lines']" class="text-md" />
          Tambah Pegawai
        </button>
        <button class="bg-green-600 text-white px-3 py-2 rounded" @click="showUploadModal = true">
          <font-awesome-icon :icon="['far', 'file-lines']" class="text-md" />
          Upload Excel
        </button>
      </template>
    </DataTable>

    <FormPegawai
      v-if="showAddModal"
      :form="form"
      :status-options="statusOptionsArray"
      @close="showAddModal = false"
      @submit="submitPegawai"
    />

    <FormExcelPegawai 
      v-if="showUploadModal" 
      @close="showUploadModal=false" 
      @success="showUploadModal=false; reloadPage()"
      @dupes="excelErrors = $event; showUploadModal = false"
    />

    <ExcelErrorModal
      :show="excelErrors.length > 0"
      :errors="excelErrors"
      @close="excelErrors = []"
    />

  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { usePage, router, Head } from '@inertiajs/vue3'
import debounce from 'lodash.debounce'

import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import FormPegawai from '@/Pages/Admin/Partials/FormPegawai.vue'
import FormExcelPegawai from '@/Pages/Admin/Partials/FormExcelPegawai.vue'
import ExcelErrorModal from '@/Pages/Admin/Partials/ExcelErrorModal.vue'
import { statusToggle } from '@/utils/statusToggle'

const page = usePage()
const pegawai = computed(() => page.props.pegawai)  
const excelErrors = ref(page.props.excelDuplicates || [])

const showAddModal = ref(false)
const showUploadModal = ref(false)

const form = reactive({
  nama: '',
  nip: '',
  pangkat: '',
  golongan: '',
  jabatan: '',
  status: 1,
})

const filters = reactive({
  search: page.props.filters?.search || '',
  status: page.props.filters?.status || '',
  from: page.props.filters?.from || '',
  to: page.props.filters?.to || '',
  page: page.props.filters?.page || 1,
})

watch(
  filters,
  debounce(() => {
    router.get(route('admin.pegawai'), { ...filters }, {
      preserveState: true,
      replace: true,
    })
  }, 300),
  { deep: true }
)

const columns = [
  { key: 'nama', label: 'Nama' },
  { key: 'nip', label: 'NIP' },
  { key: 'pangkat', label: 'Pangkat' },
  { key: 'golongan', label: 'Golongan' },
  { key: 'jabatan', label: 'Jabatan' },
  { key: 'status', label: 'Status' },
]

const statusOptionsArray = Object.entries(statusToggle).map(([key, val]) => ({
  value: Number(key),
  label: val.label,
}))

const toggleStatus = async (row) => {
  try {
    await router.patch(route('pegawai.toggleStatus', row.id), {}, { 
      preserveState: true, 
      only: ['pegawai']
    })
  } catch (e) {
    console.error(e)
    alert('Failed to update status')
  }
}

const submitPegawai = async () => {
  try {
    await router.post(route('pegawai.store'), { ...form }, { 
      preserveScroll: true,
      preserveState: true,
    })
    showAddModal.value = false
    Object.keys(form).forEach(key => form[key] = key === 'status' ? 1 : '')
    reloadPage()
  } catch (err) {
    console.error(err)
    alert('Failed to add pegawai')
  }
}

const closeModal = () => {
  excelErrors.value = []
}

const reloadPage = () => router.reload({ only: ['pegawai', 'excelDuplicates'] })
</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
}
</script>

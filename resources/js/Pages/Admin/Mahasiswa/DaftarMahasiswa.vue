<template>
  <Head title="Mahasiswa" />
  <div class="bg-white w-full h-full rounded-md">
    <HeaderPage/>
    <div class="p-8">
      <h1 class="text-3xl font-bold">Daftar Mahasiswa</h1>
    </div>

    <DataTable
      :columns="columns"
      :data="mahasiswa.data"
      :meta="mahasiswa.meta"
      :links="mahasiswa.links"
      :filters="filters"
      route-name="admin.mahasiswa.index"
      :enable-search="true"
      @update:filters="Object.assign(filters, $event)"
    >
      <template #filters-extra>
        <button class="bg-primary-default text-white px-3 py-2 rounded" @click="showAddModal = true">
          <font-awesome-icon :icon="['far', 'file-lines']" class="text-md" />
          Tambah Mahasiswa
        </button>
        <button class="bg-green-600 text-white px-3 py-2 rounded" @click="showUploadModal = true">
          <font-awesome-icon :icon="['far', 'file-lines']" class="text-md" />
          Upload Excel
        </button>
      </template>
        <template #actions="{ row }">
        <div class="flex gap-2">
            <button
            v-for="action in getRowActions(row, auth.user.role)"
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

    <FormMahasiswa
      v-if="showAddModal"
      :form="form"
      @close="showAddModal = false"
      @submit="submitMahasiswa"
    />

    <FormExcelMahasiswa
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
import FormMahasiswa from '@/Pages/Admin/Mahasiswa/Partials/FormMahasiswa.vue'
import FormExcelMahasiswa from '@/Pages/Admin/Mahasiswa/Partials/FormExcelMahasiswa.vue'
import ExcelErrorModal from '@/Pages/Admin/Mahasiswa/Partials/ExcelErrorModal.vue'
import { getRowActions } from '@/utils/rowAction'

const page = usePage()
const { auth } = page.props
const mahasiswa = computed(() => page.props.mahasiswa)
const excelErrors = ref(page.props.import_errors || [])

const showAddModal = ref(false)
const showUploadModal = ref(false)

const form = reactive({
  id: '',
  nama: '',
  nim: '',
  jurusan: '',
  prodi: '',
})

const filters = reactive({
  search: page.props.filters?.search || '',
  page: page.props.filters?.page || 1,
})

watch(
  filters,
  debounce(() => {
    router.get(route('admin.mahasiswa.index'), { ...filters }, {
      preserveState: true,
      replace: true,
    })
  }, 300),
  { deep: true }
)

const columns = [
  { key: 'nama', label: 'Nama' },
  { key: 'nim', label: 'NIM' },
  { key: 'jurusan', label: 'Jurusan' },
  { key: 'prodi', label: 'Prodi' },
  { key: 'actions', label: 'Aksi' },
]

const submitMahasiswa = async () => {
  try {
    const url = form.id ? route('admin.mahasiswa.update', form.id) : route('admin.mahasiswa.store')
    const method = form.id ? 'put' : 'post'

    await router[method](url, { ...form }, {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        showAddModal.value = false
        Object.keys(form).forEach(key => form[key] = '')
        reloadPage()
      }
    })
  } catch (err) {
    console.error(err)
    alert('Failed to add or update mahasiswa')
  }
}

const handleAction = (type, row) => {
    switch(type) {
        case 'edit':
            editMahasiswa(row)
            break
        case 'delete':
            deleteMahasiswa(row)
            break
    }
}

const editMahasiswa = (row) => {
    form.id = row.id
    form.nama = row.nama
    form.nim = row.nim
    form.jurusan = row.jurusan
    form.prodi = row.prodi
    showAddModal.value = true
}

const deleteMahasiswa = (row) => {
    if(confirm('Are you sure you want to delete this mahasiswa?')) {
        router.delete(route('admin.mahasiswa.destroy', row.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                reloadPage()
            }
        })
    }
}

const closeModal = () => {
  excelErrors.value = []
}

const reloadPage = () => router.reload({ only: ['mahasiswa', 'import_errors'] })

</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
}
</script>

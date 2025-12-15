<template>
  <Head title="Template Surat" />
  <div class="bg-white w-full h-full rounded-md">
    <HeaderPage />

    <div class="p-8">
      <h1 class="text-3xl font-bold">Template Surat Tugas</h1>
    </div>

    <DataTable
      :columns="columns"
      :data="templates.data"
      :meta="templates.meta"
      :links="templates.links"
      :filters="filters"
      route-name="admin.template-surat"
      :enable-search="false"
      :enable-status="false"
      :enable-date="false"
      @update:filters="Object.assign(filters, $event)"
    >
      <!-- STATUS -->
      <template #status="{ row }">
        <label class="relative inline-flex cursor-pointer">
          <input
            type="checkbox"
            class="sr-only peer"
            :checked="row.status == 1"
            @change="toggleStatus(row)"
          />
          <div
            class="w-11 h-6 bg-gray-200 rounded-full peer-checked:bg-green-500
              after:content-[''] after:absolute after:top-0.5 after:left-[2px]
              after:bg-white after:rounded-full after:h-5 after:w-5
              after:transition-all peer-checked:after:translate-x-full"
          />
        </label>
      </template>

      <!-- AKSI -->
      <template #actions="{ row }">
        <button
          class="bg-yellow-400 text-black px-2 py-1 rounded"
          @click="editTemplate(row)"
        >
          <font-awesome-icon :icon="['far', 'pen-to-square']" />
        </button>
      </template>

      <!-- BUTTON ATAS -->
      <template #filters-extra>
        <button
          class="bg-primary-default text-white px-3 py-2 rounded"
          @click="openCreate"
        >
          <font-awesome-icon :icon="['far', 'file-lines']" />
          Tambah Template
        </button>
      </template>
    </DataTable>

    <!-- MODAL -->
    <FormTemplateSurat
      v-if="showModal"
      :form="form"
      @close="closeModal"
      @save="submitTemplate"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'

import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import FormTemplateSurat from './Partials/FormTemplateSurat.vue'

const page = usePage()
const templates = computed(() => page.props.templates)

const showModal = ref(false)

const form = reactive({
  id: null,
  nama_kementerian: '',
  nama_direktur: '',
  nip_direktur: '',
  status: 1,
})

const filters = reactive({
  page: page.props.filters?.page || 1,
})

const columns = [
  { key: 'nama_kementerian', label: 'Nama Kementerian' },
  { key: 'nama_direktur', label: 'Nama Direktur' },
  { key: 'nip_direktur', label: 'NIP Direktur' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Aksi' },
]

/* ===== ACTIONS ===== */

const openCreate = () => {
  resetForm()
  showModal.value = true
}

const editTemplate = (row) => {
  Object.assign(form, row)
  showModal.value = true
}

const submitTemplate = async () => {
  const url = form.id
    ? route('admin.template-surat.update', form.id)
    : route('admin.template-surat.store')

  await router.post(
    url,
    {
      ...form,
      _method: form.id ? 'PUT' : 'POST',
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        closeModal()
        reloadPage()
      },
    }
  )
}

const toggleStatus = async (row) => {
  await router.patch(
    route('admin.template-surat.toggle-status', row.id),
    {},
    { preserveState: true }
  )
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const resetForm = () => {
  Object.assign(form, {
    id: null,
    nama_kementerian: '',
    nama_direktur: '',
    nip_direktur: '',
    status: 1,
  })
}

const reloadPage = () =>
  router.reload({ only: ['templates'] })
</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
}
</script>

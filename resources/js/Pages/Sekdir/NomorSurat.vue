<template>
  <Head title="Nomor Surat" />

  <AppLayout>
    <div class="bg-white w-full rounded-md shadow">
      <HeaderPage />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-6">Nomor Surat</h1>

        <DataTable
          :columns="columns"
          :data="surat.data"
          :meta="surat.meta"
          :links="surat.links"
          :filters="filters"
          route-name="sekdir.nomorsurat"
          @update:filters="onUpdateFilters"
          @changePage="onChangePage"
        >
          <template #status_surat="{ row }">
            <StatusBadges :status="row.status_surat" />
          </template>

          <template #aksi="{ row }">
            <button
              class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm"
              @click="gotoReview(row.id)"
            >
              Review & Nomor
            </button>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, usePage, router } from '@inertiajs/vue3'
import { reactive, watch, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import debounce from 'lodash.debounce'

const page = usePage()

const surat = computed(() => page.props.surat ?? {
  data: [],
  meta: {},
  links: {},
})

const filters = reactive({
  search: page.props.filters?.search ?? '',
  from: page.props.filters?.from ?? '',
  to: page.props.filters?.to ?? '',
  range: page.props.filters?.range ?? '',
})

const fetchData = debounce(() => {
  router.get(route('sekdir.nomorsurat'), filters, {
    preserveState: true,
    replace: true,
  })
}, 300)

watch(filters, fetchData, { deep: true })

const onUpdateFilters = (newFilters) => {
  Object.assign(filters, newFilters)
}

const onChangePage = (pageNumber) => {
  router.get(
    route('sekdir.nomorsurat'),
    { ...filters, page: pageNumber },
    { preserveState: true, replace: true },
  )
}

const gotoReview = (id) => {
  router.get(route('sekdir.nomorsurat.review', id))
}

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'no_usulan_surat', label: 'Nomor Surat Usulan' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'nominal_dana', label: 'Total Dana' },
  { key: 'status_surat', label: 'Status' },
  { key: 'aksi', label: 'Aksi' },
]
</script>

<template>
        <div class=" bg-white w-full h-full rounded-md">
          <HeaderPage/>
          <div class="p-8">
            <h1 class="text-3xl font-bold mb-4">Daftar Pengajuan</h1>
          </div>
          
          <DataTable
            :columns="columns"
            :data="suratTugas.data"
            :meta="suratTugas.meta"
            :links="suratTugas.links"
            :filters="filters"
            route-name="pengusul.pengajuan"
            @update:filters="Object.assign(filters, $event)"
            @changePage="(page) =>
              router.get(route('pengusul.pengajuan'), { ...filters, page }, {
                preserveState: true,
                replace: true
              })
            "
          >
            <template #status_surat="{ row }">
              <StatusBadges :status="row.status_surat" />
            </template>
          </DataTable>
        </div>
</template>

<script setup>
import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { usePage, router } from '@inertiajs/vue3'
import { reactive, watch } from 'vue'
import debounce from 'lodash.debounce'
import { computed } from 'vue'

const page = usePage()
const suratTugas = computed(() => page.props.suratTugas)

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
    router.get(route('pengusul.pengajuan'), { ...filters, page }, {
      preserveState: true,
      replace: true,
    })
  }, 300),
  { deep: true }
)

const columns = [
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'no_usulan_surat', label: 'Nomor Surat Usulan' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'surat_undangan', label: 'Surat Undangan' },
  { key: 'status_surat', label: 'Status' },
]
</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
}
</script>

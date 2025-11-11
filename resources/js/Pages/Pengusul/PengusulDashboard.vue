<template>
  <div class="bg-white w-full h-full rounded-md shadow">
    <HeaderPage />
    <div class="p-8">
      <h1 class="text-3xl font-bold mb-4">Dashboard</h1>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        <StatCard title="Total Ulasan" icon="file" :count="10" />
        <StatCard title="Laporan Selesai" icon="square-check" :count="3" color="green" />
        <StatCard title="Belum Selesai" icon="folder-closed" :count="5" color="purple" />
        <StatCard title="Bertugas" icon="user" :count="4" color="yellow" />
        <StatCard title="Dikembalikan" icon="circle-left" :count="4" color="red" />
      </div>
    </div>

    <DataTable
      :columns="columns"
      :data="suratTugas.data"
      :meta="suratTugas.meta"
      :links="suratTugas.links"
      :filters="filters"
      route-name="pengusul.dashboard"
      @update:filters="Object.assign(filters, $event)"
      @changePage="(page) =>
        router.get(route('pengusul.dashboard'), { ...filters, page }, {
          preserveState: true,
          replace: true,
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
import StatCard from '@/Components/StatCard.vue'
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
})

watch(
  filters,
  debounce(() => {
    router.get(route('pengusul.dashboard'), filters, {
      preserveState: true,
      replace: true,
    })
  }, 300),
  { deep: true }
)

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'status_surat', label: 'Status' },
]
</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
}
</script>

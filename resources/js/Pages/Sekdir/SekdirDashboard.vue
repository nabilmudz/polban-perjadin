<template>
  <AppLayout>
    <div class="bg-white w-full rounded-md shadow">
      <HeaderPage />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-6">Dashboard</h1>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-8">
          <StatCard title="Total Usulan" icon="file" :count="summary.total_usulan" color="blue" />
          <StatCard title="Usulan Baru" icon="folder-closed" :count="summary.usulan_baru" color="green" />
          <StatCard title="Bertugas" icon="user" :count="summary.bertugas" color="purple" />
          <StatCard title="Selesai" icon="square-check" :count="summary.selesai" color="yellow" />
        </div>

        <div class="mt-10">
          <h2 class="text-xl font-semibold mb-4">
            Detail Pengajuan
          </h2>

          <DataTable
            :columns="columns"
            :data="suratTugas.data"
            :meta="suratTugas.meta"
            :links="suratTugas.links"
            :filters="filters"
            route-name="sekdir.dashboard"
            @update:filters="Object.assign(filters, $event)"
            @changePage="(page) =>
              router.get(route('sekdir.dashboard'), { ...filters, page }, {
                preserveState: true,
                replace: true
              })
            "
          >
            <template #status="{ row }">
              <StatusBadges :status="row.status_surat" />
            </template>
            
          </DataTable>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import StatCard from '@/Components/StatCard.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { usePage, router } from '@inertiajs/vue3'
import { reactive, watch, computed } from 'vue'
import debounce from 'lodash.debounce'

const page = usePage()
const summary = page.props.summary

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
    router.get(route('sekdir.dashboard'), { ...filters }, {
      preserveState: true,
      replace: true,
    })
  }, 300),
  { deep: true }
)

const columns = [
  { key: 'created_at', label: 'Tanggal Pengajuan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'nomor_surat', label: 'Nomor Surat' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'status', label: 'Status' },
]
</script>

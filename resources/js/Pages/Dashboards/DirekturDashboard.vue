<template>
  <AppLayout>
    <div class="bg-none w-full h-full rounded-md shadow">
      <HeaderPage />

      <!-- PAGE TITLE & STAT CARDS -->
      <div class="p-8">
        <h1 class="text-3xl font-bold mb-4">Dashboard</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <StatCard title="Total Ulasan" icon="file" :count="10" />
          <StatCard title="Bertugas" icon="user" :count="4" />
        </div>
      </div>

      <!-- MAIN CONTENT -->
      <div class="px-6 pb-8 space-y-10">
        <DataTable
          :columns="columns"
          :data="suratTugas.data"
          :meta="suratTugas.meta"
          :links="suratTugas.links"
          :filters="filters"
          route-name="direktur.dashboard"
          @update:filters="Object.assign(filters, $event)"
          @changePage="(page) =>
            router.get(route('direktur.dashboard'), { ...filters, page }, {
              preserveState: true,
              replace: true,
            })
          "
        >
          <!-- Status Badge slot -->
          <template #status_surat="{ row }">
            <StatusBadges :status="row.status_surat" />
          </template>

          <!-- Action Button slot -->
          <template #actions="{ row }">
            <!-- <Link
              :href="route('surat-tugas.show', row.surat_tugas_id)"
              class="px-3 py-1 rounded bg-blue-500 text-white hover:bg-blue-600 text-xs flex items-center gap-1"
            >
              <i class="fas fa-eye"></i> View
            </Link> -->
          </template>
        </DataTable>
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
import { Link, usePage, router } from '@inertiajs/vue3'
import { reactive, computed, watch } from 'vue'
import debounce from 'lodash.debounce'

const page = usePage()

const suratTugas = computed(() => page.props.suratTugas)
console.log("Surat Tugas: ", suratTugas);

const filters = reactive({
  search: page.props.filters?.search || '',
  status: page.props.filters?.status || '',
  from: page.props.filters?.from || '',
  to: page.props.filters?.to || '',
})

watch(
  filters,
  debounce(() => {
    router.get(route('direktur.dashboard'), filters, {
      preserveState: true,
      replace: true,
    })
  }, 300),
  { deep: true }
)

const columns = [
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'nomor_surat_tugas_resmi', label: 'Nomor Surat' },
  { key: 'perihal_tugas', label: 'Perihal Tugas' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'status_surat', label: 'Status', slot: 'status_surat' },
  { key: 'actions', label: 'Aksi', slot: 'actions' },
]
</script>

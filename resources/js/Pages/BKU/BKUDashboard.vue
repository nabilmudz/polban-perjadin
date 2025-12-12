<template>
  <Head title="Dashboard" />

  <AppLayout>
    <div class="bg-white w-full rounded-md shadow">
      <HeaderPage />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-4">Dashboard BKU</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <StatCard title="Total Pengusulan" icon="file" :count="stats?.total_pengusulan || 0" />
          <StatCard title="Verifikasi Baru" icon="envelope" :count="stats?.surat_tugas_baru || 0" color="green" />
          <StatCard title="Bertugas" icon="user" :count="stats?.bertugas || 0" color="yellow" />
          <StatCard title="Laporan Pending" icon="circle-exclamation" :count="stats?.laporan_belum_selesai || 0" color="red" />
        </div>
      </div>

      <div class="px-6 pb-8 space-y-10">
          <div class="flex justify-between items-end mb-4">
             <div>
                 <h3 class="text-lg font-bold text-gray-800">Daftar Penugasan</h3>
                 <p class="text-sm text-gray-500">Kelola validasi keuangan surat tugas</p>
             </div>
          </div>
          
          <DataTable
            :columns="columns"
            :data="suratData.data"
            :meta="suratData.meta"
            :links="suratData.links"
            :filters="filters"
            route-name="bku.dashboard"
            @update:filters="handleFilterUpdate"
            @changePage="handlePageChange"
          >
            <!-- 1. No Column -->
            <template #no="{ index }">
                {{ (suratData.meta.from || 1) + index }}
            </template>

            <!-- 2. No Usulan Column -->
            <template #no_usulan_surat="{ row }">
                <span class="font-medium text-gray-700">{{ row.no_usulan_surat }}</span>
            </template>

            <!-- 3. Status Badge using StatusBadges Component -->
            <template #status_surat="{ row }">
               <StatusBadges :status="row.status_surat" />
            </template>

            <!-- 4. Actions Column -->
            <template #actions="{ row }">
            <div class="flex gap-2">
              <button
                v-for="action in getRowActions(row)"
                :key="action.type"
                @click="handleAction(action.type, row)"
                :title="action.type"
                class="px-2 py-1 rounded shadow flex items-center justify-center transition hover:brightness-90"
                :class="{
                  'bg-blue-500 text-white': action.color === 'blue',
                  'bg-green-500 text-white': action.color === 'green',
                }"
              >
                <font-awesome-icon :icon="['far', action.icon]" class="text-md" />
              </button>
            </div>
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
import { reactive, computed, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  stats: Object,
  latestSurat: Object, 
  filters: Object
})

const page = usePage()

const suratData = computed(() => {
    const raw = props.latestSurat || {};
    return {
        data: raw.data || [],
        meta: {
            current_page: raw.current_page || 1,
            last_page: raw.last_page || 1,
            per_page: raw.per_page || 10,
            total: raw.total || 0,
            from: raw.from || 0,
            to: raw.to || 0
        },
        links: raw.links || []
    }
})

const filters = reactive({
  search: props.filters?.search || ''
})

let searchTimeout;

watch(
  () => filters.search,
  (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('bku.dashboard'), { search: value }, { preserveState: true, replace: true })
    }, 300);
  }
)

const handleFilterUpdate = (newFilters) => {
    Object.assign(filters, newFilters)
}

const handlePageChange = (page) => {
    router.get(route('bku.dashboard'), { ...filters, page }, { preserveState: true, replace: true })
}

const getRowActions = (row) => {
    return [
        { type: 'view', icon: 'eye', color: 'blue' }
    ];
}

const handleAction = (type, row) => {
    if (type === 'view') {
        router.get(route('bku.verifikasi', row.id)); 
    }
}

const columns = [
  { key: 'tanggal_pengusulan', label: 'Tanggal Usulan' }, 
  { key: 'no_usulan_surat', label: 'No. Usulan Surat', slot: 'no_usulan_surat' }, 
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'status_surat', label: 'Status', slot: 'status_surat' },
  { key: 'actions', label: 'Aksi', slot: 'actions' }
]
</script>
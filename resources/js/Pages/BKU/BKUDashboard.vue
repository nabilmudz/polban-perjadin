<template>
  <Head title="Dashboard" />

  <AppLayout>
    <div class="bg-white w-full rounded-md shadow overflow-hidden">
      <HeaderPage />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Dashboard BKU</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-5">
            <StatCard title="Total Pengusulan" icon="file" :count="totalPengusulan || 0" />
            <StatCard title="Laporan Selesai" icon="square-check" :count="statusCounts?.completed || 0" color="green" />
            <StatCard title="Belum Selesai" icon="folder-closed" :count="statusCounts?.published || 0" color="purple" />
            <StatCard title="Bertugas" icon="user" :count="statusCounts?.on_duty || 0" color="yellow" />
            <StatCard title="Dikembalikan" icon="circle-left" :count="statusCounts?.revision_requested || 0" color="red" />
        </div>

        <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm mt-6">
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
            :status-options="statusOptions"
            route-name="bku.dashboard"
            @update:filters="Object.assign(filters, $event)"
            @changePage="(page) => router.get(route('bku.dashboard'), { ...filters, page }, { preserveState: true, replace: true })"
          >
            <template #no="{ index }">{{ (suratData.meta.from || 1) + index }}</template>
            <template #no_usulan_surat="{ row }"><span class="font-medium text-gray-700">{{ row.no_usulan_surat }}</span></template>
            <template #nominal_biaya="{ row }">{{ formatCurrency(row.nominal_biaya) }}</template>
            <template #status_surat="{ row }"><StatusBadges :status="row.status_surat" /></template>
            <template #actions="{ row }">
            <div class="flex gap-2">
              <button
                v-for="action in getRowActions(row)"
                :key="action.type"
                @click="handleAction(action.type, row)"
                :title="action.type"
                class="px-2 py-1 rounded shadow flex items-center justify-center transition hover:brightness-90"
                :class="{'bg-blue-500 text-white': action.color === 'blue', 'bg-green-500 text-white': action.color === 'green'}"
              >
                <font-awesome-icon :icon="['far', action.icon]" class="text-md" />
              </button>
            </div>
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
import { reactive, computed, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { statusOptions } from '@/utils/statusOptions'
import debounce from 'lodash.debounce'

const props = defineProps({
  statusCounts: Object,
  totalPengusulan: Number,
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
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  from: props.filters?.from || '',
  to: props.filters?.to || '',
  range: props.filters?.range || '',
})

watch(
  filters,
  debounce(() => {
    router.get(route('bku.dashboard'), filters, { preserveState: true, replace: true })
  }, 300),
  { deep: true }
)

const formatCurrency = (value) => {
  if (!value) return 'Rp 0';
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
}

const getRowActions = (row) => {
    return [{ type: 'view', icon: 'eye', color: 'blue' }];
}

const handleAction = (type, row) => {
    if (type === 'view') {
        router.get(route('bku.verifikasi', row.id)); 
    }
}

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'tanggal_pengusulan', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' }, 
  { key: 'no_usulan_surat', label: 'Nomor Surat Usulan', slot: 'no_usulan_surat' }, 
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'nominal_biaya', label: 'Total Dana', slot: 'nominal_biaya' },
  { key: 'status_surat', label: 'Status', slot: 'status_surat' },
  { key: 'actions', label: 'Aksi', slot: 'actions' }
]
</script>
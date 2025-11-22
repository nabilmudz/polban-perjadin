<template>
  <AppLayout>
    <div class="bg-white w-full h-full rounded-md shadow overflow-hidden">
      <HeaderPage />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Dashboard BKU</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">
          <StatCard title="Total Pengusulan" icon="file" :count="stats.total_pengusulan" color="blue" />
          <StatCard title="Surat Tugas Baru" icon="plus" :count="stats.surat_tugas_baru" color="green" />
          <StatCard title="Bertugas" icon="user" :count="stats.bertugas" color="blue" />
          <StatCard title="Laporan Belum Selesai" icon="folder-closed" :count="stats.laporan_belum_selesai" color="red" />
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
          <div class="flex justify-between items-end mb-4">
             <div>
                 <h3 class="text-lg font-bold text-gray-800">Detail Penugasan</h3>
             </div>
          </div>
          
          <DataTable
            :columns="columns"
            :data="suratData.data"
            :meta="suratData.meta"
            :links="suratData.links"
            :filters="filters"
            route-name="bku.dashboard"
            @update:filters="Object.assign(filters, $event)"
            @changePage="(page) => router.get(route('bku.dashboard'), { ...filters, page }, { preserveState: true, replace: true })"
          >
            <template #no="{ index }">
                {{ (suratData.meta.from || 1) + index }}
            </template>

            <template #created_at="{ row }">
                {{ formatDate(row.created_at) }}
            </template>

            <template #tanggal_berangkat="{ row }">
                {{ formatDate(row.tanggal_berangkat) }}
            </template>

            <template #status_laporan="{ row }">
               <span 
                  class="px-3 py-1 rounded-md text-xs font-bold text-white"
                  :class="{
                      'bg-yellow-500': row.badge_color === 'yellow',
                      'bg-green-600': row.badge_color === 'green',
                      'bg-blue-500': row.badge_color === 'blue',
                      'bg-gray-400': row.badge_color === 'gray',
                  }"
               >
                  {{ row.display_status_laporan }}
               </span>
            </template>

            <template #tanggungan_biaya="{ row }">
                {{ row.tanggungan_biaya || '-' }}
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
import { reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  stats: Object,
  latestSurat: Object, 
  filters: Object
})

const suratData = computed(() => {
    const raw = props.latestSurat || {};
    return {
        data: raw.data || [],
        meta: {
            current_page: raw.current_page || 1,
            last_page: raw.last_page || 1,
            per_page: raw.per_page || 5,
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

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString; 
    
    return new Intl.DateTimeFormat('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    }).format(date);
}

const columns = [
  { key: 'created_at', label: 'Tanggal Pengusulan', slot: 'tanggal_pengusulan' }, 
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat', slot: 'tanggal_berangkat' }, 
  { key: 'nomor_surat_resmi', label: 'Nomor Surat Tugas' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'status_laporan', label: 'Status Laporan', slot: 'status_laporan' },
  { key: 'tanggungan_biaya', label: 'Tanggungan Biaya', slot: 'tanggungan_biaya' },
]
</script>
<template>
  <AppLayout>
    <div class="bg-white w-full h-full rounded-md shadow">
      <HeaderPage />
      <div class="p-8">
        <h1 class="text-3xl font-bold mb-4">Dashboard</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <StatCard title="Total Ulasan" icon="file" :count="10" />
          <StatCard title="Laporan Selesai" icon="square-check" :count="3" />
          <StatCard title="Belum Selesai" icon="folder-closed" :count="5" />
          <StatCard title="Bertugas" icon="user" :count="4" />
        </div>
      </div>

      <DataTable
        :columns="columns"
        :data="suratTugas.data"
        :meta="suratTugas.meta"
        :links="suratTugas.links"
        :filters="filters"
        :status-options="['draft', 'submitted_wadir_review']"
        route-name="surat-tugas.index"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import StatCard from '@/Components/StatCard.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import { usePage } from '@inertiajs/vue3'
import { ref } from 'vue'

const { props } = usePage()
const suratTugas = props.suratTugas
const filters = ref(props.filters)

const columns = [
  { key: 'nomor_urutan_surat', label: 'No Surat' },
  { key: 'perihal_tugas', label: 'Perihal' },
  { key: 'status_surat', label: 'Status' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
]
</script>

<template>
  <AppLayout>
        <div class=" bg-white w-full h-full rounded-md">
          <HeaderPage/>
          <div class="p-8">
            <h1 class="text-3xl font-bold mb-4">Daftar Pengusulan</h1>
          </div>
        </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
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

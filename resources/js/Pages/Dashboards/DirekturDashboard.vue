<template>
  <AppLayout>
    <div class="bg-none w-full h-full rounded-md shadow">
      <HeaderPage />

      <!-- PAGE TITLE -->
      <div class="p-8">
        <h1 class="text-3xl font-bold mb-4">Dashboard</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <StatCard title="Total Ulasan" icon="file" :count="10" />
          <StatCard title="Laporan Selesai" icon="square-check" :count="3" />
          <StatCard title="Belum Selesai" icon="folder-closed" :count="5" />
          <StatCard title="Bertugas" icon="user" :count="4" />
        </div>
      </div>

      <!-- MAIN CONTENT -->
      <div class="px-6 py-8 space-y-10">

        <!-- STAT CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          
          <!-- Total Usulan -->
          <div class="bg-white border shadow rounded-xl p-6 flex items-center justify-between">
            <div>
              <p class="text-gray-600">Total Usulan</p>
              <p class="text-4xl font-bold mt-2">{{ totalUsulan }}</p>
            </div>
            <i class="fas fa-file text-4xl text-blue-500"></i>
          </div>

          <!-- Bertugas -->
          <div class="bg-white border shadow rounded-xl p-6 flex items-center justify-between">
            <div>
              <p class="text-gray-600">Bertugas</p>
              <p class="text-4xl font-bold mt-2">{{ bertugas }}</p>
            </div>
            <i class="fas fa-users text-4xl text-blue-500"></i>
          </div>

        </div>

        <!-- TABLE CARD -->
        <div class="bg-white border shadow rounded-xl p-6 space-y-4">

          <h3 class="text-lg font-semibold">Detail</h3>

          <!-- Search input on right -->
          <div class="flex justify-end">
            <div class="flex items-center gap-2">
              <input type="text"
                     v-model="filters.search"
                     placeholder="Search..."
                     class="border rounded px-3 py-2 w-64 text-sm" />
              <button class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </div>

          <!-- TABLE -->
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-gray-100 text-sm">
                <th class="p-3 border">Tanggal Berangkat</th>
                <th class="p-3 border">Nomor Surat</th>
                <th class="p-3 border">Sumber Dana</th>
                <th class="p-3 border">Status</th>
                <th class="p-3 border">Aksi</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="item in suratTugas.data" :key="item.id" class="text-sm">
                
                <td class="p-3 border">
                  {{ item.tanggal_berangkat }}
                </td>

                <td class="p-3 border">
                  {{ item.no_surat }}
                </td>

                <td class="p-3 border">
                  {{ item.sumber_dana }}
                </td>

                <td class="p-3 border">
                  <span class="bg-green-200 text-green-800 text-xs px-2 py-1 rounded">
                    Diterbitkan
                  </span>
                </td>

                <td class="p-3 border">
                  <Link :href="route('surat-tugas.show', item.id)"
                        class="px-3 py-1 rounded bg-blue-500 text-white hover:bg-blue-600 text-xs flex items-center gap-1">
                    <i class="fas fa-eye"></i> View
                  </Link>
                </td>

              </tr>
            </tbody>
          </table>

          <!-- FOOTER ROW -->
          <div class="flex items-center justify-between pt-2 text-sm">
            
            <!-- Rows per page -->
            <div class="flex items-center gap-2">
              <span>Rows per page:</span>
              <select class="border rounded px-2 py-1 pr-10">
                <option>10</option>
                <option>25</option>
                <option>50</option>
              </select>
            </div>

            <!-- Showing info -->
            <span>
              Showing 1 - {{ suratTugas.data?.length ?? 0 }} of {{ suratTugas.meta?.total ?? 0 }}
            </span>

          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { ref } from "vue"

const { props } = usePage()

const suratTugas = props.suratTugas ?? { data: [], meta: { total: 0 } }
const totalUsulan = suratTugas.meta?.total ?? 0
const bertugas = suratTugas.data?.filter(s => s.status === 'Diterbitkan')?.length ?? 0

const filters = ref({
    search: props.filters?.search ?? ''
})
</script>
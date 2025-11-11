<template>
  <AppLayout>
    <div class="bg-none w-full h-full rounded-md shadow">
      <!-- HEADER -->
      <HeaderPage />

      <!-- PAGE TITLE -->
      <div class="p-8">
        <h1 class="text-3xl font-bold mb-4">Dashboard BKU</h1>

        <!-- STAT CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-white border shadow rounded-xl p-6 flex items-center justify-between">
            <div>
              <p class="text-gray-600">Total Pengusulan</p>
              <p class="text-4xl font-bold mt-2">{{ totalPengusulan }}</p>
            </div>
            <i class="fas fa-file text-4xl text-blue-500"></i>
          </div>

          <div class="bg-white border shadow rounded-xl p-6 flex items-center justify-between">
            <div>
              <p class="text-gray-600">Surat Tugas Baru</p>
              <p class="text-4xl font-bold mt-2 text-green-600">{{ suratBaru }}</p>
            </div>
            <i class="fas fa-envelope-open text-4xl text-green-500"></i>
          </div>

          <div class="bg-white border shadow rounded-xl p-6 flex items-center justify-between">
            <div>
              <p class="text-gray-600">Bertugas</p>
              <p class="text-4xl font-bold mt-2 text-blue-600">{{ bertugas }}</p>
            </div>
            <i class="fas fa-users text-4xl text-blue-500"></i>
          </div>

          <div class="bg-white border shadow rounded-xl p-6 flex items-center justify-between">
            <div>
              <p class="text-gray-600">Laporan Belum Selesai</p>
              <p class="text-4xl font-bold mt-2 text-red-600">{{ belumSelesai }}</p>
            </div>
            <i class="fas fa-exclamation-circle text-4xl text-red-500"></i>
          </div>
        </div>
      </div>

      <!-- MAIN CONTENT -->
      <div class="px-6 py-8 space-y-10">
        <!-- TABLE CARD -->
        <div class="bg-white border shadow rounded-xl p-6 space-y-4">
          <h3 class="text-lg font-semibold">Detail Penugasan</h3>

          <!-- Search input -->
          <div class="flex justify-end">
            <div class="flex items-center gap-2">
              <input
                type="text"
                v-model="search"
                placeholder="Cari Tugas..."
                class="border rounded px-3 py-2 w-64 text-sm"
              />
              <button
                class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
              >
                <i class="fas fa-search"></i>
              </button>
            </div>
          </div>

          <!-- TABLE -->
          <div class="overflow-auto">
            <table class="min-w-full border-collapse">
              <thead>
                <tr class="bg-gray-100 text-sm">
                  <th class="p-3 border">No</th>
                  <th class="p-3 border">Tanggal Pengusulan</th>
                  <th class="p-3 border">Tanggal Berangkat</th>
                  <th class="p-3 border">Nomor Surat</th>
                  <th class="p-3 border">Sumber Dana</th>
                  <th class="p-3 border">Status Laporan</th>
                  <th class="p-3 border">Tanggungan Biaya</th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="(item, index) in filteredList"
                  :key="index"
                  class="text-center text-sm"
                >
                  <td class="border p-3">{{ index + 1 }}</td>
                  <td class="border p-3">{{ item.tanggal_pengusulan }}</td>
                  <td class="border p-3">{{ item.tanggal_berangkat }}</td>
                  <td class="border p-3">{{ item.nomor_surat || '-' }}</td>
                  <td class="border p-3">{{ item.sumber_dana }}</td>
                  <td class="border p-3">
                    <span :class="statusClass(item.status)">
                      {{ item.status }}
                    </span>
                  </td>
                  <td class="border p-3">{{ item.tanggungan_biaya || '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- FOOTER -->
          <div
            class="flex items-center justify-between pt-2 text-sm text-gray-600"
          >
            <div class="flex items-center gap-2">
              <span>Rows per page:</span>
              <select class="border rounded px-2 py-1 pr-10">
                <option>10</option>
                <option>25</option>
                <option>50</option>
              </select>
            </div>

            <span>Showing 1 - {{ filteredList.length }} of {{ tugasList.length }}</span>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import { ref, computed } from 'vue'

// --- State ---
const search = ref('')
const tugasList = ref([
  { tanggal_pengusulan: '30 Oct 2025', tanggal_berangkat: '30 Oct 2025', nomor_surat: '-', sumber_dana: 'Polban', status: 'Belum Upload', tanggungan_biaya: '-' },
  { tanggal_pengusulan: '29 Oct 2025', tanggal_berangkat: '29 Oct 2025', nomor_surat: '-', sumber_dana: 'Polban', status: 'Sedang Bertugas', tanggungan_biaya: '-' },
  { tanggal_pengusulan: '28 Oct 2025', tanggal_berangkat: '28 Oct 2025', nomor_surat: '-', sumber_dana: 'RM', status: 'Selesai', tanggungan_biaya: '-' },
])

// --- Computed Filters ---
const filteredList = computed(() => {
  const term = search.value.toLowerCase()
  return tugasList.value.filter(item =>
    Object.values(item).some(v => String(v).toLowerCase().includes(term))
  )
})

// --- Counts ---
const totalPengusulan = tugasList.value.length
const suratBaru = tugasList.value.filter(i => i.status === 'Belum Upload').length
const bertugas = tugasList.value.filter(i => i.status === 'Sedang Bertugas').length
const belumSelesai = tugasList.value.filter(i => i.status !== 'Selesai').length

// --- Helpers ---
const statusClass = (status) => {
  return {
    'bg-yellow-400 text-white px-2 py-1 rounded': status === 'Belum Upload',
    'bg-blue-400 text-white px-2 py-1 rounded': status === 'Sedang Bertugas',
    'bg-green-500 text-white px-2 py-1 rounded': status === 'Selesai',
  }
}
</script>

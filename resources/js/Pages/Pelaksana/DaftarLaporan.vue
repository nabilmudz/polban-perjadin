<template>
  <AppLayout>
    <div class="bg-white w-full h-full rounded-md shadow">
      <HeaderPage />

      <div class="p-8 border-b">
        <h1 class="text-3xl font-bold mb-4">Daftar Laporan</h1>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div class="flex gap-3 items-center">
            <input
              v-model="search"
              type="text"
              placeholder="Cari nama kegiatan atau pengusul..."
              class="px-3 py-2 border rounded w-64 focus:ring focus:outline-none"
            />
            <button @click="search = ''" class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">
              Reset
            </button>
          </div>

          <div>
            <select v-model="statusFilter" class="px-3 py-2 border rounded">
              <option value="">Semua Status</option>
              <option value="Selesai">Selesai</option>
              <option value="Proses">Proses</option>
              <option value="Ditolak">Ditolak</option>
              <option value="Belum Upload">Belum Upload</option>
            </select>
          </div>
        </div>
      </div>

      <div class="p-8 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        <StatCard title="Total Laporan" icon="file" :count="laporan.length" />
        <StatCard title="Selesai" icon="check-circle" :count="laporan.filter(l => l.status === 'Selesai').length" />
        <StatCard title="Proses" icon="clock" :count="laporan.filter(l => l.status === 'Proses').length" />
        <StatCard title="Ditolak" icon="times-circle" :count="laporan.filter(l => l.status === 'Ditolak').length" />
      </div>

      <div class="p-8">
        <div class="overflow-x-auto">
          <table class="w-full table-auto border-collapse">
            <thead>
              <tr class="bg-gray-100 text-left">
                <th v-for="col in columns" :key="col.key" class="px-4 py-3 border">{{ col.label }}</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(row, index) in filteredLaporan"
                :key="row.id"
                class="odd:bg-white even:bg-gray-50 hover:bg-gray-100 transition"
              >
                <td class="px-4 py-3 border">{{ index + 1 }}</td>
                <td class="px-4 py-3 border">{{ row.pengusul }}</td>
                <td class="px-4 py-3 border">{{ row.wadir }}</td>
                <td class="px-4 py-3 border">{{ row.no_surat_resmi }}</td>
                <td class="px-4 py-3 border">{{ row.nama_kegiatan }}</td>
                <td class="px-4 py-3 border">{{ row.tanggal_pelaksanaan }}</td>
                <td class="px-4 py-3 border">{{ row.sumber_dana }}</td>
                <td class="px-4 py-3 border">{{ row.status }}</td>

                <td class="px-4 py-3 border">
                  <div class="flex gap-2">
                    <a
                      v-if="row.status === 'Selesai'"
                      :href="`/surat-tugas/${row.id}/pdf`"
                      target="_blank"
                      class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700"
                    >
                      Lihat PDF
                    </a>

                    <button
                      @click="openUploadModal(row)"
                      v-else-if="row.status === 'Proses'"
                      class="px-3 py-1 bg-amber-500 text-white rounded text-sm"
                    >
                      Menunggu
                    </button>

                    <button
                      v-else
                      class="px-3 py-1 bg-gray-400 text-white rounded text-sm cursor-not-allowed"
                      disabled
                    >
                      Belum Upload
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredLaporan.length === 0">
                <td colspan="9" class="px-4 py-6 text-center text-gray-500">Tidak ada laporan ditemukan.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <Modal
        :show="isUploadModalOpen"
        @close="isUploadModalOpen = false"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import StatCard from '@/Components/StatCard.vue'
import { usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Modal from './Partials/Modal.vue'

const isUploadModalOpen = ref(false)

const openUploadModal = (row) => {
  console.log('Open modal for row:', row)
  isUploadModalOpen.value = true
}

const { props } = usePage()
const laporan = ref(props.laporan ?? [])

const columns = [
  { key: 'id', label: 'No' },
  { key: 'pengusul', label: 'Pengusul' },
  { key: 'wadir', label: 'Wadir yang Memaraf' },
  { key: 'no_surat_resmi', label: 'No Surat Resmi' },
  { key: 'nama_kegiatan', label: 'Nama Kegiatan' },
  { key: 'tanggal_pelaksanaan', label: 'Tanggal Pelaksanaan' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'status', label: 'Status' },
  { key: 'aksi', label: 'Aksi' },
]

const search = ref('')
const statusFilter = ref('')

const filteredLaporan = computed(() => {
  const q = search.value.toLowerCase()
  return laporan.value.filter(l =>
    (!statusFilter.value || l.status === statusFilter.value) &&
    (l.nama_kegiatan.toLowerCase().includes(q) || l.pengusul.toLowerCase().includes(q))
  )
})
</script>

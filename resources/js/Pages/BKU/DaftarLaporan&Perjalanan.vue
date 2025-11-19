<template>
  <AppLayout>
    <div class="p-6">
      <h1 class="text-3xl font-bold mb-6">Laporan & Bukti Perjalanan Dinas</h1>

      <!-- Search Form -->
      <form @submit.prevent="search" class="flex gap-3 mb-5">
        <input
          v-model="form.search"
          type="text"
          placeholder="Cari Kegiatan..."
          class="input"
        />
        <button class="btn-primary">Cari</button>
      </form>

      <!-- Table -->
      <div class="overflow-auto bg-white shadow rounded p-4">
        <table class="table-auto w-full border-collapse">
          <thead>
            <tr class="bg-gray-100 text-left">
              <th class="px-3 py-2 border">No</th>
              <th class="px-3 py-2 border">Nama Kegiatan</th>
              <th class="px-3 py-2 border">Tanggal Pengusulan</th>
              <th class="px-3 py-2 border">Tanggal Pelaksanaan</th>
              <th class="px-3 py-2 border">Nomor Surat Tugas</th>
              <th class="px-3 py-2 border">Status Laporan</th>
              <th class="px-3 py-2 border">Aksi</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="(item, index) in laporanBuktiSafe"
              :key="item.id || index"
              v-show="filterMatch(item)"
            >
              <td class="border px-3 py-2">{{ index + 1 }}</td>
              <td class="border px-3 py-2">{{ item.nama_kegiatan || '-' }}</td>
              <td class="border px-3 py-2">{{ item.tanggal_pengusulan || '-' }}</td>
              <td class="border px-3 py-2">{{ item.tanggal_pelaksanaan || '-' }}</td>
              <td class="border px-3 py-2">{{ item.nomor_surat_tugas || '-' }}</td>
              <td class="border px-3 py-2">
                <span
                  class="px-2 py-1 rounded-md text-xs font-semibold"
                  :class="{
                    'bg-green-100 text-green-700': item.status_laporan === 'Diterima',
                    'bg-yellow-100 text-yellow-700': item.status_laporan === 'Menunggu',
                    'bg-red-100 text-red-700': item.status_laporan === 'Ditolak'
                  }"
                >
                  {{ item.status_laporan || '-' }}
                </span>
              </td>
              <td class="border px-3 py-2">
                <Link
                  :href="route('bku.daftarlaporanperjalanan')"
                  class="btn-view"
                >
                  👁 Lihat Bukti
                </Link>
              </td>
            </tr>

            <tr v-if="laporanBuktiSafe.length === 0">
              <td colspan="7" class="text-center py-4 text-gray-500">
                Tidak ada data ditemukan.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  laporanBukti: {
    type: Array,
    default: () => [] // This prevents undefined errors
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

// Computed property ensures laporanBukti is always defined
const laporanBuktiSafe = computed(() => props.laporanBukti ?? [])

// Search form setup
const form = useForm({
  search: props.filters?.search ?? ''
})

// Local filtering
function filterMatch(item) {
  if (!form.search) return true
  return item.nama_kegiatan?.toLowerCase().includes(form.search.toLowerCase())
}

// Server-side search
function search() {
  form.get(route('bku.daftarlaporanperjalanan'), {
    preserveScroll: true,
    preserveState: true
  })
}
</script>

<style scoped>
.input {
  border: 1px solid #ccc;
  padding: 8px;
  border-radius: 6px;
}
.btn-primary {
  background: #007bff;
  color: white;
  padding: 8px 12px;
  border-radius: 6px;
}
.btn-view {
  background: #28c0f3;
  padding: 6px 10px;
  border-radius: 6px;
  color: white;
}
</style>

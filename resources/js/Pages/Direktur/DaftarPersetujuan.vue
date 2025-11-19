<template>
  <AppLayout>
    <div class="p-6">

      <h1 class="text-3xl font-bold mb-6">Daftar Persetujuan</h1>

      <!-- Search Form -->
      <form @submit.prevent="search" class="flex gap-3 mb-5">
        <input v-model="form.search" type="text" placeholder="Cari Pengajuan..." class="input" />
        <button class="btn-primary">Search</button>
      </form>

      <!-- Table -->
      <div class="overflow-auto bg-white shadow rounded p-4">
        <table class="table-auto w-full border-collapse">
          <thead>
            <tr class="bg-gray-100 text-left">
              <th class="px-3 py-2 border">No</th>
              <th class="px-3 py-2 border">Pengusul</th>
              <th class="px-3 py-2 border">Wadir yang Memaraf</th>
              <th class="px-3 py-2 border">Nomor Surat Resmi</th>
              <th class="px-3 py-2 border">Nama Kegiatan</th>
              <th class="px-3 py-2 border">Tanggal Pelaksanaan</th>
              <th class="px-3 py-2 border">Sumber Dana</th>
              <th class="px-3 py-2 border">Aksi</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="(item, index) in suratTugas" :key="item.id">
              <td class="border px-3 py-2">{{ index + 1 }}</td>
              <td class="border px-3 py-2">{{ item.pengusul.name }}</td>
              <td class="border px-3 py-2">{{ item.wadir.name }}</td>
              <td class="border px-3 py-2">{{ item.nomor_surat_resmi }}</td>
              <td class="border px-3 py-2">{{ item.nama_kegiatan }}</td>
              <td class="border px-3 py-2">{{ item.tanggal_pelaksanaan }}</td>
              <td class="border px-3 py-2">{{ item.sumber_dana }}</td>
              <td class="border px-3 py-2">
                <Link :href="route('direktur.review', item.id)" class="btn-view">
                  👁 Review
                </Link>
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

const props = defineProps({
  suratTugas: Array,
  filters: Object
})

const form = useForm({
  search: props.filters?.search ?? ''
})

function search() {
  form.get(route('direktur.daftarpersetujuan'), {
    preserveScroll: true,
    preserveState: true
  })
}
</script>

<style scoped>
.input { border: 1px solid #ccc; padding: 8px; border-radius: 6px; }
.btn-primary { background: #007bff; color: white; padding: 8px 12px; border-radius: 6px; }
.btn-view { background: #28c0f3; padding: 6px 10px; border-radius: 6px; color: white; }
</style>

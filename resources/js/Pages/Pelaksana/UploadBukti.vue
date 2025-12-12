<template>
  <AppLayout>
    <div class="bg-white w-full max-w-7xl mx-auto rounded-md shadow p-8">
      <HeaderPage />

      <!-- Judul -->
      <h1 class="text-3xl font-bold mb-2">Upload Bukti</h1>
      <p class="text-gray-600 mb-6">Upload bukti pertanggungjawaban perjalanan dinas.</p>

      <!-- DETAIL LAPORAN -->
      <div class="border rounded p-4 mb-6 bg-gray-50">
        <h2 class="font-semibold mb-2">Detail Laporan</h2>
        <p><strong>No Surat Tugas:</strong> {{ laporan.nomor_surat_tugas_resmi }}</p>
        <p><strong>Nama Kegiatan:</strong> {{ laporan.perihal_tugas }}</p>
        <p><strong>Tanggal Pelaksanaan:</strong> {{ laporan.tanggal_berangkat }} s/d {{ laporan.tanggal_kembali }}</p>
      </div>

      <!-- DAFTAR BUKTI -->
      <div class="mb-6">
        <h2 class="font-semibold mb-2">Bukti yang sudah diupload</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
          <div
            v-for="file in uploadedFiles"
            :key="file.id"
            class="border rounded p-3 flex flex-col items-center gap-2"
          >
            <template v-if="file.type.includes('image')">
              <img :src="file.url" alt="Bukti" class="w-full h-48 object-contain" />
            </template>
            <template v-else-if="file.type.includes('pdf')">
              <a :href="file.url" target="_blank" class="text-blue-600 underline">Lihat PDF</a>
            </template>
            <p class="text-gray-700 text-sm">{{ file.keterangan }}</p>
            <p class="text-gray-900 font-medium" v-if="file.nominal">Rp {{ file.nominal.toLocaleString() }}</p>
          </div>
        </div>
      </div>

      <!-- FORM UPLOAD -->
      <form @submit.prevent="submitUpload" class="border rounded p-4 bg-gray-50">
        <h2 class="font-semibold mb-4">Upload Bukti Baru</h2>

        <!-- Kategori -->
        <div class="mb-4">
          <label class="font-medium mb-1 block">Kategori</label>
          <div class="flex gap-4">
            <label>
              <input type="radio" value="surat_visum" v-model="form.kategori" />
              Surat Visum
            </label>
            <label>
              <input type="radio" value="laporan_perjalanan_dinas" v-model="form.kategori" />
              Laporan Perjalanan Dinas
            </label>
            <label>
              <input type="radio" value="bukti_perjalanan_dinas" v-model="form.kategori" />
              Bukti Perjalanan Dinas
            </label>
          </div>
        </div>

        <!-- File -->
        <div class="mb-4">
          <label class="font-medium mb-1 block">File</label>
          <input type="file" @change="handleFileChange" />
        </div>

        <!-- Keterangan -->
        <div class="mb-4">
          <label class="font-medium mb-1 block">Keterangan</label>
          <input
            type="text"
            v-model="form.keterangan"
            placeholder="Contoh: tiket, nota hotel, dsb"
            class="border rounded w-full p-2"
          />
        </div>

        <!-- Nominal -->
        <div class="mb-4">
          <label class="font-medium mb-1 block">Nominal (opsional)</label>
          <input
            type="number"
            v-model="form.nominal"
            placeholder="Contoh: 150000"
            class="border rounded w-full p-2"
          />
        </div>

        <button
          type="submit"
          class="px-4 py-2 rounded bg-blue-500 text-white hover:bg-blue-600 transition"
        >
          Upload
        </button>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import { ref } from 'vue'
import axios from 'axios'
import { usePage } from '@inertiajs/vue3'

/* Props dari controller */
const { props } = usePage()
const laporan = props.laporan

/* Form Upload */
const form = ref({
  kategori: 'surat_visum',
  file: null,
  keterangan: '',
  nominal: null,
})

/* Daftar file yang sudah diupload */
const uploadedFiles = ref(props.uploadedFiles || [])

/* Handle File Change */
const handleFileChange = (e) => {
  form.value.file = e.target.files[0]
}

/* Submit Form */
const submitUpload = async () => {
  if (!form.value.file) {
    alert('Silakan pilih file terlebih dahulu!')
    return
  }

  const formData = new FormData()
  formData.append('file', form.value.file)
  formData.append('kategori', form.value.kategori)
  formData.append('keterangan', form.value.keterangan)
  formData.append('nominal', form.value.nominal || '')

  try {
    const res = await axios.post(
      route('pelaksana.bukti.upload', laporan.id),
      formData,
      { headers: { 'Content-Type': 'multipart/form-data' } }
    )

    uploadedFiles.value = res.data.uploadedFiles

    // reset form
    form.value.file = null
    form.value.keterangan = ''
    form.value.nominal = null
    form.value.kategori = 'surat_visum'

    alert(res.data.message)
  } catch (err) {
    console.error(err)
    alert('Terjadi kesalahan saat upload.')
  }
}
</script>

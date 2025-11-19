<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-[9999]">
    <div class="bg-white rounded-lg w-[600px] p-6 shadow-xl">

      <h2 class="text-xl font-bold mb-1">Upload Excel Pegawai</h2>
      <p class="text-sm text-gray-500 mb-6">
        Pastikan format Excel anda sesuai.
      </p>

      <div class="bg-gray-100 p-4 rounded mb-8">
        <h3 class="font-semibold mb-2">Contoh Format Excel:</h3>

        <table class="w-full text-left text-sm border border-gray-300">
          <thead>
            <tr class="bg-gray-200">
              <th class="border p-2">nama</th>
              <th class="border p-2">nip</th>
              <th class="border p-2">pangkat</th>
              <th class="border p-2">golongan</th>
              <th class="border p-2">jabatan</th>
              <th class="border p-2">status</th>
            </tr>
          </thead>

          <tbody>
            <tr>
              <td class="border p-2">Andi Setiawan</td>
              <td class="border p-2">1987654321</td>
              <td class="border p-2">Penata</td>
              <td class="border p-2">III/c</td>
              <td class="border p-2">Staff</td>
              <td class="border p-2">Aktif</td>
            </tr>
            <tr>
              <td class="border p-2">Rizky Fadilah</td>
              <td class="border p-2">1942984515</td>
              <td class="border p-2">Penata</td>
              <td class="border p-2">III/c</td>
              <td class="border p-2">Staff</td>
              <td class="border p-2">Tidak Aktif</td>
            </tr>
          </tbody>
        </table>
      </div>

      <input
        type="file"
        accept=".xlsx,.xls"
        @change="handleFile"
        class="mb-4"
      />

      <div class="flex justify-end gap-2">
        <button class="px-3 py-2 bg-gray-300 rounded" @click="$emit('close')">Close</button>
        <button
          class="px-3 py-2 bg-primary-default text-white rounded"
          :disabled="loading"
          @click="upload"
        >
          {{ loading ? 'Uploading...' : 'Upload' }}
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const emit = defineEmits(['close','success','dupes'])
const file = ref(null)
const loading = ref(false)

const handleFile = e => file.value = e.target.files[0]

const upload = async () => {
  if (!file.value) return alert('Pilih file dulu!')

  loading.value = true
  const formData = new FormData()
  formData.append('file', file.value)

  try {
    const res = await axios.post(route('pegawai.uploadExcel'), formData)
    if (res.data.duplicates?.length) {
      emit('dupes', res.data.duplicates) 
    } else {
      emit('success')
      emit('close')
    }
  } catch (err) {
    console.error(err)
    alert('Upload gagal')
  } finally {
    loading.value = false
  }
}
</script>

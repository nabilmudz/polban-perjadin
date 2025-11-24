<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-[9999]">
    <div class="bg-white rounded-lg w-[600px] p-6 shadow-xl">

      <h2 class="text-xl font-bold mb-1">Upload Excel Mahasiswa</h2>
      <p class="text-sm text-gray-500 mb-6">
        Pastikan format Excel anda sesuai.
      </p>

      <div class="bg-gray-100 p-4 rounded mb-8">
        <h3 class="font-semibold mb-2">Contoh Format Excel:</h3>

        <table class="w-full text-left text-sm border border-gray-300">
          <thead>
            <tr class="bg-gray-200">
              <th class="border p-2">nim</th>
              <th class="border p-2">nama</th>
              <th class="border p-2">jurusan</th>
              <th class="border p-2">prodi</th>
            </tr>
          </thead>

          <tbody>
            <tr>
              <td class="border p-2">123456789</td>
              <td class="border p-2">Andi Setiawan</td>
              <td class="border p-2">Teknik Elektro</td>
              <td class="border p-2">Teknik Informatika</td>
            </tr>
            <tr>
              <td class="border p-2">987654321</td>
              <td class="border p-2">Rizky Fadilah</td>
              <td class="border p-2">Teknik Mesin</td>
              <td class="border p-2">Teknik Mesin</td>
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
import { router } from '@inertiajs/vue3'

const emit = defineEmits(['close','success','dupes'])
const file = ref(null)
const loading = ref(false)

const handleFile = e => file.value = e.target.files[0]

const upload = async () => {
  if (!file.value) return alert('Pilih file dulu!')

  const formData = {
    file: file.value
  }

  router.post(route('admin.mahasiswa.import'), formData, {
    onStart: () => loading.value = true,
    onFinish: () => loading.value = false,
    onSuccess: (page) => {
        if (page.props.flash.import_errors) {
            emit('dupes', page.props.flash.import_errors)
        } else {
            emit('success')
        }
        emit('close')
    },
    onError: (errors) => {
        console.error(errors)
        alert('Upload gagal')
    }
  })
}
</script>

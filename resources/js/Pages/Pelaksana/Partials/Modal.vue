<template>
  <Modal :show="show" @close="$emit('close')" max-width="lg">
    <div class="p-6">
      <div class="flex justify-between items-center border-b pb-3 mb-4">
        <h2 class="text-xl font-semibold">Upload Laporan & Bukti</h2>
        <button
          @click="$emit('close')"
          class="text-gray-500 hover:text-gray-800 transition"
        >
          ✕
        </button>
      </div>

      <p class="text-gray-600 mb-4">
        Agar tidak terjadi kesalahan upload Laporan Perjalanan Dinas dan Surat Visum terlebih dahulu, lalu upload bukti seperti boarding pass, tiket taksi, hotel, dll.
      </p>

      <form class="space-y-4">
        <div>
          <label class="block font-medium mb-2">Jenis Dokumen</label>
          <div class="flex flex-col gap-2">
            <label class="flex items-center gap-2">
              <input type="radio" v-model="status" value="laporan" />
              <span>Laporan Perjalanan Dinas</span>
            </label>
            <label class="flex items-center gap-2">
              <input type="radio" v-model="status" value="visum" />
              <span>Surat Visum</span>
            </label>
            <label class="flex items-center gap-2">
              <input type="radio" v-model="status" value="bukti" />
              <span>Bukti Perjalanan Dinas</span>
            </label>
          </div>
        </div>

        <div>
          <label class="block font-medium mb-2">Upload Dokumen (PDF)</label>
          <input
            type="file"
            accept=".pdf"
            class="border rounded w-full px-3 py-2"
            @change="handleFileChange"
          />
        </div>

        <div>
          <label class="block font-medium mb-2">Nominal</label>
          <input
            v-model="catatan"
            class="w-full border rounded px-3 py-2"
            placeholder="Contoh: 100000"
            rows="3"
            type="number"
          />
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t mt-4">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 rounded-md border text-gray-700 hover:bg-gray-100"
          >
            Batal
          </button>
          <button
            type="button"
            @click="submitForm"
            class="px-4 py-2 rounded-md bg-primary-default text-white hover:bg-primary-dark"
          >
            Upload
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>

<script setup>
import Modal from '@/Components/Modal.vue'
import { ref } from 'vue'

defineProps({
  show: Boolean,
})

defineEmits(['close'])

const status = ref('selesai')
const catatan = ref('')
const file = ref(null)

const handleFileChange = (e) => {
  file.value = e.target.files[0]
}

const submitForm = () => {
  console.log({
    status: status.value,
    catatan: catatan.value,
    file: file.value?.name,
  })
}
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-gray-500 bg-opacity-75 px-4 py-6">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl p-6 relative">
      <button class="absolute top-2 right-2 text-gray-500 text-lg font-bold" @click="$emit('close')">✕</button>

      <h2 class="text-2xl font-bold mb-6">Tambah Mahasiswa</h2>

      <form @submit.prevent="$emit('submit')" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
          <div>
            <label class="block font-medium mb-1">NIM <span class="text-red-500">*</span></label>
            <input v-model="form.nim" type="text" class="w-full border rounded px-3 py-2" placeholder="NIM" required />
          </div>
        </div>
        <div class="space-y-4">
          <div>
            <label class="block font-medium mb-1">Nama <span class="text-red-500">*</span></label>
            <input v-model="form.nama" type="text" class="w-full border rounded px-3 py-2" placeholder="Nama" required />
          </div>
        </div>
        <div class="space-y-4">
          <div>
            <label class="block font-medium mb-1">Jurusan <span class="text-red-500">*</span></label>
            <input v-model="form.jurusan" type="text" class="w-full border rounded px-3 py-2" placeholder="Jurusan" required />
          </div>
        </div>
        <div class="space-y-4">
          <div>
            <label class="block font-medium mb-1">Prodi <span class="text-red-500">*</span></label>
            <input v-model="form.prodi" type="text" class="w-full border rounded px-3 py-2" placeholder="Prodi" required />
          </div>
        </div>

        <div class="col-span-1 md:col-span-2 flex justify-end mt-4">
          <button type="submit" class="px-6 py-2 bg-primary-default text-white font-semibold rounded hover:bg-primary-dark">
            Save
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { PropType } from 'vue'

interface MahasiswaForm {
  nim: string
  nama: string
  jurusan: string
  prodi: string
}

const props = defineProps<{
  form: MahasiswaForm
}>()
</script>

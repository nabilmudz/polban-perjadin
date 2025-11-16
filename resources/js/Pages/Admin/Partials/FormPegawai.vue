<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-gray-500 bg-opacity-75 px-4 py-6">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl p-6 relative">
      <button class="absolute top-2 right-2 text-gray-500 text-lg font-bold" @click="$emit('close')">✕</button>

      <h2 class="text-2xl font-bold mb-6">Tambah Pegawai</h2>

      <form @submit.prevent="$emit('submit')" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
          <div>
            <label class="block font-medium mb-1">Nama <span class="text-red-500">*</span></label>
            <input v-model="form.nama" type="text" class="w-full border rounded px-3 py-2" placeholder="Nama" required />
          </div>

          <div>
            <label class="block font-medium mb-1">NIP <span class="text-red-500">*</span></label>
            <input v-model="form.nip" type="text" class="w-full border rounded px-3 py-2" placeholder="NIP" required />
          </div>

          <div>
            <label class="block font-medium mb-1">Pangkat <span class="text-red-500">*</span></label>
            <input v-model="form.pangkat" type="text" class="w-full border rounded px-3 py-2" placeholder="Pangkat" required />
          </div>
          
        </div>
        
        <div class="space-y-4">
            <div>
              <label class="block font-medium mb-1">Golongan <span class="text-red-500">*</span></label>
              <input v-model="form.golongan" type="text" class="w-full border rounded px-3 py-2" placeholder="Golongan" required />
            </div>
          <div>
            <label class="block font-medium mb-1">Jabatan <span class="text-red-500">*</span></label>
            <input v-model="form.jabatan" type="text" class="w-full border rounded px-3 py-2" placeholder="Jabatan" required />
          </div>

          <div>
            <label class="block font-medium mb-1">Status <span class="text-red-500">*</span></label>
            <select v-model="form.status" class="w-full border rounded px-3 py-2" required>
              <option value="" disabled selected>Pilih Status</option>
              <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
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

interface PegawaiForm {
  nama: string
  nip: string
  pangkat: string
  golongan: string
  jabatan: string
  status: number | ''
}

interface StatusOption {
  value: number
  label: string
}

const props = defineProps<{
  form: PegawaiForm
  statusOptions: StatusOption[]
}>()
</script>

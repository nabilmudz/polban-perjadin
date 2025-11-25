<template>
  <div class="bg-white w-full h-auto rounded-md">
    <div class="p-8">
      <form class="grid grid-cols-1 md:grid-cols-2 gap-6" @submit.prevent="handleNext">
        <div class="space-y-4">
          <div>
            <label class="block font-medium mb-1">
              Nama Kegiatan <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.nama_kegiatan"
              type="text"
              class="w-full border rounded px-3 py-2"
              placeholder="Nama Kegiatan"
              required
            />
          </div>

          <div>
            <label class="block font-medium mb-1">
              Diajukan Kepada <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.diajukan_kepada"
              class="w-full border rounded px-3 py-2"
              required
            >
              <option value="" disabled>Pilih Wadir</option>
              <option value="wadir1">Wakil Direktur I</option>
              <option value="wadir2">Wakil Direktur II</option>
              <option value="wadir3">Wakil Direktur III</option>
              <option value="wadir4">Wakil Direktur IV</option>
            </select>
          </div>

          <div>
            <label class="block font-medium mb-1">Surat Undangan (Optional)</label>
            <input
              type="file"
              class="w-full border rounded border-gray-600"
              @change="onFileChange"
            />
          </div>

          <label class="block font-medium mb-1">
            Jenis Penyelenggara <span class="text-red-500">*</span>
          </label>
          <div class="flex gap-4">
            <label class="flex items-center gap-1">
              <input type="radio" value="polban" v-model="form.penyelenggara" required />
              Polban
            </label>
            <label class="flex items-center gap-1">
              <input type="radio" value="penyelenggara" v-model="form.penyelenggara" />
              Penyelenggara
            </label>
            <label class="flex items-center gap-1">
              <input type="radio" value="kedua" v-model="form.penyelenggara" />
              Polban &amp; Penyelenggara
            </label>
          </div>

          <div>
            <label class="block font-medium mb-1">
              Nama Penyelenggara <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.nama_penyelenggara"
              type="text"
              class="w-full border rounded px-3 py-2"
              placeholder="Nama Penyelenggara"
              required
            />
          </div>

          <div>
            <label class="block font-medium mb-1">
              Tanggal Pelaksanaan <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.tanggal"
              type="date"
              class="w-full border rounded px-3 py-2"
              required
            />
          </div>
        </div>

        <div class="space-y-4">
          <!-- Pagu -->
          <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2">
              <input type="checkbox" id="pagu" v-model="form.hasPagu" />
              <label for="pagu" class="font-medium">Pagu Desentralisasi</label>
            </div>

            <div>
              <label class="block font-medium mb-1">Nominal Pagu</label>
              <input
                v-model.number="form.nominal_pagu"
                type="number"
                class="w-full border rounded px-3 py-2"
                :disabled="!form.hasPagu"
                :class="!form.hasPagu ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'"
                placeholder="Isi nominal jika Pagu Desentralisasi diceklis"
              />
            </div>
          </div>

          <!-- Lokasi -->
          <div class="space-y-2">
            <label class="block font-medium mb-1">
              Lokasi Kegiatan <span class="text-red-500">*</span>
            </label>

            <div
              v-for="(lokasi, index) in form.lokasiList"
              :key="index"
              class="grid grid-cols-[50px_1fr_1fr] border-b"
            >
              <input
                type="text"
                :value="index + 1"
                class="border-r px-2 py-2 bg-gray-100 text-center w-full"
                disabled
              />
              <input
                v-model="lokasi.tempat"
                type="text"
                placeholder="Tempat"
                class="border-r px-3 py-2 w-full"
              />
              <input
                v-model="lokasi.alamat"
                type="text"
                placeholder="Alamat"
                class="px-3 py-2 w-full"
              />
            </div>

            <button
              type="button"
              @click="addLokasi"
              class="mt-2 px-2 py-1 border-2 border-green-600 text-green-600 rounded flex items-center gap-2 
                     hover:bg-green-600 hover:text-white transition-colors duration-200"
            >
              Tambah Lokasi
            </button>
          </div>

          <!-- Provinsi -->
          <div>
            <label class="block font-medium mb-1">
              Provinsi <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.provinsi"
              class="w-full border rounded px-3 py-2"
              required
            >
              <option value="" disabled>Pilih Provinsi</option>
              <option v-for="p in provinsiList" :key="p" :value="p">
                {{ p }}
              </option>
            </select>
          </div>

          <!-- Nomor Surat -->
          <div class="space-y-2">
            <label class="block font-medium mb-1">
              Nomor Surat Usulan <span class="text-red-500">*</span>
            </label>
            <div class="flex gap-1">
              <input
                type="text"
                placeholder="Nomor"
                class="border rounded px-2 py-1 w-20 text-center"
              />
              <span>/</span>
              <input
                type="text"
                placeholder="Nomor Pengusul"
                class="border rounded px-2 py-1 w-24 text-center"
              />
              <span>/</span>
              <input
                type="text"
                placeholder="Kode Perihal"
                class="border rounded px-2 py-1 w-20 text-center"
              />
              <span>/</span>
              <input
                type="text"
                placeholder="Tahun"
                class="border rounded px-2 py-1 w-20 text-center"
              />
            </div>
            <button
              type="button"
              class="mt-2 px-2 py-1 border-2 border-gray-500 text-gray-600 rounded 
                     hover:border-primary-default hover:text-primary-default hover:bg-primary-light/10 
                     active:bg-primary-light/30 transition-all duration-200"
            >
              Lihat Nomor Terpakai 30 Hari Terakhir
            </button>
            <textarea
              class="w-full border rounded px-3 py-2 mt-1"
              rows="3"
              disabled
              placeholder="Nomor terpakai akan muncul disini"
            ></textarea>
          </div>
        </div>

        <div class="col-span-1 md:col-span-2 flex justify-end">
          <button
            type="submit"
            class="px-6 py-2 bg-primary-default text-white font-semibold rounded hover:bg-primary-dark"
          >
            Selanjutnya
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
<script setup>

import { reactive } from 'vue'
import provinsiList from '@/utils/provinsi.js'

const props = defineProps({
  initialValue: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['next'])

const defaultForm = {
  nama_kegiatan: '',
  diajukan_kepada: '',
  penyelenggara: '',
  nama_penyelenggara: '',
  tanggal: '',
  hasPagu: false,
  nominal_pagu: '',
  provinsi: '',
  surat_undangan: null,
  lokasiList: [{ tempat: '', alamat: '' }]
}

const form = reactive({
  ...defaultForm,
  ...(props.initialValue || {}),
  lokasiList: (props.initialValue?.lokasiList?.length
    ? props.initialValue.lokasiList
    : defaultForm.lokasiList
  ).map(l => ({ ...l }))
})

const addLokasi = () => {
  form.lokasiList.push({ tempat: '', alamat: '' })
}

function onFileChange(e) {
  const file = e.target.files[0] ?? null
  form.surat_undangan = file
}

function handleNext() {
  emit('next', { ...form, lokasiList: form.lokasiList.map(l => ({ ...l })) })
}
</script>

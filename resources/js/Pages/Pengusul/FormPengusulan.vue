<template>
  <Head title="Form Pengusulan" />
  <div class="bg-white w-full h-auto rounded-md">
    <HeaderPage />
    <div class="p-8">
      <h1 class="text-3xl font-bold mb-6">Form Pengajuan</h1>

      <form class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
          <div>
            <label class="block font-medium mb-1">Nama Kegiatan <span class="text-red-500">*</span></label>
            <input type="text" class="w-full border rounded px-3 py-2" placeholder="Nama Kegiatan" required />
          </div>

          <div>
            <label class="block font-medium mb-1">Diajukan Kepada <span class="text-red-500">*</span></label>
            <select class="w-full border rounded px-3 py-2" required>
              <option value="" disabled selected>Pilih Wadir</option>
              <option value="wadir1">Wakil Direktur I</option>
              <option value="wadir2">Wakil Direktur II</option>
              <option value="wadir3">Wakil Direktur III</option>
              <option value="wadir4">Wakil Direktur IV</option>
            </select>
          </div>

          <div>
            <label class="block font-medium mb-1">Surat Undangan (Optional)</label>
            <input type="file" class="w-full border rounded border-gray-600" />
          </div>

          <label class="block font-medium mb-1">Jenis Penyelenggara <span class="text-red-500">*</span></label>
          <div class="flex gap-4">
            <label class="flex items-center gap-1">
              <input type="radio" name="penyelenggara" value="polban" required /> Polban
            </label>
            <label class="flex items-center gap-1">
              <input type="radio" name="penyelenggara" value="penyelenggara" /> Penyelenggara
            </label>
            <label class="flex items-center gap-1">
              <input type="radio" name="penyelenggara" value="kedua" /> Polban & Penyelenggara
            </label>
          </div>

          <div>
            <label class="block font-medium mb-1">Nama Penyelenggara <span class="text-red-500">*</span></label>
            <input type="text" class="w-full border rounded px-3 py-2" placeholder="Nama Penyelenggara" />
          </div>

          <div>
            <label class="block font-medium mb-1">Tanggal Pelaksanaan <span class="text-red-500">*</span></label>
            <input type="date" class="w-full border rounded px-3 py-2" />
          </div>
        </div>

        <div class="space-y-4">
          <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2">
                <input type="checkbox" id="pagu" v-model="hasPagu" />
                <label for="pagu" class="font-medium">Pagu Desentralisasi</label>
            </div>

            <div>
                <label class="block font-medium mb-1">Nominal Pagu</label>
                <input
                type="number"
                class="w-full border rounded px-3 py-2"
                :disabled="!hasPagu"
                :class="!hasPagu ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'"
                placeholder="Isi nominal jika Pagu Desentralisasi diceklis"
                />
            </div>
        </div>

          <div class="space-y-2">
            <label class="block font-medium mb-1">Lokasi Kegiatan <span class="text-red-500">*</span></label>

            <div v-for="(lokasi, index) in lokasiList" :key="index" class="grid grid-cols-[50px_1fr_1fr] border-b">
                <input type="text" :value="index + 1" class="border-r px-2 py-2 bg-gray-100 text-center w-full" disabled />
                <input v-model="lokasi.tempat" type="text" placeholder="Tempat" class="border-r px-3 py-2 w-full" />
                <input v-model="lokasi.alamat" type="text" placeholder="Alamat" class="px-3 py-2 w-full" />
            </div>
            <button
              type="button"
              @click="addLokasi"
              class="mt-2 px-2 py-1 border-2 border-green-600 text-green-600 rounded flex items-center gap-2 
                    hover:bg-green-600 hover:text-white transition-colors duration-200"
            >
              <font-awesome-icon :icon="['far', 'square-plus']" />
              Tambah Lokasi
            </button>

            </div>

            <div>
                <label class="block font-medium mb-1">Provinsi <span class="text-red-500">*</span></label>
                <select class="w-full border rounded px-3 py-2" v-model="provinsi">
                <option value="" disabled selected>Pilih Provinsi</option>
                <option v-for="p in provinsiList" :key="p" :value="p">{{ p }}</option>
                </select>
            </div>

            <div class="space-y-2">
                <label class="block font-medium mb-1">Nomor Surat Usulan <span class="text-red-500">*</span></label>
                <div class="flex gap-1">
                <input type="text" placeholder="Nomor" class="border rounded px-2 py-1 w-20 text-center" />
                <span>/</span>
                <input type="text" placeholder="Nomor Pengusul" class="border rounded px-2 py-1 w-24 text-center" />
                <span>/</span>
                <input type="text" placeholder="Kode Perihal" class="border rounded px-2 py-1 w-20 text-center" />
                <span>/</span>
                <input type="text" placeholder="Tahun" class="border rounded px-2 py-1 w-20 text-center" />
                </div>
                <button
                  type="button"
                  class="mt-2 px-2 py-1 border-2 border-gray-500 text-gray-600 rounded 
                        hover:border-primary-default hover:text-primary-default hover:bg-primary-light/10 
                        active:bg-primary-light/30 transition-all duration-200"
                >
                  Lihat Nomor Terpakai 30 Hari Terakhir
                </button>
                <textarea class="w-full border rounded px-3 py-2 mt-1" rows="3" disabled placeholder="Nomor terpakai akan muncul disini"></textarea>
            </div>
        </div>
        <div class="col-span-1 md:col-span-2 flex justify-end">
        <button 
          type="button"
          @click="handleNext"
          class="px-6 py-2 bg-primary-default text-white font-semibold rounded hover:bg-primary-dark"
        >
          Selanjutnya
        </button>
        </div>

      </form>
    </div>
  </div>
  <FormPersonel
    :show="showDataPersonel"
    @close="showDataPersonel = false"
  />

</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import HeaderPage from '@/Components/HeaderPage.vue'
import { reactive, ref } from 'vue'
import provinsiList from '@/utils/provinsi.js'
import FormPersonel from './FormPersonel.vue'

const lokasiList = reactive([
  { tempat: '', alamat: '' }
])
const addLokasi = () => {
  lokasiList.push({ tempat: '', alamat: '' })
}
const showDataPersonel = ref(false)

const handleNext = () => {
  showDataPersonel.value = true
}

const hasPagu = ref(false)
const provinsi = ref('')
</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
}
</script>

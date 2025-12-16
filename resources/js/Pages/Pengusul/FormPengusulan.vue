<template>
  <div class="bg-white w-full h-auto rounded-md">
    <div class="p-8">
      <div
        v-if="showRevisionNote"
        class="mb-6 w-full rounded-md border border-red-300 bg-red-50 p-4"
      >
        <div class="flex items-start gap-3">
          <div class="mt-0.5 h-2.5 w-2.5 rounded-full bg-red-500"></div>

          <div class="flex-1">
            <p class="text-sm font-semibold text-red-700">
              Catatan Revisi
            </p>
            <p class="mt-1 text-sm text-red-700 whitespace-pre-line">
              {{ revisionNote }}
            </p>
          </div>
        </div>
      </div>
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
              Tanggal<span class="text-red-500">*</span>
            </label>

            <span class="text-gray-600 text-sm">Dari</span>
            <input
              v-model="form.tanggal_berangkat"
              type="date"
              class="w-full border rounded px-3 py-2 mb-2"
              required
            />

            <span class="text-gray-600 text-sm">Sampai</span>
            <input
              v-model="form.tanggal_kembali"
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
                v-model.number="form.nomor_urutan_surat"
                type="number"
                placeholder="Nomor"
                class="border rounded px-2 py-1 w-20 text-center"
                min="1"
              />
              <span>/</span>
              <input
                v-model="form.kode_pengusul"
                type="text"
                class="border rounded px-2 py-1 w-32 text-center bg-gray-100 cursor-not-allowed"
                disabled
              />
              <span>/</span>
              <input
                v-model="form.kode_perihal"
                type="text"
                class="border rounded px-2 py-1 w-28 text-center"
                placeholder="Kode Perihal"
              />
              <span>/</span>
              <input
                v-model="form.tahun_nomor_surat"
                type="text"
                class="border rounded px-2 py-1 w-20 text-center bg-gray-100 cursor-not-allowed"
                disabled
              />
            </div>
            <button
              type="button"
              @click="fetchUsedNumbers"
              :disabled="usedNumbersLoading"
              class="mt-2 px-2 py-1 border-2 border-gray-500 text-gray-600 rounded
                    hover:border-primary-default hover:text-primary-default hover:bg-primary-light/10
                    active:bg-primary-light/30 transition-all duration-200 disabled:opacity-60"
            >
              {{ usedNumbersLoading ? 'Memuat...' : 'Lihat Nomor Terpakai 30 Hari Terakhir' }}
            </button>

            <p v-if="usedNumbersError" class="text-sm text-red-600 mt-2">
              {{ usedNumbersError }}
            </p>

            <textarea
              class="w-full border rounded px-3 py-2 mt-1"
              rows="6"
              disabled
              :value="usedNumbersText"
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
import provinsiList from '@/utils/provinsi.js'
import axios from 'axios'
import { ref, computed, reactive, watch } from 'vue'

const usedNumbersText = ref('')
const usedNumbersLoading = ref(false)
const usedNumbersError = ref('')

async function fetchUsedNumbers() {
  usedNumbersLoading.value = true
  usedNumbersError.value = ''
  usedNumbersText.value = ''

  try {
    const { data } = await axios.get('/pengusul/nomor-terpakai', {
      params: {
        days: 30,
        tahun: form.tahun_nomor_surat,
        kode_perihal: form.kode_perihal,
      },
    })

    const list = data?.data ?? []
    if (!list.length) {
      usedNumbersText.value = 'Tidak ada nomor terpakai dalam 30 hari terakhir.'
      return
    }

    usedNumbersText.value = list
      .map((x, i) => `${i + 1}. ${x.nomor} (${x.status}) - ${x.tanggal}`)
      .join('\n')
  } catch (e) {
    usedNumbersError.value = 'Gagal mengambil data nomor terpakai. Coba lagi.'
  } finally {
    usedNumbersLoading.value = false
  }
}

const props = defineProps({
  initialValue: { type: Object, default: () => ({}) },
  currentUser: Object,
})

const emit = defineEmits(['next'])

const currentYear = new Date().getFullYear()
const defaultForm = {
  nama_kegiatan: '',
  diajukan_kepada: '',
  penyelenggara: '',
  nama_penyelenggara: '',
  tanggal_berangkat: '',
  tanggal_kembali: '',
  hasPagu: false,
  nominal_pagu: '',
  provinsi: '',
  surat_undangan: null,
  kode_pengusul: '',
  tahun_nomor_surat: currentYear,
  nomor_urutan_surat: null,
  kode_perihal: '',
  lokasiList: [{ tempat: '', alamat: '' }],
}


const form = reactive({
  ...defaultForm,
  ...Object.fromEntries(
    Object.entries(props.initialValue || {})
      .filter(([key]) => key in defaultForm)
  ),
  lokasiList: (props.initialValue?.lokasiList?.length
    ? props.initialValue.lokasiList
    : defaultForm.lokasiList
  ).map(l => ({ ...l })),
  kode_pengusul: props.currentUser?.kode_pengusul ?? '',
  tahun_nomor_surat: props.initialValue?.tahun_nomor_surat ?? currentYear,
})

watch(
  () => props.currentUser?.kode_pengusul,
  (val) => {
    form.kode_pengusul = val ?? ''
  },
  { immediate: true }
)

const addLokasi = () => {
  form.lokasiList.push({ tempat: '', alamat: '' })
}

function onFileChange(e) {
  form.surat_undangan = e.target.files?.[0] ?? null
}

function handleNext() {
  emit('next', { ...form, lokasiList: form.lokasiList.map(l => ({ ...l })) })
}

const revisionNote = computed(() => {
  const raw = props.initialValue?.catatan_revisi
  if (raw === null || raw === undefined) return ''
  const text = String(raw).trim()
  return text
})

const showRevisionNote = computed(() => revisionNote.value.length > 0)
</script>

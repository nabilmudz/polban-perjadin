<template>
  <Head title="Form Pengusulan" />

  <div class="bg-white w-full rounded-md shadow">
    <HeaderPage />

    <div class="p-8">
      <h1 class="text-3xl font-bold mb-4">Form Pengusulan</h1>

      <div class="mb-6 flex gap-3 text-sm font-bold">
        <div :class="currentStep === 1 ? 'text-primary-default' : 'text-gray-500'">Pengusulan</div>
        <p>></p>
        <div :class="currentStep === 2 ? 'text-primary-default' : 'text-gray-500'">Personel</div>
        <p>></p>
        <div :class="currentStep === 3 ? 'text-primary-default' : 'text-gray-500'">Submit</div>
      </div>

      <FormPengusulan
        v-if="currentStep === 1"
        :initial-value="form.pengusulan"
        @next="handlePengusulanNext"
      />

      <FormPersonel
        v-else-if="currentStep === 2"
        :value="form.personel"
        @prev="() => goToStep(1)"
        @next="handlePersonelNext"
      />

      <div v-else-if="currentStep === 3" class="space-y-6 p-2">
        <h2 class="text-2xl font-semibold">Preview Surat Tugas</h2>
        <p class="text-sm text-gray-600">
          Periksa kembali isi surat sebelum dikirim. Jika ada yang salah, klik "Kembali" dan perbaiki.
        </p>

        <div class="border rounded-lg overflow-hidden">
            <pre class="text-xs">{{ JSON.stringify(personnelList, null, 2) }}</pre>
          <LaporanSurat :surat="previewSurat" />
        </div>

        <div class="mt-4 flex justify-end gap-3">
          <button @click="goToStep(2)" class="px-3 py-2 border rounded">
            Kembali
          </button>
          <button
            @click="submitFinal"
            class="px-4 py-2 bg-primary-default text-white rounded disabled:opacity-50"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Submitting...' : 'Submit' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'

import HeaderPage from '@/Components/HeaderPage.vue'
import FormPengusulan from './FormPengusulan.vue'
import FormPersonel from './FormPersonel.vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'

const currentStep = ref(1)

const form = useForm({
  pengusulan: {
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
  },
  personel: []
})

function goToStep(step) {
  currentStep.value = step
}

function handlePengusulanNext(payload) {
  form.pengusulan = payload
  goToStep(2)
}

function handlePersonelNext(payload) {
    form.personel = payload
    console.log("payload: ",payload)
    goToStep(3)
}
const previewSurat = computed(() => {
    const p = form.pengusulan || {}
    const personel = form.personel || []
    
    return {
        template_nama_kementerian: null,
        
        nomor_surat_tugas_resmi: null,
        perihal_tugas: p.nama_kegiatan || '',
        nama_penyelenggara: p.nama_penyelenggara || '',
        
        tanggal_berangkat: p.tanggal || null,
        tanggal_kembali: p.tanggal || null,
        lokasi_kegiatan: p.lokasiList || [],
        
        template_tembusan: ['-'],
        template_nama_direktur: null,
        template_nip_direktur: null,
        tanggal_persetujuan_direktur: p.tanggal || null,
        direktur_signature_data: null,
        
        personel: personel.map(pr => ({
            id: pr.id,
            type: pr.type,
            nama: pr.nama,
            nip: pr.nip ?? null,
            nim: pr.nim ?? null,
            pangkat: pr.pangkat ?? null,
            golongan: pr.golongan ?? null,
            jabatan: pr.jabatan ?? null,
            jurusan: pr.jurusan ?? null,
            prodi: pr.prodi ?? null,
        })),
    }
})

function submitFinal() {
  form.post(route('pengusul.pengusulan.submit'), {
    forceFormData: true
  })
}
</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'

export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
}
</script>
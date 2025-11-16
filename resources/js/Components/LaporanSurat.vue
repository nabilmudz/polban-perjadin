<template>
  <div class="p-6 flex justify-center overflow-auto min-h-screen">
    <div class="bg-white w-[21cm] min-h-[29.7cm] p-[2.5cm] border border-gray-300 shadow">
      <!-- Header -->
      <table class="w-full border-collapse">
        <tr>
          <td class="align-top w-[90px]">
            <img src="/images/polban.png" alt="Logo" class="w-[80px]" />
          </td>
          <td class="text-center">
            <h1 class="font-bold text-[14pt] uppercase leading-tight m-0">
              {{ surat.template_nama_kementerian || 'KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET DAN TEKNOLOGI' }}
            </h1>
            <h2 class="font-bold text-[12pt] uppercase leading-tight mt-[3px] mb-0">
              POLITEKNIK NEGERI BANDUNG
            </h2>
            <p class="text-[9.5pt] leading-snug mt-[2px] mb-0">
              Jalan Gegerkalong Hilir, Desa Ciwaruga, Bandung 40012<br />
              Telepon: (022) 2013789, Faksimile: (022) 2013889<br />
              Laman: www.polban.ac.id, Pos Elektronik: polban@polban.ac.id
            </p>
          </td>
        </tr>
      </table>

      <hr class="border-t-[1.5px] border-black my-3" />

      <!-- Title -->
      <div class="text-center mt-2">
        <h3 class="text-[13pt] font-bold underline uppercase m-0">SURAT TUGAS</h3>
        <p class="text-[11pt] mt-[2px]">Nomor: {{ surat.nomor_surat_tugas_resmi || '-' }}</p>
      </div>

      <!-- Body -->
      <div class="mt-4 text-[11pt] leading-[1.6]">
        <p>
          Untuk mengikuti kegiatan <strong>{{ surat.perihal_tugas }}</strong>,
          diselenggarakan oleh <strong>{{ surat.nama_penyelenggara }}</strong> pada:
        </p>

        <table class="w-full border-collapse">
          <tr>
            <td class="w-[160px]">Hari/Tanggal</td>
            <td class="w-[10px]">:</td>
            <td>
              {{ formatDate(surat.tanggal_berangkat) }} s.d. {{ formatDate(surat.tanggal_kembali) }}
            </td>
          </tr>
          <tr>
            <td class="align-top">Tempat Kegiatan</td>
            <td class="align-top">:</td>
            <td>
              <ol class="list-decimal pl-5 m-0">
                <li v-for="(lokasi, i) in surat.lokasi_kegiatan" :key="i" class="mb-1">
                  <strong>{{ lokasi.tempat }}</strong><br />
                  <span v-html="lokasi.alamat"></span>
                </li>
              </ol>
            </td>
          </tr>
        </table>

        <p class="mt-4">
          Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.
        </p>
      </div>

      <!-- Footer -->
      <table class="w-full mt-6">
        <tr>
          <td class="align-top text-[10pt] w-1/2">
            <p class="mb-1">Tembusan:</p>
            <ol class="pl-5 m-0">
              <li v-for="(t, i) in surat.template_tembusan" :key="i">{{ t }}</li>
            </ol>
          </td>
          <td class="align-top text-left w-1/2">
            <p>Bandung, {{ formatDate(surat.tanggal_persetujuan_direktur) }}</p>
            <p class="mb-1">Direktur,</p>
            <div class="relative h-[60px]">
              <img
                v-if="surat.direktur_signature_data"
                :src="surat.direktur_signature_data"
                class="absolute top-[-10px] left-0 w-[100px] h-[60px]"
              />
            </div>
            <p class="font-bold m-0">{{ surat.template_nama_direktur || '[Nama Direktur]' }}</p>
            <p class="m-0">NIP {{ surat.template_nip_direktur || '[NIP Direktur]' }}</p>
          </td>
        </tr>
      </table>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'
import dayjs from 'dayjs'
import 'dayjs/locale/id'
dayjs.locale('id')

const props = defineProps({
  surat: { type: Object, default: () => ({}) },
})

function formatDate(dateStr) {
  return dateStr ? dayjs(dateStr).format('D MMMM YYYY') : '-'
}
</script>

<style scoped>
* {
  font-family: 'Times New Roman', Times, serif;
}
</style>

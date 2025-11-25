<template>
  <div class="p-6 flex justify-center overflow-auto min-h-screen bg-gray-100">
    <div class="space-y-6">
      <div class="bg-white w-[21cm] min-h-[29.7cm] p-[2.5cm] border border-gray-300 shadow page">
        <table class="w-full border-collapse surat-tugas-header">
          <tr>
            <td class="align-top w-[90px] logo-cell">
              <img src="/images/polban.png" alt="Logo" class="w-[80px]" />
            </td>
            <td class="text-center text-cell">
              <div class="surat-tugas-header-text">
                <h1 class="font-bold text-[14pt] uppercase leading-tight m-0">
                  {{ surat.template_nama_kementerian || 'KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET DAN TEKNOLOGI' }}
                </h1>
                <h2 class="font-bold text-[12pt] uppercase leading-tight mt-[3px] mb-0">
                  POLITEKNIK NEGERI BANDUNG
                </h2>
                <p class="text-[9.5pt] leading-snug mt-[2px] mb-0">
                  Jalan Gegerkalong Hilir, Desa Ciwaruga, Bandung 40012, Kotak Pos 1234<br />
                  Telepon: (022) 2013789, Faksimile: (022) 2013889<br />
                  Laman: www.polban.ac.id, Pos Elektronik: polban@polban.ac.id
                </p>
              </div>
            </td>
          </tr>
        </table>

        <hr class="border-t-[1.5px] border-black my-3 header-line" />

        <div class="text-center mt-2 surat-tugas-title-wrapper">
          <h3 class="text-[13pt] font-bold underline uppercase m-0">SURAT TUGAS</h3>
          <p class="text-[11pt] mt-[2px] nomor">
            Nomor: {{ surat.nomor_surat_tugas_resmi || '-' }}
          </p>
        </div>

        <div class="mt-4 text-[11pt] leading-[1.6] surat-tugas-content">
          <div v-if="personnelList.length">
            <p class="mb-2">Menugaskan kepada:</p>

            <template v-if="!isLampiran">
              <div v-if="pegawaiList.length" class="mb-4">
                <p class="font-semibold mb-1">Pegawai</p>
                <table class="w-full table-bordered mb-2">
                  <thead>
                    <tr>
                      <th class="w-[30px]">No</th>
                      <th>Nama</th>
                      <th style="width: 120px;">NIP</th>
                      <th style="width: 120px;">Pangkat</th>
                      <th style="width: 100px;">Golongan</th>
                      <th style="width: 160px;">Jabatan</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(p, idx) in pegawaiList" :key="p.id || `peg-main-${idx}`">
                      <td class="text-center">{{ idx + 1 }}</td>
                      <td>{{ p.nama }}</td>
                      <td>{{ p.nip || '-' }}</td>
                      <td>{{ p.pangkat || '-' }}</td>
                      <td>{{ p.golongan || '-' }}</td>
                      <td>{{ p.jabatan || '-' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div v-if="mahasiswaList.length">
                <p class="font-semibold mb-1">Mahasiswa</p>
                <table class="w-full table-bordered">
                  <thead>
                    <tr>
                      <th class="w-[30px]">No</th>
                      <th>Nama</th>
                      <th style="width: 120px;">NIM</th>
                      <th style="width: 160px;">Jurusan</th>
                      <th style="width: 160px;">Prodi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(m, idx) in mahasiswaList" :key="m.id || `mhs-main-${idx}`">
                      <td class="text-center">{{ idx + 1 }}</td>
                      <td>{{ m.nama }}</td>
                      <td>{{ m.nim || '-' }}</td>
                      <td>{{ m.jurusan || '-' }}</td>
                      <td>{{ m.prodi || '-' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </template>

            <template v-else>
              <p class="mt-1 mb-3">
                Daftar lengkap personel terlampir pada Lampiran Surat Tugas.
              </p>
            </template>
          </div>

          <p>
            Untuk mengikuti kegiatan <strong>{{ surat.perihal_tugas }}</strong>,
            diselenggarakan oleh <strong>{{ surat.nama_penyelenggara }}</strong> pada:
          </p>

          <table class="w-full border-collapse detail-table">
            <tr>
              <td class="w-[160px] detail-label">Hari/Tanggal</td>
              <td class="w-[10px] detail-separator">:</td>
              <td>
                <span v-if="surat.tanggal_berangkat && surat.tanggal_kembali">
                  <span v-if="isSameDay(surat.tanggal_berangkat, surat.tanggal_kembali)">
                    {{ formatDateFull(surat.tanggal_berangkat) }}
                  </span>
                  <span v-else>
                    {{ formatDateFull(surat.tanggal_berangkat) }}
                    s.d.
                    {{ formatDateFull(surat.tanggal_kembali) }}
                  </span>
                </span>
                <span v-else>
                  {{ formatDate(surat.tanggal_berangkat) }}
                  s.d.
                  {{ formatDate(surat.tanggal_kembali) }}
                </span>
              </td>
            </tr>
            <tr>
              <td class="align-top detail-label">Tempat Kegiatan</td>
              <td class="align-top detail-separator">:</td>
              <td>
                <ol class="list-decimal pl-5 m-0">
                  <li v-for="(lokasi, i) in lokasiList" :key="i" class="mb-1">
                    <strong>{{ lokasi.tempat }}</strong><br />
                    <span v-html="formatAlamat(lokasi.alamat)"></span>
                  </li>
                </ol>
              </td>
            </tr>
          </table>
          <p class="mt-4">
            Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.
          </p>
        </div>

        <table class="w-full mt-24 footer-table">
          <tr>
            <td colspan="2" class="text-[11pt] text-right">
              Bandung,
              {{ formatDateFull(surat.tanggal_persetujuan_direktur || surat.tanggal_berangkat) }}
            </td>
          </tr>

          <tr>
            <td class="align-top text-[10pt] w-1/2 tembusan-cell">
              <template v-if="(surat.template_tembusan || []).length">
                <p class="mb-1">Tembusan:</p>
                <ol class="pl-5 m-0 tembusan-list">
                  <li v-for="(t, i) in surat.template_tembusan" :key="i">
                    {{ t }}
                  </li>
                </ol>
              </template>
            </td>

            <td class="align-top w-1/2 signature-cell text-[11pt]">
              <div class="signature-cell-inner">
                <p class="mb-1">Direktur,</p>
                <div class="relative h-[60px] mt-1 signature-block">
                  <img
                    v-if="surat.direktur_signature_data"
                    :src="surat.direktur_signature_data"
                    class="absolute top-[-10px] right-0 w-[100px] h-[60px]"
                  />
                </div>
                <p class="font-bold m-0 mt-2">
                  {{ surat.template_nama_direktur || '[Nama Direktur]' }}
                </p>
                <p class="m-0">
                  NIP {{ surat.template_nip_direktur || '[NIP Direktur]' }}
                </p>
              </div>
            </td>
          </tr>
        </table>

      </div>

      <template v-if="isLampiran && personnelList.length">
        <div
          v-if="!needChunking"
          class="bg-white w-[21cm] min-h-[29.7cm] p-[2.5cm] border border-gray-300 shadow page page-break"
        >
          <div class="surat-tugas-content text-[11pt] leading-[1.6]">
            <p class="mb-3">
              Lampiran: {{ surat.nomor_surat_tugas_resmi || '-' }}
            </p>

            <div v-if="pegawaiList.length">
              <p><strong>1. Pegawai</strong></p>
              <table class="attachment-table">
                <thead>
                  <tr>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Pangkat</th>
                    <th>Golongan</th>
                    <th>Jabatan</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(p, idx) in pegawaiList" :key="'peg-lamp-single-' + idx">
                    <td>{{ p.nama }}</td>
                    <td>{{ p.nip || '-' }}</td>
                    <td>{{ p.pangkat || '-' }}</td>
                    <td>{{ p.golongan || '-' }}</td>
                    <td>{{ p.jabatan || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-if="mahasiswaList.length" class="mt-6">
              <p><strong>2. Mahasiswa</strong></p>
              <table class="attachment-table">
                <thead>
                  <tr>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Jurusan</th>
                    <th>Prodi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(m, idx) in mahasiswaList" :key="'mhs-lamp-single-' + idx">
                    <td>{{ m.nama }}</td>
                    <td>{{ m.nim || '-' }}</td>
                    <td>{{ m.jurusan || '-' }}</td>
                    <td>{{ m.prodi || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="mt-10">
              <table class="w-full footer-table">
                <tr>
                  <td colspan="2" class="text-[11pt] text-right">
                    Bandung,
                    {{ formatDateFull(surat.tanggal_persetujuan_direktur || surat.tanggal_berangkat) }}
                  </td>
                </tr>
                <tr>
                  <td class="w-1/2"></td>
                  <td class="signature-cell text-[11pt]">
                    <div class="signature-cell-inner">
                      <p class="mb-1">Direktur,</p>
                      <div class="relative h-[60px] mt-1 signature-block">
                        <img
                          v-if="surat.direktur_signature_data"
                          :src="surat.direktur_signature_data"
                          class="absolute top-[-10px] right-0 w-[100px] h-[60px]"
                        />
                      </div>
                      <p class="m-0 font-bold mt-2">
                        {{ surat.template_nama_direktur || '[Nama Direktur]' }}
                      </p>
                      <p class="m-0">
                        NIP {{ surat.template_nip_direktur || '[NIP Direktur]' }}
                      </p>
                    </div>
                  </td>
                </tr>
              </table>
            </div>
          </div>
        </div>

        <template v-else>
          <div
            v-for="(chunk, pageIndex) in personnelChunks"
            :key="pageIndex"
            class="bg-white w-[21cm] min-h-[29.7cm] p-[2.5cm] border border-gray-300 shadow page page-break"
          >
            <div class="surat-tugas-content text-[11pt] leading-[1.6]">
              <p class="mb-3">
                Lampiran: {{ surat.nomor_surat_tugas_resmi || '-' }}
              </p>

              <div v-if="chunkPegawai(chunk).length">
                <p><strong>1. Pegawai</strong></p>
                <table class="attachment-table">
                  <thead>
                    <tr>
                      <th>Nama</th>
                      <th>NIP</th>
                      <th>Pangkat</th>
                      <th>Golongan</th>
                      <th>Jabatan</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(p, idx) in chunkPegawai(chunk)"
                      :key="'peg-lamp-' + pageIndex + '-' + idx"
                    >
                      <td>{{ p.nama }}</td>
                      <td>{{ p.nip || '-' }}</td>
                      <td>{{ p.pangkat || '-' }}</td>
                      <td>{{ p.golongan || '-' }}</td>
                      <td>{{ p.jabatan || '-' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div v-if="chunkMahasiswa(chunk).length" class="mt-6">
                <p><strong>2. Mahasiswa</strong></p>
                <table class="attachment-table">
                  <thead>
                    <tr>
                      <th>Nama</th>
                      <th>NIM</th>
                      <th>Jurusan</th>
                      <th>Prodi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(m, idx) in chunkMahasiswa(chunk)"
                      :key="'mhs-lamp-' + pageIndex + '-' + idx"
                    >
                      <td>{{ m.nama }}</td>
                      <td>{{ m.nim || '-' }}</td>
                      <td>{{ m.jurusan || '-' }}</td>
                      <td>{{ m.prodi || '-' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div v-if="pageIndex === personnelChunks.length - 1" class="mt-10">
                <table class="w-full footer-table">
                  <tr>
                    <td class="w-1/2"></td>
                    <td class="signature-cell text-[11pt]">
                      <div class="signature-cell-inner">
                        <p>
                          Bandung,
                          {{ formatDateFull(surat.tanggal_persetujuan_direktur || surat.tanggal_berangkat) }}
                        </p>
                        <p class="mb-1">Direktur,</p>
                        <div class="relative h-[60px] mt-1 signature-block">
                          <img
                            v-if="surat.direktur_signature_data"
                            :src="surat.direktur_signature_data"
                            class="absolute top-[-10px] right-0 w-[100px] h-[60px]"
                          />
                        </div>
                        <p class="m-0 font-bold mt-2">
                          {{ surat.template_nama_direktur || '[Nama Direktur]' }}
                        </p>
                        <p class="m-0">
                          NIP {{ surat.template_nip_direktur || '[NIP Direktur]' }}
                        </p>
                      </div>
                    </td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
        </template>
      </template>
    </div>
  </div>
</template>

<script setup>
import dayjs from 'dayjs'
import 'dayjs/locale/id'
import { computed } from 'vue'
dayjs.locale('id')

const props = defineProps({
  surat: { type: Object, default: () => ({}) }
})
const rawPersonnel = computed(() => {
  const s = props.surat || {}

  if (Array.isArray(s.personel)) {
    return s.personel
  }

  if (Array.isArray(s.detailPelaksanaTugas)) {
    return s.detailPelaksanaTugas.map((d) => {
      const p = d.personable || {}
      const isMhs = (d.personable_type || '').includes('Mahasiswa')

      return {
        id: p.id ?? d.id,
        type: isMhs ? 'mahasiswa' : 'pegawai',
        nama: p.nama || '',
        nip: p.nip || '',
        nim: p.nim || '',
        pangkat: p.pangkat || '',
        golongan: p.golongan || '',
        jabatan: p.jabatan || '',
        jurusan: p.jurusan || '',
        prodi: p.prodi || '',
      }
    })
  }

  return []
})

const personnelList = computed(() =>
  rawPersonnel.value.map((p) => {
    if (p.type === 'pegawai' || p.type === 'mahasiswa') return p

    const isMhs = !!p.nim && !p.nip

    if (isMhs) {
      return {
        id: p.id,
        type: 'mahasiswa',
        nama: p.nama,
        nim: p.nim ?? '',
        jurusan: p.jurusan ?? '',
        prodi: p.prodi ?? '',
      }
    }

    return {
      id: p.id,
      type: 'pegawai',
      nama: p.nama,
      nip: p.nip ?? '',
      pangkat: p.pangkat ?? '',
      golongan: p.golongan ?? '',
      jabatan: p.jabatan ?? '',
    }
  })
)

const pegawaiList = computed(() =>
  personnelList.value.filter(isPegawai)
)

const mahasiswaList = computed(() =>
  personnelList.value.filter(isMahasiswa)
)

function isMahasiswa(p) {
  if (p.type === 'mahasiswa') return true
  if (p.type === 'pegawai') return false

  return !!(p.nim || p.jurusan || p.prodi)
}

function isPegawai(p) {
  if (p.type === 'pegawai') return true
  if (p.type === 'mahasiswa') return false
  if (p.nip) return true

  return !isMahasiswa(p)
}

const lokasiList = computed(
  () => props.surat.lokasi_kegiatan || props.surat.lokasiList || []
)
const isLampiran = computed(() => {
  const p = personnelList.value.length
  const l = lokasiList.value.length
  return p > 3 || (p > 2 && l > 1)
})

const itemsPerPage = 9
const needChunking = computed(() => personnelList.value.length > itemsPerPage)

const personnelChunks = computed(() => {
  const data = personnelList.value
  const chunks = []
  for (let i = 0; i < data.length; i += itemsPerPage) {
    chunks.push(data.slice(i, i + itemsPerPage))
  }
  return chunks
})

const chunkPegawai = (chunk) => chunk.filter((p) => p.type === 'pegawai')
const chunkMahasiswa = (chunk) => chunk.filter((p) => p.type === 'mahasiswa')

function formatDate(dateStr) {
  return dateStr ? dayjs(dateStr).format('D MMMM YYYY') : '-'
}
function formatDateFull(dateStr) {
  return dateStr ? dayjs(dateStr).format('D MMMM YYYY') : '-'
}
function isSameDay(a, b) {
  if (!a || !b) return false
  return dayjs(a).isSame(dayjs(b), 'day')
}
function formatAlamat(alamat) {
  if (!alamat) return ''
  return String(alamat).replace(/\n/g, '<br>')
}
</script>

<style scoped>
* {
  font-family: 'Times New Roman', Times, serif;
}

.surat-tugas-header .logo-cell {
  vertical-align: top;
}
.surat-tugas-header img {
  width: 80px;
}
.surat-tugas-header-text h1 {
  font-size: 14pt;
  font-weight: bold;
  margin: 0;
  line-height: 1.1;
  text-transform: uppercase;
}
.surat-tugas-header-text h2 {
  font-size: 12pt;
  font-weight: bold;
  margin: 3px 0 0;
  line-height: 1.1;
  text-transform: uppercase;
}
.surat-tugas-header-text p {
  font-size: 9.5pt;
  margin: 1px 0;
  line-height: 1.4;
}
.header-line {
  margin-top: 5px;
  margin-bottom: 20px;
}
.surat-tugas-title-wrapper h3 {
  font-size: 13pt;
  font-weight: bold;
  margin: 0;
  text-transform: uppercase;
  text-decoration: underline;
}
.surat-tugas-title-wrapper .nomor {
  font-size: 11pt;
  margin-top: 2px;
}
.detail-table td {
  padding: 1px 0;
  vertical-align: top;
}
.detail-label {
  width: 160px;
}
.detail-separator {
  width: 10px;
}
.footer-table .tembusan-cell {
  vertical-align: bottom;
  font-size: 10pt;
}
.footer-table .signature-cell {
  vertical-align: bottom;
}
.tembusan-list {
  padding-left: 20px;
  margin: 0;
}
.signature-block {
  position: relative;
  height: 60px;
}.signature-cell {
  text-align: right;
  padding-right: 0;
}

.signature-cell-inner {
  margin-left: auto;
  width: 175px;
  text-align: left;
}

.table-bordered {
  border: 1px solid #000;
  border-collapse: collapse;
}
.table-bordered th,
.table-bordered td {
  border: 1px solid #000;
  padding: 5px;
}
.table-bordered th {
  background-color: #f2f2f2;
  text-align: center;
}

.attachment-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
  font-size: 11pt;
}
.attachment-table th,
.attachment-table td {
  border: 1px solid #000;
  padding: 5px;
  vertical-align: top;
}
.attachment-table th {
  background-color: #f2f2f2;
  text-align: center;
}

.page + .page {
  margin-top: 24px;
}
.page-break {
  page-break-before: always;
}
</style>

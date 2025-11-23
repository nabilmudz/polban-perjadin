<template>
  <div class="bg-white p-10 shadow-md border border-gray-300 text-black font-serif text-[12pt] leading-relaxed max-w-[210mm] mx-auto h-auto min-h-[297mm]">
    
    <div class="flex items-center border-b-4 border-double border-black pb-4 mb-6">
        <div class="w-1/6 flex justify-center">
             <div class="h-20 w-20 bg-gray-200 rounded-full flex items-center justify-center text-xs text-center">Logo</div>
        </div>
        <div class="w-5/6 text-center">
            <h2 class="text-[14pt] font-bold uppercase">KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</h2>
            <h1 class="text-[16pt] font-bold uppercase tracking-wider">POLITEKNIK NEGERI BANDUNG</h1>
            <p class="text-[10pt]">Jalan Gegerkalong Hilir, Ds. Ciwaruga Kotak Pos 1234 Bandung 40012</p>
            <p class="text-[10pt]">Telepon (022) 2013789, Fax. (022) 2013889 | Laman: www.polban.ac.id</p>
        </div>
    </div>

    <div class="text-center mb-8">
        <h3 class="font-bold underline uppercase text-[14pt]">SURAT TUGAS</h3>
        <p class="font-medium">Nomor: {{ surat.nomor_surat_resmi || '......./PL1.R1/......./20...' }}</p>
    </div>

    <div class="mb-6 text-justify">
        <p>Direktur Politeknik Negeri Bandung dengan ini menugaskan kepada:</p>
    </div>

    <div class="ml-8 mb-6">
        <table class="w-full">
            <tr>
                <td class="align-top w-[150px]">Nama</td>
                <td class="align-top w-[20px]">:</td>
                <td class="align-top font-bold">{{ surat.pengusul?.name || 'Nama Tidak Ditemukan' }}</td>
            </tr>
            <tr>
                <td class="align-top">NIP / NIM</td>
                <td class="align-top">:</td>
                <td class="align-top">{{ surat.pengusul?.nip || surat.pengusul?.nim || '-' }}</td>
            </tr>
            <tr>
                <td class="align-top">Jabatan / Unit</td>
                <td class="align-top">:</td>
                <td class="align-top">{{ surat.pengusul?.unit_kerja || 'Politeknik Negeri Bandung' }}</td>
            </tr>
        </table>
    </div>

    <div class="mb-6 text-justify">
        <p class="mb-2">
            Untuk melaksanakan tugas sebagai peserta/pemateri dalam kegiatan:
        </p>
        <div class="ml-4 border-l-2 border-gray-200 pl-4 italic bg-gray-50 p-2 rounded">
            <strong>"{{ surat.nama_kegiatan }}"</strong>
        </div>
        
        <p class="mt-4">Yang akan diselenggarakan oleh <strong>{{ surat.nama_penyelenggara || 'Panitia Kegiatan' }}</strong> pada:</p>

        <table class="w-full mt-2 ml-4">
            <tr>
                <td class="align-top w-[150px]">Hari, Tanggal</td>
                <td class="align-top w-[20px]">:</td>
                <td class="align-top">{{ formatTanggal(surat.tanggal_pelaksanaan) }}</td>
            </tr>
            <tr>
                <td class="align-top">Lokasi</td>
                <td class="align-top">:</td>
                <td class="align-top">
                    <div v-if="parsedLokasi.length > 0">
                        <ul class="list-disc ml-4">
                            <li v-for="(loc, index) in parsedLokasi" :key="index">
                                {{ loc.tempat }} <span v-if="loc.alamat">({{ loc.alamat }})</span>
                            </li>
                        </ul>
                    </div>
                    <span v-else>{{ surat.tempat_pelaksanaan || 'Tempat Belum Diatur' }}</span>
                    <div class="mt-1 font-semibold text-sm text-gray-600">Provinsi: {{ surat.provinsi || '-' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="mb-6 text-justify">
        <p>
            Segala biaya yang timbul akibat diterbitkannya surat tugas ini dibebankan pada anggaran: 
            <strong>{{ surat.sumber_dana || 'DIPA Politeknik Negeri Bandung' }}</strong>
            <span v-if="surat.nominal_pagu"> sebesar Rp {{ formatCurrency(surat.nominal_pagu) }}</span>.
        </p>
    </div>

    <div class="mb-12 text-justify">
        <p>
            Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab dan 
            setelah selesai harap segera melaporkan hasilnya.
        </p>
    </div>

    <div class="flex justify-end">
        <div class="w-[300px]">
            <p>Bandung, {{ formatTanggal(new Date()) }}</p>
            <p>Direktur,</p>
            
            <div class="h-24 flex items-end">
                <span v-if="surat.status_surat === 'approved'" class="text-green-600 font-bold border-2 border-green-600 px-2 py-1 transform -rotate-12 opacity-70">
                    SIGNED DIGITALLY
                </span>
            </div>

            <p class="font-bold underline mt-2">Marwansyah, S.E., M.Si., Ph.D.</p>
            <p>NIP. 19781021 200501 1 001</p>
        </div>
    </div>

    <div class="mt-12 text-[10pt]">
        <p class="font-bold underline">Tembusan:</p>
        <ol class="list-decimal ml-5">
            <li>Para Wakil Direktur</li>
            <li>Ka. Bagian Administrasi Umum dan Keuangan</li>
            <li>Ketua Jurusan/Unit Terkait</li>
        </ol>
    </div>

  </div>
</template>

<script setup>
import { defineProps, computed } from 'vue';

const props = defineProps({
    surat: {
        type: Object,
        required: true
    }
});

const formatTanggal = (dateString) => {
    if (!dateString) return '-';
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

const formatCurrency = (value) => {
    if (!value) return '0';
    return new Intl.NumberFormat('id-ID').format(value);
};

const parsedLokasi = computed(() => {
    if (!props.surat.lokasi_kegiatan) return [];
    
    if (Array.isArray(props.surat.lokasi_kegiatan)) {
        return props.surat.lokasi_kegiatan;
    }

    try {
        return JSON.parse(props.surat.lokasi_kegiatan);
    } catch (e) {
        return [{ tempat: props.surat.lokasi_kegiatan, alamat: '' }];
    }
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Times+New+Roman&display=swap');

.font-serif {
    font-family: 'Times New Roman', Times, serif;
}
</style>
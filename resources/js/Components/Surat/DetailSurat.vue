<template>
  <div class="bg-white p-8 shadow-sm border border-gray-200 text-gray-800 font-serif text-sm leading-relaxed">
    <div class="text-center mb-8 pb-4 border-b-2 border-gray-800">
        <h2 class="font-bold text-xl uppercase mb-1">KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</h2>
        <h3 class="font-bold text-lg uppercase">POLITEKNIK NEGERI BANDUNG</h3>
        <p class="text-xs">Jalan Gegerkalong Hilir, Ds. Ciwaruga Kotak Pos 1234 Bandung 40012</p>
        <p class="text-xs">Telepon (022) 2013789, Fax. (022) 2013889 Laman: www.polban.ac.id Email: polban@polban.ac.id</p>
    </div>

    <div class="text-center mb-6">
        <h4 class="font-bold underline uppercase text-base">SURAT TUGAS</h4>
        <p v-if="surat.nomor_surat_resmi">Nomor: {{ surat.nomor_surat_resmi }}</p>
        <p v-else class="text-gray-400 italic">[Nomor Surat Belum Terbit]</p>
    </div>

    <div class="mb-6">
        <p>Direktur Politeknik Negeri Bandung dengan ini menugaskan kepada:</p>
    </div>

    <div class="mb-6 ml-4">
        <table class="w-full">
            <tr>
                <td class="align-top py-1" width="150px">Nama</td>
                <td class="align-top py-1" width="20px">:</td>
                <td class="align-top py-1 font-semibold">{{ surat.pengusul?.name }}</td>
            </tr>
            <tr>
                 <td class="align-top py-1">Unit Kerja</td>
                 <td class="align-top py-1">:</td>
                 <td class="align-top py-1">Politeknik Negeri Bandung</td>
            </tr>
        </table>
    </div>

    <div class="mb-6 text-justify">
        <p>
            Untuk melaksanakan kegiatan <strong>"{{ surat.nama_kegiatan }}"</strong> 
            yang akan dilaksanakan pada tanggal {{ formatDate(surat.tanggal_pelaksanaan) }}
            bertempat di {{ surat.tempat_pelaksanaan ?? '[Tempat Belum Diisi]' }}.
        </p>
        <p class="mt-2">
            Segala biaya yang timbul akibat diterbitkannya surat tugas ini dibebankan pada anggaran
            {{ surat.sumber_dana }}.
        </p>
        <p class="mt-4">
            Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.
        </p>
    </div>

    <div class="flex justify-end mt-16">
        <div class="text-left" style="min-width: 250px;">
            <p>Bandung, {{ formatDate(new Date()) }}</p>
            <p>Direktur,</p>
            <br><br><br><br>
            <p class="font-bold underline">[Nama Direktur]</p>
            <p>NIP. [NIP Direktur]</p>
        </div>
    </div>
    
     <div class="mt-12 pt-4 border-t border-gray-200 text-xs">
        <p class="font-bold">Tembusan:</p>
        <ol class="list-decimal ml-4 mt-1">
            <li>Para Wakil Direktur</li>
            <li>Ketua Jurusan terkait</li>
            <li>Arsip</li>
        </ol>
     </div>

  </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    surat: {
        type: Object,
        required: true
    }
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};
</script>
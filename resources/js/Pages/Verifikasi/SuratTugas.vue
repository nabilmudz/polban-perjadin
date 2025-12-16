<template>
    <Head title="Verifikasi Barcode TTE"/>
  <div class="p-6">
    <h1 class="text-2xl font-bold">Verifikasi Surat Tugas</h1>

    <div class="mt-4 border rounded p-4">
      <div><b>Nomor:</b> {{ surat.nomor || '-' }}</div>
      <div><b>Perihal:</b> {{ surat.perihal || '-' }}</div>
      <div><b>Status:</b> {{ surat.status || '-' }}</div>
      <div><b>Tanggal:</b> {{ surat.tanggal_berangkat }} s/d {{ surat.tanggal_kembali }}</div>
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
const props = defineProps({
  surat: { type: Object, required: true },
})
</script>

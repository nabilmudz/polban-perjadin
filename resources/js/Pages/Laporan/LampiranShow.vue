<template>
  <Head title="Lihat Lampiran" />

  <div class="bg-white w-full max-w-7xl mx-auto rounded-md shadow">
    <HeaderPage />

    <div class="p-6 border-b">
      <h1 class="text-2xl font-bold">{{ lampiran.nama_file }}</h1>
      <p class="text-gray-600 text-sm">
        Jenis: {{ lampiran.jenis_dokumen }} • Tgl unggah: {{ lampiran.tanggal_unggah }}
        <span v-if="lampiran.jenis_dokumen === 'bukti'"> • Nominal: {{ formatRupiah(lampiran.nominal) }}</span>
      </p>
    </div>

    <div class="p-6">
      <iframe :src="fileUrl" class="w-full" style="height: 80vh;" />
    </div>

  </div>
</template>

<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import HeaderPage from '@/Components/HeaderPage.vue'
import { computed } from 'vue'

const page = usePage()
const lampiran = computed(() => page.props.lampiran)
const fileUrl = computed(() => page.props.fileUrl)

const formatRupiah = (n) => new Intl.NumberFormat('id-ID', {
  style: 'currency', currency: 'IDR', maximumFractionDigits: 0
}).format(Number(n ?? 0))
</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'

export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
}
</script>

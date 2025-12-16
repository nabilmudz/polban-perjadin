<template>
  <Head title="Lampiran Laporan" />

  <div class="bg-white w-full max-w-7xl mx-auto rounded-md shadow">
    <HeaderPage />

    <div class="p-8 border-b">
      <h1 class="text-3xl font-bold mb-2">Lampiran Laporan Perjalanan Dinas</h1>
      <p class="text-gray-600">
        Kelola Laporan, Visum, dan Bukti. Halaman ini juga dipakai BKU untuk verifikasi nominal bukti.
      </p>

      <div class="mt-4 text-sm text-gray-700">
        <div><b>No Surat:</b> {{ surat?.nomor_surat_tugas_resmi ?? '-' }}</div>
        <div><b>Kegiatan:</b> {{ surat?.perihal_tugas ?? '-' }}</div>
        <div><b>Status:</b> {{ surat?.status_surat ?? '-' }}</div>
      </div>
    </div>

    <div class="p-8">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Dokumen Terunggah</h2>

        <div class="flex gap-2">
            <button
            class="px-4 py-2 rounded-md bg-primary-default text-white hover:bg-primary-dark shadow"
            @click="openUploadModal()"
            >
            Tambah / Upload Baru
            </button>

            <button
            class="px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-700 shadow"
            @click="submitToBku()"
            >
            Selesai Upload
            </button>
        </div>
    </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <DocCard
          title="Laporan Perjalanan Dinas"
          :doc="singleDoc('laporan')"
          @open="openDoc"
        />
        <DocCard
          title="Surat Visum"
          :doc="singleDoc('visum')"
          @open="openDoc"
        />

        <div class="border rounded-lg p-4 shadow-sm">
            <div class="font-semibold">Bukti Perjalanan Dinas</div>
            <div class="text-sm text-gray-600 mb-2">
            Total: <b>{{ formatRupiah(totalBuktiNominal) }}</b>
            </div>

          <div v-if="buktiDocs.length === 0" class="text-gray-500 text-sm">
            Belum ada bukti yang diunggah.
          </div>

          <div v-else class="space-y-2">
            <button
              v-for="b in buktiDocs"
              :key="b.id"
              class="w-full text-left border rounded p-3 hover:bg-gray-50"
              @click="openDoc(b)"
            >
              <div class="font-medium">{{ b.nama_file }}</div>
              <div class="text-xs text-gray-600">
                Nominal: {{ formatRupiah(b.nominal) }} • {{ b.tanggal_unggah }}
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>

    <UploadBuktiModal
      :show="isUploadModalOpen"
      :surat="surat"
      @close="closeUploadModal"
      @success="onUploadSuccess"
    />
  </div>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import UploadBuktiModal from '../Pelaksana/Partials/UploadBuktiModal.vue'
import DocCard from '@/Components/DocCard.vue'

const totalBuktiNominal = computed(() =>
  buktiDocs.value.reduce((sum, d) => sum + Number(d?.nominal ?? 0), 0)
)


const page = usePage()

const surat = computed(() => page.props.surat ?? null)

const lampiran = computed(() => {
  const raw = page.props.lampiran ?? []
  return raw.map((d) => ({
    ...d,
    id: d.id ?? d.dokumen_lampiran_id,
  }))
})

const singleDoc = (type) =>
  lampiran.value.find((d) => d.jenis_dokumen === type) ?? null

const buktiDocs = computed(() =>
  lampiran.value.filter((d) => d.jenis_dokumen === 'bukti')
)

const openDoc = (doc) => {
  const docId = doc?.id ?? doc?.dokumen_lampiran_id
  if (!docId) return
  router.get(route('pelaksana.lampiran.show', { lampiran: docId }))
}

const formatRupiah = (n) => {
  const val = Number(n ?? 0)
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val)
}

const isUploadModalOpen = ref(false)

const openUploadModal = () => {
  isUploadModalOpen.value = true
}
const closeUploadModal = () => {
  isUploadModalOpen.value = false
}
const onUploadSuccess = () => {
  router.reload({ only: ['lampiran', 'surat'] })
  closeUploadModal()
}
const submitToBku = () => {
  router.post(
    route('pelaksana.lampiran.submit', { suratTugas: surat.value.id }),
    {},
    {
      preserveScroll: true,
      onSuccess: () => router.reload({ only: ['lampiran', 'surat'] }),
    }
  )
}

</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
}
</script>

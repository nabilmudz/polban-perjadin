<template>
  <Head title="Lampiran Laporan" />

  <div class="bg-white w-full max-w-7xl mx-auto rounded-md shadow">
    <HeaderPage />
    <div v-if="isBku" class="p-8 border-b bg-gray-50">
    <h2 class="text-lg font-semibold mb-2">Verifikasi BKU</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="border rounded-lg p-4 bg-white">
        <div class="text-sm text-gray-500">Nominal Pengajuan</div>
        <div class="text-lg font-semibold">{{ formatRupiah(surat?.nominal_dana) }}</div>
        </div>

        <div class="border rounded-lg p-4 bg-white">
        <div class="text-sm text-gray-500">Total Bukti</div>
        <div class="text-lg font-semibold">{{ formatRupiah(totalBukti) }}</div>
        </div>

        <div class="border rounded-lg p-4 bg-white">
        <div class="text-sm text-gray-500">Selisih</div>
        <div class="text-lg font-semibold">
            {{ formatRupiah((surat?.nominal_dana ?? 0) - totalBukti) }}
        </div>
        </div>
    </div>

    <label class="block font-medium mb-2">Catatan Verifikasi BKU</label>
    <textarea
        v-model="catatanBku"
        class="w-full border rounded px-3 py-2"
        rows="3"
        placeholder="Isi catatan verifikasi (wajib jika dikembalikan untuk koreksi)"
        :disabled="!canVerify"
    />

    <div class="flex gap-2 justify-end mt-4">
        <button
            class="px-4 py-2 rounded-md border bg-white hover:bg-gray-50 shadow"
            type="button"
            @click="goBack"
            >
            Kembali
        </button>
        <button
        class="px-4 py-2 rounded-md border"
        :disabled="!canVerify"
        @click="returnForCorrection"
        >
        Kembalikan untuk Koreksi
        </button>

        <button
        class="px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-700"
        :disabled="!canVerify"
        @click="approve"
        >
        Approve
        </button>
    </div>

    <div v-if="!canVerify" class="text-sm text-gray-500 mt-2">
        Verifikasi hanya tersedia saat status surat = <b>under_bku_review</b>.
    </div>
    </div>

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
        <div class="flex items-center gap-3">

            <h2 class="text-lg font-semibold">Dokumen Terunggah</h2>
        </div>

        <div class="flex gap-2">
            <button
                class="px-4 py-2 rounded-md border bg-white hover:bg-gray-50 shadow"
                type="button"
                @click="goBack"
                >
                Kembali
            </button>
            <button
            v-if="routePrefix === 'pelaksana'"
            class="px-4 py-2 rounded-md bg-primary-default text-white hover:bg-primary-dark shadow"
            @click="openUploadModal()"
            >
            Tambah / Upload Baru
            </button>

            <button
            v-if="routePrefix === 'pelaksana'"
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

const page = usePage()
const role = computed(() => page.props?.role ?? page.props?.auth?.user?.role ?? 'pelaksana')
const isBku = computed(() => role.value === 'bku')

const surat = computed(() => page.props.surat ?? null)
const laporanMeta = computed(() => page.props.laporanMeta ?? null)

const totalBukti = computed(() => Number(page.props.totalBukti ?? 0))

const catatanBku = ref(laporanMeta.value?.catatan_verifikasi_bku ?? '')

const formatRupiah = (n) => new Intl.NumberFormat('id-ID', {
  style: 'currency', currency: 'IDR', maximumFractionDigits: 0
}).format(Number(n ?? 0))

const canVerify = computed(() => surat.value?.status_surat === 'under_bku_review')

const approve = () => {
  if (!surat.value?.id) return
  router.post(route('bku.lampiran.approve', { suratTugas: surat.value.id }), {
    catatan: catatanBku.value || null,
  }, { preserveScroll: true })
}
const fallbackRoute = computed(() => {
  return role.value === 'bku'
    ? 'bku.daftarlaporanperjalanan'
    : 'pelaksana.status-laporan'
})

const goBack = () => {
  if (window.history.length > 1) {
    window.history.back()
    return
  }

  router.get(route(fallbackRoute.value), {}, { preserveScroll: true })
}

const returnForCorrection = () => {
  if (!surat.value?.id) return
  if (!catatanBku.value?.trim()) {
    alert('Catatan wajib diisi untuk koreksi.')
    return
  }
  router.post(route('bku.lampiran.return', { suratTugas: surat.value.id }), {
    catatan: catatanBku.value,
  }, { preserveScroll: true })
}

const totalBuktiNominal = computed(() =>
  buktiDocs.value.reduce((sum, d) => sum + Number(d?.nominal ?? 0), 0)
)

const routePrefix = computed(() => (role.value === 'bku' ? 'bku' : 'pelaksana'))

const openDoc = (doc) => {
  const docId = doc?.id ?? doc?.dokumen_lampiran_id
  if (!docId) return
  router.get(route(`${routePrefix.value}.lampiran.show`, { lampiran: docId }))
}

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

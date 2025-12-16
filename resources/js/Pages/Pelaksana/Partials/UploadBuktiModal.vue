<template>
  <Modal :show="show" @close="handleClose" max-width="lg">
    <div class="p-6">
      <div class="flex justify-between items-center border-b pb-3 mb-4">
        <div>
          <h2 class="text-xl font-semibold">Upload Laporan / Visum / Bukti</h2>
          <p class="text-gray-600 text-sm" v-if="surat">
            {{ surat.perihal_tugas }} (ID: {{ suratId }})
          </p>
        </div>
        <button @click="handleClose" class="text-gray-500 hover:text-gray-800 transition">
          ✕
        </button>
      </div>

      <p class="text-gray-600 mb-4">
        Sesuai SRS: unggah Laporan Perjalanan Dinas, Surat Visum, dan Bukti (kwitansi/boarding pass/dll).
      </p>

      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="block font-medium mb-2">Jenis Dokumen</label>
          <div class="flex flex-col gap-2">
            <label class="flex items-center gap-2">
              <input type="radio" v-model="form.doc_type" value="laporan" />
              <span>Laporan Perjalanan Dinas</span>
            </label>
            <label class="flex items-center gap-2">
              <input type="radio" v-model="form.doc_type" value="visum" />
              <span>Surat Visum</span>
            </label>
            <label class="flex items-center gap-2">
              <input type="radio" v-model="form.doc_type" value="bukti" />
              <span>Bukti Perjalanan Dinas</span>
            </label>
          </div>
          <p v-if="form.errors.doc_type" class="text-red-600 text-sm mt-1">{{ form.errors.doc_type }}</p>
        </div>

        <div>
          <label class="block font-medium mb-2">Upload Dokumen (PDF)</label>
          <input
            type="file"
            accept=".pdf,application/pdf"
            class="border rounded w-full px-3 py-2"
            @change="handleFileChange"
          />
          <p v-if="form.errors.file" class="text-red-600 text-sm mt-1">{{ form.errors.file }}</p>
        </div>

        <div v-if="form.doc_type === 'bukti'">
          <label class="block font-medium mb-2">Nominal (wajib untuk Bukti)</label>
          <input
            v-model="form.nominal"
            class="w-full border rounded px-3 py-2"
            placeholder="Contoh: 100000"
            type="number"
            min="0"
          />
          <p v-if="form.errors.nominal" class="text-red-600 text-sm mt-1">{{ form.errors.nominal }}</p>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t mt-4">
          <button
            type="button"
            @click="handleClose"
            class="px-4 py-2 rounded-md border text-gray-700 hover:bg-gray-100"
            :disabled="form.processing"
          >
            Batal
          </button>

          <button
            type="submit"
            class="px-4 py-2 rounded-md bg-primary-default text-white hover:bg-primary-dark"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Mengunggah...' : 'Upload' }}
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>

<script setup>
import Modal from '@/Components/Modal.vue'
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  show: Boolean,
  surat: { type: Object, default: null },
})

const emit = defineEmits(['close', 'success'])

const suratId = computed(() => props.surat?.surat_tugas_id ?? props.surat?.id ?? null)

const form = useForm({
  doc_type: 'laporan',
  file: null,
  nominal: null,
})

watch(() => props.show, (val) => {
  if (val) {
    form.clearErrors()
    form.doc_type = 'laporan'
    form.file = null
    form.nominal = ''
  }
})

const handleFileChange = (e) => {
  form.file = e.target.files?.[0] ?? null
}

const handleClose = () => {
  if (!form.processing) emit('close')
}

const submit = () => {
  if (!suratId.value) return

  form.post(
    route('pelaksana.laporan.upload', { suratTugas: suratId.value }),
    {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => emit('success'),
    }
  )
}

const canUpload = (row) => {
  const st = row?.status_surat
  const today = new Date().toISOString().slice(0, 10)
  const kembali = String(row?.tanggal_kembali ?? '').slice(0, 10)

  if (st === 'awaiting_proof_upload' || st === 'returned_for_correction') return true
  if (st === 'published' && kembali && kembali <= today) return true

  return false
}

</script>

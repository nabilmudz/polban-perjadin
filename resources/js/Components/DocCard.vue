<template>
  <div
    class="border rounded-lg shadow-sm overflow-hidden bg-white hover:shadow transition cursor-pointer"
    @click="handleOpen"
    role="button"
    tabindex="0"
    @keydown.enter.prevent="handleOpen"
    @keydown.space.prevent="handleOpen"
  >
    <div class="bg-gray-50 border-b">
      <iframe
        v-if="previewUrl"
        :src="previewUrl"
        class="w-full"
        style="height: 140px"
        title="PDF Preview"
      />
      <div v-else class="h-[140px] flex items-center justify-center text-sm text-gray-500">
        Belum ada dokumen
      </div>
    </div>

    <div class="p-4">
      <div class="text-base font-semibold text-gray-900 line-clamp-2">
        {{ titleText }}
      </div>

      <div class="mt-2 text-sm text-gray-600">
        <div class="flex items-center justify-between gap-2">
          <span class="capitalize">Jenis: {{ jenisText }}</span>
          <span v-if="showNominal" class="font-medium">
            {{ formatRupiah(doc.nominal) }}
          </span>
        </div>

        <div v-if="doc?.tanggal_unggah" class="mt-1 text-xs text-gray-500">
          Diunggah: {{ doc.tanggal_unggah }}
        </div>
      </div>

      <div class="mt-3">
        <span
          class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium"
          :class="doc ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
        >
          {{ doc ? 'Klik untuk lihat' : 'Tidak ada file' }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: { type: String, default: '' },
  doc: { type: Object, default: null },
  fileUrl: { type: String, default: '' },
  clickable: { type: Boolean, default: true },
})

const emit = defineEmits(['open'])

const titleText = computed(() => {
  if (props.doc?.nama_file) return props.doc.nama_file
  if (props.title) return props.title
  return 'Dokumen'
})

const jenisText = computed(() => props.doc?.jenis_dokumen ?? '-')

const showNominal = computed(() => {
  if (!props.doc) return false
  return props.doc.jenis_dokumen === 'bukti' && props.doc.nominal != null
})

const previewUrl = computed(() => {
  if (props.fileUrl) return props.fileUrl
  if (props.doc?.file_url) return props.doc.file_url
  return ''
})

const handleOpen = () => {
  if (!props.clickable) return
  if (!props.doc) return
  emit('open', props.doc)
}

const formatRupiah = (n) => {
  const val = Number(n ?? 0)
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val)
}
</script>

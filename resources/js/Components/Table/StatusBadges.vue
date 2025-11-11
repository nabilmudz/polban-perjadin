<template>
  <span :class="['px-2 py-1 rounded-full text-xs font-medium capitalize', colorClass]">
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ status: String })

// Penamaan status
const label = computed(() => {
  switch (props.status) {
    case 'draft':
      return 'Draft'
    case 'submitted_wadir_review':
      return 'Menunggu Review Wadir'
    case 'revision_requested':
      return 'Revisi Diminta'
    case 'rejected':
      return 'Ditolak'
    case 'approved_wadir':
      return 'Disetujui Wadir'
    case 'pending_sekdir_numbering':
      return 'Menunggu Penomoran Sekdir'
    case 'pending_direktur_signature':
      return 'Menunggu Tanda Tangan Direktur'
    case 'published':
      return 'Dipublikasikan'
    case 'awaiting_proof_upload':
      return 'Menunggu Upload Bukti'
    case 'under_bku_review':
      return 'Sedang Direview BKU'
    case 'returned_for_correction':
      return 'Dikembalikan untuk Koreksi'
    case 'completed':
      return 'Selesai'
    default:
      return props.status?.replaceAll('_', ' ') ?? ''
  }
})

// Background Color
const colorClass = computed(() => {
  switch (props.status) {
    case 'draft':
      return 'bg-gray-200 text-gray-700'
    case 'submitted_wadir_review':
      return 'bg-blue-100 text-blue-700'
    case 'revision_requested':
      return 'bg-yellow-100 text-yellow-700'
    case 'rejected':
      return 'bg-red-100 text-red-700'
    case 'approved_wadir':
      return 'bg-emerald-100 text-emerald-700'
    case 'pending_sekdir_numbering':
      return 'bg-indigo-100 text-indigo-700'
    case 'pending_direktur_signature':
      return 'bg-purple-100 text-purple-700'
    case 'published':
      return 'bg-green-100 text-green-700'
    case 'awaiting_proof_upload':
      return 'bg-orange-100 text-orange-700'
    case 'under_bku_review':
      return 'bg-cyan-100 text-cyan-700'
    case 'returned_for_correction':
      return 'bg-rose-100 text-rose-700'
    case 'completed':
      return 'bg-teal-100 text-teal-700'
    default:
      return 'bg-gray-100 text-gray-600'
  }
})
</script>

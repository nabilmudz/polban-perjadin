<template>
  <Head title="Review Nomor Surat" />

  <AppLayout>
    <div class="bg-white w-full rounded-md shadow overflow-hidden">
      <HeaderPage />

      <div class="p-8">
        <h1 class="text-3xl font-bold">Review & Penomoran Sekdir</h1>

        <div class="mt-6 border rounded-md bg-gray-50 max-h-[75vh] overflow-hidden">
          <div class="relative h-[75vh] flex">
            <div
              ref="paperScrollEl"
              class="flex-1 overflow-y-auto overflow-x-hidden p-4 pr-10"
              @scroll="onPaperScroll"
            >
              <LaporanSurat :surat="surat" />
            </div>

            <div class="w-10 border-l bg-white/70 backdrop-blur">
              <div class="h-full flex items-center justify-center">
                <div
                  ref="trackEl"
                  class="w-[6px] h-[70%] rounded-full bg-gray-200 relative overflow-hidden select-none"
                  @pointerdown="onTrackPointerDown"
                >
                  <div
                    class="absolute left-0 w-full rounded-full bg-gray-500 transition-[height] duration-150 cursor-grab active:cursor-grabbing touch-none"
                    :style="{ top: thumbTop + 'px', height: thumbHeight + 'px' }"
                    @pointerdown.stop.prevent="onThumbPointerDown"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="border-t" />

      <div class="px-10 py-8">
        <h2 class="text-xl font-semibold mb-3">
          Catatan / Komentar (Wajib jika Dikembalikan)
        </h2>

        <textarea
          v-model="actionForm.catatan_revisi"
          rows="4"
          placeholder="Masukkan catatan jika dikembalikan untuk koreksi"
          class="w-full border rounded-md p-3 text-gray-700 focus:ring focus:ring-orange-300"
        />

        <p v-if="actionForm.errors.catatan_revisi" class="text-sm text-red-600 mt-2">
          {{ actionForm.errors.catatan_revisi }}
        </p>
        <p v-if="actionForm.errors.status_surat" class="text-sm text-red-600 mt-1">
          {{ actionForm.errors.status_surat }}
        </p>

        <div class="flex justify-end mt-6 gap-4">
          <button
            @click="goBack"
            class="flex items-center gap-2 bg-gray-300 text-black px-5 py-2 rounded-md hover:brightness-95 shadow"
          >
            Kembali
          </button>

          <button
            :disabled="actionForm.processing"
            @click="submitStatus('sekdir_revision_requested')"
            class="flex items-center gap-2 bg-yellow-500 text-black px-5 py-2 rounded-md hover:brightness-95 shadow disabled:opacity-60 disabled:cursor-not-allowed"
          >
            Kembalikan untuk Koreksi
          </button>

          <button
            :disabled="actionForm.processing"
            @click="openNumberingModal"
            class="flex items-center gap-2 bg-orange-600 text-white px-5 py-2 rounded-md hover:brightness-95 shadow disabled:opacity-60 disabled:cursor-not-allowed"
          >
            Terapkan Nomor Surat Resmi
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
    >
      <div class="bg-white w-full max-w-lg rounded-lg shadow-xl p-6">
        <h2 class="text-xl font-semibold mb-4">Input Nomor Surat Resmi</h2>

        <div class="grid grid-cols-4 gap-3 mb-2">
          <input
            type="number"
            v-model="numberForm.nomor_urutan_surat"
            class="border p-2 rounded"
            placeholder="No"
            min="1"
          />
          <input
            type="text"
            v-model="numberForm.kode_unit"
            class="border p-2 rounded"
            placeholder="Unit"
          />
          <input
            type="text"
            v-model="numberForm.kode_perihal"
            class="border p-2 rounded"
            placeholder="Perihal"
          />
          <input
            type="number"
            v-model="numberForm.tahun"
            class="border p-2 rounded"
            placeholder="Tahun"
          />
        </div>

        <div class="text-sm text-gray-600 mt-2 space-y-1">
          <p>Nomor urut terakhir tahun ini: <strong>{{ next_number - 1 }}</strong></p>
          <p>Saran nomor berikutnya: <strong>{{ next_number }}</strong></p>
        </div>

        <div class="mt-3 space-y-1">
          <p v-if="numberForm.errors.nomor_urutan_surat" class="text-sm text-red-600">
            {{ numberForm.errors.nomor_urutan_surat }}
          </p>
          <p v-if="numberForm.errors.kode_unit" class="text-sm text-red-600">
            {{ numberForm.errors.kode_unit }}
          </p>
          <p v-if="numberForm.errors.kode_perihal" class="text-sm text-red-600">
            {{ numberForm.errors.kode_perihal }}
          </p>
          <p v-if="numberForm.errors.tahun" class="text-sm text-red-600">
            {{ numberForm.errors.tahun }}
          </p>
        </div>

        <div class="flex justify-end gap-3 mt-6">
          <button
            @click="closeModal"
            class="px-4 py-2 border rounded hover:bg-gray-100 transition"
          >
            Batal
          </button>

          <button
            :disabled="numberForm.processing"
            @click="applyNumbering"
            class="px-6 py-2 bg-orange-500 text-white rounded hover:bg-orange-700 transition disabled:opacity-60 disabled:cursor-not-allowed"
          >
            Terapkan & Kirim ke Direktur
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, useForm, usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'
import { computed, ref, onMounted, onBeforeUnmount, nextTick } from 'vue'

const paperScrollEl = ref(null)
const trackEl = ref(null)

const thumbTop = ref(0)
const thumbHeight = ref(40)

let isDragging = false
let dragStartY = 0
let dragStartTop = 0

const clamp = (v, min, max) => Math.min(max, Math.max(min, v))

const getMetrics = () => {
  const el = paperScrollEl.value
  const track = trackEl.value
  if (!el || !track) return null

  const scrollHeight = el.scrollHeight
  const clientHeight = el.clientHeight
  const scrollTop = el.scrollTop
  const trackHeight = track.getBoundingClientRect().height

  return { el, scrollHeight, clientHeight, scrollTop, trackHeight }
}

const syncScrollFromThumb = (newTop) => {
  const m = getMetrics()
  if (!m) return

  const { el, scrollHeight, clientHeight, trackHeight } = m
  const maxScroll = scrollHeight - clientHeight
  if (maxScroll <= 0) return

  const maxTop = Math.max(0, trackHeight - thumbHeight.value)
  if (maxTop <= 0) return

  const ratio = newTop / maxTop
  el.scrollTop = ratio * maxScroll
}

const updateThumb = () => {
  const m = getMetrics()
  if (!m) return

  const { scrollHeight, clientHeight, scrollTop, trackHeight } = m

  if (scrollHeight <= clientHeight) {
    thumbHeight.value = trackHeight
    thumbTop.value = 0
    return
  }

  const minThumb = 36
  const ratio = clientHeight / scrollHeight
  const h = Math.max(minThumb, trackHeight * ratio)
  const maxTop = Math.max(0, trackHeight - h)

  const t = (scrollTop / (scrollHeight - clientHeight)) * maxTop

  thumbHeight.value = h
  thumbTop.value = clamp(t, 0, maxTop)
}

const onPaperScroll = () => {
  if (!isDragging) updateThumb()
}

const onThumbPointerMove = (e) => {
  if (!isDragging) return

  const m = getMetrics()
  if (!m) return

  const { trackHeight } = m
  const maxTop = Math.max(0, trackHeight - thumbHeight.value)

  const delta = e.clientY - dragStartY
  const newTop = clamp(dragStartTop + delta, 0, maxTop)

  thumbTop.value = newTop
  syncScrollFromThumb(newTop)
}

const onThumbPointerUp = () => {
  isDragging = false
  window.removeEventListener('pointermove', onThumbPointerMove)
  updateThumb()
}

const onThumbPointerDown = (e) => {
  isDragging = true
  dragStartY = e.clientY
  dragStartTop = thumbTop.value

  window.addEventListener('pointermove', onThumbPointerMove)
  window.addEventListener('pointerup', onThumbPointerUp, { once: true })
  window.addEventListener('pointercancel', onThumbPointerUp, { once: true })
}

const onTrackPointerDown = (e) => {
  const m = getMetrics()
  if (!m || !trackEl.value) return

  const { trackHeight } = m
  const rect = trackEl.value.getBoundingClientRect()

  const clickY = e.clientY - rect.top
  const maxTop = Math.max(0, trackHeight - thumbHeight.value)
  const newTop = clamp(clickY - thumbHeight.value / 2, 0, maxTop)

  thumbTop.value = newTop
  syncScrollFromThumb(newTop)
}

onMounted(async () => {
  await nextTick()
  updateThumb()
  window.addEventListener('resize', updateThumb)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', updateThumb)
  window.removeEventListener('pointermove', onThumbPointerMove)
})

const { props } = usePage()

const surat = computed(() => props.surat)
const next_number = computed(() => props.next_number ?? 1)
const year = computed(() => props.year ?? new Date().getFullYear())

const suratId = computed(() => surat.value?.surat_tugas_id ?? surat.value?.id)

const showModal = ref(false)

const actionForm = useForm({
  status_surat: '',
  catatan_revisi: '',
})

const numberForm = useForm({
  nomor_urutan_surat: next_number.value,
  kode_unit: 'PL1',
  kode_perihal: 'RT.01.00',
  tahun: year.value,
})

const goBack = () => router.get(route('sekdir.nomorsurat'))

const submitStatus = (status) => {
  actionForm.clearErrors()
  actionForm.status_surat = status

  const needNote = ['returned_for_correction'].includes(status)
  if (needNote && !actionForm.catatan_revisi?.trim()) {
    actionForm.setError('catatan_revisi', 'Catatan wajib diisi jika dikembalikan untuk koreksi.')
    return
  }

  actionForm.patch(route('surat-tugas.update-status', { surat_tugas: suratId.value }), {
    preserveScroll: true,
    onSuccess: () => {
      router.get(route('sekdir.nomorsurat'))
    },
  })
}

const openNumberingModal = () => {
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  numberForm.clearErrors()
}

const applyNumbering = () => {
  numberForm.post(route('sekdir.nomorsurat.apply', suratId.value), {
    preserveScroll: true,
    onSuccess: () => {
      closeModal()
      router.get(route('sekdir.nomorsurat'))
    },
  })
}
</script>

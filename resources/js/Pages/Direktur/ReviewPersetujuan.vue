<template>
  <Head title="Review Persetujuan Direktur" />

  <AppLayout>
    <div class="bg-white w-full rounded-md shadow overflow-hidden">
      <HeaderPage title="Review Persetujuan Direktur" />

      <div class="p-8">
        <h1 class="text-3xl font-bold">Review Persetujuan Direktur</h1>

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
          Catatan / Komentar (Wajib untuk Revisi / Tolak)
        </h2>

        <textarea
          v-model="form.catatan"
          rows="4"
          placeholder="Masukkan catatan jika revisi / tolak"
          class="w-full border rounded-md p-3 text-gray-700 focus:ring focus:ring-indigo-300"
        />

        <p v-if="form.errors.catatan" class="text-sm text-red-600 mt-2">
          {{ form.errors.catatan }}
        </p>

        <div class="flex justify-end mt-6 gap-4">
          <button
            @click="goBack"
            class="flex items-center gap-2 bg-gray-300 text-black px-5 py-2 rounded-md hover:brightness-95 shadow"
          >
            Kembali
          </button>

          <button
            :disabled="form.processing"
            @click="submit('revise')"
            class="flex items-center gap-2 bg-yellow-500 text-black px-5 py-2 rounded-md hover:brightness-95 shadow disabled:opacity-60 disabled:cursor-not-allowed"
          >
            Revisi
          </button>

          <button
            :disabled="form.processing"
            @click="submit('reject')"
            class="flex items-center gap-2 bg-red-600 text-white px-5 py-2 rounded-md hover:brightness-95 shadow disabled:opacity-60 disabled:cursor-not-allowed"
          >
            Tolak
          </button>

          <button
            :disabled="form.processing"
            @click="submit('approve')"
            class="flex items-center gap-2 bg-green-600 text-white px-5 py-2 rounded-md hover:brightness-95 shadow disabled:opacity-60 disabled:cursor-not-allowed"
          >
            Publish
          </button>
        </div>

        <p class="text-xs text-gray-500 mt-4">
          Publish saat ini akan memindahkan surat ke tahap berikutnya (TTE akan kita develop setelah ini).
        </p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, useForm, usePage, router } from '@inertiajs/vue3'
import { computed, ref, onMounted, onBeforeUnmount, nextTick } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'

const { props } = usePage()
const surat = computed(() => props.surat)
const suratId = computed(() => surat.value?.id ?? surat.value?.surat_tugas_id)

const goBack = () => router.get(route('direktur.daftarpersetujuan'))

const form = useForm({
  catatan: '',
})

const submit = (action) => {
  if (!suratId.value) return

  const needNote = ['reject', 'revise'].includes(action)
  if (needNote && !form.catatan?.trim()) {
    form.setError('catatan', 'Catatan wajib diisi untuk Revisi / Tolak.')
    return
  }

  const routes = {
    approve: 'direktur.persetujuan.approve',
    reject: 'direktur.persetujuan.reject',
    revise: 'direktur.persetujuan.revise',
  }

  form.post(route(routes[action], suratId.value), {
    preserveScroll: true,
    onSuccess: () => goBack(),
  })
}

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
</script>

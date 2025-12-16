<template>
  <Head title="Persetujuan Surat Tugas" />

  <div class="bg-white w-full rounded-md shadow overflow-hidden">
    <HeaderPage />

    <div class="p-8">
      <h1 class="text-3xl font-bold">Persetujuan Surat Tugas</h1>

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
        Catatan / Komentar (Wajib jika Revisi / Ditolak)
      </h2>

      <textarea
        v-model="form.catatan_revisi"
        rows="4"
        placeholder="Masukkan catatan jika minta revisi/penolakan"
        class="w-full border rounded-md p-3 text-gray-700 focus:ring focus:ring-blue-300"
      ></textarea>
      <p v-if="form.errors.catatan_revisi" class="text-sm text-red-600 mt-2">
        {{ form.errors.catatan_revisi }}
      </p>
      <p v-if="form.errors.status_surat" class="text-sm text-red-600 mt-1">
        {{ form.errors.status_surat }}
      </p>

      <div class="flex justify-end mt-6 gap-4">
        <button
          @click="goDashboard"
          class="flex items-center gap-2 bg-gray-300 text-black px-5 py-2 rounded-md hover:brightness-95 shadow"
        >
          Kembali ke Dashboard
        </button>

        <template v-if="surat.status_surat === 'submitted_wadir_review'">
          <button
            @click="submit('revision_requested')"
            class="flex items-center gap-2 bg-yellow-500 text-black px-5 py-2 rounded-md hover:brightness-95 shadow"
          >
            Kembalikan untuk Revisi
          </button>

          <button
            @click="submit('rejected')"
            class="flex items-center gap-2 bg-red-500 text-white px-5 py-2 rounded-md hover:brightness-95 shadow"
          >
            Tolak
          </button>

          <button
            :disabled="form.processing"
            @click="submit('approved_wadir')"
            class="flex items-center gap-2 bg-green-600 text-white px-5 py-2 rounded-md hover:brightness-95 shadow disabled:opacity-60 disabled:cursor-not-allowed"
          >
            Setujui
          </button>
        </template>
      </div>
    </div>
  </div>
</template>


<script setup>
import HeaderPage from '@/Components/HeaderPage.vue'
import { Head, useForm, usePage, router } from '@inertiajs/vue3'
import { reactive, onBeforeUnmount  } from 'vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'
import { ref, onMounted } from 'vue'

const paperScrollEl = ref(null)

const thumbTop = ref(0)
const thumbHeight = ref(40)
const trackEl = ref(null)

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

const onThumbPointerDown = (e) => {
  isDragging = true
  dragStartY = e.clientY
  dragStartTop = thumbTop.value

  window.addEventListener('pointermove', onThumbPointerMove)
  window.addEventListener('pointerup', onThumbPointerUp, { once: true })
  window.addEventListener('pointercancel', onThumbPointerUp, { once: true })
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

const onTrackPointerDown = (e) => {
  const m = getMetrics()
  if (!m) return

  const { trackHeight } = m
  const rect = trackEl.value.getBoundingClientRect()

  const clickY = e.clientY - rect.top
  const maxTop = Math.max(0, trackHeight - thumbHeight.value)
  const newTop = clamp(clickY - thumbHeight.value / 2, 0, maxTop)

  thumbTop.value = newTop
  syncScrollFromThumb(newTop)
}

onMounted(() => {
  updateThumb()
  window.addEventListener('resize', updateThumb)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', updateThumb)
  window.removeEventListener('pointermove', onThumbPointerMove)
})

const page = usePage()
const user = page.props.auth.user
const surat = page.props.data

const form = useForm({
  status_surat: '',
  catatan_revisi: '',
})

const goDashboard = () => {
  router.get(route(`${user.role}.dashboard`))
}
const getSuratTugasId = () => surat?.surat_tugas_id ?? surat?.id

const submit = (status) => {
  form.clearErrors()
  form.status_surat = status

  const needNote = ['revision_requested', 'rejected'].includes(status)
  if (needNote && !form.catatan_revisi?.trim()) {
    form.setError('catatan_revisi', 'Catatan wajib diisi untuk revisi atau tolak.')
    return
  }

  form.patch(
    route('surat-tugas.update-status', { surat_tugas: getSuratTugasId() }),
    {
      preserveScroll: true,
      onSuccess: () => {
      },
      onError: (errors) => {
        const msg =
          errors?.catatan_revisi ||
          errors?.status_surat ||
          Object.values(errors || {})[0]

        if (msg) alert(msg)
      },
    }
  )
}

</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page })
}
</script>

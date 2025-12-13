<template>
  <Head title="Persetujuan Surat Tugas" />

  <div class="bg-white w-full h-full rounded-md">
    <HeaderPage />

    <div class="p-8">
      <h1 class="text-3xl font-bold">Persetujuan Surat Tugas</h1>

      <div class="mt-6">
        <LaporanSurat :surat="surat" />
      </div>
    </div>

    <div class="px-10 pb-20">

      <h2 class="text-xl font-semibold mb-3">
        Catatan / Komentar (Wajib jika Revisi / Ditolak)
      </h2>

      <textarea
        v-model="form.catatan_revisi"
        rows="4"
        placeholder="Masukkan catatan jika minta revisi/penolakan"
        class="w-full border rounded-md p-3 text-gray-700 focus:ring focus:ring-blue-300"
      ></textarea>

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
            <font-awesome-icon icon="rotate-left" />
            Kembalikan untuk Revisi
          </button>

          <button
            @click="submit('rejected')"
            class="flex items-center gap-2 bg-red-500 text-white px-5 py-2 rounded-md hover:brightness-95 shadow"
          >
            <font-awesome-icon :icon="['fas', 'xmark']" />
            Tolak
          </button>

          <button
            @click="submit('approved_wadir')"
            class="flex items-center gap-2 bg-green-600 text-white px-5 py-2 rounded-md hover:brightness-95 shadow"
          >
            <font-awesome-icon :icon="['fas', 'square-check']" />
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
import { reactive } from 'vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'

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

const submit = (status) => {
  if (['revision_requested', 'rejected'].includes(status) && !form.catatan_revisi) {
    alert('Catatan wajib diisi untuk revisi atau tolak.')
    return
  }

  form.status_surat = status

  form.patch(route('surat-tugas.update-status', surat.id), {
    preserveScroll: true,
    onSuccess: () => {
      goDashboard()
    }
  })
}
</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page })
}
</script>

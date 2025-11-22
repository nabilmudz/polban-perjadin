<template>
  <Head title="Persetujuan Surat Tugas" />

  <div class="bg-white w-full h-full rounded-md">
    <HeaderPage />

    <div class="p-8">
    <h1 class="text-3xl font-bold">Persetujuan Surat Tugas</h1>

    <!-- TAMPILKAN DATA SURAT DI SINI -->
    <div class="mt-6">
      <LaporanSurat :surat="surat" />
    </div>
  </div>


    <div class="px-10 pb-20">

      <h2 class="text-xl font-semibold mb-3">
        Catatan / Komentar (Jika Perlu Revisi / Ditolak)
      </h2>

      <textarea
        v-model="form.catatan"
        rows="4"
        placeholder="Masukkan catatan atau alasan penolakan/revisi..."
        class="w-full border rounded-md p-3 text-gray-700 focus:ring focus:ring-blue-300"
      ></textarea>

      <div class="flex justify-end mt-6 gap-4">

        <button
          @click="goDashboard"
          class="flex items-center gap-2 bg-gray-300 text-black px-5 py-2 rounded-md hover:brightness-95 shadow"
        >
          Kembali ke Dashboard
        </button>


        <button
          @click="submit('revisi')"
          class="flex items-center gap-2 bg-yellow-500 text-black px-5 py-2 rounded-md hover:brightness-95 shadow"
        >
          <font-awesome-icon icon="rotate-left" />
          Kembalikan untuk Revisi
        </button>

        <button
          @click="submit('tolak')"
          class="flex items-center gap-2 bg-red-500 text-white px-5 py-2 rounded-md hover:brightness-95 shadow"
        >
          <font-awesome-icon :icon="['fas', 'xmark']" />
          Tolak
        </button>

        <button
          @click="submit('setujui')"
          class="flex items-center gap-2 bg-green-600 text-white px-5 py-2 rounded-md hover:brightness-95 shadow"
        >
          <font-awesome-icon :icon="['fas', 'Square-Check']" />
          Setujui
        </button>

      </div>

    </div>
  </div>
</template>

<script setup>
import HeaderPage from '@/Components/HeaderPage.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, reactive, onMounted } from 'vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'

const page = usePage()
const user = page.props.auth.user
const surat = page.props.data

const form = reactive({
  catatan: '',
})

const goDashboard = () => {
  router.get(route(`${user.role}.dashboard`))
}

const submit = (aksi) => {
  if (aksi === 'setujui') {
    router.post(route(`${user.role}.persetujuan.approve`, surat.id), {
      catatan: form.catatan,
    })
  }

  if (aksi === 'tolak' || aksi === 'revisi') {
    router.post(route(`${user.role}.persetujuan.reject`, surat.id), {
      catatan: form.catatan,
    })
  }
}
</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page })
}
</script>

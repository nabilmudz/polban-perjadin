<template>
    <Head title="Review Nomor Surat" />

  <AppLayout>
    <div class="bg-white w-full rounded-md shadow">
      <HeaderPage />

      <div class="p-8">
        <h1 class="text-3xl font-bold mb-2">Preview Surat Tugas</h1>

        <div class="mt-4">
          <div class="bg-gray-100 p-8 rounded-lg">
              <LaporanSurat :surat="surat" />
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
          <button
            @click="goBack"
            class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-100 transition"
          >
            Kembali
          </button>

          <button
            @click="showModal = true"
            class="px-6 py-2 bg-orange-500 text-white rounded hover:bg-orange-700 transition flex items-center gap-2"
          >
            Input Nomor Surat Resmi
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

        <div class="grid grid-cols-4 gap-3 mb-4">
          <input type="number" v-model="form.nomor_urutan_surat" class="border p-2 rounded" />
          <input type="text" v-model="form.kode_unit" class="border p-2 rounded" />
          <input type="text" v-model="form.kode_perihal" class="border p-2 rounded" />
          <input type="number" v-model="form.tahun" class="border p-2 rounded" />
        </div>

        <p class="text-gray-600 text-sm mb-4 leading-relaxed">
          Nomor urut terakhir tahun ini:
          <strong>{{ next_number - 1 }}</strong><br>
          Saran nomor berikutnya:
          <strong>{{ next_number }}</strong>
        </p>

        <div class="flex justify-end gap-3 mt-4">
          <button
            @click="closeModal"
            class="px-4 py-2 border rounded hover:bg-gray-100 transition"
          >
            Batal
          </button>
          <button
            @click="submit"
            class="px-6 py-2 bg-orange-500 text-white rounded hover:bg-orange-700 transition"
          >
            Terapkan Nomor Surat
          </button>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { usePage, router } from '@inertiajs/vue3'
import { reactive, ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'

const { props } = usePage()

const surat = props.surat
const next_number = props.next_number
const year = props.year

const form = reactive({
  nomor_urutan_surat: next_number,
  kode_unit: 'PL1',
  kode_perihal: 'RT.01.00',
  tahun: year
})

const showModal = ref(false)

const closeModal = () => (showModal.value = false)

const submit = () => {
  router.post(route('sekdir.nomorsurat.apply', surat.id), form, {
    onSuccess: () => closeModal()
  })
}

const goBack = () => {
  router.get(route('sekdir.nomorsurat'))
}
</script>

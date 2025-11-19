<template>
  <AppLayout>
    <div class="max-w-xl mx-auto bg-white p-8 rounded shadow">

      <h1 class="text-2xl font-bold mb-4">Input Nomor Surat Resmi</h1>

      <form @submit.prevent="submit">

        <label class="font-semibold">Format Nomor Surat Tugas Final</label>

        <div class="grid grid-cols-4 gap-2 mt-2 mb-4">
          <input type="number" v-model="form.nomor_urutan_surat" class="border p-2 rounded" />
          <input type="text" v-model="form.kode_unit" class="border p-2 rounded" />
          <input type="text" v-model="form.kode_perihal" class="border p-2 rounded" />
          <input type="number" v-model="form.tahun" class="border p-2 rounded" />
        </div>

        <p class="text-gray-500 text-sm">
          Nomor urut terakhir yang digunakan tahun ini:
          <strong>{{ next_number - 1 }}</strong><br />
          Saran nomor berikutnya: <strong>{{ next_number }}</strong>
        </p>

        <div class="mt-6 flex justify-end space-x-3">
          <button class="px-4 py-2 border rounded">Batalkan</button>
          <button class="px-4 py-2 bg-blue-600 text-white rounded">
            Terapkan ke Pratinjau
          </button>
        </div>

      </form>

    </div>
  </AppLayout>
</template>

<script setup>
import { usePage, router } from '@inertiajs/vue3'
import { reactive } from 'vue'
const { props } = usePage()

const form = reactive({
  nomor_urutan_surat: props.next_number,
  kode_unit: "PLI",
  kode_perihal: "RT.01.00",
  tahun: props.year
})

function submit() {
  router.post(route('sekdir.nomorsurat.apply', props.surat.id), form)
}
</script>
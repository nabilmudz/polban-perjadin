<template>
  <AppLayout>
    <Head title="Upload Bukti" />

    <div class="bg-white rounded-md shadow">
      <HeaderPage />

      <div class="p-8 border-b">
        <h1 class="text-3xl font-bold">Upload Bukti Perjalanan Dinas</h1>
      </div>

      <div class="p-8 border-b">
        <LaporanSurat
          :data="laporan"
          :tables="['surat', 'personel', 'lokasi']"
        />
      </div>

      <div class="p-8 border-b">
        <h2 class="text-xl font-semibold mb-4">Bukti Tersimpan</h2>

        <div v-if="buktiList.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div
            v-for="bukti in buktiList"
            :key="bukti.id"
            class="border rounded p-4 shadow-sm"
          >
            <div class="mb-2 font-semibold capitalize">
              {{ labelKategori(bukti.kategori) }}
            </div>

            <div class="mb-2">
              <a
                :href="bukti.url"
                target="_blank"
                class="text-blue-600 underline"
              >
                Lihat File
              </a>
            </div>

            <div class="text-sm text-gray-600">
              Nominal: Rp {{ Number(bukti.nominal ?? 0).toLocaleString('id-ID') }}
            </div>

            <div class="text-sm text-gray-600">
              {{ bukti.keterangan }}
            </div>
          </div>
        </div>

        <div v-else class="text-gray-500 italic">
          Belum ada bukti diupload
        </div>
      </div>

      <div class="p-8">
        <h2 class="text-xl font-semibold mb-4">Upload Bukti Baru</h2>

        <form @submit.prevent="submit">
          <div class="mb-4">
            <label class="block font-medium mb-2">Kategori Bukti</label>

            <div class="flex flex-col gap-2">
              <label class="flex items-center gap-2">
                <input type="radio" value="surat_visum" v-model="form.kategori" />
                Surat Visum
              </label>

              <label class="flex items-center gap-2">
                <input
                  type="radio"
                  value="laporan_perjalanan_dinas"
                  v-model="form.kategori"
                />
                Laporan Perjalanan Dinas
              </label>

              <label class="flex items-center gap-2">
                <input
                  type="radio"
                  value="bukti_perjalanan_dinas"
                  v-model="form.kategori"
                />
                Bukti Perjalanan Dinas
              </label>
            </div>

            <div v-if="errors.kategori" class="text-red-500 text-sm">
              {{ errors.kategori }}
            </div>
          </div>

          <div class="mb-4">
            <label class="block font-medium mb-2">File</label>
            <input type="file" @change="onFileChange" />

            <div v-if="errors.file" class="text-red-500 text-sm">
              {{ errors.file }}
            </div>
          </div>

          <div class="mb-4">
            <label class="block font-medium mb-2">Nominal</label>
            <input
              type="number"
              v-model="form.nominal"
              class="border rounded w-full p-2"
            />
          </div>

          <div class="mb-4">
            <label class="block font-medium mb-2">Keterangan</label>
            <textarea
              v-model="form.keterangan"
              class="border rounded w-full p-2"
            ></textarea>
          </div>

          <button
            type="submit"
            class="bg-blue-600 text-white px-6 py-2 rounded"
            :disabled="processing"
          >
            Upload Bukti
          </button>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import LaporanSurat from '@/Components/LaporanSurat.vue'

const page = usePage()

const laporan = computed(() => page.props.laporan)
const buktiList = computed(() => page.props.bukti ?? [])
const errors = computed(() => page.props.errors ?? {})

const form = useForm({
  kategori: '',
  file: null,
  nominal: '',
  keterangan: '',
})

const processing = computed(() => form.processing)

const onFileChange = (e) => {
  form.file = e.target.files[0]
}

const submit = () => {
  form.post(
    route('pelaksana.bukti.store', laporan.value.id),
    {
      preserveScroll: true,
      onSuccess: () => {
        form.reset()
      },
    }
  )
}

const labelKategori = (val) => {
  return {
    surat_visum: 'Surat Visum',
    laporan_perjalanan_dinas: 'Laporan Perjalanan Dinas',
    bukti_perjalanan_dinas: 'Bukti Perjalanan Dinas',
  }[val] ?? val
}
</script>

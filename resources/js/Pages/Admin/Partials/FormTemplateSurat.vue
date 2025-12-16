<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75 px-4 py-6">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl p-6 relative">
      <button
        class="absolute top-2 right-2 text-gray-500 text-lg font-bold"
        @click="$emit('close')"
      >
        ✕
      </button>

      <h2 class="text-2xl font-bold mb-6">
        {{ form.id ? 'Edit Template Surat' : 'Tambah Template Surat' }}
      </h2>

      <form
        @submit.prevent="$emit('save')"
        class="grid grid-cols-1 md:grid-cols-2 gap-6"
      >
        <!-- KIRI -->
        <div class="space-y-4">
          <div>
            <label class="block font-medium mb-1">
              Nama Kementerian <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.nama_kementerian"
              type="text"
              class="w-full border rounded px-3 py-2"
              required
            />
          </div>

          <div>
            <label class="block font-medium mb-1">
              Nama Direktur <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.nama_direktur"
              type="text"
              class="w-full border rounded px-3 py-2"
              required
            />
          </div>
        </div>

        <!-- KANAN -->
        <div class="space-y-4">
          <div>
            <label class="block font-medium mb-1">
              NIP Direktur <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.nip_direktur"
              type="text"
              class="w-full border rounded px-3 py-2"
              required
            />
          </div>

          <div>
            <label class="block font-medium mb-1">
              Status <span class="text-red-500">*</span>
            </label>

            <div class="flex items-center gap-3">
              <label class="relative inline-flex cursor-pointer items-center">
                <input
                  type="checkbox"
                  class="sr-only peer"
                  :checked="Number(form.status) === 1"
                  @change="onToggleStatus"
                />
                <div
                  class="w-11 h-6 bg-gray-200 rounded-full peer-checked:bg-green-500
                    after:content-[''] after:absolute after:top-0.5 after:left-[2px]
                    after:bg-white after:rounded-full after:h-5 after:w-5
                    after:transition-all peer-checked:after:translate-x-full"
                />
              </label>

              <span
                class="text-sm font-medium"
                :class="Number(form.status) === 1 ? 'text-green-600' : 'text-gray-600'"
              >
                {{ Number(form.status) === 1 ? 'Aktif' : 'Nonaktif' }}
              </span>
            </div>
          </div>
        </div>

        <div class="col-span-2">
          <label class="block font-medium mb-1">Tembusan Default (1 baris = 1 item)</label>

          <textarea
            v-model="tembusanText"
            rows="4"
            class="w-full border rounded px-3 py-2"
            placeholder="Contoh:
Ketua Jurusan
BKU"
          />
        </div>

        <!-- BUTTON -->
        <div class="col-span-2 flex justify-end gap-3 mt-4">
          <button
            type="button"
            class="px-6 py-2 bg-gray-500 text-white rounded"
            @click="$emit('close')"
          >
            Batal
          </button>

          <button
            type="submit"
            class="px-6 py-2 bg-primary-default text-white rounded"
          >
            Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  form: { type: Object, required: true },
})

const tembusanText = computed({
  get() {
    return (props.form.tembusan_default || []).join('\n')
  },
  set(val) {
    props.form.tembusan_default = String(val)
      .split('\n')
      .map(s => s.trim())
      .filter(Boolean)
  },
})
const onToggleStatus = (e) => {
  props.form.status = e.target.checked ? 1 : 0
}
</script>

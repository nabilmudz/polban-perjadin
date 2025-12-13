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
            <select
              v-model="form.status"
              class="w-full border rounded px-3 py-2"
              required
            >
              <option :value="1">Aktif</option>
              <option :value="0">Nonaktif</option>
            </select>
          </div>
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
defineProps({
  form: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-[9999]">
    <!-- overlay -->
    <div class="absolute inset-0 bg-black/40" @click="$emit('close')" />

    <!-- modal -->
    <div class="absolute inset-0 flex items-start justify-center p-6">
      <div class="bg-white w-full max-w-5xl rounded-lg shadow-lg overflow-hidden">
        <!-- header -->
        <div class="flex items-center justify-between px-5 py-4 border-b">
          <div class="min-w-0">
            <div class="font-semibold text-lg">{{ title }}</div>
            <div class="text-sm text-gray-500 truncate">
              {{ file?.url }}
            </div>
          </div>

          <div class="flex items-center gap-2">
            <a
              v-if="file?.url"
              :href="file.url"
              target="_blank"
              rel="noopener noreferrer"
              class="px-3 py-2 rounded bg-yellow-400 text-black hover:brightness-95"
              title="Open in new tab"
            >
              Open in new tab
            </a>

            <button
              class="px-3 py-2 rounded bg-gray-200 hover:bg-gray-300"
              @click="$emit('close')"
            >
              Tutup
            </button>
          </div>
        </div>

        <!-- body -->
        <div class="p-4">
          <div v-if="loading" class="text-sm text-gray-600">
            Loading preview...
          </div>

          <div v-else-if="error" class="text-sm text-red-600">
            {{ error }}
          </div>

          <div v-else class="border rounded bg-white h-[75vh] overflow-auto">
            <!-- PDF -->
            <iframe
              v-if="file?.kind === 'pdf'"
              :src="file.previewUrl || file.url"
              class="w-full h-full"
            />

            <!-- IMAGE -->
            <div v-else-if="file?.kind === 'image'" class="p-4 flex justify-center">
              <img
                :src="file.previewUrl || file.url"
                :alt="file?.name || 'Preview'"
                class="max-w-full h-auto"
              />
            </div>

            <!-- UNSUPPORTED -->
            <div v-else class="p-6 text-sm text-gray-600">
              Preview untuk tipe file ini belum didukung.
            </div>
          </div>

          <div class="mt-3 text-xs text-gray-500">
            {{ file?.kind?.toUpperCase() || "-" }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: { type: Boolean, default: false },
  file: { type: Object, default: null },
  title: { type: String, default: "File Preview" },
  loading: { type: Boolean, default: false },
  error: { type: String, default: "" },
});

defineEmits(["close"]);
</script>

<template>
  <Head title="Template Surat Tugas" />

  <div class="bg-white w-full h-full rounded-md">
    <HeaderPage />

    <div class="p-8">
      <h1 class="text-3xl font-bold mb-6">Template Surat Tugas</h1>

      <div class="w-full">
        <div class="bg-white w-full rounded-md shadow p-6">

          <div class="space-y-5">

            <div>
              <label class="block font-semibold mb-1">Nama Kementerian</label>
              <input
                v-model="form.nama_kementerian"
                :disabled="!editing"
                class="w-full px-3 py-2 rounded bg-gray-100 border-0 focus:ring-0"
              />
            </div>

            <div>
              <label class="block font-semibold mb-1">Nama Direktur</label>
              <input
                v-model="form.nama_direktur"
                :disabled="!editing"
                class="w-full px-3 py-2 rounded bg-gray-100 border-0 focus:ring-0"
              />
            </div>

            <div>
              <label class="block font-semibold mb-1">NIP Direktur</label>
              <input
                v-model="form.nip_direktur"
                :disabled="!editing"
                class="w-full px-3 py-2 rounded bg-gray-100 border-0 focus:ring-0"
              />
            </div>

            <div>
              <label class="block font-semibold mb-1">Tembusan Default</label>
              <textarea
                v-model="form.tembusan_default"
                :disabled="!editing"
                class="w-full px-3 py-2 rounded bg-gray-100 border-0 focus:ring-0 resize-none h-[42px]"
              ></textarea>
            </div>

          </div>

          <div class="flex gap-3 mt-6">
            <button
              v-if="!editing"
              @click="startEdit"
              class="px-4 py-2 bg-primary-default text-white rounded"
            >
              Edit
            </button>

            <button
              v-if="editing"
              @click="save"
              class="px-4 py-2 bg-green-600 text-white rounded"
            >
              Simpan
            </button>

            <button
              v-if="editing"
              @click="cancel"
              class="px-4 py-2 bg-gray-500 text-white rounded"
            >
              Batal
            </button>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import HeaderPage from "@/Components/HeaderPage.vue";

const page = usePage();
const data = page.props.template ?? {};

const editing = ref(false);

const form = ref({
  nama_kementerian: "",
  nama_direktur: "",
  nip_direktur: "",
  tembusan_default: ""
});

function startEdit() {
  editing.value = true;
}

function save() {
  router.put(route("admin.template.update"), {
    ...form.value,
    tembusan_default: form.value.tembusan_default
      .split("\n")
      .filter(Boolean),
  });

  resetForm();
  editing.value = false;
}

// BATAL
function cancel() {
  resetForm();
  editing.value = false;
}

function resetForm() {
  form.value = {
    nama_kementerian: "",
    nama_direktur: "",
    nip_direktur: "",
    tembusan_default: ""
  };
}
</script>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
};
</script>

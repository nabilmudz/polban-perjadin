<template>
  <Transition name="fade">
    <div
      v-if="show"
      class="fixed inset-0 bg-black/40 flex items-center justify-center z-[9999]"
    >
      <div class="bg-white rounded-md w-[90%] md:w-[70%] lg:w-[60%] p-6 max-h-[90vh] overflow-y-auto">

        <h2 class="text-2xl font-bold mb-4">Data Personel</h2>

        <!-- Tabs -->
        <div class="flex gap-3 border-b mb-4">
          <button
            v-for="option in ['mahasiswa', 'pegawai']"
            :key="option"
            @click="tab = option"
            class="px-4 py-2 font-semibold"
            :class="tab === option
              ? 'border-b-2 border-primary-default text-primary-default'
              : 'text-gray-500 hover:text-gray-700'"
          >
            {{ option === 'mahasiswa' ? 'Mahasiswa' : 'Pegawai' }}
          </button>
        </div>

        <!-- Search -->
        <input
          v-model="search"
          type="text"
          placeholder="Cari nama atau NIM/NIP..."
          class="w-full border rounded px-3 py-2 mb-4"
        />

        <DataTable
            :columns="columns"
            :data="filteredRows"

            :meta="{
                from: 1,
                to: filteredRows.length,
                total: filteredRows.length,
                per_page: filteredRows.length || 1
            }"

            :links="{
                prev: null,
                next: null
            }"

            :filters="filters"
            route-name=""
            :enable-search="false"
            :enable-status="false"
            :enable-date="false"
            @update:filters="Object.assign(filters, $event)"
            >
            <template #status="{ row }">
                <span
                class="px-2 py-1 rounded text-sm"
                :class="row.status === 'aktif'
                    ? 'bg-green-200 text-green-800'
                    : 'bg-red-200 text-red-800'"
                >
                {{ row.status }}
                </span>
            </template>
            </DataTable>

        <!-- Actions -->
        <div class="flex justify-end gap-3 mt-6">
          <button
            class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-100"
            @click="$emit('close')"
          >
            Tutup
          </button>

          <button
            class="px-4 py-2 bg-primary-default text-white rounded hover:bg-primary-dark"
            @click="confirm"
          >
            Konfirmasi
          </button>
        </div>

      </div>
    </div>
  </Transition>
</template>

<script setup>
import DataTable from '@/Components/Table/DataTable.vue'
import { ref, computed } from 'vue'

defineProps({
  show: Boolean
})
defineEmits(['close', 'confirm'])

const tab = ref('mahasiswa')
const search = ref('')
const filters = ref({
  search: '',
  status: '',
  from: '',
  to: '',
  page: 1,
})

const mahasiswa = [
  { id: 1, nama: 'Budi', identity: '2101001', status: 'aktif' },
  { id: 2, nama: 'Rika', identity: '2101045', status: 'cuti' }
]

const pegawai = [
  { id: 1, nama: 'Pak Joko', identity: '198800123', status: 'aktif' },
  { id: 2, nama: 'Bu Sinta', identity: '198900891', status: 'aktif' }
]

const columns = [
  { key: 'nama', label: 'Nama' },
  { key: 'identity', label: 'NIM / NIP' },
  { key: 'status', label: 'Status' }
]

const filteredRows = computed(() => {
  const base = tab.value === 'mahasiswa' ? mahasiswa : pegawai

  if (!search.value) return base

  return base.filter(item =>
    item.nama.toLowerCase().includes(search.value.toLowerCase()) ||
    item.identity.includes(search.value)
  )
})

const confirm = () => {
  alert('Personel dipilih')
}
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: 0.2s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

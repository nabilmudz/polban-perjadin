<template>
  <div class="p-2">
    <h1 class="text-2xl font-semibold mb-4 mt-4">Data Personel</h1>

    <div class="flex gap-3 border-b mb-6">
      <button
        v-for="option in ['mahasiswa', 'pegawai']"
        :key="option"
        @click="changeTab(option)"
        class="px-4 py-2 font-semibold capitalize"
        :class="tab === option
          ? 'border-b-2 border-primary-default text-primary-default'
          : 'text-gray-500 hover:text-gray-700'"
      >
        {{ option }}
      </button>
    </div>

    <input
      v-model="filters.search"
      type="text"
      placeholder="Cari nama atau NIM/NIP..."
      class="w-full border rounded px-3 py-2 mb-4"
    />

    <div class="overflow-x-auto">
      <DataTable
        :columns="columns"
        :data="persons.data"
        :meta="persons.meta"
        :links="persons.links"
        :filters="filters"
        :show-filters="false"
        :enable-search="false"
        :enable-status="false"
        :enable-date="false"
        route-name="pengusul.form"
        @update:filters="Object.assign(filters, $event)"
      >
        <template #nama="{ row }">
          <span
            class="cursor-pointer"
            @click="toggleSelect(row)"
            :class="isSelected(row)
              ? 'font-semibold text-primary-default'
              : 'hover:underline'"
          >
            {{ row.nama }}
          </span>
        </template>

        <template #identity="{ row }">
          <span
            class="cursor-pointer"
            @click="toggleSelect(row)"
            :class="isSelected(row)
              ? 'font-semibold text-primary-default'
              : 'hover:underline'"
          >
            {{ row.identity ?? row.nim ?? row.nip }}
          </span>
        </template>

        <template #jabatan_jurusan="{ row }">
          <span
            class="text-sm cursor-pointer"
            @click="toggleSelect(row)"
            :class="isSelected(row)
              ? 'text-orange-500 font-semibold'
              : 'text-gray-700 hover:underline'
            "
          >
            <template v-if="tab === 'pegawai'">
              {{ row.jabatan || '-' }}
              <span v-if="row.jabatan && row.golongan"> / </span>
              <span v-if="row.golongan">{{ row.golongan }}</span>
            </template>

            <template v-else>
              {{ row.jurusan || '-' }}
              <span v-if="row.jurusan && row.prodi"> / </span>
              <span v-if="row.prodi">{{ row.prodi }}</span>
            </template>
          </span>
        </template>

        <template #select="{ row }">
          <input
            type="checkbox"
            :checked="isSelected(row)"
            @change="toggleSelect(row)"
          />
        </template>
      </DataTable>
    </div>

    <div class="mt-6 p-5">
      <h2 class="text-lg font-semibold mb-2">Personel yang dipilih</h2>

      <div v-if="selectedPersons.length" class="overflow-x-auto">
        <table class="min-w-full text-sm border rounded-md bg-white">
          <thead class="bg-gray-100 border-b text-gray-600 uppercase text-xs">
            <tr>
              <th class="px-4 py-2 w-12 text-left">No</th>
              <th class="px-4 py-2 text-left">Nama</th>
              <th class="px-4 py-2 text-left">NIM / NIP</th>
              <th class="px-4 py-2 text-left">Jabatan / Jurusan</th>
              <th class="px-4 py-2 w-24 text-left">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(p, index) in selectedPersons" :key="p.type + '-' + p.id" class="border-b">
              <td class="px-4 py-2 text-center">{{ index + 1 }}</td>
              <td class="px-4 py-2">{{ p.nama }}</td>
              <td class="px-4 py-2">
                <span v-if="p.type === 'pegawai'">
                  {{ p.nip || '-' }}
                </span>
                <span v-else>
                  {{ p.nim || '-' }}
                </span>
              </td>
              <td class="px-4 py-2">
                <span v-if="p.type === 'pegawai'">
                  {{ p.jabatan || '-' }}<span v-if="p.golongan"> / {{ p.golongan }}</span>
                </span>
                <span v-else>
                  {{ p.jurusan || '-' }}<span v-if="p.prodi"> / {{ p.prodi }}</span>
                </span>
              </td>
              <td class="px-4 py-2 text-center">
                <button
                  type="button"
                  class="inline-flex items-center justify-center px-3 py-2 rounded bg-red-500 hover:bg-red-600"
                  @click="removeSelected(p.id, p.type)"
                >
                  <font-awesome-icon :icon="['far', 'trash-can']" class="text-white" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <p v-else class="text-sm text-gray-500">
        Belum ada personel yang dipilih.
      </p>
    </div>

    <div class="mt-6 flex justify-end gap-3">
      <button
        @click="emit('prev')"
        class="px-4 py-2 bg-gray-200 rounded"
      >
        Kembali
      </button>

      <button
        :disabled="!selectedPersons.length"
        @click="emit('next', selectedPersons)"
        class="px-4 py-2 bg-primary-default text-white rounded disabled:opacity-40"
      >
        Lanjut
      </button>
    </div>
  </div>
</template>

<script setup>
import DataTable from '@/Components/Table/DataTable.vue'
import { reactive, ref, watch, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import debounce from 'lodash.debounce'

const props = defineProps({
  value: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['next', 'prev'])
const page = usePage()

const normalizeFromWizard = (p) => {
  if (p.type) return p

  if (p.nim) {
    return {
      id: p.id,
      type: 'mahasiswa',
      nama: p.nama,
      nim: p.nim,
      jurusan: p.jurusan ?? '',
      prodi: p.prodi ?? ''
    }
  }

  return {
    id: p.id,
    type: 'pegawai',
    nama: p.nama,
    nip: p.nip ?? p.identity ?? '',
    pangkat: p.pangkat ?? '',
    golongan: p.golongan ?? '',
    jabatan: p.jabatan ?? ''
  }
}

const persons = computed(() => {
  const p = page.props.personel || {}
  const data = Array.isArray(p.data) ? p.data : []
  const rawMeta = p.meta || {}

  const meta = {
    per_page: Number(rawMeta.per_page ?? data.length ?? 0),
    from: rawMeta.from ?? 0,
    to: rawMeta.to ?? data.length ?? 0,
    total: rawMeta.total ?? data.length ?? 0,
    ...rawMeta,
  }

  const links = p.links || { prev: null, next: null }
  return { data, meta, links }
})

const serverFilters = page.props.filters || {}
const initialTab = page.props.tab || serverFilters.tab || 'pegawai'
const tab = ref(initialTab)

const filters = reactive({
  search: serverFilters.search || '',
  status: serverFilters.status || '',
  from: serverFilters.from || '',
  to: serverFilters.to || '',
  tab: initialTab,
})

const columns = [
  { key: 'nama', label: 'Nama' },
  { key: 'identity', label: 'NIM / NIP' },
  { key: 'jabatan_jurusan', label: 'Jabatan / Jurusan' },
  { key: 'select', label: 'Aksi' },
]

const selected = ref(
  Array.isArray(props.value)
    ? props.value.map(normalizeFromWizard)
    : []
)

const normalizePerson = (row) => {
  if (tab.value === 'pegawai') {
    return {
      id: row.id,
      type: 'pegawai',
      nama: row.nama,
      nip: row.nip ?? row.identity ?? '',
      pangkat: row.pangkat ?? '',
      golongan: row.golongan ?? '',
      jabatan: row.jabatan ?? '',
    }
  }

  return {
    id: row.id,
    type: 'mahasiswa',
    nama: row.nama,
    nim: row.nim ?? row.identity ?? '',
    jurusan: row.jurusan ?? '',
    prodi: row.prodi ?? '',
  }
}

const isSamePerson = (a, b) => a.id === b.id && a.type === b.type

const toggleSelect = (row) => {
  const normalized = normalizePerson(row)
  const idx = selected.value.findIndex((p) => isSamePerson(p, normalized))

  if (idx !== -1) {
    selected.value.splice(idx, 1)
  } else {
    selected.value.push(normalized)
  }
}

const removeSelected = (id, type) => {
  selected.value = selected.value.filter(
    (p) => !(p.id === id && p.type === type)
  )
}

const selectedPersons = computed(() => selected.value)

const isSelected = (row) => {
  const normalized = normalizePerson(row)
  return selected.value.some(p => isSamePerson(p, normalized))
}

const changeTab = (val) => {
  tab.value = val
  filters.tab = val
  filters.page = 1
  fetchPersons()
}

const fetchPersons = debounce(() => {
  router.get(
    route('pengusul.form'),
    { ...filters },
    {
      preserveState: true,
      replace: true,
      only: ['personel', 'filters', 'tab'],
    }
  )
}, 300)

watch(
  filters,
  () => {
    fetchPersons()
  },
  { deep: true }
)

</script>

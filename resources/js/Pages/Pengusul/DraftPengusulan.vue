<template>
  <Head title="Daftar Pengusulan" />  
  <div class=" bg-white w-full h-full rounded-md">
    <HeaderPage/>
    <div class="p-8">
      <h1 class="text-3xl font-bold">Draft Pengusulan</h1>
    </div>
    
    <DataTable
      :columns="columns"
      :data="suratTugas.data"
      :meta="suratTugas.meta"
      :links="suratTugas.links"
      :filters="filters"
      :status-options="statusOptions"
      :enable-status="false"
      :enable-date="false"
      route-name="pengusul.draft"
      @update:filters="Object.assign(filters, $event)"
      @changePage="(page) =>
        router.get(route('pengusul.draft'), { ...filters }, {
          preserveState: true,
          replace: true
        })
      "
    >
      <template #status_surat="{ row }">
        <StatusBadges :status="row.status_surat" />
      </template><template #action="{ row }">
        <div class="flex gap-2">
          <button
            v-for="action in getRowActions(row, currentUser.role)"
            :key="action.type"
            @click="handleAction(action.type, row)"
            :title="action.type"
            class="px-2 py-1 rounded shadow flex items-center justify-center transition hover:brightness-90"
            :class="{
              'bg-blue-500 text-white': action.color === 'blue',
              'bg-green-500 text-white': action.color === 'green',
              'bg-red-500 text-white': action.color === 'red',
              'bg-yellow-400 text-black': action.color === 'yellow',
              'bg-purple-500 text-white': action.color === 'purple',
            }"
          >
            <font-awesome-icon :icon="['far', action.icon]" class="text-md" />
          </button>
        </div>
      </template>

    </DataTable>
  </div>
</template>

<script setup>
import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import { usePage, router, Head } from '@inertiajs/vue3'
import { reactive, watch } from 'vue'
import debounce from 'lodash.debounce'
import { computed } from 'vue'
import { getRowActions } from '@/utils/rowAction'
import { statusOptions } from '@/utils/statusOptions'

const page = usePage()
const currentUser = page.props.auth.user
const suratTugas = computed(() => page.props.suratTugas)

const filters = reactive({
  search: page.props.filters?.search || '',
  status: page.props.filters?.status || '',
  from: page.props.filters?.from || '',
  to: page.props.filters?.to || '',
  page: page.props.filters?.page || 1,
})

watch(
  filters,
  debounce(() => {
    router.get(route('pengusul.draft'), { ...filters }, {
      preserveState: true,
      replace: true,
    })
  }, 300),
  { deep: true }
)

const columns = [
  { key: 'perihal_tugas', label: 'Nama Kegiatan' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_berangkat', label: 'Tanggal Berangkat' },
  { key: 'no_usulan_surat', label: 'Nomor Surat Usulan' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  // { key: 'surat_undangan', label: 'Surat Undangan' },
  { key: 'action', label: 'Aksi', fixedWidth: '180px' }
]

const handleAction = (type, row) => {
  switch(type) {
    case 'view':
      router.get(route('pengusul.view', row.id))
      break
    case 'edit':
      router.get(route('pengusul.edit', row.id))
      break
    case 'delete':
      if (confirm('Are you sure?')) {
        router.delete(route('pengusul.destroy', row.id))
      }
      break
    case 'download':
      router.get(route('pengusul.download', row.id))
      break
  }
}

</script>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
export default {
  layout: (h, page) => h(AppLayout, null, { default: () => page }),
}
</script>

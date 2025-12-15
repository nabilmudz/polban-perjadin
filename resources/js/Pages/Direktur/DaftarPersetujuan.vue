<template>
  <Head title="Daftar Persetujuan" />

  <AppLayout>
    <div class="bg-white w-full rounded-md shadow overflow-hidden relative">
      <HeaderPage title="Daftar Persetujuan Direktur" />

      <div class="p-8" :class="{ 'blur-sm': showModal }">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Menunggu Tanda Tangan</h1>

        <DataTable
          :columns="columns"
          :data="suratTugas.data"
          :meta="suratTugas.meta"
          :links="suratTugas.links"
          :filters="filters"
          route-name="direktur.daftarpersetujuan"
          @update:filters="Object.assign(filters, $event)"
          @changePage="(page) => router.get(route('direktur.daftarpersetujuan'), { ...filters, page }, { preserveState: true, replace: true })"
        >
          <template #pengusul="{ row }"><span class="text-gray-700 font-medium">{{ row.pengusul ? row.pengusul.name : '-' }}</span></template>
          <template #wadir="{ row }"><span class="text-gray-600">{{ row.wadir ? row.wadir.name : '-' }}</span></template>
          <template #nominal_biaya="{ row }">{{ formatCurrency(row.nominal_biaya) }}</template>
          <template #status_surat="{ row }"><StatusBadges :status="row.status_surat" /></template>
          <template #actions="{ row }">
            <div class="flex justify-end gap-2">
                <button @click="openReviewModal(row)" class="px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none transition-colors">Review</button>
            </div>
          </template>
        </DataTable>
      </div>

      <div v-if="showModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 transition-opacity" @click="closeModal"></div>
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true">
         <div class="bg-gray-100 rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-5xl sm:w-full max-h-[95vh] flex flex-col">
            <div class="bg-white px-6 py-4 border-b flex justify-between items-center sticky top-0 z-10">
                <h3 class="text-lg leading-6 font-bold text-gray-900">Review Surat Tugas</h3>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-500 focus:outline-none"><span class="sr-only">Close</span><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            <div class="p-6 overflow-y-auto bg-gray-100">
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="w-full md:w-2/3 shadow-lg h-full overflow-y-auto bg-white"><LaporanSurat v-if="selectedSurat" :surat="selectedSurat" /></div>
                    <div class="w-full md:w-1/3 flex flex-col gap-4">
                        <div class="bg-white p-6 rounded shadow-md">
                            <h4 class="font-bold text-lg mb-4 text-gray-800">Keputusan Direktur</h4>
                            <div class="mb-4">
                                <label for="catatan" class="block text-sm font-medium text-gray-700 mb-1">Catatan / Komentar</label>
                                <textarea id="catatan" v-model="form.catatan" rows="6" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Masukkan catatan..."></textarea>
                            </div>
                            <div class="flex flex-col gap-3 mt-6">
                                <button type="button" @click="submitDecision('revise')" :disabled="form.processing" class="w-full flex justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600">Revisi</button>
                                <div class="flex gap-3">
                                    <button type="button" @click="submitDecision('reject')" :disabled="form.processing" class="flex-1 flex justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">Tolak</button>
                                    <button type="button" @click="submitDecision('approve')" :disabled="form.processing" class="flex-1 flex justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">Setujui</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
         </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import LaporanSurat from '@/Components/LaporanSurat.vue' 
import { Head, router, useForm } from '@inertiajs/vue3' 
import { reactive, computed, ref, watch } from 'vue'
import debounce from 'lodash.debounce'

const props = defineProps({ suratTugas: Object, filters: Object })

const suratTugas = computed(() => {
  const raw = props.suratTugas || {};
  return {
    data: raw.data || [],
    links: raw.links || [],
    meta: {
        current_page: Number(raw.current_page || raw.meta?.current_page || 1),
        last_page: Number(raw.last_page || raw.meta?.last_page || 1),
        per_page: Number(raw.per_page || raw.meta?.per_page || 10),
        total: Number(raw.total || raw.meta?.total || 0),
        from: Number(raw.from || raw.meta?.from || 0),
        to: Number(raw.to || raw.meta?.to || 0)
    }
  }
})

const filters = reactive({
  search: props.filters?.search || '',
  from: props.filters?.from || '',
  to: props.filters?.to || '',
  range: props.filters?.range || '',
})

watch(filters, debounce(() => {
    router.get(route('direktur.daftarpersetujuan'), filters, { preserveState: true, replace: true })
  }, 300), { deep: true }
)

const formatCurrency = (value) => {
  if (!value) return 'Rp 0';
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
}

const columns = [
  { key: 'nama_kegiatan', label: 'Nama Kegiatan' },
  { key: 'pengusul', label: 'Pengusul', slot: 'pengusul' },
  { key: 'wadir', label: 'Wadir', slot: 'wadir' },
  { key: 'created_at', label: 'Tanggal Pengusulan' },
  { key: 'tanggal_pelaksanaan', label: 'Tanggal Berangkat' },
  { key: 'no_usulan_surat', label: 'Nomor Surat Usulan', slot: 'no_usulan_surat' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'nominal_biaya', label: 'Total Dana', slot: 'nominal_biaya' },
  { key: 'status_surat', label: 'Status', slot: 'status_surat' },
  { key: 'actions', label: 'Aksi', slot: 'actions' }
]

const showModal = ref(false);
const selectedSurat = ref(null);
const form = useForm({ catatan: '' });

const openReviewModal = (row) => { selectedSurat.value = row; form.reset(); showModal.value = true; };
const closeModal = () => { showModal.value = false; selectedSurat.value = null; };

const submitDecision = (action) => {
    if (!selectedSurat.value) return;
    let routeName = '';
    switch(action) { case 'approve': routeName = 'direktur.approve'; break; case 'reject': routeName = 'direktur.reject'; break; case 'revise': routeName = 'direktur.revise'; break; }
    form.post(route(routeName, selectedSurat.value.id), { preserveScroll: true, onSuccess: () => closeModal() });
};
</script>
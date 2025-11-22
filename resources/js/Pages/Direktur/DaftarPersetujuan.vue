<template>
  <AppLayout>
    <div class="bg-white w-full rounded-md shadow overflow-hidden relative">
      
      <HeaderPage title="Daftar Persetujuan Direktur" />

      <div class="p-8" :class="{ 'blur-sm': showModal }"> <h1 class="text-3xl font-bold mb-6 text-gray-800">Menunggu Tanda Tangan</h1>

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
          <template #pengusul="{ row }">
            <span class="text-gray-700 font-medium">{{ row.pengusul ? row.pengusul.name : '-' }}</span>
          </template>
          <template #wadir="{ row }">
            <span class="text-gray-600">{{ row.wadir ? row.wadir.name : '-' }}</span>
          </template>
          <template #status_surat="{ row }">
            <StatusBadges :status="row.status_surat" />
          </template>

          <template #actions="{ row }">
            <div class="flex justify-end gap-2">
                <button
                  @click="openReviewModal(row)" 
                  class="px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none transition-colors"
                >
                  Review
                </button>
            </div>
          </template>
        </DataTable>
      </div>

      <div v-if="showModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 transition-opacity" @click="closeModal"></div>

      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true">
         <div class="bg-gray-100 rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-5xl sm:w-full max-h-[95vh] flex flex-col">
            
            <div class="bg-white px-6 py-4 border-b flex justify-between items-center sticky top-0 z-10">
                <h3 class="text-lg leading-6 font-bold text-gray-900">
                    Review Surat Tugas
                </h3>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto bg-gray-100">
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="w-full md:w-2/3 shadow-lg">
                         <DetailSurat v-if="selectedSurat" :surat="selectedSurat" />
                    </div>

                    <div class="w-full md:w-1/3 flex flex-col gap-4">
                        
                        <div class="bg-teal-50 border-l-4 border-teal-500 p-4 rounded text-sm text-teal-700">
                            <div class="flex">
                                <div class="py-1"><svg class="fill-current h-6 w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                                <div>
                                    <p class="font-bold">Status Saat Ini</p>
                                    <p v-if="selectedSurat.nomor_surat_resmi">
                                      Sudah Diberi Nomor Resmi: {{ selectedSurat.nomor_surat_resmi }}
                                    </p>
                                    <p>Menunggu persetujuan dan tanda tangan Anda.</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded shadow-md">
                            <h4 class="font-bold text-lg mb-4 text-gray-800">Keputusan Direktur</h4>
                            
                            <div class="mb-4">
                                <label for="catatan" class="block text-sm font-medium text-gray-700 mb-1">
                                    Catatan / Komentar (Wajib jika Revisi/Ditolak)
                                </label>
                                <textarea 
                                    id="catatan" 
                                    v-model="form.catatan"
                                    rows="6" 
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    placeholder="Masukkan catatan atau alasan..."
                                ></textarea>
                                <p v-if="form.errors.catatan" class="text-red-500 text-xs mt-1">{{ form.errors.catatan }}</p>
                            </div>

                            <div class="flex flex-col gap-3 mt-6">
                                <button type="button" @click="submitDecision('revise')" :disabled="form.processing"
                                    class="w-full flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400 disabled:opacity-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    Kembalikan untuk Revisi
                                </button>
                                
                                <div class="flex gap-3">
                                    <button type="button" @click="submitDecision('reject')" :disabled="form.processing"
                                        class="flex-1 flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        Tolak
                                    </button>
                                    
                                    <button type="button" @click="submitDecision('approve')" :disabled="form.processing"
                                        class="flex-1 flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        Setujui & Terbitkan
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
             <div class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t sticky bottom-0 z-10">
                 <button @click="closeModal" type="button" class="text-sm text-gray-600 hover:text-gray-900 font-medium flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Kembali ke Daftar Persetujuan
                 </button>
            </div>

         </div>
      </div>
      </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import HeaderPage from '@/Components/HeaderPage.vue'
import StatusBadges from '@/Components/Table/StatusBadges.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import DetailSurat from '@/Components/Surat/DetailSurat.vue'
import { reactive, computed, ref } from 'vue'
import { router, usePage, useForm } from '@inertiajs/vue3'

const page = usePage()

const suratTugas = computed(() => {
  const raw = page.props.suratTugas || {};
  return {
    data: raw.data || [],
    links: raw.links || [],
    meta: raw.meta || { current_page: raw.current_page || 1, last_page: raw.last_page || 1, from: raw.from || 0, to: raw.to || 0, total: raw.total || 0, per_page: raw.per_page || 10 }
  }
})

const filters = reactive({
  search: page.props.filters?.search || '',
  status: page.props.filters?.status || '',
  from: page.props.filters?.from || '',
  to: page.props.filters?.to || ''
})

const columns = [
  { key: 'nama_kegiatan', label: 'Nama Kegiatan' },
  { key: 'pengusul', label: 'Pengusul', slot: 'pengusul' },
  { key: 'wadir', label: 'Wadir', slot: 'wadir' },
  { key: 'nomor_surat_resmi', label: 'Nomor Surat' },
  { key: 'tanggal_pelaksanaan', label: 'Tanggal' },
  { key: 'sumber_dana', label: 'Sumber Dana' },
  { key: 'status_surat', label: 'Status', slot: 'status_surat' },
  { key: 'actions', label: 'Aksi', slot: 'actions' }
]

const showModal = ref(false);
const selectedSurat = ref(null);

const form = useForm({
    catatan: '',
});

// Function triggered when "Review" button in table is clicked
const openReviewModal = (row) => {
    selectedSurat.value = row; 
    form.reset(); 
    form.clearErrors();
    showModal.value = true; 
    document.body.style.overflow = 'hidden';
};

const closeModal = () => {
    showModal.value = false;
    selectedSurat.value = null;
    document.body.style.overflow = '';
};

const submitDecision = (action) => {
    if (!selectedSurat.value) return;

    let routeName = '';
    switch(action) {
        case 'approve': routeName = 'direktur.persetujuan.approve'; break;
        case 'reject': routeName = 'direktur.persetujuan.reject'; break;
        case 'revise': routeName = 'direktur.persetujuan.revise'; break;
    }
    
    form.post(route(routeName, selectedSurat.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
        onError: () => {
        }
    });
};

</script>
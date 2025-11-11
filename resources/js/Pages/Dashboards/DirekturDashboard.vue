<template>
  <AppLayout>
    <div class="flex w-full h-screen bg-gray-100">

      <!-- ================= SIDEBAR ================= -->
      <aside class="w-64 bg-white border-r shadow-sm flex flex-col">
        
        <!-- Logo -->
        <div class="flex items-center gap-3 px-6 py-6 border-b">
          <img src="/images/polban2.png" class="w-10" />
          <div class="font-semibold leading-tight">
            <p class="text-sm">Aplikasi SPPD</p>
            <p class="text-xs text-gray-500">Polban</p>
          </div>
        </div>

        <!-- Menu -->
        <nav class="px-4 py-4 space-y-2 text-sm">

          <Link href="/direktur/dashboard"
                class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100">
            <i class="fas fa-home w-5"></i> Dashboard
          </Link>

          <Link href="/direktur/persetujuan"
                class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100">
            <i class="fas fa-check-square w-5"></i> Persetujuan
          </Link>

          <Link href="/direktur/tandatangan"
                class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100">
            <i class="fas fa-pen w-5"></i> Tanda Tangan
          </Link>

          <Link href="/direktur/history"
                class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100">
            <i class="fas fa-clock w-5"></i> History
          </Link>

          <!-- Ganti Role Dropdown -->
          <details class="px-2">
            <summary class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-100 cursor-pointer">
              <i class="fas fa-sync-alt w-5"></i> Ganti Role
            </summary>
            <div class="ml-8 mt-1 space-y-1 text-sm text-gray-600">
              <Link href="/role/admin" class="block hover:text-gray-800">Admin</Link>
              <Link href="/role/pegawai" class="block hover:text-gray-800">Pegawai</Link>
            </div>
          </details>

        </nav>
      </aside>

      <!-- ================= MAIN AREA ================= -->
      <div class="flex-1 flex flex-col">

        <!-- TOP RIGHT HEADER -->
        <header class="w-full bg-white border-b shadow-sm flex justify-end px-6 py-4">
          <div class="flex items-center gap-3">
            <div class="text-right text-sm leading-tight">
              <p class="font-semibold">Naufal Syafiq S</p>
              <p class="text-gray-500 text-xs">Direktur</p>
            </div>
            <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center font-semibold">
              NS
            </div>
          </div>
        </header>

        <!-- ================= CONTENT ================= -->
        <div class="bg-white w-full h-full rounded-md shadow p-8 space-y-8 overflow-y-auto">

          <!-- DASHBOARD TITLE -->
          <h1 class="text-3xl font-bold mb-4">Dashboard</h1>

          <!-- STAT CARDS -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Total Usulan -->
            <div class="bg-white border rounded-xl p-6 shadow flex items-center justify-between">
              <div>
                <p class="text-gray-600 text-lg">Total Usulan</p>
                <p class="text-4xl font-bold mt-2">{{ totalUsulan }}</p>
              </div>
              <i class="fas fa-file text-4xl text-blue-500"></i>
            </div>

            <!-- Bertugas -->
            <div class="bg-white border shadow rounded-xl p-6 flex items-center justify-between">
              <div>
                <p class="text-gray-600 text-lg">Bertugas</p>
                <p class="text-4xl font-bold mt-2">{{ bertugas }}</p>
              </div>
              <i class="fas fa-users text-4xl text-blue-500"></i>
            </div>

          </div>

          <!-- TABLE CARD -->
          <div class="bg-white border shadow rounded-xl p-6 space-y-6">

            <h3 class="text-xl font-semibold">Detail</h3>

            <!-- Search Align Right -->
            <div class="flex justify-end">
              <div class="flex items-center gap-2">
                <input type="text" v-model="filters.search"
                      class="border rounded px-3 py-2 w-64 text-sm"
                      placeholder="Search..." />
                <button class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>

            <!-- TABLE -->
            <table class="w-full border-collapse text-sm">
              <thead>
                <tr class="bg-gray-100">
                  <th class="p-3 border">Tanggal Berangkat</th>
                  <th class="p-3 border">Nomor Surat</th>
                  <th class="p-3 border">Sumber Dana</th>
                  <th class="p-3 border">Status</th>
                  <th class="p-3 border">Aksi</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="item in suratTugas.data" :key="item.id">
                  
                  <td class="p-3 border">{{ item.tanggal_berangkat }}</td>
                  <td class="p-3 border">{{ item.no_surat }}</td>
                  <td class="p-3 border">{{ item.sumber_dana }}</td>

                  <td class="p-3 border">
                    <span class="bg-green-200 text-green-700 px-3 py-1 rounded text-xs font-semibold">
                      Diterbitkan
                    </span>
                  </td>

                  <td class="p-3 border">
                    <Link :href="route('surat-tugas.show', item.id)"
                          class="px-3 py-1 rounded bg-blue-500 text-white hover:bg-blue-600 text-xs flex items-center gap-1">
                      <i class="fas fa-eye"></i> View
                    </Link>
                  </td>

                </tr>
              </tbody>
            </table>

            <!-- FOOTER -->
            <div class="flex items-center justify-between pt-2 text-sm text-gray-600">
              <div class="flex items-center gap-2">
                <span>Rows per page:</span>
                <select class="border rounded px-2 py-1 pr-8">
                  <option>10</option>
                  <option>25</option>
                  <option>50</option>
                </select>
              </div>

              <span>
                Showing 1 - {{ suratTugas.data.length }} of {{ suratTugas.meta.total }}
              </span>
            </div>

          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

const { props } = usePage();

const suratTugas = props.suratTugas ?? { data: [], meta: { total: 0 } };
const totalUsulan = suratTugas.meta?.total ?? 0;
const bertugas = suratTugas.data?.filter(s => s.status === "Diterbitkan")?.length ?? 0;

const filters = ref({
  search: props.filters?.search ?? ""
});
</script>

<style>
/* Ensures correct font weight and spacing like screenshot */
body {
  font-family: 'Inter', sans-serif;
}
</style>

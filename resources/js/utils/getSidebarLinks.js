
export default function getSidebarLinks(role) {
  switch (role) {
    // case 'WADIR1':
    //   return [
    //     { name: "Dashboard", route: "admin.dashboard", icon: "tachometer-alt" },
    //     { name: "Users", route: "admin.users", icon: "users" },
    //   ];
    case 'pengusul':
      return [
        {
          name: "Dashboard",
          route: "pengusul.dashboard",
          icon: "house",
          component: "Dashboards/PengusulDashboard"
        },
        {
          name: "Tambah Pengajuan",
          route: "pengusul.form",
          icon: "file-lines",
          component: "Pengusul/FormPengusulan"
        },
        {
          name: "Daftar Pengajuan",
          route: "pengusul.pengajuan",
          icon: "file-lines",
          component: "Pengusul/DaftarPengajuan"
        },
      ];

      case 'pelaksana':
      return [
        {
          name: "Dashboard",
          route: "pelaksana.dashboard",
          icon: "house",
          component: "Dashboards/PelaksanaDashboard"
        },
        {
          name: "Daftar Laporan",
          route: "pelaksana.daftarlaporan",
          icon: "clipboard",
          component: "Pelaksana/DaftarLaporan"
        },

      ];
      
    case 'direktur':
      return [
        {
          name: "Dashboard",
          route: "direktur.dashboard",
          icon: "house",
          component: "Dashboards/DirekturDashboard"
        },
        {
          name: "Daftar Persetujuan",
          route: "direktur.daftarpersetujuan",
          icon: "square-check",
          component: "Direktur/DaftarPersetujuan"
        }
      ];

    case 'bku':
      return [
        {
          name: "Dashboard",
          route: "bku.dashboard",
          icon: "house",
          component: "Dashboards/BKUDashboard"
        },
        {
          name: "Laporan & Bukti Perjalanan Dinas",
          route: "bku.daftarlaporan&perjalanan",
          icon: "file-lines",
          component: "BKU/DaftarLaporan&Perjalanan"
        },
        {
          name: "History",
          route: "bku.historyperjalanandinas",
          icon: "clock-rotate-left",
          component: "BKU/HistoryPerjalananDinas"
        }
      ];

    default:
      return [];
  }
}
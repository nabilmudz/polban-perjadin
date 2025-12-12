export default function getSidebarLinks(role) {
  switch (role) {
    case 'wadir1':
    case 'wadir2':
    case 'wadir3':
    case 'wadir4':
      return [
        {
          name: "Dashboard",
          route: `${role}.dashboard`,
          icon: "house",
          component: "Wadir/WadirDashboard"
        },
        {
          name: "Persetujuan",
          route: `${role}.persetujuan`,
          icon: "square-check",
          component: "Wadir/Persetujuan"
        },
        {
          name: "History",
          route: `${role}.history`,
          icon: "clock",
          component: "Wadir/HistoryWadir"
        },
        {
          name: "Tambah Pengusulan",
          route: "pengusul.form",
          icon: "file-lines",
          component: "Pengusul/FormPengusulan"
        },
      ];

    case 'pengusul':
      return [
        {
          name: "Dashboard",
          route: "pengusul.dashboard",
          icon: "house",
          component: "Dashboards/PengusulDashboard"
        },
        {
          name: "Tambah Pengusulan",
          route: "pengusul.form",
          icon: "file-lines",
          component: "Pengusul/FormPengusulan"
        },
        {
          name: "Daftar Pengusulan",
          route: "pengusul.pengajuan",
          icon: "file-lines",
          component: "Pengusul/DaftarPengajuan"
        },
        {
          name: "Draft",
          route: "pengusul.draft",
          icon: "file",
          component: "Pengusul/DraftPengajuan"
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
          name: "Perjalanan Dinas",
          route: "pelaksana.daftarlaporan",
          icon: "clipboard",
          component: "Pelaksana/DaftarLaporan"
        },
        {
          name: "History",
          route: "pelaksana.historypelaksana",
          icon: "clock",
          component: "Pelaksana/HistoryPelaksana"
        },
        {
          name: "Status Laporan",
          route: "pelaksana.status-laporan",
          icon: "file-search",
          component: "Pelaksana/StatusLaporan"
        }
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
          name: "Tambah Pengusulan",
          route: "pengusul.form",
          icon: "file-lines",
          component: "Pengusul/FormPengusulan"
        },
        {
          name: "Daftar Persetujuan",
          route: "direktur.daftarpersetujuan",
          icon: "square-check",
          component: "Direktur/DaftarPersetujuan"
        },
        {
          name: "History",
          route: "direktur.history",
          icon: "clock",
          component: "Direktur/DirekturHistory"
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
          name: "Tambah Pengusulan",
          route: "pengusul.form",
          icon: "file-lines",
          component: "Pengusul/FormPengusulan"
        },
        {
          name: "Laporan & Bukti Perjalanan Dinas",
          route: "bku.daftarlaporanperjalanan",
          icon: "file-lines",
          component: "BKU/DaftarLaporanPerjalanan"
        },
        {
          name: "History",
          route: "bku.historyperjalanandinas",
          icon: "clock",
          component: "BKU/HistoryPerjalananDinas"
        }
      ];

    case 'sekdir':
      return [
        {
          name: "Dashboard",
          route: "sekdir.dashboard",
          icon: "house",
          component: "Sekdir/SekdirDashboard",
        },
        {
          name: "Nomor Surat",
          route: "sekdir.nomorsurat",
          icon: "file-lines",
          component: "Sekdir/NomorSurat",
        },
        {
          name: "History",
          route: "sekdir.history",
          icon: "clock",
          component: "Sekdir/HistoryPersetujuan",
        },
        {
          name: "Tambah Pengusulan",
          route: "pengusul.form",
          icon: "file-lines",
          component: "Pengusul/FormPengusulan"
        },
      ];

    case 'admin':
      return [
        {
          name: "Daftar Pegawai",
          route: "admin.pegawai.index",
          icon: "user",
          component: "Admin/DaftarPegawai"
        },
        {
          name: "Daftar Mahasiswa",
          route: "admin.mahasiswa.index",
          icon: "user",
          component: "Admin/Mahasiswa/DaftarMahasiswa"
        },
        {
          name: "Template Surat",
          route: "admin.template",  
          icon: "file-lines",
          component: "Admin/TemplateSurat"
        }
        // {
        //   name: "Laporan & Bukti Perjalanan Dinas",
        //   route: "bku.daftarlaporan&perjalanan",
        //   icon: "file-lines",
        //   component: "BKU/DaftarLaporan&Perjalanan"
        // }
      ];

    default:
      return [];
  }
}


export default function getSidebarLinks(role) {
  switch (role) {
    // case 'admin':
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
        // {
        //   name: "Pengajuan",
        //   route: "pengusul.pengajuan",
        //   icon: "file-lines",
        //   component: "Pengusul/Pengajuan"
        // },
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
      
    default:
      return [];
  }
}
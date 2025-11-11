
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
          name: "Daftar Pengajuan",
          route: "pengusul.pengajuan",
          icon: "file-lines",
          component: "Pengusul/DaftarPengajuan"
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
          name: "Persetujuan",
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
        }
      ];

    default:
      return [];
  }
}

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
    default:
      return [];
  }
}
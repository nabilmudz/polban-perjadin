
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
    default:
      return [];
  }
}
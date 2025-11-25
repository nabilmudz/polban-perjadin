export function getSuratUndanganAction(row, userRole = '') {
    if (userRole.includes('wadir')) {
        if (row.path_file_surat_usulan) {
            return [
                { type: 'lihat_surat', icon: 'file', color: 'blue' }
            ];
        } else {
            return [
                { type: 'lihat_surat', icon: 'file', color: 'gray', disabled: true }
            ];
        }
    }

    return [];
}

export function getRowActions(row, userRole = '') {
    const actions = [];

    const isWadirPersetujuan = window.location.pathname.includes('persetujuan');

    if (userRole === 'admin') {
        actions.push({ type: 'edit', icon: 'pen-to-square', color: 'yellow' });
        actions.push({ type: 'delete', icon: 'trash-can', color: 'red' });
    }

    if (userRole !== 'admin') {
        actions.push({ type: 'view', icon: 'eye', color: 'blue' });
        actions.push({ type: 'download', icon: 'circle-down', color: 'green' });
    }

    if (userRole === 'pengusul' &&
        (row.status_surat === 'draft' || row.status_surat === 'revision_requested')
    ) {
        actions.push({ type: 'edit', icon: 'pen-to-square', color: 'yellow' });
        actions.push({ type: 'delete', icon: 'trash-can', color: 'red' });
    }

    if (userRole === 'pelaksana') {
        const isPelaksanaDashboard = window.location.pathname.includes('pelaksana/dashboard');
        if (isPelaksanaDashboard) {
            actions.push({ type: 'edit', icon: 'pen-to-square', color: 'yellow' });
        }
    }

    if (userRole.includes('wadir')) {
        
        if (isWadirPersetujuan) {
          actions.push({ type: 'review', icon: 'comment', color: 'purple' })
            return actions.filter(a => a.type !== 'view');
        }

        return actions;
    }

    return actions;
}

export function getSuratUndanganAction(row, userRole = '') {
    if (userRole.includes('wadir')) {
        return [
            {
                type: 'lihat_surat',
                icon: 'file',
                color: row.path_file_surat_usulan ? 'blue' : 'gray',
                disabled: !row.path_file_surat_usulan
            }
        ]
    }

    return []
}

export function getRowActions(row, userRole = '') {
    let actions = []

    const pathname = window.location.pathname
    const status = row.status_surat

    const isWadirPersetujuan = pathname.includes('persetujuan')
    const isPelaksanaDashboard = pathname.includes('pelaksana/dashboard')
    const isStatusLaporan = pathname.includes('pelaksana/status-laporan')

    if (userRole === 'admin') {
        actions.push({ type: 'edit', icon: 'pen-to-square', color: 'yellow' })
        actions.push({ type: 'delete', icon: 'trash-can', color: 'red' })
        return actions
    }

    actions.push({ type: 'view', icon: 'eye', color: 'blue' })
    actions.push({ type: 'download', icon: 'circle-down', color: 'green' })

    if (
        userRole === 'pengusul' &&
        (status === 'draft' || status === 'revision_requested')
    ) {
        actions.push({ type: 'edit', icon: 'pen-to-square', color: 'yellow' })
        actions.push({ type: 'delete', icon: 'trash-can', color: 'red' })
    }

    if (userRole === 'pelaksana') {

        if (isPelaksanaDashboard) {
            actions.push({ type: 'edit', icon: 'pen-to-square', color: 'yellow' })
        }

        if (isStatusLaporan) {
            actions = []

            if (
                status === 'awaiting_proof_upload' ||
                status === 'returned_for_correction'
            ) {
                actions.push({
                    type: 'upload-bukti',
                    icon: 'upload',
                    color: 'green'
                })
            } else {
                actions.push({
                    type: 'view',
                    icon: 'eye',
                    color: 'blue'
                })
            }

            return actions
        }
    }

    if (userRole.includes('wadir')) {

        if (isWadirPersetujuan) {
            actions.push({
                type: 'review',
                icon: 'comment',
                color: 'purple'
            })
            return actions.filter(a => a.type !== 'view')
        }

        return actions
    }

    return actions
}

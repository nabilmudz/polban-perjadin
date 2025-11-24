export function getRowActions(row, userRole='') {
  const actions = []
  
  if (userRole === 'admin') {
    actions.push({ type: 'edit', icon: 'pen-to-square', color: 'yellow' })
    actions.push({ type: 'delete', icon: 'trash-can', color: 'red' })
  }

  if (userRole !== 'admin') {
    actions.push({ type: 'view', icon: 'eye', color: 'blue' })
    actions.push({ type: 'download', icon: 'circle-down', color: 'green' })
  }
  if (userRole === 'pengusul' && (row.status_surat === 'draft' || row.status_surat === 'revision_requested')) {
    actions.push({ type: 'edit', icon: 'pen-to-square', color: 'yellow' })
    actions.push({ type: 'delete', icon: 'trash-can', color: 'red' })
  }

  if (userRole === 'pelaksana') {
    const isPelaksanaDashboard = window.location.pathname.includes('pelaksana/dashboard')

    if (isPelaksanaDashboard) {
      actions.push({ type: 'edit', icon: 'pen-to-square', color: 'yellow' })
    }
  }

  if (userRole.startsWith('wadir')) {
    actions.push({ type: 'review', icon: 'file-lines', color: 'purple' })
  }

  return actions
}

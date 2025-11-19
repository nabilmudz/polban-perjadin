export function getRowActions(row, userRole) {
  const actions = []
  
  if (userRole !== 'admin') {
    actions.push({ type: 'view', icon: 'eye', color: 'blue' })
    actions.push({ type: 'download', icon: 'circle-down', color: 'green' })
  }
  if (userRole === 'pengusul' && (row.status_surat === 'draft' || row.status_surat === 'revision_requested')) {
    actions.push({ type: 'edit', icon: 'pen-to-square', color: 'yellow' })
    actions.push({ type: 'delete', icon: 'trash-can', color: 'red' })
  }

  return actions
}

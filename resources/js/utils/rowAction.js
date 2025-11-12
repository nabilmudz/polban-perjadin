export function getRowActions(row, userRole) {
  const actions = []

  actions.push({ type: 'view', icon: 'eye', color: 'blue' })

  if (userRole === 'pengusul' && row.status_surat === 'draft') {
    actions.push({ type: 'edit', icon: 'pen-to-square', color: 'yellow' })
    actions.push({ type: 'delete', icon: 'trash-can', color: 'red' })
  }

  actions.push({ type: 'download', icon: 'circle-down', color: 'green' })

  return actions
}

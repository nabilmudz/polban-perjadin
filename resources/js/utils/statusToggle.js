export const statusToggle = {
  1: { label: 'Aktif', color: 'green' },
  0: { label: 'Nonaktif', color: 'red' },
}

export const getStatusBadge = (status) => {
  const st = statusToggle[status]
  return `<span class="px-2 py-1 rounded text-white text-xs bg-${st.color}-500">${st.label}</span>`
}

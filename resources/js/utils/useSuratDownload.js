export function useSuratDownload() {
  const resolveId = (rowOrId) =>
    typeof rowOrId === 'object' ? (rowOrId.surat_tugas_id ?? rowOrId.id) : rowOrId

  const downloadPdf = (rowOrId) => {
    const id = resolveId(rowOrId)
    window.location.href = route('surat.download', id)
  }

  const openPdfInline = (rowOrId) => {
    const id = resolveId(rowOrId)
    window.open(route('surat.pdf', id), '_blank')
  }

  return { downloadPdf, openPdfInline, resolveId }
}

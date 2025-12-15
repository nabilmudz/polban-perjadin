export function applyActiveTemplate(surat = {}, tpl = null) {
  const t = tpl || {}

  const clean = (v) => {
    if (v === null || v === undefined) return null
    const s = String(v).trim()
    return s.length ? v : null
  }

  const tembusanFromTpl = Array.isArray(t.tembusan_default) ? t.tembusan_default : []
  const tembusanFromSurat = Array.isArray(surat.template_tembusan) ? surat.template_tembusan : []

  return {
    ...surat,
    template_nama_kementerian: clean(surat.template_nama_kementerian) ?? clean(t.nama_kementerian),
    template_nama_direktur: clean(surat.template_nama_direktur) ?? clean(t.nama_direktur),
    template_nip_direktur: clean(surat.template_nip_direktur) ?? clean(t.nip_direktur),
    template_tembusan: tembusanFromSurat.length ? tembusanFromSurat : tembusanFromTpl,
  }
}

export const statusToggle = {
  1: { label: 'Aktif', color: 'green' },
  0: { label: 'Nonaktif', color: 'red' },
}

export const getStatusBadge = (status) => {
  const st = statusToggle[status]
  return `<span class="px-2 py-1 rounded text-white text-xs bg-${st.color}-500">${st.label}</span>`
}

export const roleActions = {
  pengusul: {
    draft: ['submit_wadir'],
    revision_requested: ['resubmit_wadir'],
    returned_for_correction: ['resubmit_wadir'],
  },

  wadir1: {
    submitted_wadir_review: ['approve', 'request_revision', 'reject'],
  },
  wadir2: {
    submitted_wadir_review: ['approve', 'request_revision', 'reject'],
  },
  wadir3: {
    submitted_wadir_review: ['approve', 'request_revision', 'reject'],
  },
  wadir4: {
    submitted_wadir_review: ['approve', 'request_revision', 'reject'],
  },

  sekdir: {
    approved_wadir: ['number_surat'],
    pending_sekdir_numbering: ['send_to_direktur', 'return_for_correction'],
  },

  direktur: {
    pending_direktur_signature: ['publish', 'return_for_correction', 'reject'],
  },

  bku: {
    under_bku_review: ['mark_completed', 'return_for_correction'],
  },
};

export const actionToStatus = {
  submit_wadir: 'submitted_wadir_review',
  resubmit_wadir: 'submitted_wadir_review',

  approve: 'approved_wadir',
  request_revision: 'revision_requested',
  reject: 'rejected',

  number_surat: 'pending_sekdir_numbering',
  send_to_direktur: 'pending_direktur_signature',
  return_for_correction: 'returned_for_correction',

  publish: 'published',

  mark_completed: 'completed',
};
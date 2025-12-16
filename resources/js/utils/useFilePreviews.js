import { reactive } from "vue";

function guessKind(url = "") {
  const clean = url.split("?")[0].toLowerCase();
  const ext = clean.includes(".") ? clean.split(".").pop() : "";

  if (["png", "jpg", "jpeg", "webp", "gif", "bmp"].includes(ext)) return "image";
  if (ext === "pdf") return "pdf";
  return "unknown";
}

function normalizeUrl(inputUrl = "") {
  if (/^https?:\/\//i.test(inputUrl)) return inputUrl;
  if (inputUrl.startsWith("/")) return inputUrl;

  return `/storage/${inputUrl.replace(/^storage\//, "")}`;
}

export function useFilePreview() {
  const state = reactive({
    show: false,
    loading: false,
    error: "",
    file: null,
    _objectUrl: null,
  });

  async function open(fileInput) {
    const raw = fileInput?.url || fileInput?.path || "";
    const url = normalizeUrl(raw);

    state.show = true;
    state.loading = true;
    state.error = "";
    state.file = {
      name: fileInput?.name || "File",
      url,
      kind: guessKind(url),
      mime: "",
      previewUrl: "",
    };

    if (state._objectUrl) {
      URL.revokeObjectURL(state._objectUrl);
      state._objectUrl = null;
    }

    try {
      const res = await fetch(url, { credentials: "same-origin" });
      if (!res.ok) throw new Error(`Failed to load file (${res.status})`);

      const blob = await res.blob();
      state.file.mime = blob.type || "";
      state._objectUrl = URL.createObjectURL(blob);
      state.file.previewUrl = state._objectUrl;

      if (state.file.kind === "unknown") {
        if ((blob.type || "").includes("pdf")) state.file.kind = "pdf";
        if ((blob.type || "").startsWith("image/")) state.file.kind = "image";
      }
    } catch (e) {
      state.error = e?.message || "Failed to preview file";
    } finally {
      state.loading = false;
    }
  }

  function close() {
    state.show = false;
    state.loading = false;

    if (state._objectUrl) {
      URL.revokeObjectURL(state._objectUrl);
      state._objectUrl = null;
    }

    state.file = null;
    state.error = "";
  }

  return {
    show: state.show,
    loading: state.loading,
    error: state.error,
    file: state.file,
    open,
    close,

    state,
  };
}

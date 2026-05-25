// ---- File-drop zone factory ----
// Creates self-contained drag-and-drop + preview behaviour for a single-file input.
// currentUrl: if set, shows the existing image as the initial preview.
function makeFileDrop(dropZoneId, fileInputId, previewsId, currentUrl) {
  const dropZone = document.getElementById(dropZoneId);
  const fileInput = document.getElementById(fileInputId);
  const previews = document.getElementById(previewsId);
  if (!dropZone || !fileInput || !previews) return;

  const ALLOWED = ["image/jpeg", "image/png", "image/webp"];
  let selectedFile = null;

  function renderPreview() {
    previews.innerHTML = "";
    if (!selectedFile) return;

    const wrap = document.createElement("div");
    wrap.className = "file-preview";

    const img = document.createElement("img");
    img.src = URL.createObjectURL(selectedFile);
    img.alt = selectedFile.name;
    img.onload = function () {
      URL.revokeObjectURL(img.src);
    };
    wrap.appendChild(img);

    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "file-preview__remove";
    btn.textContent = "✕";
    btn.title = "Remove";
    btn.addEventListener("click", function () {
      selectedFile = null;
      syncInput();
      renderPreview();
    });
    wrap.appendChild(btn);

    previews.appendChild(wrap);
  }

  function renderCurrentUrl(url) {
    previews.innerHTML = "";
    if (!url) return;

    const wrap = document.createElement("div");
    wrap.className = "file-preview";

    const img = document.createElement("img");
    img.src = url;
    img.alt = "Current image";
    wrap.appendChild(img);

    const label = document.createElement("span");
    label.style.cssText =
      "position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.55);" +
      "color:#fff;font-size:0.6rem;text-align:center;padding:2px 0;";
    label.textContent = "current";
    wrap.appendChild(label);

    previews.appendChild(wrap);
  }

  function syncInput() {
    const dt = new DataTransfer();
    if (selectedFile) dt.items.add(selectedFile);
    fileInput.files = dt.files;
  }

  function pickFile(file) {
    if (!file || !ALLOWED.includes(file.type)) return;
    selectedFile = file;
    syncInput();
    renderPreview();
  }

  fileInput.addEventListener("change", function () {
    if (this.files[0]) pickFile(this.files[0]);
    this.value = "";
  });

  dropZone.addEventListener("dragover", function (e) {
    e.preventDefault();
    dropZone.classList.add("drag-over");
  });
  dropZone.addEventListener("dragleave", function () {
    dropZone.classList.remove("drag-over");
  });
  dropZone.addEventListener("drop", function (e) {
    e.preventDefault();
    dropZone.classList.remove("drag-over");
    const file = e.dataTransfer.files[0];
    if (file) pickFile(file);
  });

  // Expose a reset helper used when the modal closes or reopens
  return {
    reset: function (url) {
      selectedFile = null;
      fileInput.value = "";
      if (url) {
        renderCurrentUrl(url);
      } else {
        previews.innerHTML = "";
      }
    },
  };
}

// ---- Init drop zones ----
const createDrop = makeFileDrop(
  "create-file-drop-zone",
  "create-image-upload",
  "create-file-previews",
);
const editDrop = makeFileDrop(
  "edit-file-drop-zone",
  "edit-image-upload",
  "edit-file-previews",
);

// ---- Live search & category filter ----
const searchInput = document.getElementById("product-search");
const categoryFilter = document.getElementById("category-filter");
const tbody = document.getElementById("products-tbody");
const countEl = document.getElementById("visible-count");

const filterTable = () => {
  const q = searchInput.value.toLowerCase();
  const catId = categoryFilter.value;
  let visible = 0;

  tbody.querySelectorAll("tr[data-name]").forEach(function (row) {
    const name = row.dataset.name || "";
    const rowCatId = row.dataset.categoryId || "";

    const show = (!q || name.includes(q)) && (!catId || rowCatId === catId);
    row.style.display = show ? "" : "none";
    if (show) visible++;
  });

  countEl.textContent = visible + " product" + (visible !== 1 ? "s" : "");
};

searchInput.addEventListener("input", filterTable);
categoryFilter.addEventListener("change", filterTable);

// ---- Edit modal ----
const editModal = document.getElementById("edit-modal");
const editForm = document.getElementById("edit-form");

document.querySelectorAll(".js-edit-btn").forEach(function (btn) {
  btn.addEventListener("click", function () {
    document.getElementById("edit-name").value = btn.dataset.name;
    document.getElementById("edit-price").value = btn.dataset.price;
    document.getElementById("edit-material").value = btn.dataset.material;
    document.getElementById("edit-description").value = btn.dataset.description;
    document.getElementById("edit-category").value =
      btn.dataset.categoryId || "";

    editDrop.reset(btn.dataset.imageUrl || "");
    editForm.action = "/admin/products/" + btn.dataset.id + "/update";
    editModal.style.display = "flex";
  });
});

document.getElementById("edit-cancel").addEventListener("click", function () {
  editModal.style.display = "none";
  editDrop.reset("");
});
editModal.addEventListener("click", function (e) {
  if (e.target === editModal) {
    editModal.style.display = "none";
    editDrop.reset("");
  }
});

// ---- Delete modal ----
const deleteModal = document.getElementById("delete-modal");
const deleteForm = document.getElementById("delete-form");
const deleteProductName = document.getElementById("delete-product-name");

document.querySelectorAll(".js-delete-btn").forEach(function (btn) {
  btn.addEventListener("click", function () {
    deleteProductName.textContent = btn.dataset.name;
    deleteForm.action = "/admin/products/" + btn.dataset.id + "/delete";
    deleteModal.style.display = "flex";
  });
});

document.getElementById("delete-cancel").addEventListener("click", function () {
  deleteModal.style.display = "none";
});
deleteModal.addEventListener("click", function (e) {
  if (e.target === deleteModal) deleteModal.style.display = "none";
});

// ---- Create modal ----
const createModal = document.getElementById("create-modal");

document
  .getElementById("btn-open-create")
  .addEventListener("click", function () {
    createModal.style.display = "flex";
  });
document.getElementById("create-cancel").addEventListener("click", function () {
  createModal.style.display = "none";
  createDrop.reset("");
});
createModal.addEventListener("click", function (e) {
  if (e.target === createModal) {
    createModal.style.display = "none";
    createDrop.reset("");
  }
});

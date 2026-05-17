/**
 * order-wizard.js — Multi-step form navigation for the Custom Order Wizard.
 * Handles:
 *   - Step panel show/hide
 *   - Progress bar active / done state
 *   - Custom-dimensions field visibility
 *   - Character counter on the textarea
 *   - Image file preview thumbnails
 *   - Summary panel population before final submission
 */
(function () {
  "use strict";

  let currentStep = 1;
  const TOTAL_STEPS = 5;

  // ---- Step navigation helpers ----

  function showStep(n) {
    // Hide all panels
    document.querySelectorAll(".wizard-panel").forEach(function (panel) {
      panel.classList.remove("is-active");
    });

    // Show target panel
    const target = document.getElementById("step-" + n);
    if (target) target.classList.add("is-active");

    // Update progress indicators
    document.querySelectorAll(".wizard-step").forEach(function (stepEl) {
      const s = parseInt(stepEl.dataset.step, 10);
      stepEl.classList.toggle("wizard-step--active", s === n);
      stepEl.classList.toggle("wizard-step--done", s < n);
    });

    currentStep = n;

    // Populate summary when reaching the review step
    if (n === TOTAL_STEPS) populateSummary();

    // Scroll back to the top of the form smoothly
    const form = document.getElementById("wizard-form");
    if (form) form.scrollIntoView({ behavior: "smooth", block: "start" });
  }

  // ---- Next / Prev button wiring ----

  document.querySelectorAll(".js-next").forEach(function (btn) {
    btn.addEventListener("click", function () {
      const next = parseInt(btn.dataset.next, 10);
      if (next > 0 && next <= TOTAL_STEPS) showStep(next);
    });
  });

  document.querySelectorAll(".js-prev").forEach(function (btn) {
    btn.addEventListener("click", function () {
      const prev = parseInt(btn.dataset.prev, 10);
      if (prev > 0) showStep(prev);
    });
  });

  // ---- Custom dimensions toggle ----

  document
    .querySelectorAll('input[name="size_type"]')
    .forEach(function (radio) {
      radio.addEventListener("change", function () {
        const wrap = document.getElementById("custom-dimensions-wrap");
        if (wrap)
          wrap.style.display = this.value === "custom" ? "flex" : "none";
      });
    });

  // ---- Textarea character counter ----

  const reqTextarea = document.getElementById("special_requests");
  const reqCount = document.getElementById("req-count");
  if (reqTextarea && reqCount) {
    reqTextarea.addEventListener("input", function () {
      reqCount.textContent = this.value.length;
    });
  }

  // ---- Image previews ----

  const fileInput = document.getElementById("image-upload");
  const previewsWrap = document.getElementById("file-previews");
  const dropZone = document.getElementById("file-drop-zone");
  const MAX_FILES = 5;

  // Collect selected files across multiple picks
  let selectedFiles = [];

  if (fileInput && previewsWrap) {
    fileInput.addEventListener("change", function () {
      addFiles(Array.from(this.files));
      this.value = ""; // reset so the same file can be re-added after removal
    });
  }

  // Drag-and-drop onto the drop zone
  if (dropZone) {
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
      addFiles(
        Array.from(e.dataTransfer.files).filter(function (f) {
          return f.type.startsWith("image/");
        }),
      );
    });
  }

  function addFiles(newFiles) {
    const allowed = ["image/jpeg", "image/png", "image/webp"];
    newFiles.forEach(function (file) {
      if (selectedFiles.length >= MAX_FILES) return;
      if (!allowed.includes(file.type)) return;
      selectedFiles.push(file);
    });
    renderPreviews();
    syncFileInput();
  }

  function renderPreviews() {
    if (!previewsWrap) return;
    previewsWrap.innerHTML = "";
    selectedFiles.forEach(function (file, idx) {
      const wrap = document.createElement("div");
      wrap.className = "file-preview";

      const img = document.createElement("img");
      img.src = URL.createObjectURL(file);
      img.alt = file.name;
      img.onload = function () {
        URL.revokeObjectURL(img.src);
      };
      wrap.appendChild(img);

      const removeBtn = document.createElement("button");
      removeBtn.type = "button";
      removeBtn.className = "file-preview__remove";
      removeBtn.textContent = "✕";
      removeBtn.title = "Remove";
      removeBtn.addEventListener("click", function () {
        selectedFiles.splice(idx, 1);
        renderPreviews();
        syncFileInput();
      });
      wrap.appendChild(removeBtn);

      previewsWrap.appendChild(wrap);
    });
  }

  /**
   * Transfer the in-memory file list back onto the file input using a
   * DataTransfer object so the form POST picks them up correctly.
   */
  function syncFileInput() {
    if (!fileInput) return;
    const dt = new DataTransfer();
    selectedFiles.forEach(function (f) {
      dt.items.add(f);
    });
    fileInput.files = dt.files;
  }

  // ---- Summary population ----

  function populateSummary() {
    // Object type
    const objRadio = document.querySelector(
      'input[name="object_type_id"]:checked',
    );
    if (objRadio) {
      const label = document.querySelector(
        'label[for="' + objRadio.id + '"] .object-option__name',
      );
      set("sum-object", label ? label.textContent : objRadio.value);
    }

    // Glaze
    const glazeRadio = document.querySelector('input[name="glaze_id"]:checked');
    if (glazeRadio) {
      const label =
        document.querySelector(
          'label[for="' + glazeRadio.id + '"] ~ .glaze-option__name',
        ) ||
        glazeRadio
          .closest(".glaze-option")
          ?.querySelector(".glaze-option__name");
      set("sum-glaze", label ? label.textContent : glazeRadio.value);
    }

    // Size
    const sizeRadio = document.querySelector('input[name="size_type"]:checked');
    if (sizeRadio) {
      const custom = document.getElementById("custom_dimensions");
      const sizeLabel = document.querySelector(
        'label[for="' + sizeRadio.id + '"] .size-option__name',
      );
      let sizeText = sizeLabel ? sizeLabel.textContent : sizeRadio.value;
      if (sizeRadio.value === "custom" && custom && custom.value.trim()) {
        sizeText += " — " + custom.value.trim();
      }
      set("sum-size", sizeText);
    }

    // Budget
    const minVal = document.getElementById("budget_min")?.value;
    const maxVal = document.getElementById("budget_max")?.value;
    if (minVal || maxVal) {
      const parts = [];
      if (minVal) parts.push("from $" + minVal);
      if (maxVal) parts.push("to $" + maxVal);
      set("sum-budget", parts.join(" "));
    } else {
      set("sum-budget", "Not specified");
    }

    // Special requests
    const requests = document.getElementById("special_requests")?.value.trim();
    set("sum-requests", requests || "None");
  }

  function set(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
  }
})();

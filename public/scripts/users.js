// ---- Live search & role filter ----
const searchInput = document.getElementById("user-search");
const roleFilter = document.getElementById("role-filter");
const tbody = document.getElementById("users-tbody");
const countEl = document.getElementById("visible-count");

const filterTable = () => {
  const q = searchInput.value.toLowerCase();
  const role = roleFilter.value;
  let visible = 0;

  tbody.querySelectorAll("tr").forEach(function (row) {
    const name = row.dataset.username || "";
    const email = row.dataset.email || "";
    const r = row.dataset.role || "";

    const matchQ = !q || name.includes(q) || email.includes(q);
    const matchRole = !role || r === role;

    const show = matchQ && matchRole;
    row.style.display = show ? "" : "none";
    if (show) visible++;
  });

  countEl.textContent = visible + " user" + (visible !== 1 ? "s" : "");
};

searchInput.addEventListener("input", filterTable);
roleFilter.addEventListener("change", filterTable);

// ---- Edit modal ----
const editModal = document.getElementById("edit-modal");
const editForm = document.getElementById("edit-form");

document.querySelectorAll(".js-edit-btn").forEach(function (btn) {
  btn.addEventListener("click", function () {
    const id = btn.dataset.id;
    document.getElementById("edit-username").value = btn.dataset.username;
    document.getElementById("edit-email").value = btn.dataset.email;
    document.getElementById("edit-is-admin").checked =
      btn.dataset.isAdmin === "1";
    document.getElementById("edit-is-active").checked =
      btn.dataset.isActive === "1";
    editForm.action = "/admin/users/" + id + "/update";
    editModal.style.display = "flex";
  });
});

document.getElementById("edit-cancel").addEventListener("click", function () {
  editModal.style.display = "none";
});
editModal.addEventListener("click", function (e) {
  if (e.target === editModal) editModal.style.display = "none";
});

// ---- Delete modal ----
const deleteModal = document.getElementById("delete-modal");
const deleteForm = document.getElementById("delete-form");
const deleteUsername = document.getElementById("delete-username");

document.querySelectorAll(".js-delete-btn").forEach(function (btn) {
  btn.addEventListener("click", function () {
    deleteUsername.textContent = btn.dataset.username;
    deleteForm.action = "/admin/users/" + btn.dataset.id + "/delete";
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
});
createModal.addEventListener("click", function (e) {
  if (e.target === createModal) createModal.style.display = "none";
});

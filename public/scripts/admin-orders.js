const searchInput = document.getElementById("order-search");
const statusFilter = document.getElementById("status-filter");
const tbody = document.getElementById("orders-tbody");
const countEl = document.getElementById("visible-count");

const filterOrders = () => {
  const q = searchInput.value.toLowerCase();
  const status = statusFilter.value;
  let visible = 0;

  tbody.querySelectorAll("tr[data-status]").forEach(function (row) {
    const customer = row.dataset.customer || "";
    const object = row.dataset.object || "";
    const s = row.dataset.status || "";

    const matchQ = !q || customer.includes(q) || object.includes(q);
    const matchStatus = !status || s === status;

    const show = matchQ && matchStatus;
    row.style.display = show ? "" : "none";
    if (show) visible++;
  });

  countEl.textContent = visible + " order" + (visible !== 1 ? "s" : "");
};

searchInput.addEventListener("input", filterOrders);
statusFilter.addEventListener("change", filterOrders);

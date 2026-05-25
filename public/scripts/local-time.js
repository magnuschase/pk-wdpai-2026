document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("time[data-local]").forEach((el) => {
    const date = new Date(el.getAttribute("datetime"));
    if (isNaN(date)) return;

    const withTime = el.hasAttribute("data-time");
    el.textContent = date.toLocaleString(
      navigator.language,
      withTime
        ? {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
          }
        : { day: "2-digit", month: "short", year: "numeric" },
    );
  });
});

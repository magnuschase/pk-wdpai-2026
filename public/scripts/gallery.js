/**
 * gallery.js — Favourite toggle using the Fetch API.
 * POSTs to /gallery/favourite with JSON {item_id} and toggles the
 * .is-faved class + swaps the SVG heart icon on success.
 */
(function () {
  "use strict";

  // SVG paths for the two heart states
  const HEART_FILLED =
    '<path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402 0-3.791 3.068-5.191 5.281-5.191 1.312 0 4.151.501 5.719 4.457 1.59-3.968 4.464-4.447 5.726-4.447 2.54 0 5.274 1.621 5.274 5.181 0 4.069-5.136 8.625-11 14.402z"/>';
  const HEART_OUTLINE =
    '<path d="M12 4.419c-2.826-5.695-11.999-4.064-11.999 3.27 0 7.27 9.903 10.938 11.999 15.311 2.096-4.373 12-8.041 12-15.311 0-7.327-9.17-8.972-12-3.27z"/>';

  function makeSvg(pathContent) {
    return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">${pathContent}</svg>`;
  }

  function handleFavClick(e) {
    const btn = e.currentTarget;
    const itemId = parseInt(btn.dataset.itemId, 10);
    if (!itemId) return;

    // Optimistic UI update
    const wasFaved = btn.classList.contains("is-faved");
    btn.classList.toggle("is-faved", !wasFaved);
    btn.innerHTML = makeSvg(wasFaved ? HEART_OUTLINE : HEART_FILLED);
    btn.disabled = true;

    fetch("/gallery/favourite", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ item_id: itemId }),
    })
      .then(function (res) {
        if (!res.ok) throw new Error("Server error: " + res.status);
        return res.json();
      })
      .then(function (data) {
        // Sync with actual server state
        btn.classList.toggle("is-faved", data.favourited);
        btn.innerHTML = makeSvg(data.favourited ? HEART_FILLED : HEART_OUTLINE);
      })
      .catch(function () {
        // Revert optimistic update on failure
        btn.classList.toggle("is-faved", wasFaved);
        btn.innerHTML = makeSvg(wasFaved ? HEART_FILLED : HEART_OUTLINE);
      })
      .finally(function () {
        btn.disabled = false;
      });
  }

  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".js-fav-btn").forEach(function (btn) {
      btn.addEventListener("click", handleFavClick);
    });
  });
})();

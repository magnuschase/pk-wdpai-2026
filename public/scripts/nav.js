const navOpenBtn = document.querySelector("#navOpenBtn");
const sidebar = document.getElementById("sidebar");
const navbar = document.getElementById("navbar");

navOpenBtn.addEventListener("click", () => {
  navbar.classList.toggle("hidden");
  sidebar.classList.toggle("hidden");
});

window.addEventListener("click", (event) => {
  // If the click is outside the sidebar and the sidebar is open, close it
  if (
    !sidebar.contains(event.target) &&
    !navOpenBtn.contains(event.target) &&
    !sidebar.classList.contains("hidden")
  ) {
    sidebar.classList.add("hidden");
    navbar.classList.remove("hidden");
  }
});

const userDropdownToggle = document.getElementById("user-dropdown-toggle");
const userDropdown = document.querySelector(".dropdown");

if (userDropdownToggle && userDropdown) {
  userDropdownToggle.addEventListener("click", () => {
    userDropdown.classList.toggle("dropdown--hidden");
  });

  window.addEventListener("click", (event) => {
    if (
      !userDropdown.contains(event.target) &&
      !userDropdownToggle.contains(event.target) &&
      !userDropdown.classList.contains("dropdown--hidden")
    ) {
      userDropdown.classList.add("dropdown--hidden");
    }
  });
}

window.addEventListener("resize", () => {
  if (AppBreakpoints.isDesktop()) {
    sidebar.classList.add("hidden");
    navbar.classList.remove("hidden");
  }
});

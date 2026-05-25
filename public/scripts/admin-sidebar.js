const MOBILE_BREAKPOINT = 768;

// Highlight active nav item based on current URL
document.querySelectorAll(".admin-nav__item").forEach((link) => {
  if (window.location.pathname.startsWith(link.getAttribute("href"))) {
    link.classList.add("admin-nav__item--active");
  }
});

const navOpenBtn = document.querySelector("#adminNavOpenBtn");
const sidebar = document.getElementById("adminSidebar");
const navbar = document.getElementById("adminNavbar");

const isMobile = () => window.innerWidth < MOBILE_BREAKPOINT;

const syncAdminNavLayout = () => {
  if (isMobile()) {
    sidebar.classList.add("hidden");
    navbar.classList.remove("hidden");
  } else {
    sidebar.classList.remove("hidden");
    navbar.classList.add("hidden");
  }
};

if (navOpenBtn && sidebar && navbar) {
  navOpenBtn.addEventListener("click", () => {
    if (!isMobile()) return;
    sidebar.classList.toggle("hidden");
  });

  window.addEventListener("click", (event) => {
    if (
      !isMobile() ||
      sidebar.classList.contains("hidden") ||
      sidebar.contains(event.target) ||
      navOpenBtn.contains(event.target)
    ) {
      return;
    }
    sidebar.classList.add("hidden");
  });

  syncAdminNavLayout();
  window.addEventListener("resize", syncAdminNavLayout);
}

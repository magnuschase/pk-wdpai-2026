/**
 * Canonical layout breakpoints - keep in sync with :root in base.css.
 * Mobile layout matches @media (max-width: 930px) across stylesheets.
 */
window.AppBreakpoints = Object.freeze({
  MOBILE_MAX: 930,
  TABLET_MAX: 1024,

  isMobile() {
    return window.matchMedia(`(max-width: ${this.MOBILE_MAX}px)`).matches;
  },

  isDesktop() {
    return window.matchMedia(`(min-width: ${this.MOBILE_MAX + 1}px)`).matches;
  },
});

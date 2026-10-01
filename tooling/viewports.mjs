// SHELTER responsive viewport matrix — single source of truth for Playwright projects, screenshots and RESPONSIVE-QA-MATRIX.md.
// Mandatory widths from the owner's RESPONSIVE DESIGN — MANDATORY rules (website + owner dashboard), plus landscape and 200% zoom.
// [name, width, height, touch, group]
export const VIEWPORTS = [
  ['m320', 320, 568, true, 'mobile'], ['m360', 360, 800, true, 'mobile'], ['m375', 375, 667, true, 'mobile'],
  ['m390', 390, 844, true, 'mobile'], ['m412', 412, 915, true, 'mobile'], ['m430', 430, 932, true, 'mobile'],
  ['t768', 768, 1024, true, 'tablet'], ['t820', 820, 1180, true, 'tablet'],
  ['t1024-land', 1024, 768, true, 'tablet-landscape'], ['t1180-land', 1180, 820, true, 'tablet-landscape'],
  ['l1280', 1280, 800, false, 'laptop'], ['l1366', 1366, 768, false, 'laptop'],
  ['d1440', 1440, 900, false, 'desktop'], ['d1536', 1536, 864, false, 'desktop'], ['d1920', 1920, 1080, false, 'desktop'],
  ['uw2560', 2560, 1080, false, 'ultrawide'],
  ['m568-land', 568, 320, true, 'mobile-landscape'], ['m844-land', 844, 390, true, 'mobile-landscape'], ['m932-land', 932, 430, true, 'mobile-landscape'],
  // Browser zoom 200% on a 1280×800 laptop = 640×400 CSS px layout viewport at 2× density (WCAG 1.4.4 / 1.4.10 reflow check).
  ['zoom200-1280', 640, 400, false, 'zoom-200'],
];
export const DESKTOP_MIN = 1024; // IA spec R-01: desktop layout (sidebar + modal) from 1024px

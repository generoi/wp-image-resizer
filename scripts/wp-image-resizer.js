import lozad from 'lozad'

// @see https://web.dev/browser-level-image-lazy-loading/#distance-from-viewport-thresholds
function rootMargin() {
  let margin = Math.round(window.innerHeight * 0.2);

  if (navigator.connection?.effectiveType === '4g') {
    margin = Math.round(window.innerHeight * 0.5);
  }

  return `${margin}px ${Math.round(margin * 0.5)}px`;
}

window.wpImageResizer = {
  selector: 'img[loading="lazy"], iframe[loading="lazy"], video[loading="lazy"], [data-background-image], [data-background-image-set]',
  options: {
    rootMargin: rootMargin(),
    loaded(element) {
      // Support data-sizes="auto"
      const sizes = element.dataset.sizes;
      if (sizes) {
        const width = element instanceof HTMLSourceElement
          ? element.parentElement?.getElementsByTagName('img')[0]?.offsetWidth
          : element.offsetWidth

        element.sizes = sizes === 'auto' ? (width ? `${width}px` : '100vw') : sizes
      }

      // Cleanup DOM
      delete element.dataset.srcset;
      delete element.dataset.sizes;
      delete element.dataset.src;
      delete element.dataset.backgroundImage;
      delete element.dataset.backgroundImageSet;
    },
  },
  ...(window.wpImageResizer || {})
}
const observer = lozad(
  window.wpImageResizer.selector,
  window.wpImageResizer.options
);
observer.observe();

// Facet WP integration
document.addEventListener('facetwp-loaded', function() {
  observer.observe();
});


// Expose for others to use
window.wpImageResizer = {
  ...window.wpImageResizer,
  observer,
};

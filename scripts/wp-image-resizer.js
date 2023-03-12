import lozad from 'lozad'

window.wpImageResizer = {
  selector: 'img[loading="lazy"], iframe[loading="lazy"], video[loading="lazy"]',
  options: {
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
    },
  },
  ...(window.wpImageResizer || {})
}
const observer = lozad(window.wpImageResizer.selector, window.wpImageResizer.options);
observer.observe();

// Expose for others to use
window.wpImageResizer = {
  observer,
};

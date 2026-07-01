import lozad from 'lozad'

// @see https://web.dev/browser-level-image-lazy-loading/#distance-from-viewport-thresholds
function rootMargin() {
  let margin = Math.round(window.innerHeight * 0.2);

  if (navigator.connection?.effectiveType === '4g') {
    margin = Math.round(window.innerHeight * 0.5);
  }

  return `${margin}px ${Math.round(margin * 0.5)}px`;
}

// Resolve the <img> that a lazyloaded element ultimately renders.
function resolveImage(element) {
  if (element instanceof HTMLSourceElement) {
    return element.parentElement?.getElementsByTagName('img')[0] ?? null;
  }
  if (element instanceof HTMLImageElement) {
    return element;
  }
  return null;
}

// Read the image's intrinsic aspect ratio without waiting for it to load by
// preferring the width/height attributes (always present) and only falling back
// to the decoded dimensions once available.
function intrinsicRatio(img) {
  const attrWidth = parseFloat(img.getAttribute('width'));
  const attrHeight = parseFloat(img.getAttribute('height'));
  if (attrWidth > 0 && attrHeight > 0) {
    return attrWidth / attrHeight;
  }
  if (img.naturalWidth > 0 && img.naturalHeight > 0) {
    return img.naturalWidth / img.naturalHeight;
  }
  return 0;
}

// Compute the effective rendered width for `data-sizes="auto"`.
//
// The layout box width (offsetWidth) is correct for the default object-fit
// (`fill`), but for `cover`/`contain` the image is scaled to cover/fit a box
// with a potentially different aspect ratio, so the painted image can be wider
// than its box. Using offsetWidth there picks a too-small candidate and the
// image looks blurry. Account for the object-fit using the box dimensions and
// the intrinsic aspect ratio instead.
function autoWidth(element) {
  const img = resolveImage(element);
  const width = img ? img.offsetWidth : element.offsetWidth;
  if (!img || !width) {
    return width;
  }

  const objectFit = getComputedStyle(img).objectFit;
  if (objectFit !== 'cover' && objectFit !== 'contain') {
    return width;
  }

  const ratio = intrinsicRatio(img);
  const height = img.offsetHeight;
  if (!ratio || !height) {
    return width;
  }

  const widthFromHeight = height * ratio;
  return objectFit === 'cover'
    ? Math.max(width, widthFromHeight)
    : Math.min(width, widthFromHeight);
}

window.wpImageResizer = {
  selector: 'img[loading="lazy"], iframe[loading="lazy"], video[loading="lazy"], [data-background-image], [data-background-image-set]',
  options: {
    rootMargin: rootMargin(),
    loaded(element) {
      // Support data-sizes="auto"
      const sizes = element.dataset.sizes;
      if (sizes) {
        if (sizes === 'auto') {
          const width = autoWidth(element);
          element.sizes = width ? `${Math.round(width)}px` : '100vw'
        } else {
          element.sizes = sizes
        }
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
document.addEventListener('facetwp-loaded', () => observer.observe());
window.addEventListener('gds-cmp.consent', () => observer.observe());

// Expose for others to use
window.wpImageResizer = {
  ...window.wpImageResizer,
  observer,
};

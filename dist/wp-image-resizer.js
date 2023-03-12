/*! For license information please see wp-image-resizer.js.LICENSE.txt */
!function(t){var e={};function r(o){if(e[o])return e[o].exports;var n=e[o]={i:o,l:!1,exports:{}};return t[o].call(n.exports,n,n.exports,r),n.l=!0,n.exports}r.m=t,r.c=e,r.d=function(t,e,o){r.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:o})},r.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},r.t=function(t,e){if(1&e&&(t=r(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var o=Object.create(null);if(r.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var n in t)r.d(o,n,function(e){return t[e]}.bind(null,n));return o},r.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return r.d(e,"a",e),e},r.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},r.p="/",r(r.s=1)}([function(t,e,r){t.exports=function(){"use strict";var t="undefined"!=typeof document&&document.documentMode,e={rootMargin:"0px",threshold:0,load:function(e){if("picture"===e.nodeName.toLowerCase()){var r=e.querySelector("img"),o=!1;null===r&&(r=document.createElement("img"),o=!0),t&&e.getAttribute("data-iesrc")&&(r.src=e.getAttribute("data-iesrc")),e.getAttribute("data-alt")&&(r.alt=e.getAttribute("data-alt")),o&&e.append(r)}if("video"===e.nodeName.toLowerCase()&&!e.getAttribute("data-src")&&e.children){for(var n=e.children,a=void 0,i=0;i<=n.length-1;i++)(a=n[i].getAttribute("data-src"))&&(n[i].src=a);e.load()}e.getAttribute("data-poster")&&(e.poster=e.getAttribute("data-poster")),e.getAttribute("data-src")&&(e.src=e.getAttribute("data-src")),e.getAttribute("data-srcset")&&e.setAttribute("srcset",e.getAttribute("data-srcset"));var u=",";if(e.getAttribute("data-background-delimiter")&&(u=e.getAttribute("data-background-delimiter")),e.getAttribute("data-background-image"))e.style.backgroundImage="url('"+e.getAttribute("data-background-image").split(u).join("'),url('")+"')";else if(e.getAttribute("data-background-image-set")){var d=e.getAttribute("data-background-image-set").split(u),c=d[0].substr(0,d[0].indexOf(" "))||d[0];c=-1===c.indexOf("url(")?"url("+c+")":c,1===d.length?e.style.backgroundImage=c:e.setAttribute("style",(e.getAttribute("style")||"")+"background-image: "+c+"; background-image: -webkit-image-set("+d+"); background-image: image-set("+d+")")}e.getAttribute("data-toggle-class")&&e.classList.toggle(e.getAttribute("data-toggle-class"))},loaded:function(){}};function r(t){t.setAttribute("data-loaded",!0)}var o=function(t){return"true"===t.getAttribute("data-loaded")},n=function(t){var e=1<arguments.length&&void 0!==arguments[1]?arguments[1]:document;return t instanceof Element?[t]:t instanceof NodeList?t:e.querySelectorAll(t)};return function(){var t,a,i=0<arguments.length&&void 0!==arguments[0]?arguments[0]:".lozad",u=1<arguments.length&&void 0!==arguments[1]?arguments[1]:{},d=Object.assign({},e,u),c=d.root,s=d.rootMargin,l=d.threshold,g=d.load,b=d.loaded,f=void 0;"undefined"!=typeof window&&window.IntersectionObserver&&(f=new IntersectionObserver((t=g,a=b,function(e,n){e.forEach((function(e){(0<e.intersectionRatio||e.isIntersecting)&&(n.unobserve(e.target),o(e.target)||(t(e.target),r(e.target),a(e.target)))}))}),{root:c,rootMargin:s,threshold:l}));for(var p,m=n(i,c),v=0;v<m.length;v++)(p=m[v]).getAttribute("data-placeholder-background")&&(p.style.background=p.getAttribute("data-placeholder-background"));return{observe:function(){for(var t=n(i,c),e=0;e<t.length;e++)o(t[e])||(f?f.observe(t[e]):(g(t[e]),r(t[e]),b(t[e])))},triggerLoad:function(t){o(t)||(g(t),r(t),b(t))},observer:f}}}()},function(t,e,r){t.exports=r(2)},function(t,e,r){"use strict";r.r(e);var o=r(0),n=r.n(o);function a(t){return(a="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function i(t,e){var r=Object.keys(t);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(t);e&&(o=o.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),r.push.apply(r,o)}return r}function u(t,e,r){return(e=function(t){var e=function(t,e){if("object"!==a(t)||null===t)return t;var r=t[Symbol.toPrimitive];if(void 0!==r){var o=r.call(t,e||"default");if("object"!==a(o))return o;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===e?String:Number)(t)}(t,"string");return"symbol"===a(e)?e:String(e)}(e))in t?Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}):t[e]=r,t}window.wpImageResizer=function(t){for(var e=1;e<arguments.length;e++){var r=null!=arguments[e]?arguments[e]:{};e%2?i(Object(r),!0).forEach((function(e){u(t,e,r[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(r)):i(Object(r)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(r,e))}))}return t}({selector:'img[loading="lazy"], iframe[loading="lazy"], video[loading="lazy"], [data-background-image], [data-background-image-set]',options:{loaded:function(t){var e=t.dataset.sizes;if(e){var r,o,n=t instanceof HTMLSourceElement?null===(r=t.parentElement)||void 0===r||null===(o=r.getElementsByTagName("img")[0])||void 0===o?void 0:o.offsetWidth:t.offsetWidth;t.sizes="auto"===e?n?"".concat(n,"px"):"100vw":e}delete t.dataset.srcset,delete t.dataset.sizes,delete t.dataset.src,delete t.dataset.backgroundImage,delete t.dataset.backgroundImageSet}}},window.wpImageResizer||{});var d=n()(window.wpImageResizer.selector,window.wpImageResizer.options);d.observe(),window.wpImageResizer={observer:d}}]);
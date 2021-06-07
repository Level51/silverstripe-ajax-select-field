/**
 * Watch the DOM for elements by a given selector
 *
 * @param {string} selector
 * @param {function} fn
 *
 * @see http://ryanmorr.com/using-mutation-observers-to-watch-for-element-availability/
 */
const watchElement = (selector, fn) => {
  const listeners = [];
  const doc = window.document;
  const MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
  let observer;

  const check = () => {
    for (let i = 0, len = listeners.length, listener, elements; i < len; i += 1) {
      listener = listeners[i];
      // Query for elements matching the specified selector
      elements = doc.querySelectorAll(listener.selector);
      for (let j = 0, jLen = elements.length, element; j < jLen; j += 1) {
        element = elements[j];
        // Make sure the callback isn't invoked with the
        // same element more than once
        if (!element.ready) {
          element.ready = true;
          // Invoke the callback with the element
          listener.fn.call(element, element);
        }
      }
    }
  };

  if (!observer) {
    observer = new MutationObserver(check);
    observer.observe(doc.documentElement, {
      childList: true,
      subtree: true
    });
  }

  listeners.push({
    selector,
    fn
  });

  check();
};

export default watchElement;

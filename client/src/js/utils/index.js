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

const arrayCompareFunction = (key, order = 'asc') => (a, b) => {
  let varA = key.split('.').reduce((o, i) => o[i], a);
  if (typeof varA === 'string') varA = varA.toUpperCase();

  let varB = key.split('.').reduce((o, i) => o[i], b);
  if (typeof varB === 'string') varB = varB.toUpperCase();

  if (!varA && !varB) return 0;

  let comparison = 0;

  if (!varA) comparison = -1;
  if (!varB) comparison = 1;

  if (varA > varB) comparison = 1;
  else if (varA < varB) comparison = -1;

  return order === 'desc' ? comparison * -1 : comparison;
};

const sortArray = (array, key, order = 'asc') => array.sort(arrayCompareFunction(key, order));

const cloneObject = (value) => JSON.parse(JSON.stringify(value));

export { watchElement, sortArray, cloneObject };

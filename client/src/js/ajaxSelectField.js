import Vue from 'vue';
import AjaxSelectField from 'src/App.vue';
import watchElement from './util';

const render = (el) => {
  new Vue({
    render(h) {
      return h(AjaxSelectField, {
        props: {
          payload: JSON.parse(el.dataset.payload)
        }
      });
    }
  }).$mount(`#${el.id}`);
};

watchElement('.level51-ajaxSelectFieldPlaceholder', (el) => {
  setTimeout(() => {
    render(el);
  }, 1);
});

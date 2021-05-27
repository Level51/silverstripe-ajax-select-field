import Vue from 'vue';
import AjaxSelectField from 'src/components/AjaxMultiSelectField.vue';
import watchElement from 'src/utils';

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

watchElement('.level51-ajaxMultiSelectFieldPlaceholder', (el) => {
  setTimeout(() => {
    render(el);
  }, 1);
});

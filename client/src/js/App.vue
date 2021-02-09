<template>
  <div class="level51-ajaxSelectField">
    <vue-simple-suggest
      v-model="term"
      :list="suggest"
      display-attribute="title"
      value-attribute="id"
      :debounce="400"
      :destyled="false"
      @select="selected"
      ref="suggestField"
      :prevent-submit="false">
      <input
        :placeholder="payload.placeholder"
        type="text"
        name="term"
        :value="term"
        autocomplete="off"
        autocorrect="off"
        autocapitalize="off"
        spellcheck="false">
    </vue-simple-suggest>

    <input
      type="hidden"
      :name="payload.name"
      :value="dataValue">
  </div>
</template>

<script>
import axios from 'axios';
import qs from 'qs';
import VueSimpleSuggest from 'vue-simple-suggest/dist/cjs'; // Use commonJS build for ie compatibility

/*
 * TODO
  * possibility to add custom GET parameters
  * possibility to add custom ajax config
 */
export default {
  props: {
    payload: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      term: '',
      selection: null
    };
  },
  watch: {
    cleanTerm(newVal) {
      if (newVal.length < this.payload.config.minSearchChars && this.selection) {
        this.selection = null;
      }
    }
  },
  components: { VueSimpleSuggest },
  created() {
    if (this.payload.value && typeof this.payload.value === 'object') {
      this.selection = this.payload.value;

      this.term = this.payload.value.title;
    }
  },
  computed: {
    endpoint() {
      return this.payload.config.searchEndpoint;
    },
    endpointWithParams() {
      const params = {
        query: this.cleanTerm
      };

      return `${this.endpoint}?${qs.stringify(params, { encode: true })}`;
    },
    cleanTerm() {
      return this.term && typeof this.term === 'string' ? this.term.trim() : '';
    },
    dataValue() {
      return this.selection ? JSON.stringify(this.selection) : null;
    }
  },
  methods: {
    suggest(term) {
      const cleanTerm = term && typeof term === 'string' ? term.trim() : null;

      return new Promise((resolve) => {
        if (this.cleanTerm.length < this.payload.config.minSearchChars) resolve([]);
        else {
          axios
            .get(this.endpointWithParams)
            .then((response) => {
              resolve(response.data);
            });
        }
      });
    },
    selected(suggestion) {
      if (!suggestion) return;

      this.selection = { ...suggestion };
    },
    i18n(label) {
      const { i18n } = this.payload;
      return i18n.hasOwnProperty(label) ? i18n[label] : label;
    }
  }
};
</script>

<style lang="less">
@color-white: #ffffff;
@color-light-grey: #ced5e1;
@color-highlight: #66afe9;
@border-radius: 0.23rem;

@space-1: 4px;
@space-2: 8px;
@space-3: 16px;
@space-4: 24px;
@space-5: 48px;

.noselect() {
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

.level51-ajaxSelectField {
  position: relative;

  input[type=text] {
    display: block;
    width: 100%;
    height: 40px;
    padding: @space-2;
    border: 1px solid @color-light-grey;
    background: @color-white;
    transition: all 250ms ease-in-out;
    outline: none;
    box-shadow: none;
    -webkit-appearance: none;
    border-radius: @border-radius;

    &:focus {
      border-color: @color-highlight;
    }
  }

  .vue-simple-suggest {
    position: relative;

    > ul {
      list-style: none;
      padding-left: 0;
      margin: 0;
    }

    .suggestions {
      position: absolute;
      width: 100%;
      left: 0;
      top: 100%;
      background: fade(@color-white, 93);
      z-index: 1000;
      box-shadow: 0 3px 5px rgba(0, 0, 0, .16);
      border: 1px solid @color-highlight;
      border-top: 0;
      border-bottom-left-radius: @border-radius;
      border-bottom-right-radius: @border-radius;

      .suggest-item {
        cursor: pointer;
        .noselect();

        &.hover, &.selected {
          background-color: @color-highlight;
          color: @color-white;
        }
      }

      .suggest-item, .misc-item {
        padding: @space-2;
      }
    }
  }
}
</style>

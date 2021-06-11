<template>
  <div class="level51-ajaxSelectFieldBase level51-ajaxSelectField">
    <vue-simple-suggest
      v-model="term"
      :list="suggest"
      display-attribute="title"
      value-attribute="id"
      :debounce="400"
      :destyled="false"
      @select="selected"
      ref="suggestField"
      :prevent-submit="false"
      :min-length="payload.config.minSearchChars">
      <input
        :placeholder="payload.config.placeholder"
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
import selectFieldMixin from 'src/mixins/selectField';

export default {
  mixins: [selectFieldMixin],
  data() {
    return {
      term: '',
      selection: null
    };
  },
  watch: {
    cleanTerm(newVal) {
      if ((newVal.length < this.payload.config.minSearchChars || newVal.length === 0)
        && this.selection) {
        this.selection = null;
      }
    }
  },
  created() {
    if (this.payload.value) {
      if (typeof this.payload.value === 'object') {
        this.selection = this.payload.value;
        this.term = this.payload.value.title;
      }

      if (this.idOnlyMode && typeof this.payload.value === 'string') {
        this.loadInitialValueDetails();
      }
    }
  },
  computed: {
    dataValue() {
      if (this.selection) {
        return this.idOnlyMode ? this.selection.id : JSON.stringify(this.selection);
      }

      return null;
    },
    idOnlyMode() {
      return !!this.payload.config.idOnlyMode;
    }
  },
  methods: {
    selected(suggestion) {
      if (!suggestion) return;

      this.selection = { ...suggestion };
    },
    loadInitialValueDetails() {
      if (!this.idOnlyMode) return;

      let params = {};

      if (this.payload.config.getVars && typeof this.payload.config.getVars === 'object' && this.payload.config.getVars !== null) {
        params = {
          ...this.payload.config.getVars
        };
      }

      params.id = this.payload.value;

      axios
        .get(`${this.endpoint}?${qs.stringify(params, { encode: true })}`, this.searchAxiosConfig)
        .then((response) => {
          if (response && response.data) {
            this.selection = response.data;
            this.term = response.data.title;
          }
        });
    }
  }
};
</script>

<style lang="less">
@import '~styles/base';
</style>

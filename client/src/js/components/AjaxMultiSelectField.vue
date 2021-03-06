<template>
  <div class="level51-ajaxSelectFieldBase level51-ajaxMultiSelectField">
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

    <div
      class="level51-ajaxMultiSelectField-items"
      v-if="items.length > 0">
      <table>
        <tr>
          <th
            v-for="(label, key) in payload.config.displayFields"
            :key="key">
            {{ label }}
          </th>
          <th class="level51-ajaxMultiSelectField-actions" />
        </tr>
        <tr
          v-for="item in items"
          :key="item.id">
          <td
            v-for="(label, key) in payload.config.displayFields"
            :key="`${key}_${item.id}`">
            {{ item[key] }}
          </td>
          <td class="level51-ajaxMultiSelectField-actions">
            <a
              href=""
              :title="$t('actions.remove')"
              @click.prevent="remove(item.id)">
              <i class="font-icon-link-broken" />
            </a>
          </td>
        </tr>
      </table>
    </div>

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
      items: []
    };
  },
  created() {
    if (this.payload.value) {
      this.loadInitialValueDetails();
    }
  },
  computed: {
    dataValue() {
      if (this.items.length > 0) {
        return JSON.stringify(this.items.map((item) => item.id));
      }

      return null;
    },
    endpointWithParams() {
      let params = {};

      if (this.payload.config.getVars && typeof this.payload.config.getVars === 'object') {
        params = {
          ...this.payload.config.getVars
        };
      }

      params.query = this.cleanTerm;

      if (this.items.length > 0) {
        params.items = this.items.map((item) => item.id);
      }

      return `${this.endpoint}?${qs.stringify(params, { encode: true })}`;
    },
  },
  methods: {
    selected(suggestion) {
      if (!suggestion) return;

      this.items.push({ ...suggestion });
      setTimeout(() => {
        this.term = '';
      }, 10);
    },
    remove(id) {
      const index = this.items.findIndex((item) => item.id === id);
      this.items.splice(index, 1);
    },
    loadInitialValueDetails() {
      let params = {};

      if (this.payload.config.getVars && typeof this.payload.config.getVars === 'object') {
        params = {
          ...this.payload.config.getVars
        };
      }

      params.ids = this.payload.value;

      axios
        .get(`${this.endpoint}?${qs.stringify(params, { encode: true })}`, this.searchAxiosConfig)
        .then((response) => {
          if (response && response.data) {
            this.items = response.data;
          }
        });
    }
  }
};
</script>

<style lang="less">
@import '~styles/base';

.level51-ajaxMultiSelectField {
  .level51-ajaxMultiSelectField-items {
    margin-top: @space-3;

    table {
      border-collapse: separate;
      border-spacing: 0;
      table-layout: fixed;
      width: 100%;
      border-radius: @border-radius;
      border: 1px solid @color-light-grey;

      tr:not(:last-child) {
        td, th {
          border-bottom: 1px solid @color-light-grey;
        }
      }

      tr:hover {
        td {
          background: @color-ultralight-grey;
        }
      }

      td, th {
        padding: @space-3;
      }

      .level51-ajaxMultiSelectField-actions {
        width: 60px;
        text-align: center;
      }
    }
  }
}
</style>

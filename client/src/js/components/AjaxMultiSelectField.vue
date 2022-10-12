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
      :min-length="payload.config.minSearchChars"
      :max-suggestions="0">
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
      <SlickList
        lock-axis="y"
        v-model="items"
        use-drag-handle
        tag="table"
        :class="{ 'level51-ajaxMultiSelectField-table--dragable': manualSortingEnabled }">
        <tr>
          <th
            class="level51-ajaxMultiSelectField-dragHandle"
            v-if="payload.config.isManuallySortable"
            @click.prevent="sort = null;"
            :title="$t('actions.manualSort')">
            <i class="font-icon-drag-handle" />
          </th>

          <template v-if="payload.config.isSortable">
            <SortableColumnHeader
              v-for="(label, key) in displayFields"
              :key="key"
              :label="label"
              :sort-key="key"
              :current-sort="sort"
              @input="sort = $event" />
          </template>
          <template v-else>
            <th
              v-for="(label, key) in displayFields"
              :key="key">
              {{ label }}
            </th>
          </template>
          <th class="level51-ajaxMultiSelectField-actions" />
        </tr>
        <SlickItem
          v-for="(item, index) in preparedItems"
          :key="item.id"
          :index="index"
          tag="tr"
          :disabled="!manualSortingEnabled">
          <td
            class="level51-ajaxMultiSelectField-dragHandle"
            v-if="payload.config.isManuallySortable"
            v-handle>
            <i
              v-if="manualSortingEnabled"
              class="font-icon-drag-handle" />
          </td>
          <td
            v-for="(label, key) in displayFields"
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
        </SlickItem>
      </SlickList>
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
import { sortArray, cloneObject } from 'src/utils';
import SortableColumnHeader from 'src/components/SortableColumnHeader.vue';
import { SlickList, SlickItem, HandleDirective } from 'vue-slicksort';

export default {
  mixins: [selectFieldMixin],
  components: { SortableColumnHeader, SlickList, SlickItem },
  directives: { handle: HandleDirective },
  data() {
    return {
      term: '',
      items: [],
      sort: null,
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
    displayFields() {
      return this.payload.config.displayFields;
    },
    preparedItems() {
      if (this.sort && this.sort.field) {
        return sortArray(cloneObject(this.items), this.sort.field, this.sort.order || 'asc');
      }

      return this.items;
    },
    manualSortingEnabled() {
      return this.payload.config.isManuallySortable && !this.sort;
    }
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
            this.payload.value.forEach((id) => {
              this.items.push(response.data.find((row) => row.id === id));
            });
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

      &.level51-ajaxMultiSelectField-table--dragable {
        .level51-ajaxMultiSelectField-dragHandle {
          cursor: move;
        }
      }

      .level51-ajaxMultiSelectField-dragHandle {
        width: 30px;
      }

      th.level51-ajaxMultiSelectField-dragHandle {
        cursor: pointer;
      }

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

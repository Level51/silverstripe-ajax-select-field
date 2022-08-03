<template>
  <th
    class="level51-ajaxMultiSelectField-itemHeader"
    :class="{'level51-ajaxMultiSelectField-itemHeader--active': isActive}">
    <a
      href=""
      @click.prevent="setSort">
      {{ label }}

      <span
        :class="icon" />
    </a>
  </th>
</template>

<script>
export default {
  props: {
    label: {
      type: String,
      required: true,
    },
    sortKey: {
      type: String,
      required: true,
    },
    currentSort: {
      type: Object,
      required: false,
      default: null,
    }
  },
  computed: {
    isActive() {
      return this.currentSort && this.currentSort.field === this.sortKey;
    },
    activeOrder() {
      if (this.isActive) {
        return this.currentSort.order;
      }

      return null;
    },
    icon() {
      if (this.isActive) {
        return this.activeOrder === 'asc' ? 'font-icon-caret-up-two' : 'font-icon-caret-down-two';
      }

      return 'font-icon-caret-up-down';
    }
  },
  methods: {
    setSort() {
      this.$emit('input', {
        field: this.sortKey,
        order: this.activeOrder === 'asc' ? 'desc' : 'asc'
      });
    }
  }
};
</script>

<style lang="less">
@import '~styles/base';

.level51-ajaxMultiSelectField-itemHeader {
  a {
    display: flex;
    align-items: center;
    color: @color-textBase;
  }

  span {
    display: flex;
    margin-left: @space-1;
  }

  &.level51-ajaxMultiSelectField-itemHeader--active {
    a {
      color: @color-highlight;
    }
  }
}
</style>

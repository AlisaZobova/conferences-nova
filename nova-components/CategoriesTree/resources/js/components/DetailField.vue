<template>
  <PanelItem :index="index" :field="field">
      <template #value>
          <tree v-if="field.tree.children.length > 0" :tree-data="field.tree"></tree>
          <span v-else>—</span>
      </template>
  </PanelItem>
</template>

<script>

import Tree from "./Tree";

export default {
  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],
    components: {
        Tree
    },
}
</script>

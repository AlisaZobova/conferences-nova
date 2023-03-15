<template>
  <PanelItem :index="index" :field="field">
      <template #value>
          <tree :tree-data="field.tree"></tree>
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

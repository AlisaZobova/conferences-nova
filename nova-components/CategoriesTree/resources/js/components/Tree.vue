<template>
    <div class="tree">
        <ul class="tree-list">
            <node-tree :node="treeData"></node-tree>
        </ul>
    </div>
</template>

<script>

import NodeTree from "./NodeTree";

export default {
    name: "Tree",
    props: {
        treeData: Object
    },
    components: {
        NodeTree
    }
}

</script>

<style>
.tree-list ul:not(:first-child) {
    padding-left: 16px;
    margin: 6px 0;
}

</style>

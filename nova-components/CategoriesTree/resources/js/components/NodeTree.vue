<template>
    <li class="node-tree">

        <PanelItem v-if="!node.root" :field="node">
            <template #value>
                <div v-if="node.name" class="text-right">
                    <a :href="`/nova/resources/categories/${node.id}/edit`" class="edit-link">Edit</a>
                    <button
                        @click="deleteCategory" class="ml-2 delete-btn">Delete</button>
                </div>
            </template>
        </PanelItem>

        <ul v-if="node.children && node.children.length">
            <node-tree v-for="child in node.children" :node="child"></node-tree>
        </ul>
    </li>

</template>

<script>

export default {
    name: "NodeTree",
    props: {
        node: Object
    },
    methods: {
        deleteCategory() {
            Nova.request().delete(`/nova-api/categories?search=&filters=&trashed=&resources[]=${this.node.id}`)
                .then(() => window.location.reload())
        }
    }
}
</script>

<style>

.delete-btn {
    color: red;
}

.edit-link {
    color: blue;
}

</style>

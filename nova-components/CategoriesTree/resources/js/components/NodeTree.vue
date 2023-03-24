<template>
    <li class="node-tree">

        <PanelItem v-if="!node.root" :field="node">
            <template #value>
                <div v-if="node.name" class="text-right">
                    <a :href="`/nova/resources/categories/${node.id}/edit`" class="edit-link">Edit</a>
                    <button
                        @click="showModal=true" class="delete-btn">Delete
                    </button>
                </div>
            </template>
        </PanelItem>

        <div v-if="showModal">
            <div class="delete-modal">
                <div class="delete-modal-content">
                    <div class="delete-modal-text">
                        Are you sure you want to delete this category?
                    </div>
                    <div class="delete-modal-actions">
                        <button @click="showModal=false">Close</button>
                        <button
                            @click="deleteCategory" class="delete-btn">Delete
                        </button>
                    </div>
                </div>

            </div>

        </div>

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
    data() {
        return {
            showModal: false
        }
    },
    methods: {
        deleteCategory() {
            this.showModal = false
            Nova.request()
                .delete(`/nova-api/categories?search=&filters=&trashed=&resources[]=${this.node.id}`)
                .then(() => window.location.reload())
        }
    }
}
</script>

<style>

.delete-btn {
    color: red;
    margin-left: 20px;
}

.edit-link {
    color: blue;
}

.delete-modal {
    position: fixed;
    z-index: 100;
    padding-top: 150px;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
}

.delete-modal-content {
    background-color: white;
    margin: auto;
    padding: 20px;
    border: 1px solid #888;
    width: 50%;
}

.delete-modal-actions {
    display: flex;
    place-content: center;
    margin-top: 15px;
}

.delete-modal-text {
    display: flex;
    place-content: center;
}

</style>

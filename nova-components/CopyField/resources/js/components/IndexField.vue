<template>
    <span v-if="!field.value"> — </span>
    <button
        v-if="field.value"
        @click="copyFieldValue">Copy &#128203;</button>
</template>

<script>
export default {
    props: ['resourceName', 'field'],

    computed: {
        fieldValue() {
            return this.field.displayedAs || this.field.value
        },
    },

    methods: {
        copyFieldValue() {
            navigator.clipboard.writeText(this.field.value);
        }
    }
}
</script>

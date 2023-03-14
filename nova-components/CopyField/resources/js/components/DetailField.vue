<template>
    <PanelItem :index="index" :field="field" class="copy-item">
        <template #value>
            <div v-if="field.value" class="flex">
                <button
                    @click="copyFieldValue">Copy &#128203;</button>
            </div>
        </template>
    </PanelItem>
</template>

<script>
export default {
    props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],
    methods: {
      copyFieldValue() {
          navigator.clipboard.writeText(this.field.value)
      },
    }
}
</script>

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
        unsecuredCopyToClipboard() {
            const textArea = document.createElement("textarea");
            textArea.value = this.field.value;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
            } catch (err) {
                console.error('Unable to copy to clipboard', err);
            }
            document.body.removeChild(textArea);
        },
        copyFieldValue() {
            if (window.isSecureContext && navigator.clipboard) {
                navigator.clipboard.writeText(this.field.value);
            } else {
                this.unsecuredCopyToClipboard();
            }
        },
    }
}
</script>

<template>
    <span>
        {{ field.latitude && field.longitude ? 'Latitide: '
        + field.latitude + ', longitude: ' + field.longitude : '—' }}
    </span>
</template>

<script>
export default {
    props: ['resourceName', 'field']
}
</script>

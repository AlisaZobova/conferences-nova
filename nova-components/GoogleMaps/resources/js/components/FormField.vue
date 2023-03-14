<template>
    <DefaultField
        :field="field"
        :errors="errors"
        :show-help-text="showHelpText"
    >
        <template #field>
            <div class="space-y-4">
                <GMapMap
                    :center="getCenter()"
                    :zoom="10"
                    map-type-id="terrain"
                    style="width: 100%; height: 300px"
                    @click="setLatLng($event.latLng)"
                >
                    <GMapMarker
                        :position="{
                            lat: parseFloat(
                                field.latitude
                            ),
                            lng: parseFloat(
                                field.longitude
                            ),
                        }"
                        :clickable="true"
                        :draggable="true"
                        @dragend="setLatLng($event.latLng)"
                    />
                </GMapMap>
                <div class="flex">
                    <div class="w-1/2 mr-6" v-if="!hideLatitude">
                        <p class="mb-1">Latitude</p>
                        <input
                            type="text"
                            class="w-full form-control form-input form-input-bordered"
                            :class="errorClasses"
                            v-model="field.latitude"
                        />
                    </div>
                    <div class="w-1/2" v-if="!hideLongitude">
                        <p class="mb-1">Longitude</p>
                        <input
                            type="text"
                            class="w-full form-control form-input form-input-bordered"
                            :class="errorClasses"
                            v-model="field.longitude"
                        />
                    </div>
                </div>
            </div>
        </template>
    </DefaultField>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova'

const map = {center: {}, selectedPlace: false}

export default {
    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    methods: {
        setLatLng(location) {
            this.field.latitude = parseFloat(location.lat().toFixed(3))
            this.field.longitude = parseFloat(location.lng().toFixed(3))
        },
        getCenter() {
            let lat = this.field.latitude
            let lng = this.field.longitude
            if (
                lat &&
                lng &&
                Number(lat) &&
                Number(lng) &&
                lat <= 90 &&
                lat >= -90 &&
                lng <= 180 &&
                lng >= -180
            ) {
                return {lat: parseFloat(lat), lng: parseFloat(lng)}
            } else {
                return {lat: 50, lng: 30}
            }
        },
    }
}
</script>

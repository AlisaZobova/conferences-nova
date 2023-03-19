import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

import VueGoogleMaps from '@fawmi/vue-google-maps'

Nova.booting((app, store) => {
    app.component('index-google-maps', IndexField)
    app.component('detail-google-maps', DetailField)
    app.component('form-google-maps', FormField)

    let load = Nova.appConfig.api_key ? { key: Nova.appConfig.api_key, libraries: 'places' } : { libraries: 'places'}

    app.use(VueGoogleMaps, {
        load: load
    })
})


import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-google-maps', IndexField)
  app.component('detail-google-maps', DetailField)
  app.component('form-google-maps', FormField)
})

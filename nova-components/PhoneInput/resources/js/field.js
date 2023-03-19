import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-phone-input', IndexField)
  app.component('detail-phone-input', DetailField)
  app.component('form-phone-input', FormField)
})

import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-copy-field', IndexField)
  app.component('detail-copy-field', DetailField)
  app.component('form-copy-field', FormField)
})

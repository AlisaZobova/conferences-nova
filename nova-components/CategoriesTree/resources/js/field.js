import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-categories-tree', IndexField)
  app.component('detail-categories-tree', DetailField)
  app.component('form-categories-tree', FormField)
})

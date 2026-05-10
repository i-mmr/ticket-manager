import './bootstrap.js'
import '../css/app.css'
import '../scss/app.scss'
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import type { DefineComponent } from 'vue'

const pages = import.meta.glob<{ default: DefineComponent }>('./Pages/**/*.vue')

createInertiaApp({
  resolve: (name) =>
    resolvePageComponent(`./Pages/${name}.vue`, pages).then((page) => page.default),
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
  },
})

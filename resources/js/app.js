import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { route } from '../../vendor/tightenco/ziggy';

createInertiaApp({
   resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
    const key = `./Pages/${name}.vue`;
    const page = pages[key] || pages[key.toLowerCase()];
    if (!page) {
        throw new Error(`Page component not found: ${key}`);
    }
    return page.default ?? page;
},

    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .mixin({ methods: { route } })
            .use(plugin)
            .mount(el);
    }
});
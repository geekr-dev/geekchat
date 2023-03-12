import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import store from './store'
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import Markdown from 'vue3-markdown-it';

// 代码高亮
import 'highlight.js/styles/github.css';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    title: (title) => `${title}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(store)
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .component('markdown', Markdown)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

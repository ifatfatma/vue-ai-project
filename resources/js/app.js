import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import Prompts from './components/prompts.vue';

const appElement = document.getElementById('app');

if (!appElement) {
    console.warn(
        'Vue prompts were not mounted: #app element was not found on this page.'
    );
} else {
    createApp({}).
    component('home-prompts',Prompts).
    mount(appElement);
}
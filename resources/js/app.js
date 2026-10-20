
import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import Prompts from './components/prompts.vue';

document.addEventListener('DOMContentLoaded', () => {
    const appElement = document.getElementById('app');

    if (!appElement) {
        console.warn(
            'Vue prompts were not mounted: #app element was not found in home.blade.php.'
        );
        return;
    }

    createApp(Prompts).mount(appElement);
});

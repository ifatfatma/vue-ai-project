import './bootstrap';

import '../css/app.css';

import { createApp } from 'vue';
import prompts from './components/prompts.vue';

createApp(prompts).mount('#app');
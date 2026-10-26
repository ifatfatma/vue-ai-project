
import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import Prompts from './components/prompts.vue';
import SavedPrompts from './components/saved-prompts.vue';
import UserPrompts from './components/user-prompts.vue';
import UserProfile from './components/user-profile.vue';
import EditPrompt from './components/edit-prompt.vue';

const appElement = document.getElementById('app');

if (!appElement) {
    console.warn(
        'Vue components were not mounted: #app element was not found on this page.'
    );
} else {
    createApp({})
        .component('home-prompts', Prompts)
        .component('saved-prompts', SavedPrompts)
        .component('user-prompts', UserPrompts)
        .component('user-profile', UserProfile)
        .component('edit-prompt', EditPrompt)
        .mount(appElement);
}

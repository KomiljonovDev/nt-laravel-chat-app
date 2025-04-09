import '../css/app.css';
import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';

// Create the Vue application
const app = createApp(App);
const pinia = createPinia();
app.use(pinia);
// Mount the app to the #app element
app.mount('#app');

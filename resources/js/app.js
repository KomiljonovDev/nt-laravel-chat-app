import '../css/app.css';
import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';

// Create the Vue application
const app = createApp(App);

// Mount the app to the #app element
app.mount('#app');

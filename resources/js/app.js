import '../css/app.css';
import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';

// Create the Vue application
const app = createApp(App);
app.provide('currentUserId', window.currentUserId);
// Mount the app to the #app element
app.mount('#app');


- "./bootstrap";
import { createApp } from "vue";
// import ExampleComponent from './components/ExampleComponent.vue';
// app.component('example-component', ExampleComponent);

import chat from "./components/chat.vue";
import Echo from 'laravel-echo';

const app = createApp({});
app.component("chat", chat);
app.mount("#app");

// require('./bootstrap');

// import { createApp } from 'vue';
// import Echo from 'laravel-echo';
// import chat from './components/chat.vue';


// const app = createApp({});

// app.component('chat',chat);

// app.mount("#app");


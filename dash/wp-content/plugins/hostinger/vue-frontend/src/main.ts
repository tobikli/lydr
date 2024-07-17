import { createApp } from "vue";
import App from "./App.vue";
import piniaPluginPersistedstate from "pinia-plugin-persistedstate";
import { createPinia } from "pinia";
import router from "@/router";
import "@/scss/main.scss";
import 'vue3-toastify/dist/index.css';

const initializeVueApp = () => {
  const pinia = createPinia();
  pinia.use(piniaPluginPersistedstate);


  const app = createApp(App);
  app.use(pinia);
  app.use(router);

  app.config.globalProperties.window = window

  app.mount("#hostinger-tools-vue-app");
};

document.addEventListener("DOMContentLoaded", (event) => {
  initializeVueApp();
});

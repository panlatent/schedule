import Vue from 'vue';
import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import App from './App.vue';
import router from './router';
import './plugins/element.js'
import VueResource from "vue-resource";

Vue.config.productionTip = false;
Vue.use(ElementUI);
Vue.use(VueResource);
(<any>window).ELEMENT = ElementUI

new Vue({
  router,
  render: (h) => h(App),
}).$mount('#app');

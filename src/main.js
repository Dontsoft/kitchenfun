import Vue from 'vue'
import App from './App.vue'

Vue.config.productionTip = false

import Dropzone from './components/Dropzone.vue';
import Dropdown from './components/Dropdown.vue';

Vue.component('dontsoft-dropzone', Dropzone);
Vue.component('dontsoft-dropdown', Dropdown);

new Vue({
  render: h => h(App),
}).$mount('#app')

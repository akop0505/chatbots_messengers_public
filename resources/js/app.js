
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import configs from './configs/default';
window.configs = configs;

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

//Vue.component('example-component', require('./components/ExampleComponent.vue'));

import 'core-js/es6/promise'
import 'core-js/es6/string'
import 'core-js/es7/array'
// import cssVars from 'css-vars-ponyfill'
import Vue from 'vue'
import BootstrapVue from 'bootstrap-vue'
import App from './App.vue'
import router from './router'
import axios from 'axios';
import VueRouterUserRoles from "vue-router-user-roles";
import 'vue-resize/dist/vue-resize.css';
import VueResize from 'vue-resize';
import VueSpinners from 'vue-spinners';
import VueSocketIO from 'vue-socket.io';


Vue.use(VueSpinners);

Vue.use(VueRouterUserRoles, { router });

//let authenticate = Promise.resolve({ role: "guest" });

axios.interceptors.response.use(null, error=>{
    if (error.response.status === 401) {
        localStorage.removeItem('token');
        //location = "/";
    }
    return Promise.reject(error);
});

Vue.use(BootstrapVue);

Vue.use(VueResize);

Vue.prototype.$user.set({ role: "guest" });
if(localStorage.getItem('user')){
    let userData  = JSON.parse(localStorage.getItem('user'));
    if(!['admin','manager','client'].includes(userData.role.name)){
        Vue.prototype.$user.set(Object.assign(userData, { role: "guest" }));
    }else{
        Vue.prototype.$user.set(Object.assign(userData, { role: userData.role.name }));
    }
}
Vue.use(new VueSocketIO({
    debug: true,
    connection: 'http://localhost:3001',
}));

new Vue({
    el: '#app',
    router,
    template: '<App/>',
    components: {
        App
    }
});

/* eslint-disable no-new */
/*const app = new Vue({
    el: '#app',
    router,
    template: '<App/>',
    components: {
        App
    }
});*/

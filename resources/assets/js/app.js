//load application bootstrap file
require('./bootstrap')

Vue.component('builder', require('./components/Builder.vue'));
Vue.component('main-nav', require('./components/MainNav.vue'));


const app = new Vue({
    el: '#app',
    data: {
        message: 'Hello Vue!'
    }
});

console.log('app loaded');
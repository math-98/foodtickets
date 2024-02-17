/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.scss in this case)
import './styles/app.scss';

global.$ = global.jQuery = require('jquery');
global.bootstrap = require('bootstrap');

require('./custom');
require('./sidebarmenu');
require('./waves');

import { createApp } from "vue/dist/vue.esm-bundler";
import AmountInputComponent from './components/AmountInputComponent.vue';
import ContractFormComponent from "./components/ContractFormComponent.vue";
import HomeStatsComponent from "./components/HomeStatsComponent.vue";
import TransactionLinesGroupComponent from './components/TransactionLinesGroupComponent.vue';
import TransactionLineComponent from './components/TransactionLineComponent.vue';
const app = createApp({});

app.component('amount-input', AmountInputComponent);
app.component('contract-form', ContractFormComponent);
app.component('home-stats', HomeStatsComponent);
app.component('transaction-lines-group', TransactionLinesGroupComponent);
app.component('transaction-line', TransactionLineComponent);
app.mount('#main-wrapper');


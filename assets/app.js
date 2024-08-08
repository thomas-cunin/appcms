// assets/app.js

import './bootstrap.js';
import $ from 'jquery';
import 'datatables.net';
import 'datatables.net-bm';
import './bundles/datatables/js/datatables';
console.log('Hello from app.js');
global.$ = $;
global.jQuery = $;

$(document).ready(function() {
    console.log('jQuery is ready');

});
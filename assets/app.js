// assets/app.js

import './bootstrap.js';
import $ from 'jquery';
import 'datatables.net';
import 'datatables.net-bm';
import './bundles/datatables/js/datatables';
// import Bulma from '@vizuaalog/bulmajs';
console.log('Hello from app.js');
global.$ = $;
global.jQuery = $;

$(document).ready(function () {
    console.log('jQuery is ready');

});

// Get all dropdowns on the page that aren't hoverable.


document.addEventListener('click', function (e) {

    console.log('click', e.target);
    console.log('closest', e.target.closest('.dropdown-trigger'));
    if (null === e.target.closest('.dropdown-trigger')) return;
    // stop immediate propagation
    e.stopImmediatePropagation();
    console.log('dropdown', e.target.closest('.dropdown'));
    e.target.closest('.dropdown').classList.toggle('is-active');
});


// If user clicks outside dropdown, close it.
document.addEventListener('click', function (e) {
    closeDropdowns();
});


/*
 * Close dropdowns by removing `is-active` class.
 */
function closeDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown:not(.is-hoverable)');
    dropdowns.forEach(function (el) {
        el.classList.remove('is-active');
    });
}

// Close dropdowns if ESC pressed
document.addEventListener('keydown', function (event) {
    let e = event || window.event;
    if (e.key === 'Esc' || e.key === 'Escape') {
        closeDropdowns();
    }
});
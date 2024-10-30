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

    if (null === e.target.closest('.dropdown-trigger')) return;
    // stop immediate propagation
    e.stopImmediatePropagation();
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

document.addEventListener('DOMContentLoaded', function () {
    const submitContainer = document.querySelector('.sticky-submit');
    const editor = document.getElementById('editor-content');

    if (!submitContainer || !editor) {
        return;
    }
    function checkSticky() {
        const editorBottom = editor.getBoundingClientRect().bottom;
        const viewportHeight = window.innerHeight;

        if (editorBottom < viewportHeight) {
            submitContainer.style.position = 'relative';
        } else {
            submitContainer.style.position = 'sticky';
        }
    }

    window.addEventListener('scroll', checkSticky);
    window.addEventListener('resize', checkSticky);
});
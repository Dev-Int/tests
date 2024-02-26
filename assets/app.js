import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import '@picocss/pico/css/pico.min.css';
import './styles/app.css';

/* Timeout on flashes */
const flashes = document.getElementById('flashes');
setTimeout(() => {flashes.style.display = 'none'}, 2000);


console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉')

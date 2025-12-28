import { Controller } from '@hotwired/stimulus';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import { French } from 'flatpickr/dist/l10n/fr.js';

export default class extends Controller {
    connect()
    {
        flatpickr(this.element, {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            locale: French,
            altInput: true,
            altFormat: 'j F Y',
            allowInput: true,
            theme: 'pico',
        });
    }
}

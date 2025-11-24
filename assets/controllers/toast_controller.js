import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    connect()
    {
        const flashes = this.element;

        setTimeout(() => {flashes.classList.add('hidden')}, 5000);
    }
}

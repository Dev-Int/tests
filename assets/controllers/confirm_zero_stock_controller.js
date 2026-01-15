import { Controller } from '@hotwired/stimulus';

/**
 * Controller Stimulus pour confirmer la soumission si des stocks sont à zéro.
 *
 * Usage :
 * <form data-controller="confirm-zero-stock"
 *       data-confirm-zero-stock-message-value="Message de confirmation"
 *       data-action="submit->confirm-zero-stock#submit">
 */
export default class extends Controller {
    static values = { message: String }

    submit(event)
    {
        const zeroInputs = [];
        this.element.querySelectorAll('input[name*="_consumer_unit"]').forEach(input => {
            if (input.value === '0' || input.value === '0.0' || input.value === '0.000') {
                const row = input.closest('tr');
                if (row && row.dataset.articleName) {
                    zeroInputs.push(row.dataset.articleName);
                }
            }
        });

        if (zeroInputs.length > 0) {
            const message = this.messageValue + '\n\n' + zeroInputs.join('\n');
            if (!confirm(message)) {
                event.preventDefault();
            }
        }
    }
}

import { Controller } from '@hotwired/stimulus';

/**
 * Controller Stimulus pour nettoyer les formulaires GET (filtres).
 * Désactive les champs vides avant soumission pour avoir des URLs propres.
 *
 * Usage :
 * <form data-controller="clean-form" data-action="submit->clean-form#submit">
 */
export default class extends Controller {
    submit(event)
    {
        // Désactive les champs vides pour qu'ils ne soient pas envoyés dans l'URL
        this.element.querySelectorAll('input, select').forEach(field => {
            if (field.value === '' || field.value === null) {
                field.disabled = true;
            }
        });
    }
}

import { Controller } from '@hotwired/stimulus';

/**
 * Controller Stimulus pour cocher/décocher tous les checkboxes.
 *
 * Usage :
 * <div data-controller="select-all">
 *     <input type="checkbox" data-action="change->select-all#toggle">
 *     <input type="checkbox" data-select-all-target="checkbox">
 *     <input type="checkbox" data-select-all-target="checkbox">
 * </div>
 */
export default class extends Controller {
    static targets = ['checkbox']

    toggle(event)
    {
        this.checkboxTargets.forEach(checkbox => {
            checkbox.checked = event.target.checked;
        });
    }
}

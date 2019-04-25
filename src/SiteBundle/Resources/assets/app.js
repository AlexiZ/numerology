import 'bootstrap';
import 'jquery';
import 'jquery-easing';
import 'magnific-popup';
import './creative.js';

import './main.scss';

$(document).ready(() => {
    let addCollectionItems = document.querySelectorAll('.add-another-collection-widget');
    if (addCollectionItems) {
        addCollectionItems.forEach((addCollectionItem) => {
            addCollectionItem.addEventListener('click', () => {
                var label = $(addCollectionItem).attr('data-label');
                var list = $($(addCollectionItem).attr('data-list'));
                // Try to find the counter of the list or use the length of the list
                var counter = list.data('widget-counter') | list.children().length;

                // grab the prototype template
                var newWidget = list.attr('data-prototype');
                // replace the "__name__" used in the id and name of the prototype
                // with a number that's unique to your noms
                // end name attribute looks like name="contact[noms][2]"
                newWidget = newWidget.replace(/__name__/g, counter);
                newWidget = newWidget.replace(/__label__/g, label);
                // Increase the counter
                counter++;
                // And store it, the length cannot be used if deleting widgets is allowed
                list.data('widget-counter', counter);

                // create a new list element and add it to the list
                var newElem = $(list.attr('data-widget-tags')).html(newWidget);
                newElem.appendTo(list);
            });
        });
    }
});
import 'bootstrap';
import 'jquery';
import 'jquery-easing';
import 'magnific-popup';
import tippy from 'tippy.js';
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

    let automaticTippies = document.querySelectorAll('.automatic-tippy');
    if (automaticTippies) {
        automaticTippies.forEach((automaticTippy) => {
            let leftTippy = "ltippy" in automaticTippy.dataset ? automaticTippy.dataset.ltippy : '',
                rightTippy = "rtippy" in automaticTippy.dataset ? automaticTippy.dataset.rtippy : '',
                urlParams = {
                    'filename': /filename=([^&]+)/.exec(window.location.href)[1],
                },
                commonOptions = {
                    interactive: true,
                    trigger: 'click',
                    theme: 'light-border',
                    animateFill: false,
                    animation: 'scale',
                    arrow: 'true',
                    arrowType: 'round',
                    multiple: 'true',
                    flipOnUpdate: true,
                    content: '<div class="spinner-border" role="status"><span class="sr-only">Chargement...</span></div>',
                }
            ;

            if ("ltippy" in automaticTippy.dataset) {
                urlParams = Object.assign({
                    'definition': leftTippy,
                }, urlParams);
                let fetchUrl = Routing.generate('site_show_details', urlParams);
                const options = Object.assign({
                    placement: 'left',
                    onShow(instance) {
                        let xmlhttp = new XMLHttpRequest(),
                            data = '';
                        xmlhttp.onreadystatechange = () => {
                            if (xmlhttp.readyState === XMLHttpRequest.DONE) {
                                if (xmlhttp.status === 200) {
                                    data = JSON.parse(xmlhttp.response);

                                    instance.setContent(data.definition);
                                }
                            }
                        };
                        xmlhttp.open("GET", fetchUrl, true);
                        xmlhttp.send();
                    },
                }, commonOptions);
                tippy(automaticTippy, options);
            }

            if ("rtippy" in automaticTippy.dataset) {
                urlParams = Object.assign({
                    'value': rightTippy,
                }, urlParams);
                let fetchUrl = Routing.generate('site_show_details', urlParams);
                const options = Object.assign({
                    placement: 'right',
                    onShow(instance) {
                        let xmlhttp = new XMLHttpRequest(),
                            data = '';
                        xmlhttp.onreadystatechange = () => {
                            if (xmlhttp.readyState === XMLHttpRequest.DONE) {
                                if (xmlhttp.status === 200) {
                                    data = JSON.parse(xmlhttp.response);

                                    if ("value" in data) {
                                        instance.setContent(data.value);
                                    } else {
                                        instance.setContent('<em>Information manquante</em>');
                                    }
                                }
                            }
                        };
                        xmlhttp.open("GET", fetchUrl, true);
                        xmlhttp.send();
                    },
                }, commonOptions);
                tippy(automaticTippy, options);
            }
        });
    }
});
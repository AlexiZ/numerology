import 'bootstrap';
import 'jquery';
import 'jquery-easing';
import 'startbootstrap-sb-admin-2/js/sb-admin-2.min';
import 'chart.js';
import 'datatables.net-bs4';
import Shepherd from "shepherd.js";

import './main.scss';

// Handle sidebar navigation
let customTabs = document.querySelectorAll('.custom-tab'),
    customTabLinks = document.querySelectorAll('.custom-tab-link');
if (customTabs && customTabLinks) {
    customTabLinks.forEach((customTabLink) => {
        customTabLink.addEventListener('click', () => {
            customTabs.forEach((customTab) => {
                customTab.classList.add('d-none');
            });

            document.querySelector('#' + customTabLink.dataset.target).classList.remove('d-none');

            customTabLinks.forEach((customTabLink) => {
                customTabLink.classList.remove('active');
            });
            customTabLink.classList.add('active');
        });
    });
}

$(document).ready(() => {
    let automaticTables = document.querySelectorAll('.automatic-table');
    if (automaticTables) {
        automaticTables.forEach((automaticTable) => {
            $(automaticTable).DataTable({
                sorting: false,
                paging: false,
                language: {
                    search: "",
                    searchPlaceholder: "Rechercher...",
                    info: "Affichage de l'&eacute;lement _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
                    infoEmpty: "Affichage de l'&eacute;lement 0 &agrave; 0 sur 0 &eacute;l&eacute;ments",
                    infoFiltered: "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
                    loadingRecords: "Chargement en cours...",
                    zeroRecords: "Aucun &eacute;l&eacute;ment &agrave; afficher",
                    emptyTable: "Aucune donnée disponible dans le tableau",
                },
                dom: '<"top"f>rt<"bottom"i><"clear">'
            });
        });
    }

    let addCollectionItem = document.querySelector('.add-another-collection-widget');
    if (addCollectionItem) {
        $('.add-another-collection-widget').click(function (e) {
            var list = $($(this).attr('data-list'));
            // Try to find the counter of the list or use the length of the list
            var counter = list.data('widget-counter') | list.children().length;

            // grab the prototype template
            var newWidget = list.attr('data-prototype');
            // replace the "__name__" used in the id and name of the prototype
            // with a number that's unique to your noms
            // end name attribute looks like name="contact[noms][2]"
            newWidget = newWidget.replace(/__name__/g, counter);
            // Increase the counter
            counter++;
            // And store it, the length cannot be used if deleting widgets is allowed
            list.data('widget-counter', counter);

            // create a new list element and add it to the list
            var newElem = $(list.attr('data-widget-tags')).html(newWidget);
            newElem.appendTo(list);
        });

        $('.delete-from-collection-widget').click(function (e) {
            var list = $($(this).attr('data-list'));
            // Try to find the counter of the list or use the length of the list
            list.children().last().remove();
        });
    }

    konami();

    let wrapper = document.getElementById('wrapper');
    if (wrapper) {
        getUnreadMessagesCount();
    }

    let answerSubmits = document.querySelectorAll('.answerSubmit');
    if (answerSubmits) {
        answerSubmits.forEach((answerSubmit) => {
            answerSubmit.addEventListener('click', (e) => {
                e.preventDefault();

                let answerText = document.querySelector('form[name="' + answerSubmit.dataset['form'] + '"] .answerText');
                sendMessage(answerText.value);
            });
        });
    }

    let conversationDetails = document.querySelectorAll('.conversationDetails');
    if (conversationDetails) {
        conversationDetails.forEach((details) => {
            details.scrollTop = details.scrollHeight;
        });
    }

    let startTutorial = document.querySelector('#startTutorial');
    if (startTutorial) {
        // remove tutorial button if page does not have sufficiant body attr
        if (!document.querySelector('body').dataset.hasOwnProperty('tutorial')) {
            startTutorial.remove();
        } else {
            startTutorial.addEventListener('click', (e) => {
                e.preventDefault();

                tutorial();
            });
        }
    }
});

// Handle automatic pie charts
Chart.defaults.global.defaultFontFamily = 'Nunito,-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

// Pie Chart generation
let automaticPieCharts = document.querySelectorAll(".automaticPieChart"),
    pieCharts = [], // eslint-disable-line no-unused-vars
    pieChartsIndex = 0;
if (automaticPieCharts) {
    automaticPieCharts.forEach((automaticPieChart) => {
        let labels = automaticPieChart.dataset.labels.split(','),
            data = automaticPieChart.dataset.values.split(',');
        pieCharts[pieChartsIndex] = new Chart(automaticPieChart, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                    callbacks: {
                        label: (tooltipItem, data) => {
                            let label = data.labels[tooltipItem.index] || '',
                                value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] || '';

                            return label + ' : ' + value + '%';
                        },
                    },
                },
                legend: {
                    display: true,
                },
                cutoutPercentage: 0,
            },
        });

        pieChartsIndex++;
    });
}

// Bar Chart generation
let automaticBarCharts = document.querySelectorAll(".automaticBarChart"),
    barCharts = [], // eslint-disable-line no-unused-vars
    barChartsIndex = 0;
if (automaticBarCharts) {
    automaticBarCharts.forEach((automaticBarChart) => {
        let labels = automaticBarChart.dataset.labels.split(','),
            rawData = automaticBarChart.dataset.values,
            legends = automaticBarChart.dataset.legends.split(','),
            datasets = [],
            datasetOptions = {
                borderWidth: '2',
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            };

        if (-1 !== rawData.indexOf('||')) {
            let data = rawData.split('||');

            for (let i = 0; i < data.length; i++) {
                let colors = [32, 64, 96, 128, 160, 192],
                    color = 'rgba(' + colors[Math.floor(Math.random() * colors.length)] + ', ' + colors[Math.floor(Math.random() * colors.length)] + ', ' + colors[Math.floor(Math.random() * colors.length)] + ', 0.5)';
                datasets[i] = Object.assign(
                    {
                        data: data[i].split(','),
                        label: legends[i],
                        backgroundColor: color,
                        borderColor: color,
                        hoverBackgroundColor: color
                    },
                    datasetOptions
                );
            }
        } else {
            datasets = [Object.assign(
                {data: rawData.split(',')},
                datasetOptions
            )];
        }

        barCharts[barChartsIndex] = new Chart(automaticBarChart, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: datasets,
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                    callbacks: {
                        label: (tooltipItem, data) => {
                            let label = data.labels[tooltipItem.index] || '',
                                value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] || '';

                            return label + ' : ' + value + '%';
                        },
                    },
                },
                legend: {
                    display: true,
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                },
            },
        });

        barChartsIndex++;
    });
}

// Activate user
let userValidates = document.querySelectorAll('.userValidate');
if (userValidates) {
    userValidates.forEach((userValidate) => {
        userValidate.addEventListener('click', (e) => {
            e.preventDefault();

            let xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = () => {
                if (xmlhttp.readyState === XMLHttpRequest.DONE) {
                    if (xmlhttp.status === 200) {
                        let data = JSON.parse(xmlhttp.response);
                        modalMessage('Mise à jour réussie', 'L\'utilisateur ' + data.nickname + ' a bien été activé');
                    }
                    else {
                        modalMessage('Raté', 'Il semblerait qu\'il y ait eu un souci...');
                    }
                }
            };

            xmlhttp.open("GET", Routing.generate('admin_user_validate', {'userId': userValidate.dataset.userid}), true);
            xmlhttp.send();
        });
    });
}

// Revoke user access
let userRefuses = document.querySelectorAll('.userRefuse');
if (userRefuses) {
    userRefuses.forEach((userRefuse) => {
        userRefuse.addEventListener('click', (e) => {
            e.preventDefault();

            let xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = () => {
                if (xmlhttp.readyState === XMLHttpRequest.DONE) {
                    if (xmlhttp.status === 200) {
                        let data = JSON.parse(xmlhttp.response);
                        modalMessage('Blocage réussi', 'L\'utilisateur ' + data.nickname + ' a bien été bloqué');
                    } else {
                        modalMessage('Raté', 'Il semblerait qu\'il y ait eu un souci...');
                    }
                }
            };

            xmlhttp.open("GET", Routing.generate('admin_user_refuse', {'userId': userRefuse.dataset.userid}), true);
            xmlhttp.send();
        });
    });
}

let messagesDropdown = document.querySelector('#messagesDropdown');
if (messagesDropdown) {
    messagesDropdown.addEventListener('click', (e) => {
        e.preventDefault();

        getMessages();
    });
}

// Get unread messages count
const getUnreadMessagesCount = () => {
    let xmlhttp = new XMLHttpRequest(),
        messageUnreadSpin = document.querySelector('#messageUnreadSpin');
    xmlhttp.onreadystatechange = () => {
        if (xmlhttp.readyState === XMLHttpRequest.OPENED) {
            messageUnreadSpin.classList.remove('d-none');
        }
        else if (xmlhttp.readyState === XMLHttpRequest.DONE) {
            if (xmlhttp.status === 200) {
                let data = JSON.parse(xmlhttp.response),
                    badge = document.querySelector('#unreadMessagesBadge');
                if (data > 0) {
                    badge.classList.remove('d-none');
                    badge.innerHTML = '<i class="fa fa-exclamation"></i>';
                } else {
                    badge.classList.add('d-none');
                    badge.innerHTML = '';
                }

                messageUnreadSpin.classList.add('d-none');
            }
        }
    };

    xmlhttp.open("GET", Routing.generate('messages_number'), true);
    xmlhttp.send();
};

const getMessages = () => {
    let xmlhttp = new XMLHttpRequest(),
        messageUnreadSpin = document.querySelector('#messageUnreadSpin');
    xmlhttp.onreadystatechange = () => {
        if (xmlhttp.readyState === XMLHttpRequest.OPENED) {
            messageUnreadSpin.classList.remove('d-none');
        }
        else if (xmlhttp.readyState === XMLHttpRequest.DONE) {
            if (xmlhttp.status === 200) {
                let data = JSON.parse(xmlhttp.response),
                    messagesList = document.querySelector('#messagesList');
                if (data) {
                    messagesList.innerHTML = data;
                } else {
                    messagesList.innerHTML = '';
                }

                messageUnreadSpin.classList.add('d-none');
            }
        }
    };

    xmlhttp.open("GET", Routing.generate('messages_list'), true);
    xmlhttp.send();
};

const modalMessage = (title, message) => {
    let modale = document.querySelector('#standardModal');
    modale.querySelector('.modal-title').innerHTML = title;
    modale.querySelector('.modal-body').innerHTML = message;

    $(modale).modal('show');
};

const sendMessage = (message) => {
    let xmlhttp = new XMLHttpRequest(),
        url = Routing.generate('messages_send'),
        params = new FormData();
    params.append('message', message);
    xmlhttp.onreadystatechange = () => {
        if (xmlhttp.readyState === XMLHttpRequest.DONE) {
            let data = JSON.parse(xmlhttp.response);

            if (xmlhttp.status === 200) {
                 window.location.reload();
            } else {
                modalMessage('Une erreur est survenue', data);
            }
        }
    };

    xmlhttp.open("POST", url, true);
    xmlhttp.send(params);
};

const tutorial = () => {
    let tour = new Shepherd.Tour(),
        section = document.querySelector('body').dataset.tutorial;

    if (!section) {
        return;
    }

    let tourSteps = {},
        buttonNext = {
            text: 'Suivant <i class="fa fa-arrow-right"></i>',
            action: tour.next,
            classes: 'btn-real-primary'
        },
        buttonBack = {
            text: '<i class="fa fa-arrow-left"></i> Précédent',
            action: tour.back,
            classes: 'btn-real-warning'
        },
        buttonEnd = {
            text: 'Terminer <i class="fa fa-check"></i>',
            action: tour.next,
            classes: 'btn-real-secondary'
        }
    ;

    switch (section) {
        case 'numerologie_index':
            tourSteps = {
                'stepOne': {
                    title: 'Bienvenue !',
                    text: 'Pour démarrer cette nouvelle aventure du bon pied, rien de tel qu\'une petite explication.<br>On y va ?',
                    buttons: [
                        {
                            text: 'Une autre fois <i class="fa fa-times"></i>',
                            action: tour.cancel,
                            classes: 'btn-real-danger'
                        },
                        {
                            text: 'C\'est parti ! <i class="fa fa-arrow-right"></i>',
                            action: tour.next,
                            classes: 'btn-real-success'
                        }
                        ],
                        tippyOptions: { maxWidth: '500px' }
                },
                'stepTwo': {
                    title: 'Nouvelle personne',
                    text: 'Vous pouvez ajouter une nouvelle personne pour en consulter l\'analyse numérologique.',
                    buttons: [buttonBack, buttonNext],
                    attachTo: {element: '#newAnalysis', on: 'left'},
                    highlightClass: 'reallyWhite',
                    showCancelLink: true,
                    tippyOptions: { maxWidth: '500px' }
                },
                'stepThree': {
                    title: 'Votre historique',
                    text: 'Consulter vos précédentes analyses numérologiques.<br><em>Rien ne perd, rien ne se crée, tout se transforme !</em>',
                    buttons: [buttonBack, buttonNext],
                    attachTo: {element: '#historyTable', on: 'right'},
                    highlightClass: 'reallyWhite',
                    showCancelLink: true,
                    tippyOptions: { maxWidth: '500px' }
                },
                'stepFour': {
                    title: 'Vos messages',
                    text: 'Vous nous écrivez, on vous répond !<br>Retrouvez ici vos derniers messages.',
                    buttons: [buttonBack, {
                        text: 'Essayer !',
                        classes: 'btn-real-success',
                        action: () => {
                            window.location.href = Routing.generate('messages_show');
                        },
                    }, buttonNext],
                    attachTo: {element: '#messagesDropdown', on: 'bottom'},
                    highlightClass: 'reallyWhite',
                    showCancelLink: true,
                    tippyOptions: { maxWidth: '500px' }
                },
                'stepFive': {
                    title: 'Vous êtes ici',
                    text: 'Sécurisez votre travail : cliquez ici pour vous déconnecter.',
                    buttons: [buttonBack, buttonNext],
                    attachTo: {element: '#userDropdown', on: 'bottom'},
                    highlightClass: 'reallyWhite',
                    showCancelLink: true,
                    tippyOptions: { maxWidth: '500px' }
                },
                'lastStep': {
                    title: 'Prochaine étape',
                    text: 'Vous en savez suffisamment sur votre page d\'accueil pour démarrer, mais n\'hésitez pas à <a href="' + Routing.generate('messages_show') + '">nous écrire</a> si besoin.<br><br>Générez votre première <a href="' + Routing.generate('numerologie_add') + '">analyse numérologique</a> dès à présent.',
                    buttons: [buttonBack, {
                        text: '<i class="fa fa-arrow-right"></i> Commencer <i class="fa fa-arrow-left"></i>',
                        classes: 'btn-real-success',
                        action: () => {
                            window.location.href = Routing.generate('numerologie_add');
                        },
                    }, buttonEnd],
                    tippyOptions: { maxWidth: '500px' }
                }
            };
            break;
        case 'numerologie_add':
            tourSteps = {
                'stepOne': {
                    title: 'Ajouter une nouvelle personne',
                    text: 'C\'est sur cette page que tout commence :<br>Renseignez les informations de base de votre sujet pour en découvrir l\'analyse numérologique.',
                    buttons: [buttonNext],
                    showCancelLink: true,
                    tippyOptions: { maxWidth: '500px' }
                },
                'stepTwo': {
                    title: 'Nom de famille de naissance',
                    text: 'Renseignez la 1<sup>ère</sup> information : le nom de famille de naissance.<br>Celui-ci fait partie des deux seuls champs obligatoires et nécessaires pour construire l\'analyse.',
                    buttons: [buttonBack, buttonNext],
                    attachTo: {element: '#app_bundle_numerologie_birthName', on: 'bottom'},
                    highlightClass: 'reallyWhite',
                    showCancelLink: true,
                    tippyOptions: { maxWidth: '500px' }
                },
                'stepThree': {
                    title: 'Nom d\'usage',
                    text: '2<sup>ème</sup> information : le nom d\'usage.<br>Vous renseignerez ici le nom de famille officiel. Cela peut être le nom de marige, d\'adoption ou encore un surnom ou un nom de scène.',
                    buttons: [buttonBack, buttonNext],
                    attachTo: {element: '#app_bundle_numerologie_useName', on: 'bottom'},
                    highlightClass: 'reallyWhite',
                    showCancelLink: true,
                    tippyOptions: { maxWidth: '500px' }
                },
                'stepFour': {
                    title: '1<sup>er</sup> prénom',
                    text: 'Données obligatoire également, le premier prénom doit être rempli.<br><em>Ne mettez ici qu\'un seul prénom, composé ou non.</em>',
                    buttons: [buttonBack, buttonNext],
                    attachTo: {element: '#app_bundle_numerologie_firstname', on: 'bottom'},
                    highlightClass: 'reallyWhite',
                    showCancelLink: true,
                    tippyOptions: { maxWidth: '500px' }
                },
                'stepFive': {
                    title: 'Prénoms secondaires',
                    text: 'Vous pouvez cliquer sur ce bouton pour ajouter un nouveau prénom.<br>Un nouveau champ apparaîtra pour vous en permettre la saisie.',
                    buttons: [buttonBack, buttonNext],
                    attachTo: {element: '.add-another-collection-widget', on: 'top'},
                    highlightClass: 'reallyWhite',
                    showCancelLink: true,
                    tippyOptions: { maxWidth: '500px' }
                },
                'stepSix': {
                    title: 'Date et heure de naissance',
                    text: 'La date et l\'heure de naissance vont vous permettre d\'accéder à une analyse numérologique bien plus complète.',
                    buttons: [buttonBack, buttonNext],
                    attachTo: {element: '#app_bundle_numerologie_birthDate', on: 'top'},
                    highlightClass: 'reallyWhite',
                    showCancelLink: true,
                    tippyOptions: { maxWidth: '500px' }
                },
                'stepSeven': {
                    title: 'Lieu de naissance',
                    text: 'De la même manière, le lieu de naissance permet de comprendre pleinement un individu.',
                    buttons: [buttonBack, buttonNext],
                    attachTo: {element: '#app_bundle_numerologie_birthPlace', on: 'top'},
                    highlightClass: 'reallyWhite',
                    showCancelLink: true,
                    tippyOptions: { maxWidth: '500px' }
                },
                'stepEight': {
                    title: 'Voir les résultats',
                    text: 'Terminez votre saisie en cliquant sur ce bouton.',
                    buttons: [buttonBack, buttonNext],
                    attachTo: {element: '#submitAdd', on: 'top'},
                    highlightClass: 'reallyWhite',
                    showCancelLink: true,
                    tippyOptions: { maxWidth: '500px' }
                },
                'lastStep': {
                    title: 'Prochaine étape',
                    text: 'Vous savez maintenant comment générer votre 1<sup>ère</sup> analyse numérologique complète, alors à vous de jouer !',
                    buttons: [buttonBack, buttonEnd],
                    tippyOptions: { maxWidth: '500px' }
                }
            };
            break;
        default:
            break;
    }

    Object.keys(tourSteps).map((tourStepName) => {
        tour.addStep(tourStepName, tourSteps[tourStepName]);

        return tourStepName;
    });

    $('#tour-modal-fade').modal({ backdrop: 'static', show: true });

    tour.on('complete', () => {
        $('#tour-modal-fade').hide();
        $('.modal-backdrop').hide();
        document.querySelector('#startTutorial').remove();
        document.querySelector('body').classList.remove('modal-open');
    });
    tour.on('cancel', () => {
        $('#tour-modal-fade').hide();
        $('.modal-backdrop').hide();
        document.querySelector('#startTutorial').remove();
        document.querySelector('body').classList.remove('modal-open');
    });
    tour.start();
};

const konami = () => {
    let secret = '38384040373937396665',
        input = '',
        timer;

    document.addEventListener('keyup', (e) => {
        input += e.which;
        clearTimeout(timer);
        timer = setTimeout(() => { input = ''; }, 500);
        if (input === secret) {
            nyanCat();
        }
    });
};

const nyanCat = () => {
    document.querySelector('body').insertAdjacentHTML('beforeend', '<div id="nyan"><img src="http://www.nyan.cat/cats/original.gif"></div>');
    $('#nyan').animate({
        'marginLeft' : '100%'
    }, 6000);
    setTimeout(() => {
        document.querySelector('#nyan').remove();
    }, 8000);
};
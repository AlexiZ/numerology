import 'bootstrap';
import 'jquery';
import 'jquery-easing';
import 'startbootstrap-sb-admin-2/js/sb-admin-2.min';
import 'chart.js';
import 'datatables.net-bs4';

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
                let colors = [64, 128, 255],
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
import 'bootstrap';
import 'jquery';
import 'jquery-easing';
import 'startbootstrap-sb-admin-2/js/sb-admin-2.min';
import 'chart.js';
import 'datatables.net-bs4';

import './main.scss';

// Handle sidebar navigation for "show"
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
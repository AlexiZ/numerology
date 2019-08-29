import 'bootstrap';
import 'jquery';
import 'jquery-easing';
import './creative.js';
import Chart from 'chart.js';
import 'chartjs-plugin-annotation';

import './main.scss';

var barCharts = []; // eslint-disable-line no-unused-vars

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

    // Bar Chart generation
    let automaticBarCharts = document.querySelectorAll(".automaticBarChart");
    if (automaticBarCharts) {
        buildCharts(automaticBarCharts);
    }

    // Copy permanent link to clipboard
    let freePremiumCopy = document.getElementById('free-premium-copy');
    if (freePremiumCopy) {
        freePremiumCopy.addEventListener('click', () => {
            copyTextToClipboard(window.location.href);
            freePremiumCopy.querySelector('i').style.color = '#f4623a';
        });
    }
}).on('shown.bs.collapse', () => {
    let automaticBarCharts = document.querySelectorAll(".automaticBarChart");
    if (automaticBarCharts) {
        buildCharts(automaticBarCharts);
    }
});

const buildCharts = (automaticBarCharts) => {
    let barChartsIndex = 0;
    automaticBarCharts.forEach((automaticBarChart) => {
        let labels = automaticBarChart.dataset.labels.split(','),
            rawData = automaticBarChart.dataset.values,
            legends = 'legends' in automaticBarChart.dataset ? automaticBarChart.dataset.legends.split(',') : false,
            datasetOptions = {
                borderWidth: '2',
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            },
            datasets = [
                Object.assign(
                    {
                        data: rawData.split(','),
                        backgroundColor: ['rgba(255, 105, 98, 0.5)', 'rgba(254, 254, 200, 0.5)', 'rgba(255, 203, 153, 0.5)', 'rgba(171, 225, 251, 0.5)'],
                    },
                    datasetOptions
                )],
            chart = new Chart(automaticBarChart, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: datasets,
                },
                options: {
                    maintainAspectRatio: true,
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

                                return label + ' : ' + (value > 0 ? '+' : '') + value + '%';
                            },
                        },
                    },
                    legend: {
                        display: legends ? true : false,
                    },
                    scales: {
                        yAxes: [{
                            id: 'y-axis-0',
                            ticks: {
                                beginAtZero: true,
                                suggestedMin: -15,
                                suggestedMax: 15,
                                minRotation : 90,
                            },
                            scaleLabel: {
                                display: true,
                                labelString: 'Moyenne',
                            }
                        }]
                    },
                    annotation: {
                        annotations: [{
                            type: 'line',
                            mode: 'horizontal',
                            scaleID: 'y-axis-0',
                            value: '8',
                            borderColor: 'red',
                            borderWidth: 2,
                            borderDash: [2, 5],
                            label: {
                                backgroundColor: 'rgba(1,1,1,0)',
                                fontFamily: "sans-serif",
                                fontSize: 10,
                                fontStyle: "bold",
                                fontColor: "#000",
                                position: "right",
                                xAdjust: 0,
                                yAdjust: -10,
                                enabled: true,
                                content: "Excès"
                            },
                        }, {
                            type: 'line',
                            mode: 'horizontal',
                            scaleID: 'y-axis-0',
                            value: '-8',
                            borderColor: 'red',
                            borderWidth: 2,
                            borderDash: [2, 5],
                            label: {
                                backgroundColor: 'rgba(1,1,1,0)',
                                fontFamily: "sans-serif",
                                fontSize: 10,
                                fontStyle: "bold",
                                fontColor: "#000",
                                position: "right",
                                xAdjust: 0,
                                yAdjust: 10,
                                enabled: true,
                                content: "Manque"
                            },
                        }]
                    }
                },
        });

        barCharts[barChartsIndex] = chart;
        barChartsIndex++;
    });
};

const copyTextToClipboard = (text) => {
    if (!navigator.clipboard) {
        alert('Votre navigateur ne prend pas en charge cette fonctionnalité.');
        return;
    }

    navigator.clipboard.writeText(text).then(() => {
        console.log('Async: Copying to clipboard was successful!');
    }, function(err) {
        console.error('Async: Could not copy text: ', err);
    });
};
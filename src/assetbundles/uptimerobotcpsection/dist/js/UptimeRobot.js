/**
 * UptimeRobot plugin for Craft CMS
 *
 * UptimeRobot Field JS
 *
 * @author    La Haute Société
 * @copyright Copyright (c) 2018 La Haute Société
 * @link      https://www.lahautesociete.com
 * @package   UptimeRobot
 * @since     1.0.0
 */

jQuery(function () {
    var labelFormatter = function (value) {
        return value + 'ms';
    };

    var options = {
        chart: {
            type: 'area',
            height: 350,
        },
        dataLabels: {
            enabled: false
        },
        series: [{
            name: "Response time",
            data: response_times_series
        }],
        xaxis: {
            type: 'datetime'

        },
        yaxis: {
            labels: {
                formatter: labelFormatter
            }
        },
        tooltip: {
            x: {
                format: 'HH:mm:ss'
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.5,
                opacityTo: 1,
                stops: [0, 100]
            }
        },

    }

    var chart = new ApexCharts(
        document.querySelector("#apex-response-times"),
        options
    );

    jQuery('#tab-responsetimes').on('click', function () {
        chart.destroy();
        chart.render();
    });

    chart.render();
});

<script>
    Highcharts.chart('container_@chart.name', {
        chart: {
            type: 'networkgraph',
            height: '100%'
        },
        title: {
            text: '@chart.title'
        },
        subtitle: {
            text: '@chart.subTitle'
        },
        plotOptions: {
            networkgraph: {
                keys: ['from', 'to'],
                layoutAlgorithm: {
                    enableSimulation: true,
                    friction: -0.9
                }
            }
        },
        series: [{
            dataLabels: {
                enabled: true,
                linkFormat: ''
            },
            id: 'lang-tree',
            data: @chart.data
        }]
    });
</script>
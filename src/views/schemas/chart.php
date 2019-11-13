<script>
    Highcharts.chart('container_@chart.name', {
        chart: {
            type: 'networkgraph',
            height: '100%',
            width: 600
        },
        title: {
            text: '@chart.title'
        },
        subtitle: {
            text: '@chart.subTitle'
        },
        plotOptions: {
            series: {
                marker: {
                    radius: 15
                }
            },
            networkgraph: {
                keys: ['from', 'to', 'connectionLabel'],
                layoutAlgorithm: {
                    linkLength: 80,
                    enableSimulation: true,
                    friction: -0.9
                }
            }
        },
        series: [{
            dataLabels: {
                enabled: true
            },
            nodes: @chart.nodes,
            id: 'lang-tree',
            data: @chart.data
        }]
    });
</script>
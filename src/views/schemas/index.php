<div class="card">
    <div class="card-header">Схемы</div>
    <div class="card-body">
        <ul class="list-group">
            @schemas
        </ul>
    </div>
</div>
<script !src="">
Highcharts.addEvent(
    Highcharts.Series,
    'afterSetOptions',
    function (e) {
        var colors = Highcharts.getOptions().colors,
        i = 0,
        nodes = {};

        if (
            this instanceof Highcharts.seriesTypes.networkgraph &&
            e.options.id === 'lang-tree'
        ) {
            e.options.data.forEach(function (link) {

            if (link[0] === 'Proto Indo-European') {
            nodes['Proto Indo-European'] = {
            id: 'Proto Indo-European',
            marker: {
            radius: 20
            }
            };
            nodes[link[1]] = {
            id: link[1],
            marker: {
            radius: 10
            },
            color: colors[i++]
            };
            } else if (nodes[link[0]] && nodes[link[0]].color) {
            nodes[link[1]] = {
            id: link[1],
            color: nodes[link[0]].color
            };
            }
            });

            e.options.nodes = Object.keys(nodes).map(function (id) {
            return nodes[id];
            });
        }
    }
);

</script>
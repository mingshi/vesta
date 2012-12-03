<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
</head>
<body>
<?php
require_once '../libraries/common.lib.php';
        $nowyear = date('Y',time());
        $nowmonth = date('m',time());
        $graph_data = '';
        for($i=1;$i<=intval($nowmonth);$i++){
            $p = sprintf("%02d",$i);
            $param = $nowyear.$p;
            $thecount = get_every_month_event($pdo,$param);
            $event_graph_month[$p]['date'] = "new Date($nowyear$p,0)";
            $event_graph_month[$p]['value'] = $thecount['total'];
        }
        foreach($event_graph_month as $k=>$v){
            $graph_data .= "{date:".$v['date'].",value:".$v['value']."},"; 
        }
        $graph_data = trim($graph_data,',');
        $graph_data = "[".$graph_data."]";
?>


    <div style="width:440px;height:220px;">
        <div style="float:left;font-size:15px;">事件系统</div>
        <div style="float:right;"><a href="http://vesta.corp.anjuke.com" style="color:#46A3FF;font-size:14px;text-decoration:none;">更多 >>></a></div>
        </br>
<hr color="#ddd" width="440px" size="2" style="filter:progid:DXImageTransform.Microsoft.Glow(color=#5151A2,strength=10)">
        <div id="chartdiv" style="height: 165px;width:430px;padding-left:10px;"></div>

        <div style="text-align:center;font-size:15px;">月度事件走势图</div>
    </div>


<script src="js/amcharts.js" type="text/javascript"></script>
<script type="text/javascript">
    var chartmain;
    var graph;
    var chartData = <?php echo $graph_data; ?>;
    AmCharts.ready(function () {
        // SERIAL CHART
        chartmain = new AmCharts.AmSerialChart();
        chartmain.pathToImages = "images/";
        chartmain.dataProvider = chartData;
        chartmain.marginLeft = 10;
        chartmain.categoryField = "date";
        chartmain.zoomOutButton = {
            backgroundColor: '#000000',
            backgroundAlpha: 0.15
        };

        // listen for "dataUpdated" event (fired when chartmain is inited) and call zoomChart method when it happens
        //chartmain.addListener("dataUpdated", zoomChart);

        // AXES
        // category
        var categoryAxis = chartmain.categoryAxis;
        categoryAxis.parseDates = true; // as our data is date-based, we set parseDates to true
        categoryAxis.minPeriod = "YYYY"; // our data is yearly, so we set minPeriod to YYYY
        categoryAxis.gridAlpha = 0;

        // value
        var valueAxis = new AmCharts.ValueAxis();
        valueAxis.axisAlpha = 0;
        valueAxis.inside = true;
        chartmain.addValueAxis(valueAxis);

        // GRAPH                
        graph = new AmCharts.AmGraph();
        graph.type = "smoothedLine"; // this line makes the graph smoothed line.
        graph.lineColor = "#d1655d";
        graph.negativeLineColor = "#637bb6"; // this line makes the graph to change color when it drops below 0
        graph.bullet = "round";
        graph.bulletSize = 5;
        graph.lineThickness = 2;
        graph.valueField = "value";
        chartmain.addGraph(graph);

        // CURSOR
        var chartCursor = new AmCharts.ChartCursor();
        chartCursor.cursorAlpha = 0;
        chartCursor.cursorPosition = "mouse";
        chartCursor.categoryBalloonDateFormat = "YYYY";
        chartmain.addChartCursor(chartCursor);

        // SCROLLBAR
        var chartScrollbar = new AmCharts.ChartScrollbar();
        chartScrollbar.graph = graph;
        chartScrollbar.backgroundColor = "#DDDDDD";
        chartScrollbar.scrollbarHeight = 30;
        chartScrollbar.selectedBackgroundColor = "#FFFFFF";
        chartmain.addChartScrollbar(chartScrollbar);

        // WRITE
        chartmain.write("chartdiv");
    });

    // this method is called when chartmain is first inited as we listen for "dataUpdated" event
    function zoomChart() {
        // different zoom methods can be used - zoomToIndexes, zoomToDates, zoomToCategoryValues
        chartmain.zoomToDates(new Date(201201, 0), new Date(201512, 0));
    }
</script>


</body>
</html>

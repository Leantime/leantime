<?php

$project = $this->get('project');
if(!$project['hourBudget']) {
    $project['hourBudget'] = 'no';
}

$project = $this->get('project');
if(!$project['dollarBudget']) {
    $project['dollarBudget'] = 'no';
}

$bookedHours = $this->get('bookedHours');
$bookedDollars = $this->get('bookedDollars');

$bookedHoursArr = $this->get('bookedHoursArray');

$dueDate = '';
?>
<script type="text/javascript">
    jQuery(document).ready(function(){        
        
    
    <?php 
    $arr = array();
    $arr2 = array();
    foreach($bookedHoursArr as $key => $value){
                
        $arr[] = "['".($key*1000)."', ".$value."]";
        $arr2[] = "['".($key*1000)."', ".$project['hourBudget']."]";
    }
            
    if(is_array($arr) === true) {
        $string = implode(",", $arr);
        $string2 = implode(",", $arr2);
        echo "var hours = [".$string."];";
        echo "var hoursBudget = [".$string2."];";
    }else{
        echo "var hours = [];";
        echo "var hoursBudget = [];";
    }
    ?>
            
        function showTooltip(x, y, contents) {
            jQuery('<div id="tooltip" class="tooltipflot">' + contents + '</div>').css( {
                position: 'absolute',
                display: 'none',
                top: y + 5,
                left: x + 5
            }).appendTo("body").fadeIn(200);
        }
    
        
    <?php 
    if($bookedHours > $project['hourBudget']) {
        $color="red";
    }else{
        $color="green";
    }
    ?>
            
        var plot = jQuery.plot(jQuery("#chartplace"),
               [ { data: hoursBudget, label: "Hours Budget", color: "<?php echo $color; ?>"}, { data: hours, label: "Hours Booked", color: "#ff9900"}  ], {
                   series: {
                       lines: { show: true, fill: true, fillColor: { colors: [ { opacity: 0.05 }, { opacity: 0.15 } ] } },
                       points: { show: false }
                   },
                   legend: { position: 'nw'},
                   grid: { hoverable: true, clickable: true, borderColor: '#666', borderWidth: 2, labelMargin: 10 },
                   yaxis: { min: 0 },
                   xaxis: {
                      mode: "time",
                      minTickSize: [7, "day"],
                      timeformat: "%y/%m/%d"
                  }
                 });
        
        var previousPoint = null;
        
        jQuery("#chartplace").bind("plothover", function (event, pos, item) {
            
            jQuery("#x").text(pos.x.toFixed(2));
            jQuery("#y").text(pos.y.toFixed(2));
            
            if(item) {
                if (previousPoint != item.dataIndex) {
                    previousPoint = item.dataIndex;
                        
                    jQuery("#tooltip").remove();
                    var x = item.datapoint[0].toFixed(2),
                    y = item.datapoint[1].toFixed(2);
                        
                    showTooltip(item.pageX, item.pageY,
                                    item.series.label + " " + y);
                }
            
            } else {
               jQuery("#tooltip").remove();
               previousPoint = null;            
            }
        
        });


});
</script>




    
    <h3>Hours</h3>
    <?php echo $bookedHours; ?> hours of <?php echo $project['hourBudget'] ?> estimated hours used.<br /><br />
    <div id="chartplace" style="height:300px; width:100%;"></div>
    
    <br /><br /><h3>Budget</h3>   
    <?php echo $bookedDollars; ?> dollars of <?php echo $project['dollarBudget'] ?> estimated dollars used.<br /><br />
                    
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       

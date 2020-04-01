<script type="text/javascript">
var ganttData = [
    
    <?php 
    $jsContent = array();
    foreach($this->get('projectTickets') as $ticket){
        
        if($ticket["editFrom"] != "0000-00-00 00:00:00" && $ticket["editFrom"] != "1969-12-31 00:00:00") {
            $plannedFromDate = new DateTime($ticket["editFrom"]);
            $plannedToDate = new DateTime($ticket["editTo"]);
        }else{
            $plannedFromDate = new DateTime();
            $plannedToDate = new DateTime();
            $plannedToDate->add(new DateInterval("P1D"));
        }    
        
        
        $author = str_replace("'", "", str_replace('"', "", json_encode($ticket["firstname"]." ".$ticket["lastname"])));
        
        
        $jsContent[] = "{
				id: ".$ticket["id"].", name: '<a href=\"/tickets/showTicket/".$ticket["id"]."\">".str_replace("'", "", str_replace('"', "", json_encode($ticket["headline"])))."</a>', series: [
					{name: '".$author."', start: new Date(".$plannedFromDate->format('Y').", ".($plannedFromDate->format('m')-1).", ".$plannedFromDate->format('d')."), end: new Date(".$plannedToDate->format('Y').", ".($plannedToDate->format('m')-1).", ".$plannedToDate->format('d').") }
				]
			}";
        
    } 
    
    echo implode(",", $jsContent);
    
    ?>
    
];


        jQuery(function () {
            
            var width = jQuery(".maincontentinner").width() - 500;

            jQuery("#ganttChart").ganttView({ 
                data: ganttData,
                slideWidth: width,
                behavior: {
                    onClick: function (data) { 
                        
                    },
                    onResize: function (data) { 
                        var msg = "You edited the To-Do to start: " + data.start.toString("M/d/yyyy") + ", end: " + data.end.toString("M/d/yyyy") + " }";
                        jQuery("#eventMessage").text(msg);
                        
                        jQuery.ajax({
                            type: 'POST',
                            url: leantime.appUrl+'/tickets/editTicket&raw=true&changeDate=true',
                            data: 
                            {
                                id : data.id,
                                dateFrom:data.start.toString("yyyy-M-d"),
                                dateTo:data.end.toString("yyyy-M-d")
                            }
                        });
                        jQuery("#eventMessage").show();
                    },
                    onDrag: function (data) { 
                        var msg = "You dragged the To-Do to start: " + data.start.toString("M/d/yyyy") + ", end: " + data.end.toString("M/d/yyyy") + " ";
                        jQuery("#eventMessage").text(msg);
                        
                        jQuery.ajax({
                            type: 'POST',
                            url: leantime.appUrl+'/tickets/editTicket&raw=true&changeDate=true',
                            data: 
                            {
                                id : data.id,
                                dateFrom:data.start.toString("yyyy-M-d"),
                                dateTo:data.end.toString("yyyy-M-d")
                            }
                        });
                        jQuery("#eventMessage").show();
                    }
                }
            });
            
            // $("#ganttChart").ganttView("setSlideWidth", 600);
        });
    </script>

    <?php echo $this->displayLink('tickets.newTicket', "<i class='iconfa-plus'></i> ".$this->__('NEW_TICKET'), null, array('class' => 'btn btn-primary btn-rounded')) ?>
    <div id="eventMessage" class="alert alert-success" style="display:none;"></div>
    <div id="ganttChart"></div>
    


<?php

defined( 'RESTRICTED' ) or die( 'Restricted access' );

$tickets = $this->get('objTickets');
$helper = $this->get('helper');
?>

<script type="text/javascript">
    jQuery(document).ready(function(){
        // dynamic table
       dataTable = jQuery('#dyntableX').dataTable({
        	"sDom": 'lfrW<"clear">tip',
            "sPaginationType": "full_numbers",
            "aColumns": [
                {"sType": "html" },
                {"sType": "html"},
                {"sType": "html"},
                {"sType": "date-us"},
                {"sType": "date-us"},
            	{"sType": "html"},
            	{"sType": "html"},
            	{"sType": "html"},
            	{"sType": "numeric"}
            ],
           
            "bStateSave": true,
            oColumnFilterWidgets: {
		        aiExclude: [ 0 ],
		        sSeparator: ' / ',
		        bGroupTerms: true
		
		    },
		     "fnDrawCallback": function(oSettings) {
		     	jQuery('.column-filter-widgets .btn-primary').remove();
                jQuery('.column-filter-widget-selected-terms').before('<button class="btn btn-primary"  onclick="dataTable.fnResetAllFilters();">Reset Filter</button>'); 
                jQuery.uniform.update();
            }
		   
        });
        
   
      
    });
    
    function changeStatus(id){
    	var state = new Array('label-success','label-warning','label-info','label-important','label-inverse');

		var statePlain = new Array('Finished','Problem','Unapproved','New','Seen');

		var newStatus = jQuery("#status-select-"+id+" option:selected").val();
		
		jQuery.ajax({ 
			url: '/index.php?act=general.ajaxRequest&module=tickets.showAll&export=true', 
			type: 'post',
			data: { ticketId: id, newStatus: newStatus},
			success: function(msg){
				
				jQuery("#status-"+id).show();
				
				jQuery("#status-"+id).attr("class", "f-left "+state[newStatus]);
				jQuery("#status-"+id).html(statePlain[newStatus]);
				
				jQuery("#status-spinner-"+id).show();
				
				jQuery("#status-select-"+id).hide();
				
				jQuery(".maincontentinner").prepend("<div class='alert alert-success'><button data-dismiss='alert' class='close' type='button'>×</button>"+msg+"</div>");
			}
		});

	}
</script>

<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5>All Features Summary</h5>
                <h1><?php echo $language->lang_echo('ALL_TICKETS'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

<form action='' method='POST' id='statusForm'>
	<input type='hidden' name='hiddenId' id='hiddenId' />
	<input type='hidden' name='hiddenStatus' id='hiddenStatus' />
</form>

<?php echo $this->displayLink('tickets.newTicket',$language->lang_echo('NEW_TICKET'), NULL, array('class' => 'btn btn-primary btn-rounded')) ?>
		
<div class="headtitle" style="margin:0px;">
	<div class="btn-group">
		<button class="btn dropdown-toggle" data-toggle="dropdown" onclick="jQuery('.dataTables_length').toggle('fast'); jQuery('.dataTables_filter').toggle('fast'); jQuery('.column-filter-widgets').toggle('fast');">
			Show Filter
		<span class="caret"></span>
		</button>
		
	</div>
	<h4 class="widgettitle title-primary"><?php echo $language->lang_echo('ALL_TICKETS'); ?></h4>
</div>
<table cellpadding="0" cellspacing="0" border="0" class="table table-bordered" id="dyntableX">
	<colgroup>
                       

                        <col class="con0" />
                        <col class="con1" />
                        <col class="con0" />
                        <col class="con1" />
                        <col class="con0"/>
                        <col class="con1" />
                        <col class="con0" />
                        <col class="con1" />
                        <col class="con0" />                   
                        <col class="con1" />
    </colgroup>
	<thead>
		<tr>
	
			<th class="head0"><?php echo $language->lang_echo('ID'); ?></th>
			<th class="head1"><?php echo $language->lang_echo('TITLE'); ?></th>
			<th class="head0"><?php echo $language->lang_echo('CLIENT_PROJECT'); ?></th>
			<th class="head1"><?php echo $language->lang_echo('DATE_OF_TICKET'); ?></th>
			<th class="head0"><?php echo $language->lang_echo('DATE_TO_FINISH'); ?></th>
			<th class="head1"><?php echo $language->lang_echo('AUTHOR'); ?></th>
			<th class="head1"><?php echo $language->lang_echo('EDITOR'); ?></th>
			<th class="head0"><?php echo $language->lang_echo('TYPE'); ?></th>
			<th class="head1"><?php echo $language->lang_echo('STATUS'); ?></th>
			<th class="head0"><?php echo $language->lang_echo('PRIORITY'); ?></th>
		</tr>
		
	</thead>
	<tbody>
	<?php foreach($this->get('allTickets') as $row) {?>
		<tr class="gradeA">
			
			<td><?php echo $this->displayLink('tickets.editTicket',$row['id'],array('id'=>$row['id'])); ?></td>
			<td><?php echo $this->displayLink('tickets.showTicket',$row['headline'],array('id'=>$row['id'])); ?></td>
			<td><?php echo $row['clientName'].' / '.$row['projectName']; ?></td>
			
			<td><?php echo $helper->timestamp2date($row['date'], 2); ?></td>
			<td><?php echo $helper->timestamp2date($row['dateToFinish'], 2); ?></td>
			<td>
                <?php if(isset($row['authorLastname']) || isset($row['authorFirstname'])): ?>
                    <?php echo $row['authorFirstname'] ?> <?php echo $row['authorLastname'] ?>
                <?php endif; ?>
			</td>
            <td>
                <?php if(isset($row['editorLastname']) || isset($row['editorFirstname'])): ?>
                    <?php echo $row['editorFirstname'] ?> <?php echo $row['editorLastname'] ?>
                <?php endif; ?>
            </td>
			<td><?php echo $row['type']; ?></td>
			<td>
				
				<div style="width:150px;" id="status-wrapper-<?php echo $row['id'] ?>" onclick="jQuery('#status-<?php echo $row['id'] ?>').toggle(); jQuery('#status-spinner-<?php echo $row['id'] ?>').toggle(); jQuery('#status-select-<?php echo $row['id'] ?>').toggle();">
					<span style="margin-left: 10px; width:100px; cursor:pointer;" id="status-<?php echo $row['id'] ?>" class="f-left <?php echo strtolower($tickets->getStatus($row['status']));?>">
						<?php echo $language->lang_echo($tickets->getStatusPlain($row['status'])); ?>
					</span>
					<span class='f-left statusButtons' id="status-spinner-<?php echo $row['id'] ?>">
						<div class="ui-spinner-button ui-spinner-up" ></div>
						<div class="ui-spinner-button ui-spinner-down"></div>
						<div class="clear">&nbsp;</div>
					</span>
				</div>
				
				<select style="display:none; width:150px;" onchange="changeStatus(<?php echo $row['id'] ?>)" id="status-select-<?php echo $row['id'] ?>" data-placeholder="<?php echo $language->lang_echo($tickets->getStatusPlain($row['status'])); ?>">
						<?php foreach($tickets->statePlain as $key => $row2) {?>
							<option value="<?php echo $key; ?>"
							<?php if($row['status'] == $key) {echo"selected='selected'";}?>
							><?php echo $language->lang_echo($tickets->getStatusPlain($key)); ?></option>
						<?php } ?>
				</select>
				
				
				
				
				
				
				
				
				
				
			</td>
			<td class="center"><?php echo $row['priority']; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<br />
	   		</div><!--maincontentinner-->
        </div><!--maincontent-->
        
<script type="text/javascript">

	var state = [];
	<?php foreach($this->get('status') as $key => $value): ?>
	 state[<?php echo $key ?>] = '<?php echo $value ?>';
	<?php endforeach; ?>
	
	var stateLang = [];
	<?php foreach($this->get('status') as $key => $status): ?>
	 stateLang[<?php echo $key ?>] = '<?php echo $language->lang_echo($tickets->getStatusPlain($key)); ?>';
	<?php endforeach; ?>
	
	var changes = [];
	
	


	function nextStatus(id,status,direction) {
		
		var statusCSSId = '#status-'+id;
		
		jQuery(document).ready(function($){
			
			origId = status;
			if (typeof changes[id] == 'undefined') {
				changes[id] = status + direction;
			} else {
				origId = changes[id];
				changes[id] = changes[id] + direction;
			}
			
			if (changes[id] > state.length - 1) {
				changes[id] = 0;
			} else if (changes[id] < 0) {
				changes[id] = state.length - 1;
			}
	
			jQuery('#hiddenId').val(id);
			jQuery('#hiddenStatus').val(changes[id]);		
			
			jQuery('#statusForm').ajaxSubmit({ url: '/index.php?act=general.ajaxRequest&module=tickets.showAll&export=true', type: 'post' });

			jQuery(statusCSSId).removeClass(state[origId]);
			jQuery(statusCSSId).addClass(state[changes[id]]);
			jQuery(statusCSSId).html(stateLang[changes[id]]);

			//jQuery(statusCSSId).toggle();
			jQuery('#hiddenId').val('');
			jQuery('#hiddenStatus').val('');	
		});		
	}
</script>
        
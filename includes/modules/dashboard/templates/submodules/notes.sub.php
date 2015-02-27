<script type="text/javascript">
    jQuery(document).ready(function(){
        // dynamic table
        jQuery('#dyntable4').dataTable( {
            "bScrollInfinite": true,
            "bScrollCollapse": true,
            "sScrollY": "200px"
        });
       
    });
</script>

<a id='addNote' class='btn btn-primary btn-rounded'>
	<?php echo $language->lang_echo('ADD_NOTE') ?>
</a>

<div id='noteForm' style='display: none;'>
	<form action='' method='POST'>
		
		<input type='text' name='title' placeholder='Title' />
		<textarea name='description' placeholder='Description'></textarea>
		
		<input type='submit' value='<?php echo $language->lang_echo('SAVE') ?>' name='save' />
	
	</form>
</div>

<div id='notes'>
	<?php foreach($this->get('notes') as $note): ?>
	  <div class='note'>
		<h4><?php echo $note['title'] ?></h4>
		<p><?php echo $note['description'] ?></p>
	  </div>
	<?php endforeach; ?>
</div>

<script type='text/javascript'>
	jQuery(document).ready(function(){
	  jQuery('#addNote').click(function($) {
       	jQuery('#noteForm').toggle();
    	jQuery('#notes').toggle();
    	/*jQuery('#addNote').toggle(bool);
    	if (bool) {
    		jQuery(this).innerHTML = '';
    	} else {
    		jQuery(this).innerHTML = '';
    	}*/
      }); 	
 	});
</script>

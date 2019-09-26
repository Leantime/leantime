<?php 

$user = new leantime\domain\repositories\users();
$message = new leantime\domain\repositories\messages();
$first = false; 
$displayId = $this->get('displayId');
$helper = new leantime\core\helper();
?>

<style type='text/css'>
	.hide, .message { display: none; }
	.maincontent .show { display: block; } 
	.ui-tabs-panel { padding: 0; }
	.messagemenu { margin-top: 0; }
	.msgauthor { cursor: pointer; }
</style>
		

<script type='text/javascript'>
	function changeEmail(id, type) {
		var container = '#' + type;
		var previewId = container + ' #preview-' + id;
		var msgId = container + ' #msg-' + id;
		
		jQuery(document).ready(function($){
			$(container + ' li').removeClass('selected');
			$(previewId).addClass('selected');
			$(container + ' .message').removeClass('show');
			$(msgId).addClass('show');
		});
	}
	function toggleMsgBody(id,type) {

		var msgBody = '#msgbody-'+id;
		var parent = '#' + type;
		var selector = parent + ' ' + msgBody;
		
		jQuery(document).ready(function($){
		
			var classes = $(selector).attr('class');
		
			if (classes.indexOf('hide') !== -1) {
				$(selector).removeClass('hide');
			} else {
				$(selector).addClass('hide');
			}
		});
	}
	
	jQuery(document).ready(function() {
		jQuery('.tabbedwidget').tabs();
		
		jQuery("#searchBox").keypress(function(){
			jQuery(".msglist li").each(function(){
				
				if(jQuery(this).find(".sum").html().match(jQuery("#searchBox").val()) == false){
					jQuery(this).hide();
				}else{
					jQuery(this).show();
				}
			});
		});
	});
	

</script>


<div class="pageheader">
           
            
            <div class="pageicon">
            	<span class="<?php echo $this->getModulePicture() ?>"></span>
            </div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
                <h1><?php echo $language->lang_echo('MESSAGES'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">
            	
            	<?php echo $this->displayNotification() ?>
  
<div class="tabbedwidget tab-primary">
  <div class="messagemenu">
  	<!--<div id="tabs">-->

    <ul>
      <li class="back"><a>Back</a></li>
      <li class="active"><a href="#inbox"><?php echo $language->lang_echo('MESSAGES') ?></a></li>
      <li><a href="#compose"><?php echo $language->lang_echo('COMPOSE') ?></a></li>
    </ul>
  </div>
  
  <div id="inbox">
  <div class="messagecontent">
    <div class="messageleft" id="example-list">
      <form class="messagesearch">
        <input type="text" id="searchBox" class=" search input-block-level" placeholder=
        "Search" />
      </form>
	
      <ul class="msglist list">
      	<?php foreach ($this->get('messages') as $msgList): 
      		
      		if($msgList["parent_id"] == ""){ ?>
      		<li id='preview-<?php echo $msgList['id'] ?>'
      			<?php if ($msgList['id'] == $displayId || (!in_array($displayId,$this->get('messages')) && $first !== true)) { ?>
      			 	<?php $first = true; ?>
      			 	class='selected'
			 	<?php } ?>
			 	onclick='changeEmail(<?php echo $msgList['id'] ?>, "inbox")'>
			 	
      			<div class='thumb'>
      				<img src='<?php echo $user->getProfilePicture($msgList['user_id']) ?>' />
      			</div>
      			<div class='summary'>
      				<span class='date pull-right'>
      					<small>
      						<?php echo date('M d, Y',strtotime($msgList['date_sent'])) ?>
      					</small>
      				</span>
      				<h4 class="name"><?php echo $msgList['firstname'].' '.$msgList['lastname'] ?></h4>
      				<p class="sum">
      					<!-- <?php echo $msgList['content'] ?><?php echo $msgList['subject'] ?>-->
      					<strong><?php echo substr(strip_tags($msgList['subject']),0,30) ?>...</strong> -<br/> <?php echo substr(strip_tags($msgList['content']),0,55) ?>...
      				</p>
      			</div>
      		</li>
      	<?php }
      	endforeach; ?>
      </ul>
    </div>
        
        <?php $first = false; ?>
		<?php foreach ($this->get('messages') as $msgList): ?>
	        <div id='msg-<?php echo $msgList['id'] ?>'
      			<?php if ($msgList['id'] == $displayId || (!in_array($displayId,$this->get('messages')) && $first !== true)) { ?>
      			 	 <?php $first = true; ?>
      			 	 class='message show'
			 	<?php } else { ?>
			 		class='message'
			 	<?php } ?>>
	        	
	        	<div class="messageright">
      			<div class="messageview">
	        	
		        <h1 class='subject'><?php echo $msgList['subject'] ?></h1>

		    	<?php if (count($message->getMessageChain($msgList['id'], $msgList['parent_id'])) > 1): 
		    		foreach ($message->getMessageChain($msgList['id'],$msgList['parent_id']) as $reply) { ?>

				    	<div class='msgauthor' onclick='toggleMsgBody(<?php echo $reply['id'] ?>,"inbox")'>
					    	<div class="thumb"><img src='<?php echo $user->getProfilePicture($reply['fromUserId']) ?>' /></div>
							<div class="authorinfo">
					            <span class="date pull-right">   
					            	<?php echo $helper->timestamp2date($reply['date_sent'], 3) ?><br /><br />          	
						            <span class='caret f-right'></span> 
					            </span> 
								<h5>
					            	<strong><?php echo $reply['fromFirstname'].' '.$reply['fromLastname'] ?></strong>
					            	<span><?php echo $reply['fromUsername'] ?></span>
					            </h5>				        
					            <span class="to">to <?php echo $reply['toUsername'] ?></span> 
					         </div><!--authorinfo-->	      		
				        </div><!--msgauthor-->
				        <div class='msgbody' 
				        	id='msgbody-<?php echo $reply['id'] ?>'>
				        	
				        	<p><?php echo $reply['content'] ?></p>
				        </div>
		    		<?php } 
				else: ?>  
		        	<div class='msgauthor' onclick='toggleMsgBody(<?php echo $msgList['id'] ?>,"inbox")'>
			          <div class="thumb"><img src='<?php echo $user->getProfilePicture($msgList['user_id']) ?>' /></div>
					  <div class="authorinfo">
			            <span class="date pull-right">
			            	<?php echo $helper->timestamp2date($msgList['date_sent'], 3) ?><br /><br />
				            <span class='caret'></span>		
			            </span>
						<h5>
			            	<strong><?php echo $msgList['firstname'].' '.$msgList['lastname'] ?></strong>
			            	<span><?php echo $msgList['username'] ?></span>
			            </h5>
			            <span class="to">to <?php echo $this->get('userEmail') ?></span>
			          </div><!--authorinfo-->	        
		        	</div><!--msgauthor-->
		        	<div class='msgbody' id='msgbody-<?php echo $msgList['id'] ?>'>
		        		<p><?php echo $msgList['content'] ?></p>
		        	</div>
		    	<?php endif; ?>
	        </div><!-- messageview -->
		      <div class="msgreply">
		        <div class="thumb"><img src="<?php echo $user->getProfilePicture($_SESSION["userdata"]["id"]) ?>" alt="" /></div>
		
		        <div class="reply">
		        	<form action='' method='POST'>
		        		
			         	<textarea placeholder="Type something here to reply" name='message'></textarea>

			         	<input type='hidden' name='to_id' value='<?php echo $msgList['user_id'] ?>' />

			         	<input type='hidden' name='parent_id' value='<?php echo $msgList['id'] ?>' />

						
				        <input type='submit' value='<?php echo strip_tags($language->lang_echo('REPLY')) ?>' name='reply' />
						
					</form>
		        </div><!--reply-->
		      </div><!--messagereply-->
	        </div><!-- message right -->
	       </div>
        <?php endforeach; ?>
 </div>
 </div>
 <div id="compose"> 
 
 
		<div class='message-compose'>
			
			<form action='' method='POST' class="stdform">
				
				<div class='par'>
					<div class="btn-group" style="float: left; margin-left: 40px; ">
						<button id='widgetAction' type='button' class='btn btn-primary'  style='position:relative; z-index:999;'>
							<?php echo $language->lang_echo('ADD_USER') ?>
							<span class="caret"></span>
						</button>
						<ul id='widgetList' class='dropdown-menu widgetList' style='display: none; right: auto; left: 0;'>
							<?php foreach ($this->get('friends') as $friend): ?>
								<li style='margin-left: 5px; cursor: pointer;' onclick='addUser(<?php echo $friend['id'] ?>, "<?php echo $friend['username'] ?>")'><?php echo $friend['firstname'].' '.$friend['lastname'] ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
					<span class='field'>
						<!--
						<select name='username'>
							<option value='-1' selected="selected">Send to</option>
							<?php foreach ($this->get('friends') as $friend): ?>
								<option value='<?php echo $friend['id'] ?>'>
									<?php echo $friend['firstname'].' '.$friend['lastname'] ?>
								</option>
							<?php endforeach; ?>	
						</select>-->
						<input type='hidden' name='username' id='username' value='' /> 
						<input type='text' name='' id='usernameDisplay' value='' class='input-xxlarge' style='position: relative;top: 4px;right: 15px;height: 20px;' />
					</span>
				</div>
				
				<p>
					<span class="field">
						<input type='text' placeholder='Subject' name='subject' /><br/>
					</span>
				</p>

				<p>	
					<span class='field'>			
						<textarea id='elm1' class='tinymce' rows='15' cols='100' style='width: 80%' name='content' placeholder='Type your message here'></textarea><br/>
					</span>
				</p>
				
				<p>
					<span class='field'>
						<input type='submit' class="button" value='<?php echo $language->lang_echo('SEND') ?>' name='send' />					
						<input type="reset" class="btn" value="<?php echo $language->lang_echo('RESET_BUTTON') ?>" />
					</span>
				</p>
				
			</form>
		</div>


</div>
			</div>
		</div>

 		<script type='text/javascript'>
 		
			jQuery(document).ready(function() {
				jQuery('#widgetAction').click(function(){
					jQuery('#widgetList').toggle();
				});
			});
 			
 			function addUser(id,un) {
 				jQuery(document).ready(function(){
 					id = id + ', ';
 					un = un + ', ';
 					jQuery('#usernameDisplay').val(jQuery('#usernameDisplay').val() + un);
 					jQuery('#username').val(jQuery('#username').val() + id);
 				});
 			}
 
	 		var options = {
			    valueNames: [ 'name', 'sum' ]
			};
			
			var featureList = new List('example-list', options);
 		
 		</script>
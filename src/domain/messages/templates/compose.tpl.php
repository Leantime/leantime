<?php if($this->get('msg') != '') { ?>
<div class="fail"> 
    <span class="info"><?php echo $this->__($this->get('info')) ?></span>
</div>
<?php } ?>


<div class="pageheader">
      <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
        <input type="text" name="term" placeholder="To search type and hit enter..." />
    </form>
            
    <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
     <div class="pagetitle">
    
        <h5><?php echo $this->__('OVERVIEW'); ?></h5>
    
        <h1><?php echo $this->__('COMPOSE'); ?></h1>
    
    </div>
</div><!--pageheader-->
        
<div class="maincontent">
      <div class="maincontentinner">

        <div class='message-compose'>
            <form action='' method='POST' class="stdform">
                
                <select name='username'>
                    <option value='-1' selected="selected">Send to</option>
        <?php foreach ($this->get('friends') as $friend): ?>
                        <option value='<?php echo $friend['id'] ?>'>
            <?php echo $friend['firstname'].' '.$friend['lastname'] ?>
                        </option>
        <?php endforeach; ?>    
                </select><br/>
                
                <!--<input type='text' placeholder='Username' name='user' /><br/>-->
                
                <input type='text' placeholder='Subject' name='subject' /><br/>
                
                <textarea id='elm1' class='tinymce' rows='15' cols='150' name='content' placeholder='Type your message here'></textarea><br/>
                
                <p class="stdformbutton">
                    <input type='submit' value='Send' name='send' />
                    <input type="reset" class="btn" value="<?php echo $this->__('RESET_BUTTON') ?>" />
                </p>
                
            </form>
        </div>

    </div>
</div>

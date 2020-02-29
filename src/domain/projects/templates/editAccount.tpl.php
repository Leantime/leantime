<?php $account = $this->get('account') ?>

<div class="pageheader">
            <form action="<?=BASE_URL ?>/index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
                <h1><?php echo $language->lang_echo('PROJECT') ?> #<?php echo $project['id'] ?> | <?php echo $project['name']; ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

                <?php echo $this->displayNotification() ?>    
    
                <form action="#accounts" method="post" class="stdform">
                    
                    <p>
                    <label for="accountName"><?php echo $language->lang_echo('LABEL'); ?></label> 
                    <span class='field'>
                    <input type="text" name="accountName" id="accountName" value="" /><br />
                    </span></p>
                    
                    <p>
                    <label for="kind"><?php echo $language->lang_echo('ACCOUNT_KIND'); ?></label> 
                    <span class='field'>
                    <input type="text" name="kind" id="kind" value="" /><br />
                    </span></p>
                    
                    <p>
                    <label for="username"><?php echo $language->lang_echo('USERNAME'); ?></label> 
                    <span class='field'>
                    <input type="text" name="username" id="username" value="" /><br />
                    </span></p>
                    
                    <p>
                    <label for="password"><?php echo $language->lang_echo('PASSWORD'); ?></label> 
                    <span class='field'>
                    <input type="text" name="password" value="" /><br />
                    </span></p>
                    
                    <p>
                    <label for="host"><?php echo $language->lang_echo('HOST'); ?></label> 
                    <span class='field'>
                    <input type="text" id="host" name="host" value="" /><br />
                    </span></p>
                    
                    <p class='stdformbutton'>
                        <input type="submit" name="accountSubmit" class="button" value="<?php echo $language->lang_echo('SUBMIT'); ?>" />
                    </p>
                
                </form>    
    
            </div>
        </div>
<script type="text/javascript">
    jQuery(document).ready(function() {

        <?php if(isset($_SESSION['userdata']['settings']["modals"]["showClients"]) === false || $_SESSION['userdata']['settings']["modals"]["showClients"] == 0) {     ?>
            leantime.helperController.showHelperModal("showClients");
            <?php
            //Only show once per session
            $_SESSION['userdata']['settings']["modals"]["showClients"] = 1;
        } ?>

        }
    );

</script>

<div class="pageheader">
            
            
            <div class="pageicon"><span class="fa fa-address-book"></span></div>
            <div class="pagetitle">
                <h5>Administration</h5>
                <h1>All Clients/Products</h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

            <form action="">
            
            
                <?php echo $this->displayLink('clients.newClient', "<i class='iconfa-plus'></i> ".$language->lang_echo('ADD_NEW_CLIENT'), null, array('class' => 'btn btn-primary btn-rounded')); ?>
                <h4 class="widgettitle">Client List</h4>
            <table class='table table-bordered' cellspacing="0" border="0" class="display" id="dyntable2">
                <colgroup>

                    <col class='con0' />
                    <col class='con1' />
                    <col class='con0' />
                </colgroup>
                <thead>
                    <tr>
                        <th class='head1'><?php echo $language->lang_echo('CLIENTNAME'); ?></th>
                        <th class='head0'><?php echo $language->lang_echo('EMAIL'); ?></th>
                        <th class='head1'><?php echo $language->lang_echo('NUMBER_PROJECTS'); ?></th>
                    </tr>
                </thead>
                <tbody>
            
                <?php foreach($this->get('allClients') as $row) { ?>
                    <tr>

                        <td>
                    <?php echo $this->displayLink('clients.showClient', $this->escape($row['name']), array('id' => $this->escape($row['id']))) ?>
                        </td>
                        <td><a href="http://<?php $this->e($row['internet']); ?>" target="_blank"><?php $this->e($row['internet']); ?></a></td>
                        <td><?php echo $row['numberOfProjects']; ?></td>
                    </tr>
                <?php } ?>
            
                </tbody>
            </table>
                
            </form>
    
        </div>
    </div>
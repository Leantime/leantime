<?php 
defined('RESTRICTED') or die('Restricted access');
$roles = $this->get('roles');
?>

<div class="pageheader">
           
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5>Administration</h5>
                <h1><h1><?php echo $language->lang_echo('ALL_USER'); ?></h1></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

                <?php echo $this->displayNotification() ?>

                <div class="row">
                    <div class="col-md-6">
                        <?php echo $this->displayLink('users.newUser', "<i class='iconfa-plus'></i> ".$language->lang_echo('ADD_USER'), null, array('class' => 'btn btn-primary btn-rounded')) ?>

                    </div>
                    <div class="col-md-6 align-right">

                    </div>
                </div>
                <h4 class="widgettitle">User List</h4>
                <table cellpadding="0" cellspacing="0" border="0" class='table table-bordered' id='resultTable'>
                    <colgroup>
                        <col class="con1">
                        <col class="con0">
                        <col class="con1">
                        <col class="con0">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class='head1'><?php echo $language->lang_echo('NAME'); ?></th>
                            <th class='head0'>Email</th>
                            <th class='head1'><?php echo $language->lang_echo('ROLE'); ?></th>
                            <th class='head0'>Action</th>
                        </tr>
                    </thead>
                    <tbody>
        <?php foreach($this->get('allUsers') as $row): ?>
                            <tr>
                                <td><?php echo $this->displayLink('users.editUser', $row['firstname'].' '.$row['lastname'], array('id' => $row['id'])) ?></td>
                                <td><?php echo $row['username']; ?></td>
                                <td><?php echo $row['roleName']; ?></td>
                                <td><a href="<?=BASE_URL ?>/users/delUser/<?php echo $row['id']?>" class="delete"><i class="fa fa-trash"></i> Delete User</a></td>
                            </tr>
        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>


<?php
    defined('RESTRICTED') or die('Restricted access');
?>

<?php $this->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $this->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon"><span class="fa fa-address-book"></span></div>
    <div class="pagetitle">
        <h5><?php echo $this->__('label.administration') ?></h5>
        <h1><?php echo $this->__('headline.all_clients') ?></h1>
    </div>
    <?php $this->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $this->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification() ?>

        <?php
        if ($login::userIsAtLeast('manager')) {
            echo $this->displayLink('clients.newClient', "<i class='fa fa-plus'></i> " . $this->__('link.new_client'), null, array('class' => 'btn btn-primary btn-rounded')); ?>
        <?php } ?>

        <table class="table table-bordered" cellpadding="0" cellspacing="0" border="0" id="allClientsTable">
            <colgroup>
                <col class='con0' />
                <col class='con1' />
                <col class='con0' />
            </colgroup>
            <thead>
                <tr>
                    <th class='head0'><?php echo $this->__('label.client_id'); ?></th>
                    <th class='head1'><?php echo $this->__('label.client_name'); ?></th>
                    <th class='head0'><?php echo $this->__('label.url') ?></th>
                    <th class='head1'><?php echo $this->__('label.number_of_projects'); ?></th>
                </tr>
            </thead>
            <tbody>

            <?php foreach ($this->get('allClients') as $row) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td>
                <?php echo $this->displayLink('clients.showClient', $this->escape($row['name']), array('id' => $this->escape($row['id']))) ?>
                    </td>
                    <td><a href="<?php $this->e($row['internet']); ?>" target="_blank"><?php $this->e($row['internet']); ?></a></td>
                    <td><?php echo $row['numberOfProjects']; ?></td>
                </tr>
            <?php } ?>

            </tbody>
        </table>

    </div>
</div>

<script type="text/javascript">

    <?php $this->dispatchTplEvent('scripts.afterOpen'); ?>

    jQuery(document).ready(function() {

        leantime.clientsController.initClientTable();


    });

    <?php $this->dispatchTplEvent('scripts.beforeClose'); ?>

</script>

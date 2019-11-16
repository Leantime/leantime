<?php $ticket = $this->get('ticket'); ?>

    <p>
        <strong><?php echo $this->__('OPERATING_SYSTEM'); ?>:</strong> <?php echo  $this->__($ticket['os']); ?><br />
        <strong><?php echo $this->__('BROWSER'); ?>:</strong> <?php echo  $this->__($ticket['browser']); ?><br />
        <strong><?php echo $this->__('RESOLUTION'); ?>:</strong> <?php echo  $this->__($ticket['resolution']); ?><br />
        <strong><?php echo $this->__('VERSION'); ?>:</strong> <?php echo  $ticket['version']; ?><br />
        <strong><?php echo $this->__('URL'); ?>:</strong> <?php echo $ticket['url']; ?><br />
    </p>
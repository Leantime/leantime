<?php $ticket = $this->get('ticket'); ?>

    <p>
        <strong><?php echo $language->lang_echo('OPERATING_SYSTEM'); ?>:</strong> <?php echo  $language->lang_echo($ticket['os']); ?><br />
        <strong><?php echo $language->lang_echo('BROWSER'); ?>:</strong> <?php echo  $language->lang_echo($ticket['browser']); ?><br />
        <strong><?php echo $language->lang_echo('RESOLUTION'); ?>:</strong> <?php echo  $language->lang_echo($ticket['resolution']); ?><br />
        <strong><?php echo $language->lang_echo('VERSION'); ?>:</strong> <?php echo  $ticket['version']; ?><br />
        <strong><?php echo $language->lang_echo('URL'); ?>:</strong> <?php echo $ticket['url']; ?><br />
    </p>
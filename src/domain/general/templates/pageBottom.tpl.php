<?php defined('RESTRICTED') or die('Restricted access'); ?>

<?php
$debugRenderer = $this->get('debugRenderer');
$appSettings = $this->get('appSettings');
?>


<?php
if($appSettings->debug == 1) {
    echo $debugRenderer->render();
}
?>

</body>
</html>
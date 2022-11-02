<?php 
    use \leantime\core\events;
    defined('RESTRICTED') or die('Restricted access'); 
?>

<?php if ( isset($_SESSION['do_cron'] )) { ?>
    <script>
        var req = new XMLHttpRequest();
        req.open("GET", "<?=BASE_URL?>/cron.php",true);
        req.send(null);
    </script>
<?php } ?>
<?php $this->dispatchTplEvent('beforeBodyClose'); ?>
</body>
</html>
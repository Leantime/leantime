<?php defined('RESTRICTED') or die('Restricted access'); ?>

<?php if (isset($_SESSION['do_cron'])) { ?>
    <script>
        var req = new XMLHttpRequest();
        req.open("GET", "<?=BASE_URL?>/cron/run",true);
        req.send(null);
    </script>
<?php } ?>

<?php $this->dispatchTplEvent('beforeBodyClose'); ?>

<script src="<?=BASE_URL?>/js/compiled-footer.min.js"> </script>
</body>
</html>

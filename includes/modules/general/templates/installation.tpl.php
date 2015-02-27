<?php $main->includeAction('general.header'); ?>

<div id="head">
<div class="versionContainer">Version <?php echo VERSION; ?></div>

<div class="sitename"></div>

<div id="menue"></div>
</div>

<div id="content">
<noscript>
<div class="info"><span style="color: #FF0000;">Ihr Javascript ist
ausgeschaltet. Ohne aktiviertes Javascript ist zypro nur
eingeschr&auml;nkt nutzbar</span></div>

</noscript>

<?php $main->run('installation.chooselanguage'); ?></div>

<div id="footer"><?php $main->includeAction('general.footer'); ?></div>

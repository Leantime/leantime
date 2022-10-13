<?php
/**
 * Business Model Canvas - Delete Canvas
 */
$canvasName = 'bm';
$canvasTemplate = $_SESSION[$canvasName.'template'] ?? 'l';
require(ROOT.'/../src/library/canvas/tpl.delCanvas.inc.php');
?>

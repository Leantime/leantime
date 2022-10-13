<?php
/**
 * Business Model Canvas - Delete Item
 */
$canvasName = 'bm';
$canvasTemplate = $_SESSION[$canvasName.'template'] ?? 'l';
require(ROOT.'/../src/library/canvas/tpl.delCanvasItem.inc.php');
?>

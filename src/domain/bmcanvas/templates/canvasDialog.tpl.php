<?php
/**
 * Business Model Canvas - Dialog
 */
$canvasItem = $this->get('canvasItem');
$statusDropdown =
	'<option value="info" '.($canvasItem['status'] == 'info' ? "selected='selected' " : '').'>'.$this->__("print.not_validated").'</option>'."\n".
    '<option value="success" '.($canvasItem['status'] == 'success' ? "selected='selected' " : '').'>'.$this->__("print.validated_true").'</option>'."\n".
    '<option value="danger" '.($canvasItem['status'] == 'danger' ? "selected='selected' " : '').'>'.$this->__("print.validated_false").'</option>'."\n";

$canvasName = 'bm';
$canvasTemplate = $_SESSION[$canvasName.'template'] ?? 'l';
$options = [ 'statusDropdown' => $statusDropdown ];
require(ROOT.'/../src/library/canvas/tpl.canvasDialog.inc.php');
?>

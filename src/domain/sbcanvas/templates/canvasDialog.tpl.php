<?php
/**
 * Strategy Brief - Dialog
 */
$canvasItem = $this->get('canvasItem');
$statusDropdown =
	'<option value="info" '.($canvasItem['status'] == 'info' ? "selected='selected' " : '').'>'.$this->__("print.draft").'</option>'."\n".
    '<option value="warning" '.($canvasItem['status'] == 'warning' ? "selected='selected' " : '').'>'.$this->__("print.review").'</option>'."\n".
    '<option value="success" '.($canvasItem['status'] == 'success' ? "selected='selected' " : '').'>'.$this->__("print.valid").'</option>'."\n".
    '<option value="danger" '.($canvasItem['status'] == 'danger' ? "selected='selected' " : '').'>'.$this->__("print.invalid").'</option>'."\n";

$canvasName = 'sb';
$canvasTemplate = '';
$options = [ 'statusDropdown' => $statusDropdown, 'firstTitle' => false, 'secondTitle' => false, 
			 'thirdTitle' => 'label.description', 'thirdPlaceholder' => 'input.placeholders.describe_element' ];
require(ROOT.'/../src/library/canvas/tpl.canvasDialog.inc.php');
?>

<?php

/**
 * Template
 */

defined('RESTRICTED') or die('Restricted access');

$canvasName = 'lean';
?>

<?php require($this->getTemplatePath('canvas', 'showCanvasTop.inc.php')); ?>

    <?php if (count($this->get('allCanvas')) > 0) { ?>
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(5 * 250px);">

                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 20%">
                            <?php $elementName = 'problem';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 20%">
                            <?php $elementName = 'solution';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 20%">
                            <?php $elementName = 'uniquevalue';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 20%">
                            <?php $elementName = 'unfairadvantage';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 20%">
                            <?php $elementName = 'customersegment';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                    </div>

                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 20%">
                            <?php $elementName = 'alternatives';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 20%">
                            <?php $elementName = 'keymetrics';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 20%">
                            <?php $elementName = 'highlevelconcept';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 20%">
                            <?php $elementName = 'channels';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 20%">
                            <?php $elementName = 'earlyadopters';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                    </div>

                    <div class="row canvas-row" id="thirdRow">
                        <div class="column" style="width: 50%">
                            <?php $elementName = 'cost';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 50%">
                            <?php $elementName = 'revenue';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require($this->getTemplatePath('canvas', 'showCanvasBottom.inc.php')); ?>

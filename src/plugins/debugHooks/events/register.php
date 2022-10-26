<?php

use \leantime\core\events;

if (!defined('PLUGIN_DEBUGHOOKS_ENABLED')
    || PLUGIN_DEBUGHOOKS_ENABLED == false
) {
    return false;
}

events::add_event_listener(
    'tpl.general.pageBottom.beforeBodyClose', 
    function ($eventname, $params) {
        ob_start();
        ?>
            <style>
                .modal-background {
                    position: fixed;
                    left: 0;
                    top: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.75);
                    display: none;
                }

                .modal-body {
                    position: fixed;
                    width: 640px;
                    height: 480px;
                    left: 50%;
                    top: 50%;
                    margin-left: -320px;
                    margin-top: -240px;
                    background: #fff;
                    display: none;
                }
            </style>
            <div class="modal-background">
                <div class="modal-body">
                    <button 
                    style="color: #FFF"
                    onclick="(e=>{jQuery('.modal-background').fadeOut().find('.modal-body').slideDown()})(event);">
                        Close Modal
                    </button>
                    <hr>
                    <pre>
<?php
    var_dump(
        [
            'hooks' => events::get_registries(),
            'core.template.display.content.general.pageBottom' => $params
        ]
    );
?>
                    </pre>
                </div>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('.modal-background').fadeIn().find('.modal-body').slideDown();
                });
            </script>
        <?php
        echo ob_get_clean();
    }
);
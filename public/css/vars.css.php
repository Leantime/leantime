<?php 
$color = $_GET['color']; 
$color = "#".$color; 
header("Content-type: text/css", true);

//TODO: Replace wtih custom colors

?>


:root {

    --primary-color: #0492c2;
    --secondary-color: #0492c2;

    --main-bg: #fff;
    --secondary-bg: #f6f7fa;
    --left-panel-bg:#f6f7fa;


    --main-action-bg: #0492c2;
    --main-action-color: #fff;
    --main-action-hover-bg: #D3D6DA;
    --main-action-hover-color: #555;

    --secondary-action-bg: #f6f7fa;
    --secondary-action-color: #555;
    --secondary-action-hover-bg: #ddd;
    --secondary-action-hover-color: #555;

    --main-menu-link-bg: #f6f7fa;
    --main-menu-link-color: #555;

    --main-menu-link-hover-bg: #e9ebed;
    --main-menu-link-hover-color: #555;

    --main-menu-link-active-bg: #D3D6DA;
    --main-menu-link-active-color: #555;

    --main-menu-border-color:#eee;

    --project-selector-bg:#f6f7fa;
    --project-selector-color:#555;
    --project-selector-hover-bg:#e9ebed;
    --project-selector-hover-color:#555;



    --dropdown-link-bg: #fff;
    --dropdown-link-color: #555;
    --dropdown-link-hover-bg: #e9ebed;
    --dropdown-link-hover-color: #555;


    --primary-font-family: 'RobotoRegular', 'Helvetica Neue', Helvetica, sans-serif;
    --base-font-size: 14px;
    --font-size-xs: 10px;
    --font-size-s:12px;
    --font-size-l: 16px;
    --font-size-xl: 18px;
    --font-size-xxl: 22px;


    --main-border-color:#ccc;
    --regular-shadow: 0px 2px 4px rgb(0 0 0 / 20%);
    --box-radius: 5px;
    --element-radius: 5px;

    --kanban-col-bg: #f6f7fa;
    --kanban-col-title-bg: #e9ebed;
    --kanban-col-title-color:#555;

}


<?php /*


:root {

    --primary-color: #0492c2;
    --secondary-color: #0492c2;

    --main-bg: #faf9f6;
    --secondary-bg: #f6f7fa;
    --left-panel-bg:#f6f7fa;


    --main-action-bg: #f98900;
    --main-action-color: #fff;
    --main-action-hover-bg: #e9ebed;
    --main-action-hover-color: #555;

    --secondary-action-bg: #f6f7fa;
    --secondary-action-color: #555;
    --secondary-action-hover-bg: #e9ebed;
    --secondary-action-hover-color: #555;

    --main-menu-link-bg: #f6f7fa;
    --main-menu-link-color: #555;

    --main-menu-link-hover-bg: #e9ebed;
    --main-menu-link-hover-color: #555;

    --main-menu-link-active-bg: #D3D6DA;
    --main-menu-link-active-color: #555;

    --dropdown-link-bg: #fff;
    --dropdown-link-color: #555;
    --dropdown-link-hover-bg: #e9ebed;
    --dropdown-link-hover-color: #555;


    --primary-font-family: 'RobotoRegular', 'Helvetica Neue', Helvetica, sans-serif;
    --base-font-size: 14px;
    --font-size-xs: 10px;
    --font-size-s:12px;
    --font-size-l: 16px;
    --font-size-xl: 18px;
    --font-size-xxl: 22px;


    --main-border-color:#ccc;
    --regular-shadow: 0px 2px 4px rgb(0 0 0 / 20%);
    --box-radius: 10px;
    --element-radius: 5px;

    --kanban-col-bg: #fff;

}

*/ ?>
<?php 
$color = $_GET['color']; 
$color = "#".$color; 
header("Content-type: text/css", true);

//TODO: Replace wtih custom colors

?>


:root {

    --primary-color: #1b75bb;
    --secondary-color: #1b75bb;

    --main-bg: #ffffff;
    --secondary-bg: #f3f4f5;

    --main-action-bg: #1b75bb;
    --secondary-action-bg: #f3f4f5;

    --main-action-hover-bg: #555;
    --secondary-action-hover-bg: #e6e6e6;

    --main-action-color: #fff;
    --secondary-action-color: #555;

    --main-action-hover-color: #fff;
    --secondary-action-hover-color: #555;

    --main-menu-link-bg: #f3f4f5;
    --main-menu-link-hover-bg: #e6e6e6;

    --main-border-color:#ccc;

    --primary-font-family: 'RobotoRegular', 'Helvetica Neue', Helvetica, sans-serif;

    --base-font-size: 14px;
    --font-size-xs: 10px;
    --font-size-s:12px;
    --font-size-l: 16px;
    --font-size-xl: 18px;
    --font-size-xxl: 22px;

    --regular-shadow: 0px 2px 4px rgb(0 0 0 / 20%);

    --box-radius: 5px;
    --element-radius: 3px;

}

/* */
<?php 
$color = $_GET['color']; 
$color = "#".$color; 
header("Content-type: text/css", true);

//TODO: Replace wtih custom colors

?>


:root {


    --accent1: #1b75bb;
    --accent1-hover: #145A8F;
    --accent1-color:#fff;

    --accent2:#81B1A8;
    --accent2-hover: #81B1A8;
    --accent2-color:#fff;

    --primary-background: #F4F4F6;
    --secondary-background: #fff;

    --primary-font-color: #555;
    --secondary-font-color: #555;

    --neutral: #dddde3;

    --col-title-bg: #dddde3;
    --col-content-bg: #F4F4F6;
    --col-hover-bg: #c7c7d1;


    /*Leantime Dark
       --accent1: #187CC9;
       --accent1-hover: #187CC9;
       --accent1-color:#f4f4f6;

       --accent2:#230C10;
       --accent2-hover: #230C10;
       --accent2-color:#f4f4f6;

       --primary-background: #2e2e38;
       --secondary-background: #1d1d23;

       --primary-font-color: #f4f4f6;
       --secondary-font-color: #f4f4f6;
       --neutral: #131316;

   */



    --primary-color: var(--accent1);
    --secondary-color: var(--accent2);

    --main-bg: var(--primary-background);
    --left-panel-bg: var(--secondary-background);
    --content-bg: var(--secondary-background);


    --main-action-bg: var(--accent1);
    --main-action-color: var(--accent1-color);
    --main-action-hover-bg: var(--accent1-hover);
    --main-action-hover-color: var(--accent1-color);

    --secondary-action-bg: var(--accent2);
    --secondary-action-color: var(--accent2-color);
    --secondary-action-hover-bg: var(--accent2-hover);
    --secondary-action-hover-color: var(--accent2-color);

    --main-menu-link-bg: var(--secondary-background);
    --main-menu-link-color: var(--primary-font-color);

    --main-menu-link-hover-bg: var(--primary-background);
    --main-menu-link-hover-color: var(--accent1);

    --main-menu-link-active-bg: var(--primary-background);
    --main-menu-link-active-color: var(--accent1);

    --main-menu-border-color:var(--neutral);

    --project-selector-bg: var(--secondary-background);
    --project-selector-color: var(--primary-font-color);
    --project-selector-hover-bg: var(--primary-background);
    --project-selector-hover-color: var(--primary-font-color);

    --dropdown-link-bg:var(--secondary-background);
    --dropdown-link-color: var(--primary-font-color);
    --dropdown-link-hover-bg: var(--primary-background);
    --dropdown-link-hover-color: var(--primary-font-color);

    --header-action-hover-color:var(--neutral);
    --header-bg-color:var(--accent1);
    --header-gradient:linear-gradient(90deg, var(--accent1) 20%,var(--accent2) 100%);

    --primary-font-family: 'RobotoRegular', 'Helvetica Neue', Helvetica, sans-serif;
    --base-font-size: 14px;
    --font-size-xs: 10px;
    --font-size-s:12px;
    --font-size-l: 16px;
    --font-size-xl: 18px;
    --font-size-xxl: 22px;


    --main-border-color:var(--neutral);
    --regular-shadow: 0px 2px 4px rgb(0 0 0 / 20%);
    --large-shadow: 0px 0px 10px 0 rgba(0 0 0 / 30%);
    --box-radius: 5px;
    --box-radius-small:3px;
    --element-radius: 5px;

    --kanban-col-bg: var(--col-content-bg);
    --kanban-col-title-bg: var(--col-title-bg);
    --kanban-col-title-color:var(--primary-font-color);
    --kanban-card-bg: #fff;
    --kanban-card-hover: #F4F4F6;

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
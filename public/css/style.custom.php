<?php 
$color = $_GET['color']; 
$color = "#".$color; 
header("Content-type: text/css", true);
?>

/* login */
.inputwrapper button { background: #d3401e; border-color: #b02808; }
.inputwrapper button:hover { background: #b02808; border-color: #a42406; }

/* background */

body.loginpage, .header, .leftmenu .nav-tabs.nav-stacked > li.active > a, .leftmenu .nav-tabs.nav-stacked > li.active > a:hover, .shortcuts li a, .widgettitle, .mediamgr .mediamgr_rightinner h4, .messagemenu, .msglist li.selected, .wizard .hormenu li a.done, .wizard .hormenu li a.selected, .actionBar a:hover, .actionBar a:hover, .wizard .tabbedmenu, .nav-tabs > .active > a:focus, .tabbable > .nav-tabs, .btn-primary, .btn-primary:link, .nav-tabs > li > a:hover, .nav-tabs > li > a:focus, .nav-pills > .active > a, .nav-pills > .active > a:hover, .nav-pills > .active > a:focus, .tabs-right .nav-tabs, .tabs-right > .nav-tabs > li > a, .tabs-left .nav-tabs, .tabs-left > .nav-tabs > li > a, .progress-primary .bar, .tab-primary.ui-tabs .ui-tabs-nav, .ui-datepicker-calendar td.ui-datepicker-today a, .nav-tabs > .active > a, .nav-tabs > .active > a:hover, .nav-tabs > .active > a:focus, .nav-list > .active > a, .nav-list > .active > a:hover, .nav-list > .active > a:focus, .btn-primary:hover, .btn-primary:focus, .btn-primary:active, .btn-primary.active, .btn-primary.disabled, .btn-primary[disabled], .btn-group.open .btn-primary.dropdown-toggle, .fc-widget-header, .fc-widget-header.fc-agenda-gutter.fc-last, .chzn-container-multi .chzn-choices .search-choice, div.tagsinput span.tag, .chzn-container .chzn-results .highlighted, .dropdown-menu > li > a:hover, .dropdown-menu > li > a:focus, .dropdown-submenu:hover > a, .dropdown-submenu:focus > a, .label-primary, .leftmenu .nav-tabs.nav-stacked .dropdown ul li.active a
{ background-color: <?php echo $color; ?>; }


.loginpanelinner,
.leftpanel .leftmenu .nav-tabs ul.projectselector li.active a{
	background-color: <?php echo $color; ?> !important;
}

.header .logo,
.cr-boundary,
.maincontentinner .dt-buttons .dt-button-collection button:hover,
.boxedHighlight{
	background-color: <?php echo $color; ?>;
}

/* color */

a,a:hover,a:link,a:active,a:focus,
.pageicon,
.pagetitle h1,
.userlist li .uinfo h5,
.messagemenu ul li.active a,
.msglist li h4,
.actionBar a,
.actionBar a.buttonDisabled,
.wizard .tabbedmenu li a.selected,
.wizard .tabbedmenu li a.done,
.tabbable > .nav-tabs > li.active > a,
.btn-circle.btn-primary, .btn-circle.btn-primary:hover, .btn-circle.btn-primary:focus,
.btn-circle.btn-primary:active, .btn-circle.btn-primary.active, 
.btn-circle.btn-primary.disabled, .btn-circle.btn-primary[disabled],
.tabs-right > .nav-tabs .active > a,
.tabs-right > .nav-tabs .active > a:hover,
.tabs-right > .nav-tabs .active > a:focus,
.tabs-left > .nav-tabs .active > a,
.tabs-left > .nav-tabs .active > a:hover,
.tabs-left > .nav-tabs .active > a:focus,
.ticketBox  a.userPopover:hover,
.primaryColor,
.optionLink,
.inlineDropDownContainer .ticketDropDown:hover,
.leftpanel .leftmenu .nav-tabs ul.projectselector  li.intro a,
input.secretInput,
.maincontentinner .ticketDropdown.noBg >a,
.maincontentinner .ticketDropdown.noBg >a:link,
.viewDropDown .dropdown-menu li a.active,
.paginate_button.current:hover
{ color: <?php echo $color; ?>; }


input[type='submit'],
button,
.shepherd-element.shepherd-theme-arrows .shepherd-content footer .shepherd-buttons li .shepherd-button,
.shepherd-element.shepherd-theme-arrows.shepherd-has-title .shepherd-content header,
.table th,
.dropdown-menu span.radio:hover,
.paginate_button.current,
.dropdown-menu > li > a.active
{ background-color:  <?php echo $color; ?>;  }

input[type='submit']:hover, 
button:hover {
	background-color:  #555;
	color:#fff;
} 	

.chzn-container-multi .chzn-choices .search-choice {
	color:#fff;
}

.btn-white {
	background:#fff;
}

span.btn-white:hover{
	background:#fff;
}

	
/* border color */

.pageicon,
.widgetcontent,
.messagemenu ul li.active,
.messageleft,
.messageright,
.messagesearch,
.msgreply,
.wizard .hormenu li a,
.wizard .hormenu li:first-child a,
.stepContainer,
.actionBar,
.actionBar a,
.actionBar a.buttonDisabled,
.tabbable > .nav-tabs,
.tabbable > .tab-content,
.nav-tabs.nav-stacked > li > a:focus,
.btn-circle.btn-primary, .btn-circle.btn-primary:hover, .btn-circle.btn-primary:focus,
.btn-circle.btn-primary:active, .btn-circle.btn-primary.active, 
.btn-circle.btn-primary.disabled, .btn-circle.btn-primary[disabled],
.nav-tabs,
.nav-tabs > li > a:hover, .nav-tabs > li > a:focus,
.tabs-below .tab-content,
.tabs-below > .nav-tabs > li.active > a,
.tabs-right,
.tabs-left,
.tab-primary.ui-tabs,
.btn-primary, .btn-primary:link,
.nav-tabs.nav-stacked > li > a,
.nav-tabs.nav-stacked > li > a:hover,
.nav-tabs.nav-stacked > li > a:hover,
.nav-tabs.nav-stacked > li > a:focus,
.nav-tabs > .active > a,
.nav-tabs > .active > a:hover,
.nav-tabs > .active > a:focus,
div.tagsinput span.tag
{ border-color: <?php echo $color; ?>; }


.ui-datepicker-header { background-color: <?php echo $color; ?> !important; }
.ui-datepicker { border-color: <?php echo $color; ?> !important; }


/* extras */

.tabs-below > .nav-tabs > li.active > a { border-bottom: 1px solid <?php echo $color; ?> !important; }
.nav-list > li > a { color: #666; }
.tabs-left > .nav-tabs > li,
.tabs-right > .nav-tabs > li { border-color: rgba(255,255,255,0.2); }
.leftmenu .nav-tabs.nav-stacked > li > a { border-color: #232323 !important; }
.leftmenu .nav-tabs.nav-stacked > li.active > a { border-color: rgba(0,0,0,0.1) !important; }

/* ie fix */

.no-rgba .headmenu > li { border-right: 1px solid #ca5f46; }
.no-rgba .headmenu > li:first-child { border-left: 1px solid #ca5f46; }


@media screen and (max-width: 480px) {
 
 .userloggedinfo ul li a:hover { background-color: <?php echo $color; ?>; }
 
  .userloggedinfo .userinfo,
  .wizard .hormenu li,
  .messageright { border-color: <?php echo $color; ?>; }

}

.timesheetTable {
	
}

.timesheetTable input {
	width:70%;
}

.week-picker {
	width:100px;
}

.timesheetTable select {
	width:90%;
}


.ui-weekpicker td a.ui-state-hover{
	background:#eee;
}

.ui-weekpicker td a.ui-state-highlight {
	background-color: <?php echo $color; ?>;
}


.ui-state-highlight {
	background:#eee;
}

.companyProject,
.companyProject a{
	color:#999;
	font-weight:normal
}

.filterBar {
	
	border:1px solid #ccc;
	padding:10px;
	margin-bottom:5px;
	background:#eee;
	padding-top:5px;

}

.filterBar .filterBoxLeft {
	float:left;
	margin-right:15px;
min-width:50px;
}

.filterBar .filterBoxLeft input,
.filterBar .filterBoxLeft select {
	float:left;
}

label.inline {
	float:left;
	margin-right:5px;
	padding-top:6px;	
}

.loading {
	position:absolute;
	width:100%;
	height:40px;
}

.widgettitle.title-light {
	color:<?php echo $color; ?>;
	padding-left:5px;
	background-color:#fff;
	border-bottom:1px solid #ddd;
	margin-bottom:15px;

}

.nyroModalCont .widgettitle.title-light {
    padding-top: 5px;
font-size: 16px;
}

div.tagsinput {
	border:1px solid #ccc;	
}

.btn-primary:hover, .btn-primary:active, .btn-primary:focus,
.btn-group.open .btn-primary.dropdown-toggle,
{ background: #ccc; color:#000 }

.btn-primary, .btn-primary:link { 
	
	border: 1px solid #ddd;
	 }
	 
	 .tabbedwidget .btn-primary {
	 	border:0px;
	 }

.gantt-container a:hover {
    text-decoration:underline !important;
}
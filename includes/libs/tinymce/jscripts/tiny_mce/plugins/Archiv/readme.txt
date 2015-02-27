Archiv TinyMCE File & Image manager version 1.2

=[ Created by ]===========================================================
	Wouter van Kuipers (archiv@pwnd.nl)
	Professional Webdesign & Development
	http://archiv.pwnd.nl

	
=[ About ]================================================================
	Archiv is a free file & image management plug-in for TinyMCE. 
	It is based on PHP (4/5) and uses AJAX & Flash to manage files. 
	It comes with security levels (passphrase, ip, password or cookie based), language files and is easy configurable.

	
=[ Requirements ]=========================================================
	To use Archiv you need to meet the following requirements:

	Server
		* TinyMCE version 3.x
		* PHP version 4.x or 5.x
		
	Client
		* JavaScript
		* Flash version > 9
		* Browser version IE 7, Firefox > 2.x, Opera 10, Chrome > 2 , Safari 4


=[ Installation instructions ]============================================
	To install the Archiv plug-in into a new TinyMCE instance you can follow this guide. 
	If you already have an instance of TinyMCE running and you wish to add the plug-in you can advance to step 3.


	Step 1:
	Download the latest version of TinyMCE here

	Step 2:
	Follow the installation instructions as described here

	Step 3:
	Download the latest version of the Archiv plug-in here

	Step 4:
	Add the map 'Archiv' to the 'tiny_mce\plugins' directory.

	Step 5:
	Edit the page where you added the TinyMCE instance, change

	<script language="javascript" type="text/javascript">
	tinyMCE.init({
	    mode : "textareas"
	});
	</script>

	into

	<script language="javascript" type="text/javascript">
	tinyMCE.init({
	    mode : "textareas",
	    theme : "advanced",
	    plugins : "Archiv",
	    Archiv_settings_file : "/abs/path/to/config.php",
	    theme_advanced_buttons1_add : "Archiv_files,Archiv_images"
	});
	</script>

	Step 6:
	Copy and edit the config.php file so it will fit your needs.

	Step 7:
	Launch the page you have the TinyMCE instance loaded on, you should see an instance of TinyMCE with the two icons of Archiv. 

	*note, for a complete guide to all the configuration options please refer to http://archiv.wiki.sourceforge.net/

	
=[ Important known problems ]=============================================
	- folder list icons are sometimes not displayed ok	


=[ Version history ]======================================================
	- 1.2.1: [fixed] 
			- CSS in IE 7/ 8 not correct thanks to Robert Widdick
			- Fixed SVN id set on all files to check origion & version
	
	- 1.2: [fixed] 
			- leading / in subdirectory name when adding to TinyMCE

    - 1.1:  [fixed]
    		- problem with ending / in absolute path name for link & image creation
    
	- 1.0:	
			[Fixed]
			- fixed a bug where debug option could not be set propperly
			- fixed a bug where the path was set to / after directory deletetion
			- lots of minor bugfixes
			- removed all security options (will be re-added later)
			
			[Changed]
			- changed the way the config.php now is being processed (no more ../../../ stuff)
			- code now based on jQuery
			- using post instead of get (more secure)
			- using JSON instead of XML to reduce overhead
			- updated SWFUpload to v2.2.0.1
			- nicer folder list (not perfect, needs some more tweaking!)
			- better moveable splitter
			- removed md5 prefix from file name, added file exist check
			- prompts & alerts in jQuery style
			- XHTML 1.1 valid!
			- optimized loading
			- plugin now also works with the jquery version of TinyMCE
			- optimized multi language support
			- dropped IE <= 6 support (gracefully)
			
			[Added]
			+ added scrollbar to folder view
			+ added flash detection tool ( http://www.featureblend.com/javascript-flash-detection-library.html ) 
			+ added more/better comments
			+ added abillity to remove subdirectory's	
			+ added CVS tags to every file
			+ added Catalan language (thanks to Alexandre Enrich)
			+ added Spanish language (thanks to Mario Figge)
			
			[Checked]
			- checked in browsers:
				- Opera (10)
				- Chrome (2)
				- Safari (4)
				- Firefox 2/3 (2 has some  minor display glitches, 3 recommended)
				- Internet Explorer 7/8 (7 has some  minor display glitches, 8 recommended)
			- checked working on IIS (7)
			- changed licence to LGPL (see http://www.gnu.org/copyleft/lesser.html)

	- 0.5 : * Fixed a bug in the Dutch translation (thanks to Mike)
	
	- 0.4 : * Added French translation
		* Updated SWFUpload (now supports Flash 10)
		* Changed overal Licence type (LGPL)
		* Small bug fixes
	
	- 0.3 : * Added debug for PHP errors in config file
		* Changed the way XML is created so if no valid function is availeable it will be corrected
		* Fixed a problem with getDirContent() (PHP) returning a error
	
	- 0.2 : * Added readme
		* Added Dutch translation
		* Added German translation
	
	- 0.1 : * Created the plug-in

=[ Next release ]========================================================
	- option to lock user to one dir (or a set of dirs) 
	- option to hide the dirList
	- option to remove/limit file delete
	- option to remove/limit directory delete
	- re-add some security features like IP, session and key bases
	- add more file details like (last modification date)
	- add advanced features like:
		- rename
		- move
		- download
		- crop / resize
	- show dir stats (files, size, permissions)
	- file preview/download
	- fix IE 7 support, maybe check IE 6 support and fix it
	- folder quota's

=[ Special Thanks to ]====================================================
	Joost Altena
	Florian Woehrl (German translation)
	Laurent Nunenthal ( French translation)
	Alexandre Enrich (Catalan translation)
	Mario Figge (Spanish translation)


(c) 2008-2009 Wouter van Kuipers - Professional Webdesign & Development
Last update: 26/10/2009

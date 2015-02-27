/**
 * Archiv TinyMCE plugin loader
 * 
 * @id: $Id: editor_plugin_src.js,v 1.4 2009/10/27 20:15:55 wvankuipers Exp $
 * @version 1.0
 * @author Wouter van Kuipers (Archiv@pwnd.nl)
 * @copyright 2008-2009 PWND
 * @license LGPL 
 * @see http://archiv.pwnd.nl
 */

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('Archiv');

	tinymce.create('tinymce.plugins.ArchivPlugin', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('mceArchiv_files', function() {
				ed.windowManager.open({
					file 	: url + '/dialog.htm',
					width 	: 800 + parseInt(ed.getLang('mceArchiv_files.delta_width', 0)),
					height 	: 600 + parseInt(ed.getLang('mceArchiv_files.delta_height', 0)),
					inline 	: 1
				}, {
					plugin_url 		: url, // Plugin absolute URL
					browserType 	: 'files' // Custom argument
				});
			});
			
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('mceArchiv_images', function() {
				ed.windowManager.open({
					file 	: url + '/dialog.htm',
					width 	: 800 + parseInt(ed.getLang('mceArchiv_images.delta_width', 0)),
					height 	: 600 + parseInt(ed.getLang('mceArchiv_images.delta_height', 0)),
					inline 	: 1
				}, {
					plugin_url 		: url, // Plugin absolute URL
					browserType 	: 'images' // Custom argument
				});
			});

			// Register example button
			ed.addButton('Archiv_files', {
				title 	: 'Archiv.Fdesc',
				cmd 	: 'mceArchiv_files',
				image 	: url + '/img/insertfile.gif'
			});
			
			// Register example button
			ed.addButton('Archiv_images', {
				title 	: 'Archiv.Idesc',
				cmd 	: 'mceArchiv_images',
				image 	: url + '/img/insertimage.gif'
			});
		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname 	: 'Archiv plugin',
				author 		: 'Wouter van Kuipers',
				authorurl 	: 'http://www.pwnd.nl',
				infourl 	: 'http://archiv.pwnd.nl',
				version 	: "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('Archiv', tinymce.plugins.ArchivPlugin);
})();
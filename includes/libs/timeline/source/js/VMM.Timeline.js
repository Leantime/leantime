/**
	* TimelineJS
	* Designed and built by Zach Wise at VéritéCo

	* This Source Code Form is subject to the terms of the Mozilla Public
	* License, v. 2.0. If a copy of the MPL was not distributed with this
	* file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/  

/*	* CodeKit Import
	* http://incident57.com/codekit/
================================================== */
// @codekit-prepend "VMM.Timeline.License.js";

// @codekit-prepend "Core/VMM.StoryJS.js";

// @codekit-append "VMM.Timeline.TimeNav.js";
// @codekit-append "VMM.Timeline.DataObj.js";


/* Timeline
================================================== */

if(typeof VMM != 'undefined' && typeof VMM.Timeline == 'undefined') {
	
	VMM.Timeline = function(_timeline_id, w, h) {
		
		var $timeline,
			$container,
			$feature,
			$feedback,
			$slider,
			$navigation,
			slider,
			timenav,
			version		= "2.x",
			timeline_id	= "#timelinejs",
			events		= {},
			data		= {},
			_dates		= [],
			config		= {},
			has_width	= false,
			has_height	= false,
			ie7			= false,
			is_moving	= false;
		

		if (type.of(_timeline_id) == "string") {
			if (_timeline_id.match("#")) {
				timeline_id	= _timeline_id;
			} else {
				timeline_id	= "#" + _timeline_id;
			}
		} else {
			timeline_id		= "#timelinejs";
		}
		
		
		/* CONFIG
		================================================== */
		config = {
			embed:					false,
			events: {
				data_ready:			"DATAREADY",
				messege:			"MESSEGE",
				headline:			"HEADLINE",
				slide_change:		"SLIDE_CHANGE",
				resize:				"resize"
			},
			id: 					timeline_id,
			source:					"nothing",
			type: 					"timeline",
			touch:					false,
			orientation: 			"normal", 
			maptype: 				"toner",
			version: 				"2.x", 
			preload:				4,
			current_slide:			0,
			hash_bookmark:			false,
			start_at_end: 			false,
			start_at_slide:			0,
			start_zoom_adjust:		0,
			start_page: 			false,
			api_keys: {
				google:				"",
				flickr:				"",
				twitter:			""
			},
			interval: 				10,
			something: 				0,
			width: 					960,
			height: 				540,
			spacing: 				15,
			loaded: {
				slider: 			false, 
				timenav: 			false, 
				percentloaded: 		0
			},
			nav: {
				start_page: 		false,
				interval_width: 	200,
				interval_override: 	false, // change this to override the interval of years, months etc.
				density: 			4,
				minor_width: 		0,
				minor_left:			0,
				constraint: {
					left:			0,
					right:			0,
					right_min:		0,
					right_max:		0
				},
				zoom: {
					adjust:			0
				},
				multiplier: {
					current: 		6,
					min: 			.1,
					max: 			50
				},
				rows: 				[1, 1, 1],
				width: 				960,
				height: 			200,
				marker: {
					width: 			150,
					height: 		50
				}
			},
			feature: {
				width: 				960,
				height: 			540
			},
			slider: {
				width: 				720,
				height: 			400,
				content: {
					width: 			720,
					height: 		400,
					padding: 		130,
					padding_default:130
				},
				nav: {
					width: 			100,
					height: 		200
				}
			},
			ease: 					"easeInOutExpo",
			duration: 				1000,
			gmap_key: 				"",
			language: 				VMM.Language
		};
		
		if ( w != null && w != "") {
			config.width = w;
			has_width = true;
		} 

		if ( h != null && h != "") {
			config.height = h;
			has_height = true;
		}
		
		if(window.location.hash) {
			 var hash					=	window.location.hash.substring(1);
			 if (!isNaN(hash)) {
			 	 config.current_slide	=	parseInt(hash);
			 }
		}
		
		window.onhashchange = function () {
			var hash					=	window.location.hash.substring(1);
			if (config.hash_bookmark) {
				if (is_moving) {
					goToEvent(parseInt(hash));
				} else {
					is_moving = false;
				}
			} else {
				goToEvent(parseInt(hash));
			}
		}
		
		/* CREATE CONFIG
		================================================== */
		function createConfig(conf) {
			
			// APPLY SUPPLIED CONFIG TO TIMELINE CONFIG
			if (typeof embed_config == 'object') {
				timeline_config = embed_config;
			}
			if (typeof timeline_config == 'object') {
				trace("HAS TIMELINE CONFIG");
				config = VMM.Util.mergeConfig(config, timeline_config);
			} else if (typeof conf == 'object') {
				config = VMM.Util.mergeConfig(config, conf);
			}
			
			if (VMM.Browser.device == "mobile" || VMM.Browser.device == "tablet") {
				config.touch = true;
			}
			
			config.nav.width			= config.width;
			config.nav.height			= 200;
			config.feature.width		= config.width;
			config.feature.height		= config.height - config.nav.height;
			config.nav.zoom.adjust		= parseInt(config.start_zoom_adjust, 10);
			VMM.Timeline.Config			= config;
			VMM.master_config.Timeline	= VMM.Timeline.Config;
			this.events					= config.events;
			
			if (config.gmap_key != "") {
				config.api_keys.google = config.gmap_key;
			}
			
			trace("VERSION " + config.version);
			version = config.version;
		}
		
		/* CREATE TIMELINE STRUCTURE
		================================================== */
		function createStructure() {
			// CREATE DOM STRUCTURE
			$timeline	= VMM.getElement(timeline_id);
			VMM.Lib.addClass($timeline, "vco-timeline");
			VMM.Lib.addClass($timeline, "vco-storyjs");
			
			$container	= VMM.appendAndGetElement($timeline, "<div>", "vco-container vco-main");
			$feature	= VMM.appendAndGetElement($container, "<div>", "vco-feature");
			$slider		= VMM.appendAndGetElement($feature, "<div>", "vco-slider");
			$navigation	= VMM.appendAndGetElement($container, "<div>", "vco-navigation");
			$feedback	= VMM.appendAndGetElement($timeline, "<div>", "vco-feedback", "");
			
			
			if (typeof config.language.right_to_left != 'undefined') {
				VMM.Lib.addClass($timeline, "vco-right-to-left");
			}
			
			slider		= new VMM.Slider($slider, config);
			timenav		= new VMM.Timeline.TimeNav($navigation);
			
			if (!has_width) {
				config.width = VMM.Lib.width($timeline);
			} else {
				VMM.Lib.width($timeline, config.width);
			}

			if (!has_height) {
				config.height = VMM.Lib.height($timeline);
			} else {
				VMM.Lib.height($timeline, config.height);
			}
			
			if (config.touch) {
				VMM.Lib.addClass($timeline, "vco-touch");
			} else {
				VMM.Lib.addClass($timeline, "vco-notouch");
			}
			
			
		}
		
		/* ON EVENT
		================================================== */

		function onDataReady(e, d) {
			trace("onDataReady");
			data = d.timeline;
			
			if (type.of(data.era) != "array") {
				data.era = [];
			}
			
			buildDates();
			
		};
		
		function onDatesProcessed() {
			build();
		}
		
		function reSize() {
			
			updateSize();
			
			slider.setSize(config.feature.width, config.feature.height);
			timenav.setSize(config.width, config.height);
			if (orientationChange()) {
				setViewport();
			}
			
		};
		
		function onSliderLoaded(e) {
			config.loaded.slider = true;
			onComponentLoaded();
		};
		
		function onComponentLoaded(e) {
			config.loaded.percentloaded = config.loaded.percentloaded + 25;
			
			if (config.loaded.slider && config.loaded.timenav) {
				hideMessege();
			}
		}
		
		function onTimeNavLoaded(e) {
			config.loaded.timenav = true;
			onComponentLoaded();
		}
		
		function onSlideUpdate(e) {
			is_moving = true;
			config.current_slide = slider.getCurrentNumber();
			setHash(config.current_slide);
			timenav.setMarker(config.current_slide, config.ease,config.duration);
		};
		
		function onMarkerUpdate(e) {
			is_moving = true;
			config.current_slide = timenav.getCurrentNumber();
			setHash(config.current_slide);
			slider.setSlide(config.current_slide);
		};
		
		function goToEvent(n) {
			if (n <= _dates.length - 1 && n >= 0) {
				config.current_slide = n;
				slider.setSlide(config.current_slide);
				timenav.setMarker(config.current_slide, config.ease,config.duration);
			} 
		}
		
		function setHash(n) {
			if (config.hash_bookmark) {
				window.location.hash = "#" + n.toString();
			}
		}
		
		function getViewport() {
			
		}
		
		function setViewport() {
			var viewport_content		= "",
				viewport_orientation	= searchOrientation(window.orientation);
			
			if (VMM.Browser.device == "mobile") {
				if (viewport_orientation == "portrait") {
					//viewport_content	= "width=device-width; initial-scale=0.75, maximum-scale=0.75";
					viewport_content	= "width=device-width; initial-scale=0.5, maximum-scale=0.5";
				} else if (viewport_orientation == "landscape") {
					viewport_content	= "width=device-width; initial-scale=0.5, maximum-scale=0.5";
				} else {
					viewport_content	= "width=device-width, initial-scale=1, maximum-scale=1.0";
				}
			} else if (VMM.Browser.device == "tablet") {
				//viewport_content		= "width=device-width, initial-scale=1, maximum-scale=1.0";
			}
			
			if (document.getElementById("viewport")) {
				//VMM.Lib.attr("#viewport", "content", viewport_content);
			} else {
				//VMM.appendElement("head", "<meta id='viewport' name='viewport' content=" + viewport_content + "/>");
			}

		}
		
		/* ORIENTATION
		================================================== */
		function searchOrientation(orientation) {
			var orient = "";
			
			if ( orientation == 0  || orientation == 180) {  
				orient = "portrait";
			} else if ( orientation == 90 || orientation == -90) {  
				orient = "landscape";
			} else {
				orient = "normal";
			}
			
			return orient;
		}
		
		function orientationChange() {
			var orientation	= searchOrientation(window.orientation);
			
			if (orientation == config.orientation) {
				return false;
			} else {
				config.orientation = orientation;
				return true;
			}
			
		}
		
		
		/* PUBLIC FUNCTIONS
		================================================== */
		this.init = function(c, _data) {
			trace('INIT');
			setViewport();
			createConfig(c);
			createStructure();
			
			if (type.of(_data) == "string") {
				config.source	= _data;
			}
			
			// LANGUAGE
			VMM.Date.setLanguage(config.language);
			VMM.master_config.language = config.language;
			
			// EXTERNAL API
			VMM.ExternalAPI.setKeys(config.api_keys);
			VMM.ExternalAPI.googlemaps.setMapType(config.maptype);
			
			// EVENTS
			VMM.bindEvent(global, onDataReady, config.events.data_ready);
			VMM.bindEvent(global, showMessege, config.events.messege);
			
			VMM.fireEvent(global, config.events.messege, config.language.messages.loading_timeline);
			
			/* GET DATA
			================================================== */
			if (VMM.Browser.browser == "Explorer" || VMM.Browser.browser == "MSIE") {
				if ( parseInt(VMM.Browser.version, 10) <= 7 ) {
					ie7 = true;
				}
			}
			
			if (type.of(config.source) == "string" || type.of(config.source) == "object") {
				VMM.Timeline.DataObj.getData(config.source);
			} else {
				VMM.fireEvent(global, config.events.messege, "No data source provided");
				//VMM.Timeline.DataObj.getData(VMM.getElement(timeline_id));
			}
			
			
		};
		
		this.iframeLoaded = function() {
			trace("iframeLoaded");
		};
		
		this.reload = function(_d) {
			trace("Load new timeline data" + _d);
			VMM.fireEvent(global, config.events.messege, config.language.messages.loading_timeline);
			data = {};
			VMM.Timeline.DataObj.getData(_d);
			config.current_slide = 0;
			slider.setSlide(0);
			timenav.setMarker(0, config.ease,config.duration);
		};
		
		/* DATA 
		================================================== */
		function getData(url) {
			VMM.getJSON(url, function(d) {
				data = VMM.Timeline.DataObj.getData(d);
				VMM.fireEvent(global, config.events.data_ready);
			});
		};
		
		/* MESSEGES 
		================================================== */
		function showMessege(e, msg, other) {
			trace("showMessege " + msg);
			//VMM.attachElement($timeline, $feedback);
			if (other) {
				VMM.attachElement($feedback, msg);
			} else{
				VMM.attachElement($feedback, VMM.MediaElement.loadingmessage(msg));
			}
		};
		
		function hideMessege() {
			VMM.Lib.animate($feedback, config.duration, config.ease*4, {"opacity": 0}, detachMessege);
		};
		
		function detachMessege() {
			VMM.Lib.detach($feedback);
		}
		
		/* BUILD DISPLAY
		================================================== */
		function build() {
			
			// START AT SLIDE
			if (parseInt(config.start_at_slide) > 0 && config.current_slide == 0) {
				config.current_slide = parseInt(config.start_at_slide); 
			}
			// START AT END
			if (config.start_at_end && config.current_slide == 0) {
				config.current_slide = _dates.length - 1;
			}
			
			
			// IE7
			if (ie7) {
				ie7 = true;
				VMM.fireEvent(global, config.events.messege, "Internet Explorer " + VMM.Browser.version + " is not supported by TimelineJS. Please update your browser to version 8 or higher.");
			} else {
				
				detachMessege();
				reSize();
				
				// EVENT LISTENERS
				VMM.bindEvent($slider, onSliderLoaded, "LOADED");
				VMM.bindEvent($navigation, onTimeNavLoaded, "LOADED");
				VMM.bindEvent($slider, onSlideUpdate, "UPDATE");
				VMM.bindEvent($navigation, onMarkerUpdate, "UPDATE");
				
				// INITIALIZE COMPONENTS
				slider.init(_dates);
				timenav.init(_dates, data.era);
			
				// RESIZE EVENT LISTENERS
				VMM.bindEvent(global, reSize, config.events.resize);
				
				
				
			}
			
			
		};
		
		function updateSize() {
			trace("UPDATE SIZE");
			config.width = VMM.Lib.width($timeline);
			config.height = VMM.Lib.height($timeline);
			
			config.nav.width = config.width;
			config.feature.width = config.width;
			
			config.feature.height = config.height - config.nav.height - 3;
			
			if (VMM.Browser.device == "mobile") {
				/*
				if (VMM.Browser.orientation == "portrait") {
					config.feature.height	= 480;
					config.height			= 480 + config.nav.height;
				} else if(VMM.Browser.orientation == "landscape") {
					config.feature.height	= 320;
					config.height			= 320 + config.nav.height;
				} else {
					config.feature.height = config.height - config.nav.height - 3;
				}
				*/
			}
			
			if (config.width < 641) {
				VMM.Lib.addClass($timeline, "vco-skinny");
			} else {
				VMM.Lib.removeClass($timeline, "vco-skinny");
			}
			
		};
		
		// BUILD DATE OBJECTS
		function buildDates() {
			
			_dates = [];
			VMM.fireEvent(global, config.events.messege, "Building Dates");
			updateSize();
			
			for(var i = 0; i < data.date.length; i++) {
				
				if (data.date[i].startDate != null && data.date[i].startDate != "") {
					
					var _date		= {},
						do_start	= VMM.Date.parse(data.date[i].startDate, true),
						do_end;
						
					_date.startdate		= do_start.date;
					_date.precisiondate	= do_start.precision;
					
					if (!isNaN(_date.startdate)) {
						
					
						// END DATE
						if (data.date[i].endDate != null && data.date[i].endDate != "") {
							_date.enddate = VMM.Date.parse(data.date[i].endDate);
						} else {
							_date.enddate = _date.startdate;
						}
						
						_date.needs_slug = false;
						
						if (data.date[i].headline == "") {
							if (data.date[i].slug != null && data.date[i].slug != "") {
								_date.needs_slug = true;
							}
						}
						
						_date.title				= data.date[i].headline;
						_date.headline			= data.date[i].headline;
						_date.type				= data.date[i].type;
						_date.date				= VMM.Date.prettyDate(_date.startdate, false, _date.precisiondate);
						_date.asset				= data.date[i].asset;
						_date.fulldate			= _date.startdate.getTime();
						_date.text				= data.date[i].text;
						_date.content			= "";
						_date.tag				= data.date[i].tag;
						_date.slug				= data.date[i].slug;
						_date.uniqueid			= VMM.Util.unique_ID(7);
						_date.classname			= data.date[i].classname;
						
						
						_dates.push(_date);
					} 
					
				}
				
			};
			
			/* CUSTOM SORT
			================================================== */
			if (data.type != "storify") {
				_dates.sort(function(a, b){
					return a.fulldate - b.fulldate
				});
			}
			
			/* CREATE START PAGE IF AVAILABLE
			================================================== */
			if (data.headline != null && data.headline != "" && data.text != null && data.text != "") {

				var startpage_date,
					do_start,
					_date			= {},
					td_num			= 0,
					td;
					
				if (typeof data.startDate != 'undefined') {
					do_start		= VMM.Date.parse(data.startDate, true);
					startpage_date	= do_start.date;
				} else {
					startpage_date = false;
				}
				trace("HAS STARTPAGE");
				trace(startpage_date);
				
				if (startpage_date && startpage_date < _dates[0].startdate) {
					_date.startdate = new Date(startpage_date);
				} else {
					td = _dates[0].startdate;
					_date.startdate = new Date(_dates[0].startdate);
				
					if (td.getMonth() === 0 && td.getDate() == 1 && td.getHours() === 0 && td.getMinutes() === 0 ) {
						// trace("YEAR ONLY");
						_date.startdate.setFullYear(td.getFullYear() - 1);
					} else if (td.getDate() <= 1 && td.getHours() === 0 && td.getMinutes() === 0) {
						// trace("YEAR MONTH");
						_date.startdate.setMonth(td.getMonth() - 1);
					} else if (td.getHours() === 0 && td.getMinutes() === 0) {
						// trace("YEAR MONTH DAY");
						_date.startdate.setDate(td.getDate() - 1);
					} else  if (td.getMinutes() === 0) {
						// trace("YEAR MONTH DAY HOUR");
						_date.startdate.setHours(td.getHours() - 1);
					} else {
						// trace("YEAR MONTH DAY HOUR MINUTE");
						_date.startdate.setMinutes(td.getMinutes() - 1);
					}
				}
				
				_date.uniqueid		= VMM.Util.unique_ID(7);
				_date.enddate		= _date.startdate;
				_date.precisiondate	= do_start.precision;
				_date.title			= data.headline;
				_date.headline		= data.headline;
				_date.text			= data.text;
				_date.type			= "start";
				_date.date			= VMM.Date.prettyDate(data.startDate, false, _date.precisiondate);
				_date.asset			= data.asset;
				_date.slug			= false;
				_date.needs_slug	= false;
				_date.fulldate		= _date.startdate.getTime();
				
				if (config.embed) {
					VMM.fireEvent(global, config.events.headline, _date.headline);
				}
				
				_dates.unshift(_date);
			}
			
			/* CUSTOM SORT
			================================================== */
			if (data.type != "storify") {
				_dates.sort(function(a, b){
					return a.fulldate - b.fulldate
				});
			}
			
			onDatesProcessed();
		}
		
	};

	VMM.Timeline.Config = {};
	
};
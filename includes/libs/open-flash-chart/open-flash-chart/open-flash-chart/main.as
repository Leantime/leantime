
package  {
	import charts.series.Element;
	import charts.Factory;
	import charts.ObjectCollection;
	import elements.menu.Menu;
	import charts.series.has_tooltip;
	import flash.events.Event;
	import flash.events.MouseEvent;
	
	// for image upload:
	import flash.events.ProgressEvent;
	import flash.net.URLVariables;
	
	import flash.display.Sprite;
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.display.StageAlign;
	import flash.display.StageScaleMode;
	import string.Utils;
	import global.Global;
	import com.serialization.json.JSON;
	import flash.external.ExternalInterface;
	import flash.ui.ContextMenu;
	import flash.ui.ContextMenuItem;
	import flash.events.IOErrorEvent;
	import flash.events.ContextMenuEvent;
	import flash.system.System;
	
	import flash.display.LoaderInfo;

	// export the chart as an image
	import com.adobe.images.PNGEncoder;
	import com.adobe.images.JPGEncoder;
	import mx.utils.Base64Encoder;
	// import com.dynamicflash.util.Base64;
	import flash.display.BitmapData;
	import flash.utils.ByteArray;
	import flash.net.URLRequestHeader;
	import flash.net.URLRequestMethod;
	import flash.net.URLLoaderDataFormat;
	import elements.axis.XAxis;
	import elements.axis.XAxisLabels;
	import elements.axis.YAxisBase;
	import elements.axis.YAxisLeft;
	import elements.axis.YAxisRight;
	import elements.axis.RadarAxis;
	import elements.Background;
	import elements.labels.XLegend;
	import elements.labels.Title;
	import elements.labels.Keys;
	import elements.labels.YLegendBase;
	import elements.labels.YLegendLeft;
	import elements.labels.YLegendRight;
	
	
	public class main extends Sprite {
		
		public  var VERSION:String = "2 Lug Wyrm Charmer";
		private var title:Title = null;
		//private var x_labels:XAxisLabels;
		private var x_axis:XAxis;
		private var radar_axis:RadarAxis;
		private var x_legend:XLegend;
		private var y_axis:YAxisBase;
		private var y_axis_right:YAxisBase;
		private var y_legend:YLegendBase;
		private var y_legend_2:YLegendBase;
		private var keys:Keys;
		private var obs:ObjectCollection;
		public var tool_tip_wrapper:String;
		private var sc:ScreenCoords;
		private var tooltip:Tooltip;
		private var background:Background;
		private var menu:Menu;
		private var ok:Boolean;
		private var URL:String;		// ugh, vile. The IOError doesn't report the URL
		private var id:String;		// chart id passed inf from user
		private var chart_parameters:Object;
		private var json:String;
	
		
		public function main() {
			this.chart_parameters = LoaderInfo(this.loaderInfo).parameters;
			if( this.chart_parameters['loading'] == null )
				this.chart_parameters['loading'] = 'Loading data...';
				
			var l:Loading = new Loading(this.chart_parameters['loading']);
			this.addChild( l );

			this.build_right_click_menu();
			this.ok = false;

			if( !this.find_data() )
			{
				// no data found -- debug mode?
				try {
					var file:String = "../../data-files/y-axis-auto-steps.txt";
					this.load_external_file( file );

					/*
					// test AJAX calls like this:
					var file:String = "../data-files/bar-2.txt";
					this.load_external_file( file );
					file = "../data-files/radar-area.txt";
					this.load_external_file( file );
					*/
				}
				catch (e:Error) {
					this.show_error( 'Loading test data\n'+file+'\n'+e.message );
				}
			}
			
			// inform javascript that it can call our reload method
			this.addCallback("reload", reload); // mf 18nov08, line 110 of original 'main.as'
		 
			// inform javascript that it can call our load method
			this.addCallback("load", load);
			
			// inform javascript that it can call our post_image method
			this.addCallback("post_image", post_image);
			
			// 
			this.addCallback("get_img_binary",  getImgBinary);

			// more interface
			this.addCallback("get_version",	getVersion);
			
			// TODO: chanf all external to use this:
			
			//
			// tell our external interface manager to pass out the chart ID
			// with every external call.
			//
			if ( this.chart_parameters['id'] )
			{
				var ex:ExternalInterfaceManager = ExternalInterfaceManager.getInstance();
				ex.setUp(this.chart_parameters['id']);
			}
			
			//
			// TODO: move this so it is called after set_the_stage is ready.
			//
			// tell the web page that we are ready
			if( this.chart_parameters['id'] )
				this.callExternalCallback("ofc_ready", this.chart_parameters['id']);
			else
				this.callExternalCallback("ofc_ready");
			//
			//
			//
			
			this.set_the_stage();
		}
		
		private function addCallback(functionName:String, closure:Function): void {

			// the debug player does not have an external interface
			// because it is NOT embedded in a browser
			if (ExternalInterface.available)
				ExternalInterface.addCallback(functionName, closure);
			
		}
		
		private function callExternalCallback(functionName:String, ... optionalArgs ): * {
			
			// the debug player does not have an external interface
			// because it is NOT embedded in a browser
			if (ExternalInterface.available)
				return ExternalInterface.call(functionName, optionalArgs);
			
		}
		
		public function getVersion():String {return VERSION;}
		
		// public function getImgBinary():String { return Base64.encodeByteArray(image_binary()); }
		public function getImgBinary():String {
			
			tr.ace('Saving image :: image_binary()');

			var bmp:BitmapData = new BitmapData(this.stage.stageWidth, this.stage.stageHeight);
			bmp.draw(this);
			
			var b64:Base64Encoder = new Base64Encoder();
			
			var b:ByteArray = PNGEncoder.encode(bmp);
			
			// var encoder:JPGEncoder = new JPGEncoder(80);
			// var q:ByteArray = encoder.encode(bmp);
			// b64.encodeBytes(q);
			
			//
			//
			//
			b64.encodeBytes(b);
			return b64.toString();
			//
			// commented out by J vander? why?
			// return b64.flush();
			//
			//
			
			
			/*
			var b64:Base64Encoder = new Base64Encoder();
			b64.encodeBytes(image_binary());
			tr.ace( b64 as String );
			return b64 as String;
			*/
		}
		
		
		/**
		 * Called from the context menu:
		 */
		public function saveImage(e:ContextMenuEvent):void {
			// ExternalInterface.call("save_image", this.chart_parameters['id']);// , getImgBinary());
			// ExternalInterface.call("save_image", getImgBinary());
			
			// this just calls the javascript function which will grab an image from use
			// an do something with it.
			this.callExternalCallback("save_image", this.chart_parameters['id']);
		}

		
	    private function image_binary() : ByteArray {
			tr.ace('Saving image :: image_binary()');

			var pngSource:BitmapData = new BitmapData(this.width, this.height);
			pngSource.draw(this);
			return PNGEncoder.encode(pngSource);
	    }
	
		//
		// External interface called by Javascript to
		// save the flash as an image, then POST it to a URL
		//
		//public function post_image(url:String, post_params:Object, callback:String, debug:Boolean):void {
		public function post_image(url:String, callback:String, debug:Boolean):void {
          
			var header:URLRequestHeader = new URLRequestHeader("Content-type", "application/octet-stream");

			//Make sure to use the correct path to jpg_encoder_download.php
			var request:URLRequest = new URLRequest(url);
			
			request.requestHeaders.push(header);
			request.method = URLRequestMethod.POST;
			//
			request.data = image_binary();

			var loader:URLLoader = new URLLoader();
			loader.dataFormat = URLLoaderDataFormat.VARIABLES;
            
			/*
			 * i can't figure out how to make these work
			 * 
			var urlVars:URLVariables = new URLVariables();
			for (var key:String in post_params) {
				urlVars[key] = post_params[key];
			}
			*/
			// base64:
			// urlVars.b64_image_data =  getImgBinary();
			// RAW:
			// urlVars.b64_image_data = image_binary();
			
			// request.data = urlVars;

			var id:String = '';
			if ( this.chart_parameters['id'] )
				id = this.chart_parameters['id'];
				
			if( debug )
			{
				// debug the PHP:
				flash.net.navigateToURL(request, "_blank");
			}
			else
			{
				//we have to use the PROGRESS event instead of the COMPLETE event due to a bug in flash
				loader.addEventListener(ProgressEvent.PROGRESS, function (e:ProgressEvent):void {
					
						tr.ace("progress:" + e.bytesLoaded + ", total: " + e.bytesTotal);
						if ((e.bytesLoaded == e.bytesTotal) && (callback != null)) {
							tr.aces('Calling: ', callback + '(' + id + ')'); 
							this.call(callback, id);
						}
					});

				try {
					loader.load( request );
				} catch (error:Error) {
					tr.ace("unable to load:" + error);
				}
			 
				/*
				var loader:URLLoader = new URLLoader();
				loader.dataFormat = URLLoaderDataFormat.BINARY;
				loader.addEventListener(Event.COMPLETE, function(e:Event):void {
					tr.ace('Saved image to:');
					tr.ace( url );
					//
					// when the upload has finished call the user
					// defined javascript function/method
					//
					ExternalInterface.call(callback);
					});
					
				loader.load( jpgURLRequest );
				*/
			}
		}

		
		private function onContextMenuHandler(event:ContextMenuEvent):void
		{
		}
		
		//
		// try to find some data to load,
		// check the URL for a file name,
		//
		//
		public function find_data(): Boolean {
			
			// var all:String = ExternalInterface.call("window.location.href.toString");
			var vars:String = this.callExternalCallback("window.location.search.substring", 1);
			
			if( vars != null )
			{
				var p:Array = vars.split( '&' );
				for each ( var v:String in p )
				{
					if( v.indexOf( 'ofc=' ) > -1 )
					{
						var tmp:Array = v.split('=');
						tr.ace( 'Found external file:' + tmp[1] );
						this.load_external_file( tmp[1] );
						//
						// LOOK:
						//
						return true;
					}
				}
			}
			
			if( this.chart_parameters['data-file'] )
			{
				// tr.ace( 'Found parameter:' + parameters['data-file'] );
				this.load_external_file( this.chart_parameters['data-file'] );
				//
				// LOOK:
				//
				return true;
				
			}
			
			var get_data:String = 'open_flash_chart_data';
			if( this.chart_parameters['get-data'] )
				get_data = this.chart_parameters['get-data'];
			
			var json_string:*;
			
			if( this.chart_parameters['id'] )
				json_string = this.callExternalCallback( get_data , this.chart_parameters['id']);
			else
				json_string = this.callExternalCallback( get_data );
			
			
			if( json_string != null )
			{
				if( json_string is String )
				{
					this.parse_json( json_string );
					
					//
					// We have loaded the data, so this.ok = true
					//
					this.ok = true;
					//
					// LOOK:
					//
					return true;
				}
			}
			
			return false;
		}
		
		
		//
		// an external interface, used by javascript to
		// reload JSON from a URL :: mf 18nov08
		//
		public function reload( url:String ):void {

			var l:Loading = new Loading(this.chart_parameters['loading']);
			this.addChild( l );
			this.load_external_file( url );
		}


		private function load_external_file( file:String ):void {
			
			this.URL = file;
			//
			// LOAD THE DATA
			//
			var loader:URLLoader = new URLLoader();
			loader.addEventListener( IOErrorEvent.IO_ERROR, this.ioError );
			loader.addEventListener( Event.COMPLETE, xmlLoaded );
			
			var request:URLRequest = new URLRequest(file);
			loader.load(request);
		}
		
		private function ioError( e:IOErrorEvent ):void {
			
			// remove the 'loading data...' msg:
			this.removeChildAt(0);
			var msg:ErrorMsg = new ErrorMsg( 'Open Flash Chart\nIO ERROR\nLoading test data\n' + e.text );
			msg.add_html( 'This is the URL that I tried to open:<br><a href="'+this.URL+'">'+this.URL+'</a>' );
			this.addChild( msg );
		}
		
		private function show_error( msg:String ):void {
			
			// remove the 'loading data...' msg:
			this.removeChildAt(0);

			var m:ErrorMsg = new ErrorMsg( msg );
			//m.add_html( 'Click here to open your JSON file: <a href="http://a.com">asd</a>' );
			this.addChild(m);
		}

		public function get_x_legend() : XLegend {
			return this.x_legend;
		}
		
		private function set_the_stage():void {

			// tell flash to align top left, and not to scale
			// anything (we do that in the code)
			this.stage.align = StageAlign.TOP_LEFT;
			//
			// ----- RESIZE ----
			//
			// noScale: now we can pick up resize events
			this.stage.scaleMode = StageScaleMode.NO_SCALE;
            this.stage.addEventListener(Event.ACTIVATE, this.activateHandler);
            this.stage.addEventListener(Event.RESIZE, this.resizeHandler);
			this.stage.addEventListener(Event.MOUSE_LEAVE, this.mouseOut);
			this.addEventListener( MouseEvent.MOUSE_OVER, this.mouseMove );
		}
		
		
		private function mouseMove( event:Event ):void {
			// tr.ace( 'over ' + event.target );
			// tr.ace('move ' + Math.random().toString());
			// tr.ace( this.tooltip.get_tip_style() );
			
			if ( !this.tooltip )
				return;		// <- an error and the JSON was not loaded
				
			switch( this.tooltip.get_tip_style() ) {
				case Tooltip.CLOSEST:
					this.mouse_move_closest( event );
					break;
					
				case Tooltip.PROXIMITY:
					this.mouse_move_proximity( event as MouseEvent );
					break;
					
				case Tooltip.NORMAL:
					this.mouse_move_follow( event as MouseEvent );
					break;
					
			}
		}
		
		private function mouse_move_follow( event:MouseEvent ):void {

			// tr.ace( event.currentTarget );
			// tr.ace( event.target );
			
			if ( event.target is has_tooltip )
				this.tooltip.draw( event.target as has_tooltip );
			else
				this.tooltip.hide();
		}
		
		private function mouse_move_proximity( event:MouseEvent ):void {

			//tr.ace( event.currentTarget );
			//tr.ace( event.target );
			
			var elements:Array = this.obs.mouse_move_proximity( this.mouseX, this.mouseY );
			this.tooltip.closest( elements );
		}
		
		private function mouse_move_closest( event:Event ):void {
			
			var elements:Array = this.obs.closest_2( this.mouseX, this.mouseY );
			this.tooltip.closest( elements );
		}
		
		private function activateHandler(event:Event):void {
            tr.aces("activateHandler:", event);
			tr.aces("stage", this.stage);
        }

        private function resizeHandler(event:Event):void {
            // tr.ace("resizeHandler: " + event);
            this.resize();
        }
		
		//
		// pie charts are simpler to resize, they don't
		// have all the extras (X,Y axis, legends etc..)
		//
		private function resize_pie(): ScreenCoordsBase {
			
			// should this be here?
			this.addEventListener(MouseEvent.MOUSE_MOVE, this.mouseMove);
			
			this.background.resize();
			this.title.resize();
			
			// this object is used in the mouseMove method
			this.sc = new ScreenCoords(
				this.title.get_height(), 0, this.stage.stageWidth, this.stage.stageHeight,
				null, null, null, 0, 0, false );
			this.obs.resize( sc );
			
			return sc;
		}
		
		//
		//
		private function resize_radar(): ScreenCoordsBase {
			
			this.addEventListener(MouseEvent.MOUSE_MOVE, this.mouseMove);
			
			this.background.resize();
			this.title.resize();
			this.keys.resize( 0, this.title.get_height() );
				
			var top:Number = this.title.get_height() + this.keys.get_height();
			
			// this object is used in the mouseMove method
			var sc:ScreenCoordsRadar = new ScreenCoordsRadar(top, 0, this.stage.stageWidth, this.stage.stageHeight);
			
			sc.set_range( this.radar_axis.get_range() );
			// 0-4 = 5 spokes
			sc.set_angles( this.obs.get_max_x()-this.obs.get_min_x()+1 );
			
			// resize the axis first because they may
			// change the radius (to fit the labels on screen)
			this.radar_axis.resize( sc );
			this.obs.resize( sc );
			
			return sc;
		}
		
		private function resize():void {
			//
			// the chart is async, so we may get this
			// event before the chart has loaded, or has
			// partly loaded
			//
			if ( !this.ok )
				return;			// <-- something is wrong
		
			var sc:ScreenCoordsBase;
			
			if ( this.radar_axis != null )
				sc = this.resize_radar();
			else if ( this.obs.has_pie() )
				sc = this.resize_pie();
			else
				sc = this.resize_chart();
			
			if( this.menu )
				this.menu.resize();
			
			// tell the web page that we have resized our content
			if( this.chart_parameters['id'] )
				this.callExternalCallback("ofc_resize", sc.left, sc.width, sc.top, sc.height, this.chart_parameters['id']);
			else
				this.callExternalCallback("ofc_resize", sc.left, sc.width, sc.top, sc.height);
				
			sc = null;
		}
			
		private function resize_chart(): ScreenCoordsBase {
			//
			// we want to show the tooltip closest to
			// items near the mouse, so hook into the
			// mouse move event:
			//
			this.addEventListener(MouseEvent.MOUSE_MOVE, this.mouseMove);
	
			// FlashConnect.trace("stageWidth: " + stage.stageWidth + " stageHeight: " + stage.stageHeight);
			this.background.resize();
			this.title.resize();
			
			var left:Number   = this.y_legend.get_width() /*+ this.y_labels.get_width()*/ + this.y_axis.get_width();
			
			this.keys.resize( left, this.title.get_height() );
				
			var top:Number = this.title.get_height() + this.keys.get_height();
			
			var bottom:Number = this.stage.stageHeight;
			bottom -= (this.x_legend.get_height() + this.x_axis.get_height());
			
			var right:Number = this.stage.stageWidth;
			right -= this.y_legend_2.get_width();
			//right -= this.y_labels_right.get_width();
			right -= this.y_axis_right.get_width();

			// this object is used in the mouseMove method
			this.sc = new ScreenCoords(
				top, left, right, bottom,
				this.y_axis.get_range(),
				this.y_axis_right.get_range(),
				this.x_axis.get_range(),
				this.x_axis.first_label_width(),
				this.x_axis.last_label_width(),
				false );

			this.sc.set_bar_groups(this.obs.groups);
				
			this.x_axis.resize( sc,
				// can we remove this:
				this.stage.stageHeight-(this.x_legend.get_height()+this.x_axis.labels.get_height())	// <-- up from the bottom
				);
			this.y_axis.resize( this.y_legend.get_width(), sc );
			this.y_axis_right.resize( 0, sc );
			this.x_legend.resize( sc );
			this.y_legend.resize();
			this.y_legend_2.resize();
			
			this.obs.resize( sc );
			
			
			// Test code:
			this.dispatchEvent(new Event("on-show"));
			
			
			return sc;
		}
		
		private function mouseOut(event:Event):void {
			
			if( this.tooltip != null )
				this.tooltip.hide();
			
			if( this.obs != null )
				this.obs.mouse_out();
        }
		
		//
		// an external interface, used by javascript to
		// pass in a JSON string
		//
		public function load( s:String ):void {
			this.parse_json( s );
		}

		//
		// JSON is loaded from an external URL
		//
		private function xmlLoaded(event:Event):void {
			var loader:URLLoader = URLLoader(event.target);
			this.parse_json( loader.data );
		}
		
		//
		// we have data! parse it and make the chart
		//
		private function parse_json( json_string:String ):void {
			
			// tr.ace(json_string);
			
			var ok:Boolean = false;
			
			try {
				var json:Object = JSON.deserialize( json_string );
				ok = true;
			}
			catch (e:Error) {
				// remove the 'loading data...' msg:
				this.removeChildAt(0);
				this.addChild( new JsonErrorMsg( json_string as String, e ) );
			}
			
			//
			// don't catch these errors:
			//
			if( ok )
			{
				// remove 'loading data...' msg:
				this.removeChildAt(0);
				this.build_chart( json );
				
				// force this to be garbage collected
				json = null;
			}
			
			json_string = '';
		}
		
		private function build_chart( json:Object ):void {
			
			tr.ace('----');
			tr.ace(JSON.serialize(json));
			tr.ace('----');
			
			if ( this.obs != null )
				this.die();
			
			// init singletons:
			NumberFormat.getInstance( json );
			NumberFormat.getInstanceY2( json );

			this.tooltip	= new Tooltip( json.tooltip )

			var g:Global = Global.getInstance();
			g.set_tooltip_string( this.tooltip.tip_text );
		
			//
			// these are common to both X Y charts and PIE charts:
			this.background	= new Background( json );
			this.title		= new Title( json.title );
			//
			this.addChild( this.background );
			//
			
			if ( JsonInspector.is_radar( json ) ) {
				
				this.obs = Factory.MakeChart( json );
				this.radar_axis = new RadarAxis( json.radar_axis );
				this.keys = new Keys( this.obs );
				
				this.addChild( this.radar_axis );
				this.addChild( this.keys );
				
			}
			else if ( !JsonInspector.has_pie_chart( json ) )
			{
				this.build_chart_background( json );
			}
			else
			{
				// this is a PIE chart
				this.obs = Factory.MakeChart( json );
				// PIE charts default to FOLLOW tooltips
				this.tooltip.set_tip_style( Tooltip.NORMAL );
			}

			// these are added in the Flash Z Axis order
			this.addChild( this.title );
			for each( var set:Sprite in this.obs.sets )
				this.addChild( set );
			this.addChild( this.tooltip );

			if (json['menu'] != null) {
				this.menu = new Menu('99', json['menu']);
				this.addChild(this.menu);
			}
			
			this.ok = true;
			this.resize();
			
			
		}
		
		//
		// PIE charts don't have this.
		// build grid, axis, legends and key
		//
		private function build_chart_background( json:Object ):void {
			//
			// This reads all the 'elements' of the chart
			// e.g. bars and lines, then creates them as sprites
			//
			this.obs			= Factory.MakeChart( json );
			//
			this.x_legend		= new XLegend( json.x_legend );			
			this.y_legend		= new YLegendLeft( json );
			this.y_legend_2		= new YLegendRight( json );
			this.x_axis			= new XAxis( json, this.obs.get_min_x(), this.obs.get_max_x() );
			this.y_axis			= new YAxisLeft();
			this.y_axis_right	= new YAxisRight();
			
			// access all our globals through this:
			var g:Global = Global.getInstance();
			// this is needed by all the elements tooltip
			g.x_labels = this.x_axis.labels;
			g.x_legend = this.x_legend;

			//
			// pick up X Axis labels for the tooltips
			// 
			this.obs.tooltip_replace_labels( this.x_axis.labels );
			//
			//
			//
			
			this.keys = new Keys( this.obs );
			
			this.addChild( this.x_legend );
			this.addChild( this.y_legend );
			this.addChild( this.y_legend_2 );
			this.addChild( this.y_axis );
			this.addChild( this.y_axis_right );
			this.addChild( this.x_axis );
			this.addChild( this.keys );
			
			// now these children have access to the stage,
			// tell them to init
			this.y_axis.init(json);
			this.y_axis_right.init(json);
		}
		
		/**
		 * Remove all our referenced objects
		 */
		private function die():void {
			this.obs.die();
			this.obs = null;
			
			if ( this.tooltip != null ) this.tooltip.die();
			
			if ( this.x_legend != null )	this.x_legend.die();
			if ( this.y_legend != null )	this.y_legend.die();
			if ( this.y_legend_2 != null )	this.y_legend_2.die();
			if ( this.y_axis != null )		this.y_axis.die();
			if ( this.y_axis_right != null ) this.y_axis_right.die();
			if ( this.x_axis != null )		this.x_axis.die();
			if ( this.keys != null )		this.keys.die();
			if ( this.title != null )		this.title.die();
			if ( this.radar_axis != null )	this.radar_axis.die();
			if ( this.background != null )	this.background.die();
			
			this.tooltip = null;
			this.x_legend = null;
			this.y_legend = null;
			this.y_legend_2 = null;
			this.y_axis = null;
			this.y_axis_right = null;
			this.x_axis = null;
			this.keys = null;
			this.title = null;
			this.radar_axis = null;
			this.background = null;
			
			while ( this.numChildren > 0 )
				this.removeChildAt(0);
		
			if ( this.hasEventListener(MouseEvent.MOUSE_MOVE))
				this.removeEventListener(MouseEvent.MOUSE_MOVE, this.mouseMove);
			
			// do not force a garbage collection, it is not supported:
			// http://stackoverflow.com/questions/192373/force-garbage-collection-in-as3
		
		}
		
		private function build_right_click_menu(): void {
		
			var cm:ContextMenu = new ContextMenu();
			cm.addEventListener(ContextMenuEvent.MENU_SELECT, onContextMenuHandler);
			cm.hideBuiltInItems();

			// OFC CREDITS
			var fs:ContextMenuItem = new ContextMenuItem("Charts by Open Flash Chart [Version "+VERSION+"]" );
			fs.addEventListener(
				ContextMenuEvent.MENU_ITEM_SELECT,
				function doSomething(e:ContextMenuEvent):void {
					var url:String = "http://teethgrinder.co.uk/open-flash-chart-2/";
					var request:URLRequest = new URLRequest(url);
					flash.net.navigateToURL(request, '_blank');
				});
			cm.customItems.push( fs );
			
			var save_image_message:String = ( this.chart_parameters['save_image_message'] ) ? this.chart_parameters['save_image_message'] : 'Save Image Locally';
			
			var dl:ContextMenuItem = new ContextMenuItem(save_image_message);
			dl.addEventListener(ContextMenuEvent.MENU_ITEM_SELECT, this.saveImage);
			cm.customItems.push( dl );
			
			this.contextMenu = cm;
		}
		
		public function format_y_axis_label( val:Number ): String {
//			if( this._y_format != undefined )
//			{
//				var tmp:String = _root._y_format.replace('#val#',_root.format(val));
//				tmp = tmp.replace('#val:time#',_root.formatTime(val));
//				tmp = tmp.replace('#val:none#',String(val));
//				tmp = tmp.replace('#val:number#', NumberUtils.formatNumber (Number(val)));
//				return tmp;
//			}
//			else
				return NumberUtils.format(val,2,true,true,false);
		}


	}
	
}

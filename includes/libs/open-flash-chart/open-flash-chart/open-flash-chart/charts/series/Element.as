package charts.series {
	
	import charts.series.has_tooltip;
	import flash.display.Sprite;
	import string.Utils;
	import global.Global;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import caurina.transitions.Tweener;
	import caurina.transitions.Equations;
	import flash.geom.Point;
	import flash.net.URLRequest;
	import flash.net.navigateToURL;
	import flash.external.ExternalInterface;
	import elements.axis.XAxisLabels;
	
	public class Element extends Sprite implements has_tooltip {
		//
		// for line data
		//
		public var _x:Number;
		public var _y:Number;
		
		public var index:Number;
		protected var tooltip:String;
		private var link:String;
		public var is_tip:Boolean;
		
		public var line_mask:Sprite;
		protected var right_axis:Boolean;
		
		
		public function Element()
		{
			// elements don't change shape much, so lets
			// cache it
			this.cacheAsBitmap = true;
			this.right_axis = false;	
		}
		
		public function resize( sc:ScreenCoordsBase ):void {
			
			var p:flash.geom.Point = sc.get_get_x_from_pos_and_y_from_val( this._x, this._y, this.right_axis );
			this.x = p.x;
			this.y = p.y;
		}
		
		//
		// for tooltip closest - return the middle point
		//
		public function get_mid_point():flash.geom.Point {
			
			//
			// dots have x, y in the center of the dot
			//
			return new flash.geom.Point( this.x, this.y );
		}
		
		public function get_x(): Number {
			return this._x;
		}

		/**
		 * When true, this element is displaying a tooltip
		 * and should fade-in, pulse, or become active
		 * 
		 * override this to show hovered states.
		 * 
		 * @param	b
		 */
		public function set_tip( b:Boolean ):void {}
		
		
		//
		// if this is put in the Element constructor, it is
		// called multiple times for some reason :-(
		//
		protected function attach_events():void {
			
			// weak references so the garbage collector will kill them:
			this.addEventListener(MouseEvent.MOUSE_OVER, this.mouseOver, false, 0, true);
			this.addEventListener(MouseEvent.MOUSE_OUT, this.mouseOut, false, 0, true);
		}
		
		public function mouseOver(event:Event):void {
			this.pulse();
		}
		
		public function pulse():void {
			// pulse:
			Tweener.addTween(this, {alpha:.5, time:0.4, transition:"linear"} );
			Tweener.addTween(this, {alpha:1,  time:0.4, delay:0.4, onComplete:this.pulse, transition:"linear"});
		}

		public function mouseOut(event:Event):void {
			// stop the pulse, then fade in
			Tweener.removeTweens(this);
			Tweener.addTween(this, { alpha:1, time:0.4, transition:Equations.easeOutElastic } );
		}
		
		public function set_on_click( s:String ):void {
			this.link = s;
			this.buttonMode = true;
			this.useHandCursor = true;
			// weak references so the garbage collector will kill it:
			this.addEventListener(MouseEvent.MOUSE_UP, this.mouseUp, false, 0, true);
		}
		
		private function mouseUp(event:Event):void {
			
			if ( this.link.substring(0, 6) == 'trace:' ) {
				// for the test JSON files:
				tr.ace( this.link );
			}
			else if ( this.link.substring(0, 5) == 'http:' )
				this.browse_url( this.link );
			else if ( this.link.substring(0, 6) == 'https:' )
				this.browse_url( this.link );
			else {
				//
				// TODO: fix the on click to pass out the chart id:
				//
				// var ex:ExternalInterfaceManager = ExternalInterfaceManager.getInstance();
				// ex.callJavascript(this.link, this.index);
				ExternalInterface.call( this.link, this.index );
			}
		}
			
		private function browse_url( url:String ):void {
			var req:URLRequest = new URLRequest(this.link);
			try
			{
				navigateToURL(req);
			}
			catch (e:Error)
			{
				trace("Error opening link: " + this.link);
			}
		}
		
		public function get_tip_pos():Object {
			return {x:this.x, y:this.y};
		}
		
		
		//
		// this may be overriden by Collection objects
		//
		public function get_tooltip():String {
			return this.tooltip;
		}

		/**
		 * Replace #x_label# with the label. This is called
		 * after the X Label object has been built (see main.as)
		 * 
		 * @param	labels
		 */
		public function tooltip_replace_labels( labels:XAxisLabels ):void {
			
			tr.aces('x label', this._x, labels.get( this._x ));
			this.tooltip = this.tooltip.replace('#x_label#', labels.get( this._x ) );
		}
		
		/**
		 * Mem leaks
		 */
		public function die():void {
			
			if ( this.line_mask != null ) {
				
				this.line_mask.graphics.clear();
				this.line_mask = null;
			}
		}
	}
}
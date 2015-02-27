package charts {

	import flash.display.Sprite;
	import charts.series.Element;
	import flash.geom.Point;
	import elements.axis.XAxisLabels;
	
	public class Base extends Sprite {
		
		// accessed by the Keys object to display the key
		protected var key:String;
		protected var font_size:Number;
		
		
		public var colour:Number;
		public var line_width:Number;
		public var circle_size:Number;
		
		//
		// hold the Element values, for lines this is an
		// array of string Y values, for Candle it is an
		// array of string 'high,open,low,close' values,
		// for scatter it is 'x,y' etc...
		//
		public var values:Array;
		
		protected var axis:Number;
		
		public function Base()
		{}
		
		public function get_colour(): Number {
			return this.colour;
		}
		
		//
		// return an array of key info objects:
		//
		public function get_keys(): Object {
			
			var tmp:Array = [];
			
			// some lines may not have a key
			if( (this.font_size > 0) && (this.key != '' ) )
				tmp.push( { 'text':this.key, 'font-size':this.font_size, 'colour':this.get_colour() } );
				
			return tmp;
		}
		
		//
		// whatever sets of data that *may* be attached to the right
		// Y Axis call this to see if they are attached to it or not.
		// All lines, area and bar charts call this.
		//
		protected function which_axis_am_i_attached_to( data:Array, i:Number ): Number {
			//
			// some data sets are attached to the right
			// Y axis (and min max), in the future we
			// may support many axis
			//
			if( data['show_y2'] != undefined )
				if( data['show_y2'] != 'false' )
					if( data['y2_lines'] != undefined )
					{
						var tmp:Array = data.y2_lines.split(",");
						var pos:Number = tmp.indexOf( i.toString() );
						
						if ( pos == -1 )
							return 1;
						else
							return 2;	// <-- this line found in y2_lines, so it is attached to axis 2 (right axis)
					}
					
			return 1;
		}
			
		
		/**
		 * may be called by main.as to make the X Axis labels
		 * @return
		 */
		public function get_max_x():Number {
			
			var max:Number = Number.MIN_VALUE;
			//
			// count the non-mask items:
			//
			for ( var i:Number = 0; i < this.numChildren; i++ ) {
				if ( this.getChildAt(i) is Element ) {
					
					var e:Element = this.getChildAt(i) as Element
					max = Math.max( max, e.get_x() );
				}
			}
	
			return max;
		}
		
		public function get_min_x():Number {
			
			var min:Number = Number.MAX_VALUE;
			//
			// count the non-mask items:
			//
			for ( var i:Number = 0; i < this.numChildren; i++ ) {
				if ( this.getChildAt(i) is Element ) {
					
					var e:Element = this.getChildAt(i) as Element
					min = Math.min( min, e.get_x() );
				}
			}
	
			return min;
		}
		
		//
		// this should be overriden
		//
		public function resize( sc:ScreenCoordsBase ):void{}
		
		//public function draw( val:String, mc:Object ):void {}
		
		
		
		
		//
		// TODO: old remove when tooltips tested
		//
		public function closest( x:Number, y:Number ): Object {
			var shortest:Number = Number.MAX_VALUE;
			var closest:Element = null;
			var dx:Number;
			
			for ( var i:Number = 0; i < this.numChildren; i++ ) {
			
				//
				// some of the children will will mask
				// Sprites, so filter those out:
				//
				if( this.getChildAt(i) is Element ) {
					
					var e:Element = this.getChildAt(i) as Element;
					e.set_tip( false );
				
					dx = Math.abs( x -e.x );
				
					if( dx < shortest )	{
						shortest = dx;
						closest = e;
					}
				}
			}
			
			var dy:Number = 0;
			if( closest )
				dy = Math.abs( y - closest.y );
				
			return { element:closest, distance_x:shortest, distance_y:dy };
		}
		
		//
		// Line and bar charts will normally only have one
		// Element at any X position, but when using Radar axis
		// you may get many at any give X location.
		//
		// Scatter charts can have many items at the same X position
		//
		public function closest_2( x:Number, y:Number ): Array {

			// get the closest Elements X value
			var x:Number		= closest_x(x);
			var tmp:Array		= this.get_all_at_this_x_pos(x);
			
			// tr.aces('tmp.length', tmp.length);
			
			var closest:Array	= this.get_closest_y(tmp, y);
			var dy:Number = Math.abs( y - closest.y );
			// tr.aces('closest.length', closest.length);
			
			return closest;
		}
		
		//
		// get the X value of the closest points to the mouse
		//
		private function closest_x( x:Number ):Number {
			
			var closest:Number = Number.MAX_VALUE;
			var p:flash.geom.Point;
			var x_pos:Number;
			var dx:Number;
			
			for ( var i:Number = 0; i < this.numChildren; i++ ) {
			
				//
				// some of the children will will mask
				// Sprites, so filter those out:
				//
				if( this.getChildAt(i) is Element ) {
		
					var e:Element = this.getChildAt(i) as Element;
				
					p = e.get_mid_point();
					dx = Math.abs( x - p.x );

					if( dx < closest )	{
						closest = dx;
						x_pos = p.x;
					}
				}
			}
			
			return x_pos;
		}
		
		//
		// get all the Elements at this X position
		// BarStack overrides this
		//
		protected function get_all_at_this_x_pos( x:Number ):Array {
			
			var tmp:Array = new Array();
			var p:flash.geom.Point;
			var e:Element;
			
			for ( var i:Number = 0; i < this.numChildren; i++ ) {
			
				// some of the children will will mask
				// Sprites, so filter those out:
				//
				if( this.getChildAt(i) is Element ) {
		
					e = this.getChildAt(i) as Element;
					
					//
					// Point elements are invisible by default.
					//
					// Prevent invisible points from showing tooltips
					// For scatter line area
					//if (e.visible)
					//{
						p = e.get_mid_point();
						if ( p.x == x )
							tmp.push( e );
					//}
				}
			}
			
			return tmp;
		}
		
		//
		// scatter charts may have many Elements in the same
		// x, y location
		//
		private function get_closest_y( elements:Array, y:Number):Array {
			
			var y_min:Number = Number.MAX_VALUE;
			var dy:Number;
			var closest:Array = new Array();
			var p:flash.geom.Point;
			var e:Element;
			
			// get min Y distance
			for each( e in elements ) {
				
				p = e.get_mid_point();
				dy = Math.abs( y - p.y );
				
				y_min = Math.min( dy, y_min );
			}
			
			// select all Elements at this Y pos
			for each( e in elements ) {
				
				p = e.get_mid_point();
				dy = Math.abs( y - p.y );
				if( dy == y_min )
					closest.push(e);
			}

			return closest;
		}
		
		//
		// scatter charts may have many Elements in the same
		// x, y location
		//
		public function mouse_proximity( x:Number, y:Number ): Array {
			
			var closest:Number = Number.MAX_VALUE;
			var p:flash.geom.Point;
			var i:Number;
			var e:Element;
			var mouse:flash.geom.Point = new flash.geom.Point(x, y);
			
			//
			// find the closest Elements
			//
			for ( i=0; i < this.numChildren; i++ ) {
			
				// filter mask Sprites
				if( this.getChildAt(i) is Element ) {
		
					e = this.getChildAt(i) as Element;
					closest = Math.min( flash.geom.Point.distance(e.get_mid_point(), mouse), closest );
				}
			}
			
			//
			// grab all Elements at this distance
			//
			var close:Array = [];
			for ( i=0; i < this.numChildren; i++ ) {
			
				// filter mask Sprites
				if( this.getChildAt(i) is Element ) {
		
					e = this.getChildAt(i) as Element;
					if ( flash.geom.Point.distance(e.get_mid_point(), mouse) == closest )
						close.push(e);
				}
			}
			
			return close;
		}
		
		
		
		//
		// this is a backup function so if the mouse leaves the
		// movie for some reason without raising the mouse
		// out event (this happens if the user is wizzing the mouse about)
		//
		public function mouse_out():void {
			for ( var i:Number = 0; i < this.numChildren; i++ ) {
				
				// filter out the mask elements in line charts
				if( this.getChildAt(i) is Element ) {
					
					var e:Element = this.getChildAt(i) as Element;
					e.set_tip(false);
				}
			}
		}
		
		
		//
		// index of item (bar, point, pie slice, horizontal bar) may be used
		// to look up its X value (bar,point) or Y value (H Bar) or used as
		// the sequence number (Pie)
		//
		protected function get_element( index:Number, value:Object ): Element {
			return null;
		}
		
		public function add_values():void {
			
			// keep track of the X position (column)
			var index:Number = 0;
			
			for each ( var val:Object in this.values )
			{
				var tmp:Element;
				
				//
				// TODO: fix or document what is happening in link-null-bug.txt
				//
				
				// filter out the 'null' values
				if( val != null )
				{
					tmp = this.get_element( index, val );
					
					if( tmp.line_mask != null )
						this.addChild( tmp.line_mask );
						
					this.addChild( tmp );
				}
				
				index++;
			}
		}
		
		/**
		 * See ObjectCollection tooltip_replace_labels
		 * 
		 * @param	labels
		 */
		public function tooltip_replace_labels( labels:XAxisLabels ):void {
			for ( var i:Number = 0; i < this.numChildren; i++ ) {
				
				// filter out the mask elements in line charts
				if( this.getChildAt(i) is Element ) {
					
					var e:Element = this.getChildAt(i) as Element;
					e.tooltip_replace_labels( labels );
				}
			}
		}
		
		public function die():void {
			
			for ( var i:Number = 0; i < this.numChildren; i++ )
				if ( this.getChildAt(i) is Element ) {
					
					var e:Element = this.getChildAt(i) as Element;
					e.die();
				}
			
			while ( this.numChildren > 0 )
				this.removeChildAt(0);
		}
	}
}
package charts {
	import charts.series.Element;
	import flash.geom.Point;
	import elements.axis.XAxisLabels;

	public class ObjectCollection
	{
		public var sets:Array;
		public var groups:Number;
		
		public function ObjectCollection() {
			this.sets = new Array();
		}
		
		public function add( set:Base ): void {
			this.sets.push( set );
		}
		
		
		public function get_max_x():Number {
			
			var max:Number = Number.MIN_VALUE;

			for each( var o:Base in this.sets )
				max = Math.max( max, o.get_max_x() );

			return max;
		}
		
		public function get_min_x():Number {
			
			var min:Number = Number.MAX_VALUE;

			for each( var o:Base in this.sets )
				min = Math.min( min, o.get_min_x() );

			return min;
		}
		
		
		// get x, y co-ords of vals
		public function resize( sc:ScreenCoordsBase ):void {
			for each ( var o:Base in this.sets )
				o.resize( sc );
		}
		
		/**
		 * Tell each set to update the tooltip string and
		 * eplace all #x_label# with the label
		 * 
		 * @param	labels
		 */
		public function tooltip_replace_labels( labels:XAxisLabels ):void {
			
			for each ( var o:Base in this.sets )
				o.tooltip_replace_labels( labels );			
		}
		
		public function mouse_out():void {
			for each( var s:Base in this.sets )
				s.mouse_out();
		}
		
		
		private function closest( x:Number, y:Number ):Element {
			var o:Object;
			var s:Base;
			
			// get closest points from each data set
			var closest:Array = new Array();
			for each( s in this.sets )
				closest.push( s.closest( x, y ) );
			
			// find closest point along X axis
			var min:Number = Number.MAX_VALUE;
			for each( o in closest )
				min = Math.min( min, o.distance_x );
				
			//
			// now select all points that are the
			// min (see above) distance along the X axis
			//
			var xx:Object = {element:null, distance_x:Number.MAX_VALUE, distance_y:Number.MAX_VALUE };
			for each( o in closest ) {
				
				if( o.distance_x == min )
				{
					// these share the same X position, so choose
					// the closest to the mouse in the Y
					if( o.distance_y < xx.distance_y )
						xx = o;
				}
			}
			
			// pie charts may not return an element
			if( xx.element )
				xx.element.set_tip( true );
				
			return xx.element;
		}
		
		/*
		
		hollow
		  line --> ------O---------------O-----
				
			             +-----+
			             |  B  |
			       +-----+     |   +-----+
			       |  A  |     |   |  C  +- - -
			       |     |     |   |     |  D
			       +-----+-----+---+-----+- - -
			                1    2
			
		*/
		public function mouse_move( x:Number, y:Number ):Element {
			//
			// is the mouse over, above or below a
			// bar or point? For grouped bar charts,
			// two bars will share an X co-ordinate
			// and be the same distance from the
			// mouse. For example, if the mouse is
			// in position 1 in diagram above. This
			// filters out all items that are not
			// above or below the mouse:
			//
			var e:Element = null;// this.inside__(x, y);
			
			if ( !e )
			{
				//
				// no Elements are above or below the mouse,
				// so we select the BEST item to show (mouse
				// is in position 2)
				//
				e = this.closest(x, y);
			}
			
			return e;
		}
		
		
		//
		// Usually this will return an Array of one Element to
		// the Tooltip, but some times 2 (or more) Elements will
		// be on top of each other
		//
		public function closest_2( x:Number, y:Number ):Array {

			var e:Element;
			var s:Base;
			var p:flash.geom.Point;
			
			//
			// get closest points from each data set
			//
			var closest:Array = new Array();
			for each( s in this.sets ) {
				
				var tmp:Array = s.closest_2( x, y );
				for each( e in tmp )
					closest.push( e );
			}
			
			//
			// find closest point along X axis
			// different sets may return Elements
			// in different X locations
			//
			var min_x:Number = Number.MAX_VALUE;
			for each( e in closest ) {
				
				p = e.get_mid_point();
				min_x = Math.min( min_x, Math.abs( x - p.x ) );
			}
			
			//
			// filter out the Elements that
			// are too far away along the X axis
			//
			var good_x:Array = new Array();
			for each( e in closest ) {
				
				p = e.get_mid_point();
				if ( Math.abs( x - p.x ) == min_x )
					good_x.push( e );
			}
			
			//
			// now get min_y from filtered array
			//
			var min_y:Number = Number.MAX_VALUE;
			for each( e in good_x ) {
				
				p = e.get_mid_point();
				min_y = Math.min( min_y, Math.abs( y - p.y ) );
			}
			
			//
			// now filter out any that are not min_y
			//
			var good_x_and_y:Array = new Array();
			for each( e in good_x ) {
				
				p = e.get_mid_point();
				if ( Math.abs( y - p.y ) == min_y )
					good_x_and_y.push( e );
			}

			return good_x_and_y;
		}
		
		//
		// find the closest point to the mouse
		//
		public function mouse_move_proximity( x:Number, y:Number ):Array {
			var e:Element;
			var s:Base;
			var p:flash.geom.Point;
			
			//
			// get closest points from each data set
			//
			var closest:Array = new Array();
			for each( s in this.sets ) {
				
				var tmp:Array = s.mouse_proximity( x, y );
				for each( e in tmp )
					closest.push( e );
			}
			
			//
			// find the min distance to these
			//
			var min_dist:Number = Number.MAX_VALUE;
			var mouse:flash.geom.Point = new flash.geom.Point(x, y);
			for each( e in closest ) {
				min_dist = Math.min( flash.geom.Point.distance(e.get_mid_point(), mouse), min_dist );
			}
			
			// keep these closest Elements
			var close:Array = [];
			for each( e in closest ) {
				if ( flash.geom.Point.distance(e.get_mid_point(), mouse) == min_dist )
					close.push( e );
			}
			
			return close;
		}
		
		//
		// are we resizing a PIE chart?
		//
		public function has_pie():Boolean {
			
			if ( this.sets.length > 0 && ( this.sets[0] is Pie ) )
				return true;
			else
				return false;
		}
		
		/**
		 * To stop memory leaks we explicitly kill all
		 * our children
		 */
		public function die():void {

			for each( var o:Base in this.sets )
				o.die();
		}
	}
}
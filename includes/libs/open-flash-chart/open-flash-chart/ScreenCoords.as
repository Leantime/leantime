package {
	import flash.geom.Point;
	import charts.series.dots.Point;
	
	public class ScreenCoords extends ScreenCoordsBase
	{
		private var x_range:Range;
		private var y_range:Range;
		private var y_right_range:Range;
		
		// position of the zero line
		//public var zero:Number=0;
		//public var steps:Number=0;
		
		// tick_offset is set by 3D axis
		public var tick_offset:Number;
		private var x_offset:Boolean;
		private var y_offset:Boolean;
		private var bar_groups:Number;
	
		
		public function ScreenCoords( top:Number, left:Number, right:Number, bottom:Number,
							y_axis_range:Range,
							y_axis_right_range:Range,
							x_axis_range:Range,
							x_left_label_width:Number, x_right_label_width:Number,
							three_d:Boolean )
		{
			super( top, left, right, bottom );
			
			var tmp_left:Number = left;
			
			this.x_range = x_axis_range;
			this.y_range = y_axis_range;
			this.y_right_range = y_axis_right_range;
			
			// tr.ace( '-----' );
			// tr.ace( this.x_range.count() );
			// tr.ace( this.y_range.count() );
			
			
			if( x_range ) {
				right = this.jiggle( left, right, x_right_label_width, x_axis_range.count() );
				tmp_left = this.shrink_left( left, right, x_left_label_width, x_axis_range.count() );
			}
			
			this.top = top;
			this.left = Math.max(left, tmp_left);
			
			// round this down to the nearest int:
			this.right = Math.floor( right );
			this.bottom = bottom;
			this.width = this.right-this.left;
			this.height = bottom-top;
			
			if( three_d )
			{
				// tell the box object that the
				// X axis labels need to be offset
				this.tick_offset = 12;
			}
			else
				this.tick_offset = 0;
			
			//
			//  x_offset:
			//
			//   False            True
			//
			//  |               |
			//  |               |
			//  |               |
			//  +--+--+--+      |-+--+--+--+-+
			//  0  1  2  3        0  1  2  3
			//
	
			// PIE charts don't have these:
			if( x_axis_range ) {
				this.x_offset = x_axis_range.offset;
			}
			if( y_axis_range ) {
				// tr.aces( 'YYYY', y_axis_range.offset );
				this.y_offset = y_axis_range.offset;
			}
   				
			this.bar_groups = 1;
		}
		
		//
		// if the last X label is wider than the chart area, the last few letters will
		// be outside the drawing area. So we make the chart width smaller so the label
		// will fit into the screen.
		//
		//DZ: this implementation chops off the last label on scatter charts because it
		//    assumes the label is centered on the last "item" (like a bar) instead of 
		//    at the max edge of the plot.
		public function jiggle_original( left:Number, right:Number, x_label_width:Number, count:Number ): Number {
			var r:Number = 0;

			if( x_label_width != 0 )
			{
				var item_width:Number = (right-left) / count;
				r = right - (item_width / 2);
				var new_right:Number = right;
				
				// while the right most X label is off the edge of the
				// Stage, move the box.right - 1
				while( r+(x_label_width/2) > right )
				{
					new_right -= 1;
					// changing the right also changes the item_width:
					item_width = (new_right-left) / count;
					r = new_right-(item_width/2);
				}
				right = new_right;
			}
			return right;
		}
		
		//DZ: this implementation probably add white space on the right side of a
		//    non-scatter type plot because it assumes that the label is centered at
		//    the max edge of the plot instead of centered on the last "item" 
		//    (like a bar)
		public function jiggle( left:Number, right:Number, x_label_width:Number, count:Number ): Number {
			return right - (x_label_width / 2);
		}
		
		//
		// if the left label is truncated, shrink the box until
		// it fits onto the screen
		//
		public function shrink_left( left:Number, right:Number, x_label_width:Number, count:Number ): Number {
			var pos:Number = 0;

			if( x_label_width != 0 )
			{
				var item_width:Number = (right-left) / count;
				pos = left+(item_width/2);
				var new_left:Number = left;
				
				// while the left most label is hanging off the Stage
				// move the box.left in one pixel:
				while( pos-(x_label_width/2) < 0 )
				{
					new_left += 1;
					// changing the left also changes the item_width:
					item_width = (right-new_left) / count;
					pos = new_left+(item_width/2);
				}
				left = new_left;
			}
			
			return left;
			
		}
		
		//
		// the bottom point of a bar:
		//   min=-100 and max=100, use b.zero
		//   min = 10 and max = 20, use b.bottom
		//
		public override function get_y_bottom( right_axis:Boolean = false ):Number
		{
			//
			// may have min=10, max=20, or
			// min = 20, max = -20 (upside down chart)
			//
			var r:Range = right_axis ? this.y_right_range : this.y_range;
			
			var min:Number = r.min;
			var max:Number = r.max;
			min = Math.min( min, max );
			
			return this.get_y_from_val( Math.max(0,min), right_axis );
		}
		
		// takes a value and returns the screen Y location
		public function getY_old( i:Number, right_axis:Boolean ):Number
		{
			var r:Range = right_axis ? this.y_right_range : this.y_range;
			
			var steps:Number = this.height / (r.count());// ( right_axis ));
			
			// find Y pos for value=zero
			var y:Number = this.bottom-(steps*(r.min*-1));
			
			// move up (-Y) to our point (don't forget that y_min will shift it down)
			y -= i*steps;
			return y;
		}
		
		//
		// takes a value and returns the screen Y location
		// what is the Y range?
		//
		// Horizontal bar charts are offset. Note:
		//   step = 1
		//   and step/2 is offset at the bottom and top
		// so we add 1*step so we can calculate:
		//
		//   offset = true 
		//
		//     |
		//  X -|==========
		//     |
		//  Y -|===
		//     |
		//  Z -|========
		//     +--+--+--+--+--+--
		//
		// offset = false
		//
		//  2 -|
		//     |
		//  1 -|  0--0--0--0--0
		//     |
		//  0 -+--+--+--+--+--+--
		//
		public override function get_y_from_val( i:Number, right_axis:Boolean = false ):Number {
			
			var r:Range = right_axis ? this.y_right_range : this.y_range;
			
			var steps:Number = this.height / r.count();
			
			// tr.ace( 'off' );
			// tr.ace( this.y_offset.offset );
			// tr.ace( count );
			
			var tmp:Number = 0;
			if( this.y_offset )
				tmp = (steps / 2);
				
			// move up (-Y) to our point (don't forget that y_min will shift it down)
			return this.bottom-tmp-(r.min-i)*steps*-1;
		}
		
		public override function get_get_x_from_pos_and_y_from_val( index:Number, y:Number, right_axis:Boolean = false ):flash.geom.Point {
			
			return new flash.geom.Point(
				this.get_x_from_pos( index ),
				this.get_y_from_val( y, right_axis ) );
		}
		
		public function width_():Number
		{
			return this.right-this.left_();
		}
		
		private function left_():Number
		{
			var padding_left:Number = this.tick_offset;
			return this.left+padding_left;
		}
		
		//
		// Scatter and Horizontal Bar charts use this:
		//
		//   get the x position by value
		//  (e.g. what is the x position for -5 ?)
		//
		public override function get_x_from_val( i:Number ):Number {
			// Patch from DZ:
			var rev:Boolean = this.x_range.min > this.x_range.max;
			var count:Number = this.x_range.count();
			count += (rev && this.x_range.offset) ? -2 : 0;
			var item_width:Number = this.width_() / count;
			// end DZ
			
			
			var pos:Number = i-this.x_range.min;
			
			var tmp:Number = 0;
			if( this.x_offset )
				tmp = Math.abs(item_width/2);
				
			return this.left_()+tmp+(pos*item_width);
		}
		
		//
		// get the x location of the n'th item
		//
		public override function get_x_from_pos( i:Number ):Number {
			// DZ:
//			var item_width:Number = Math.abs(this.width_() / this.x_range.count());
			var rev:Boolean = this.x_range.min > this.x_range.max;
			var count:Number = this.x_range.count();
			count += (rev && this.x_range.offset) ? -2 : 0;
			var item_width:Number = Math.abs(this.width_() / count);
				
			var tmp:Number = 0;
			if( this.x_offset )
				tmp = (item_width/2);

			return this.left_()+tmp+(i*item_width);
		}
		
		//
		// get the position of the n'th X axis tick
		//
		public function get_x_tick_pos( i:Number ):Number
		{
			return this.get_x_from_pos(i) - this.tick_offset;
		}
	
		
		//
		// make a point object, using the absolute values (e.g. -5,-5 )
		/*
		public function make_point_2( x:Number, y:Number, right_axis:Boolean ):charts.Elements.Point
		{
			return new charts.Elements.Point(
				this.get_x_from_val( x ),
				this.get_y_from_val( y, right_axis )
				
				// whats this for?
				//,y
				);
		}*/
		
		public function set_bar_groups( n:Number ): void {
			this.bar_groups = n;
		}
		
		//
		// index: the n'th bar from the left
		//
		public function get_bar_coords( index:Number, group:Number ):Object {
			var item_width:Number = this.width_() / this.x_range.count();
			
			// the bar(s) have gaps between them:
			var bar_set_width:Number = item_width*0.8;
			
			// get the margin between sets of bars:
			var tmp:Number = 0;
			if( this.x_offset )
				tmp = item_width;
			
			// 1 bar == 100% wide, 2 bars = 50% wide each
			var bar_width:Number = bar_set_width / this.bar_groups;
			//bar_width -= 0.001;		// <-- hack so bars don't quite touch
			
			var bar_left:Number = this.left_()+((tmp-bar_set_width)/2);
			var left:Number = bar_left+(index*item_width);
			left += bar_width * group;
			
			return { x:left, width:bar_width };
		}
		
		public function get_horiz_bar_coords( index:Number, group:Number ):Object {
			
			// split the height into equal heights for each bar
			var bar_width:Number = this.height / this.y_range.count();
			
			// the bar(s) have gaps between them:
			var bar_set_width:Number = bar_width*0.8;
			
			// 1 bar == 100% wide, 2 bars = 50% wide each
			var group_width:Number = bar_set_width / this.bar_groups;
			
			var bar_top:Number = this.top+((bar_width-bar_set_width)/2);
			var top:Number = bar_top+(index*bar_width);
			top += group_width * group;
			
			return { y:top, width:group_width };
		}
		
		
		public function makePointHLC( x:Number, high:Number, close:Number, low:Number, right_axis:Boolean, group:Number, group_count:Number )
		:PointHLC {
	
			var item_width:Number = this.width_() / this.x_range.count();
			// the bar(s) have gaps between them:
			var bar_set_width:Number = item_width*1;

			// get the margin between sets of bars:
			var bar_left:Number = this.left_()+((item_width-bar_set_width)/2);
			// 1 bar == 100% wide, 2 bars = 50% wide each
			var bar_width:Number = bar_set_width/group_count;

			var left:Number = bar_left+(x*item_width);
			left += bar_width*group;

			return new PointHLC(
				left,
				this.get_y_from_val( high, right_axis ),
				this.get_y_from_val( close, right_axis ),
				this.get_y_from_val( low, right_axis ),
				high,
				bar_width
//				,close
				);
	
		}
	}
}
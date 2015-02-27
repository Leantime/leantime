package {
	import flash.geom.Point;
	
	public class ScreenCoordsBase
	{
		public var top:Number;
		public var left:Number;
		public var right:Number;
		public var bottom:Number;
		public var width:Number;
		public var height:Number;
		
		public function ScreenCoordsBase( top:Number, left:Number, right:Number, bottom:Number ) {
			
			this.top = top;
			this.left = left;
			this.right = right;
			this.bottom = bottom;
			
			this.width = this.right-this.left;
			this.height = bottom-top;
		}
		
		//
		// used by the PIE slices so the pie chart is
		// centered in the screen
		//
		public function get_center_x():Number {
			return (this.width / 2)+this.left;
		}

		public function get_center_y():Number {
			return (this.height / 2)+this.top;
		}
		
		public function get_y_from_val( i:Number, right_axis:Boolean = false ):Number { return -1; }
		
		public function get_x_from_val( i:Number ):Number { return -1;  }
		
		public function get_get_x_from_pos_and_y_from_val( index:Number, y:Number, right_axis:Boolean = false ):flash.geom.Point {
			return null;
		}
		
		public function get_y_bottom( right_axis:Boolean = false ):Number {
			return -1;
		}
		
		public function get_x_from_pos( i:Number ):Number { return -1; }
	}
}
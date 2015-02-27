package charts {
	import flash.display.Sprite;
	import flash.geom.Point;
	import string.Utils;
	
	public class Arrow extends Base {
		
		private var style:Object;
		
		public function Arrow( json:Object )
		{
			this.style = {
				start:				[],
				end:				[],
				colour:				'#808080',
				alpha:				0.5,
				'barb-length':		20
				
			};
			
			object_helper.merge_2( json, this.style );
			
			this.style.colour		= string.Utils.get_colour( this.style.colour );
			
//			for each ( var val:Object in json.values )
//				this.style.points.push( new flash.geom.Point( val.x, val.y ) );
		}
		
		public override function resize( sc:ScreenCoordsBase ): void {
			
			this.graphics.clear();
			this.graphics.lineStyle(1, this.style.colour, 1);
			
			this.graphics.moveTo(
				sc.get_x_from_val(this.style.start.x),
				sc.get_y_from_val(this.style.start.y));
			
			var x:Number = sc.get_x_from_val(this.style.end.x);
			var y:Number = sc.get_y_from_val(this.style.end.y);
			this.graphics.lineTo(x, y);
			
			var angle:Number = Math.atan2(
				sc.get_y_from_val(this.style.start.y) - y,
				sc.get_x_from_val(this.style.start.x) - x
				);
		
			var barb_length:Number = this.style['barb-length'];
			var barb_angle:Number = 0.34;

			//first point is end of one barb
			var a:Number = x + (barb_length * Math.cos(angle - barb_angle));
			var b:Number = y + (barb_length * Math.sin(angle - barb_angle));

			//final point is end of the second barb
			var c:Number = x + (barb_length * Math.cos(angle + barb_angle));
			var d:Number = y + (barb_length * Math.sin(angle + barb_angle));

			this.graphics.moveTo(x, y);
			this.graphics.lineTo(a, b);
			
			this.graphics.moveTo(x, y);
			this.graphics.lineTo(c, d);
			
		}
	}
}
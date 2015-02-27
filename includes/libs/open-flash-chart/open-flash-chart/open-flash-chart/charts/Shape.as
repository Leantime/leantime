package charts {
	import flash.display.Sprite;
	import flash.geom.Point;
	import string.Utils;
	
	public class Shape extends Base {
		
		private var style:Object;
		
		public function Shape( json:Object )
		{
			this.style = {
				points:				[],
				colour:				'#808080',
				alpha:				0.5
			};
			
			object_helper.merge_2( json, this.style );
			
			this.style.colour		= string.Utils.get_colour( this.style.colour );
			
			for each ( var val:Object in json.values )
				this.style.points.push( new flash.geom.Point( val.x, val.y ) );
		}
		
		public override function resize( sc:ScreenCoordsBase ): void {
			
			this.graphics.clear();
			//this.graphics.lineStyle( this.style.width, this.style.colour );
			this.graphics.lineStyle( 0, 0, 0 );
			this.graphics.beginFill( this.style.colour, this.style.alpha );
			
			var moved:Boolean = false;
			
			for each( var p:flash.geom.Point in this.style.points ) {
				if( !moved )
					this.graphics.moveTo( sc.get_x_from_val(p.x), sc.get_y_from_val(p.y) );
				else
					this.graphics.lineTo( sc.get_x_from_val(p.x), sc.get_y_from_val(p.y) );
				
				moved = true;
			}
			
			this.graphics.endFill();
		}
	}
}
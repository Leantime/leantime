package charts.series.bars {
	
	import flash.display.Sprite;
	import flash.geom.Point;
	import charts.series.bars.Base;
	
	public class Bar extends Base {
	
		public function Bar( index:Number, props:Properties, group:Number ) {
			
			super(index, props, group);
		}
		
		public override function resize( sc:ScreenCoordsBase ):void {
			
			var h:Object = this.resize_helper( sc as ScreenCoords );
			
			this.graphics.clear();
			this.graphics.beginFill( this.colour, 1.0 );
			this.graphics.moveTo( 0, 0 );
			this.graphics.lineTo( h.width, 0 );
			this.graphics.lineTo( h.width, h.height );
			this.graphics.lineTo( 0, h.height );
			this.graphics.lineTo( 0, 0 );
			this.graphics.endFill();
		}
		
	}
}
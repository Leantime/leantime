package charts.Elements {
	
	import flash.display.Sprite;
	import charts.series.bars.Base;
	
	public class PointBarFade extends Base
	{
		
		public function PointBarFade( index:Number, value:Object, colour:Number, group:Number )
		{
			var p:Properties = new Properties(value);
			super(index, p, group);
			//super(index,value,colour,'',0.6,group);
		}
		
		public override function resize( sc:ScreenCoordsBase ):void {
			/*
			var tmp:Object = sc.get_bar_coords(this._x,this.group);
			this.screen_x = tmp.x;
			this.screen_y = sc.get_y_from_val(this._y,axis==2);
			
			var bar_bottom:Number = sc.getYbottom( false );
			
			var top:Number;
			var height:Number;
			
			if( bar_bottom < this.screen_y ) {
				top = bar_bottom;
				height = this.screen_y-bar_bottom;
			}
			else
			{
				top = this.screen_y
				height = bar_bottom-this.screen_y;
			}
			*/
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
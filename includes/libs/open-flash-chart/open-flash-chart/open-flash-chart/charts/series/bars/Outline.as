package charts.series.bars {
	
	import flash.display.Sprite;
	import charts.series.bars.Base;
	
	public class Outline extends Base {
		private var outline:Number;
		
		public function Outline( index:Number, props:Properties, group:Number )	{
			
			super(index, props, group);
			//super(index, {'top':props.get('top')}, props.get_colour('colour'), props.get('tip'), props.get('alpha'), group);
			this.outline = props.get_colour('outline-colour');
		}
		
		public override function resize( sc:ScreenCoordsBase ):void {
			
			var h:Object = this.resize_helper( sc as ScreenCoords );
			
			this.graphics.clear();
			this.graphics.lineStyle(1, this.outline, 1);
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
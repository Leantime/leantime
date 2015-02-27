package charts.series.bars {
	
	import charts.series.bars.Base;
	import flash.display.Sprite;
	import flash.geom.Point;
	
	
	public class Stack extends Base {
		private var total:Number;
		
		public function Stack( index:Number, props:Properties, group:Number ) {
			
			// we are not passed a string value, the value
			// is set by the parent collection later
			this.total =  props.get('total');

			super(index, props, group);
		}

		protected override function replace_magic_values( t:String ): String {
			
			t = super.replace_magic_values(t);
			t = t.replace('#total#', NumberUtils.formatNumber( this.total ));
			
			return t;
		}
		
		public function replace_x_axis_label( t:String ): void {
			
			this.tooltip = this.tooltip.replace('#x_label#', t );
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
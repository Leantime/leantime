package charts.series.bars {
	
	import flash.filters.DropShadowFilter;
	import flash.geom.Matrix;
	import charts.series.bars.Base;
	
	public class Glass extends Base
	{
		
		public function Glass( index:Number, props:Properties, group:Number ) {
			
			super(index, props, group);
			//super(index, {'top':props.get('top')}, props.get_colour('colour'), props.get('tip'), props.get('alpha'), group);
			//super(index, style, style.colour, style.tip, style.alpha, group);
			
			var dropShadow:DropShadowFilter = new flash.filters.DropShadowFilter();
			dropShadow.blurX = 5;
			dropShadow.blurY = 5;
			dropShadow.distance = 3;
			dropShadow.angle = 45;
			dropShadow.quality = 2;
			dropShadow.alpha = 0.4;
			// apply shadow filter
			this.filters = [dropShadow];
		}
		
		public override function resize( sc:ScreenCoordsBase ):void {
			
			this.graphics.clear();
			var h:Object = this.resize_helper( sc as ScreenCoords );
			if (h.height == 0)
				return;
			
			this.bg( h.width, h.height, h.upside_down );
			this.glass( h.width, h.height, h.upside_down );
		}
		
		private function bg( w:Number, h:Number, upside_down:Boolean ):void {
			//
			var rad:Number = 7;
			if ( rad > ( w / 2 ) )
				rad = w / 2;
				
			this.graphics.lineStyle(0, 0, 0);// this.outline_colour, 100);
			this.graphics.beginFill(this.colour, 1);
			
			if( !upside_down )
			{
				// bar goes up
				this.graphics.moveTo(0+rad, 0);
				this.graphics.lineTo(w-rad, 0);
				this.graphics.curveTo(w, 0, w, rad);
				this.graphics.lineTo(w, h);
				this.graphics.lineTo(0, h);
				this.graphics.lineTo(0, 0+rad);
				this.graphics.curveTo(0, 0, 0+rad, 0);
			}
			else
			{
				// bar goes down
				this.graphics.moveTo(0, 0);
				this.graphics.lineTo(w, 0);
				this.graphics.lineTo(w, h-rad);
				this.graphics.curveTo(w,h,w-rad, h);
				this.graphics.lineTo(rad, h);
				this.graphics.curveTo(0,h,0, h-rad);
				this.graphics.lineTo(0, 0);
			}
			this.graphics.endFill();
		}
		
		private function glass( w:Number, h:Number, upside_down:Boolean ): void {
			var x:Number = 2;
			var y:Number = x;
			var width:Number = (w/2)-x;
			
			if( upside_down )
				y -= x;
			
			h -= x;
			
			this.graphics.lineStyle(0, 0, 0);
			//set gradient fill
			var colors:Array = [0xFFFFFF,0xFFFFFF];
			var alphas:Array = [0.3, 0.7];
			var ratios:Array = [0,255];
			//var matrix:Object = { matrixType:"box", x:x, y:y, w:width, h:height, r:(180/180)*Math.PI };
			//mc.beginGradientFill("linear", colors, alphas, ratios, matrix);
			var matrix:Matrix = new Matrix();
			matrix.createGradientBox(width, height, (180 / 180) * Math.PI );
			this.graphics.beginGradientFill('linear' /*GradientType.Linear*/, colors, alphas, ratios, matrix, 'pad'/*SpreadMethod.PAD*/ );
			
			var rad:Number = 4;
			var w:Number = width;
			
			if( !upside_down )
			{
				this.graphics.moveTo(x+rad, y);		// <-- top
				this.graphics.lineTo(x+w, y);
				this.graphics.lineTo(x+w, y+h);
				this.graphics.lineTo(x, y+h);
				this.graphics.lineTo(x, y+rad);
				this.graphics.curveTo(x, y, x+rad, y);
			}
			else
			{
				this.graphics.moveTo(x, y);
				this.graphics.lineTo(x+w, y);
				this.graphics.lineTo(x+w, y+h);
				this.graphics.lineTo(x + rad, y + h);
				this.graphics.curveTo(x, y+h, x, y+h-rad);
			}
			this.graphics.endFill();
		}
	}
}
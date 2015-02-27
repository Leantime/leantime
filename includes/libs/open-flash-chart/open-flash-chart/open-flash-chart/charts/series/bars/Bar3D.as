package charts.series.bars {
	
	import flash.display.Sprite;
	import flash.geom.Matrix;
	import flash.filters.DropShadowFilter;
	import charts.series.bars.Base;
	
	public class Bar3D extends Base {
		
		public function Bar3D( index:Number, props:Properties, group:Number ) {
			
			super(index, props, group);
			//super(index, style, style.colour, style.tip, style.alpha, group);
			//super(index, {'top':props.get('top')}, props.get_colour('colour'), props.get('tip'), props.get('alpha'), group);
			
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
	
		public override function resize( sc:ScreenCoordsBase  ):void {
			
			var h:Object = this.resize_helper( sc as ScreenCoords );
			
			this.graphics.clear();
			
			this.draw_top( h.width, h.height );
			this.draw_front( h.width, h.height );
			this.draw_side( h.width, h.height );
		}
		
		private function draw_top( w:Number, h:Number ):void {
			
			this.graphics.lineStyle(0, 0, 0);
			//set gradient fill
			
			var lighter:Number = Bar3D.Lighten( this.colour );
			
			var colors:Array = [this.colour,lighter];
			var alphas:Array = [1,1];
			var ratios:Array = [0,255];
			var matrix:Matrix = new Matrix();
			matrix.createGradientBox(w + 12, 12, (270 / 180) * Math.PI );
			this.graphics.beginGradientFill('linear' /*GradientType.Linear*/, colors, alphas, ratios, matrix, 'pad'/*SpreadMethod.PAD*/ );
			
			
			var y:Number = 0;
			if( h<0 )
				y = h;
			
			this.graphics.moveTo(0, y);
			this.graphics.lineTo(w, y);
			this.graphics.lineTo(w-12, y+12);
			this.graphics.lineTo(-12, y+12);
			this.graphics.endFill();
		}
		
		private function draw_front( w:Number, h:Number ):void {
			//
			var rad:Number = 7;
			
			var lighter:Number = Bar3D.Lighten( this.colour );

			// Darken a light color
			//var darker:Number = this.colour;
			//darker &= 0x7F7F7F;

			var colors:Array = [lighter,this.colour];
			var alphas:Array = [1,1];
			var ratios:Array = [0, 127];
			
			var matrix:Matrix = new Matrix();
			matrix.createGradientBox(w - 12, h+12, (90 / 180) * Math.PI );
			this.graphics.beginGradientFill('linear' /*GradientType.Linear*/, colors, alphas, ratios, matrix, 'pad'/*SpreadMethod.PAD*/ );
			
			this.graphics.moveTo(-12, 12);
			this.graphics.lineTo(-12, h+12);
			this.graphics.lineTo(w-12, h+12);
			this.graphics.lineTo(w-12, 12);
			this.graphics.endFill();
		}
		
		private function draw_side( w:Number, h:Number ):void {
			//
			var rad:Number = 7;
			
			var lighter:Number = Bar3D.Lighten( this.colour );
			
			var colors:Array = [this.colour,lighter];
			var alphas:Array = [1,1];
			var ratios:Array = [0,255];
			var matrix:Matrix = new Matrix();
			matrix.createGradientBox(w, h+12, (270 / 180) * Math.PI );
			this.graphics.beginGradientFill('linear' /*GradientType.Linear*/, colors, alphas, ratios, matrix, 'pad'/*SpreadMethod.PAD*/ );
			
			
			this.graphics.lineStyle(0, 0, 0);
			this.graphics.moveTo(w, 0);
			this.graphics.lineTo(w, h);
			this.graphics.lineTo(w-12, h+12);
			this.graphics.lineTo(w-12, 12);
			this.graphics.endFill();
		}
		
		//
		// JG: lighten a colour by splitting it
		//     into RGB, then adding a bit to each
		//     value...
		//
		public static function Lighten( col:Number ):Number {
			var rgb:Number = col; //decimal value for a purple color
			var red:Number = (rgb & 16711680) >> 16; //extacts the red channel
			var green:Number = (rgb & 65280) >> 8; //extacts the green channel
			var blue:Number = rgb & 255; //extacts the blue channel
			var p:Number = 2;
			red += red/p;
			if( red > 255 )
				red = 255;
				
			green += green/p;
			if( green > 255 )
				green = 255;
				
			blue += blue/p;
			if( blue > 255 )
				blue = 255;
				
			return red << 16 | green << 8 | blue;
		}
	}
}
package charts.Elements {
	import charts.Elements.PointDotBase;
	import flash.display.BlendMode;
	import flash.display.Graphics;
	import flash.display.Sprite;
	
	public class Star extends PointDotBase {
		
		public function Star( index:Number, style:Object ) {
			
			super( index, style );
			
			this.visible = true;
			
			this.graphics.clear();
			this.graphics.lineStyle( style.width, style.colour, 1);// style.alpha );
			var rotation:Number = isNaN(style['rotation']) ? 0 : style['rotation'];
			
			this.drawStar( this.graphics, style['dot-size'], rotation );
			
			var haloSize:Number = style['halo-size']+style['dot-size'];
			var s:Sprite = new Sprite();
			s.graphics.lineStyle( 0, 0, 0 );
			s.graphics.beginFill( 0, 1 );
			this.drawStar(s.graphics, haloSize, rotation );
			s.blendMode = BlendMode.ERASE;
			s.graphics.endFill();
			this.line_mask = s;
			
			this.attach_events();
			
		}
		
		private function calcXOnCircle(radius:Number, degrees:Number):Number
		{
			return radius * Math.cos(degrees / 180 * Math.PI);
		}
		
		private function calcYOnCircle(radius:Number, degrees:Number):Number
		{
			return radius * Math.sin(degrees / 180 * Math.PI);
		}
		
		private function drawStar( graphics:Graphics, radius:Number, rotation:Number ):void 
		{
			var angle:Number = 360 / 5;

			// Start at top point (unrotated)
			var degrees:Number = -90 + rotation;
			for (var i:int = 0; i <= 5; i++)
			{
				var x:Number = this.calcXOnCircle(radius, degrees);
				var y:Number = this.calcYOnCircle(radius, degrees);
				
				if (i == 0)
					graphics.moveTo(x, y);
				else
					graphics.lineTo(x, y);
					
				// Move 2 points clockwise
				degrees += (2 * angle);
			}
		}
	}
}


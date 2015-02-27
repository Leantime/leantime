package charts.series.dots {
	
	import flash.display.Sprite;
	import flash.display.Graphics;
	import flash.display.BlendMode;
	import charts.series.Element;
	import caurina.transitions.Tweener;
	import caurina.transitions.Equations;
	import string.Utils;
	import flash.geom.Point;
	
	public class star extends PointDotBase {
		
		public function star( index:Number, value:Properties ) {
			
			var colour:Number = string.Utils.get_colour( value.get('colour') );
			
			super( index, value );
			
			this.tooltip = this.replace_magic_values( value.get('tip') );
			this.attach_events();

			// if style.x is null then user wants a gap in the line
			//
			// I don't understand what this is doing...
			//
//			if (style.x == null)
//			{
//				this.visible = false;
//			}
//			else
//			{
				
				if (value.get('hollow'))
				{
					// Hollow - set the fill to the background color/alpha
					if (value.get('background-colour') != null)
					{
						var bgColor:Number = string.Utils.get_colour( value.get('background-colour') );
					}
					else
					{
						bgColor = colour;
					}
					
					this.graphics.beginFill(bgColor, value.get('background-alpha')); 
				}
				else
				{
					// set the fill to be the same color and alpha as the line
					this.graphics.beginFill( colour, value.get('alpha') );
				}

				this.graphics.lineStyle( value.get('width'), colour, value.get('alpha') );

				this.drawStar_2(this.graphics, this.radius, value.get('rotation'));
				// Check to see if part of the line needs to be erased
				if (value.get('halo-size') > 0)
				{
					var s:Sprite = new Sprite();
					s.graphics.lineStyle( 0, 0, 0 );
					s.graphics.beginFill( 0, 1 );
					this.drawStar_2(s.graphics, value.get('halo-size')+this.radius, value.get('rotation'));
					s.blendMode = BlendMode.ERASE;
					s.graphics.endFill();
					this.line_mask = s;
				}
//			}
			
		}
		
		
		public override function set_tip( b:Boolean ):void {
			if ( b )
			{
				if ( !this.is_tip )
				{
					Tweener.addTween(this, {scaleX:1.3, time:0.4, transition:"easeoutbounce"} );
					Tweener.addTween(this, {scaleY:1.3, time:0.4, transition:"easeoutbounce" } );
					if (this.line_mask != null)
					{
						Tweener.addTween(this.line_mask, {scaleX:1.3, time:0.4, transition:"easeoutbounce"} );
						Tweener.addTween(this.line_mask, {scaleY:1.3, time:0.4, transition:"easeoutbounce" } );
					}
				}
				this.is_tip = true;
			}
			else
			{
				Tweener.removeTweens(this);
				Tweener.removeTweens(this.line_mask);
				this.scaleX = 1;
				this.scaleY = 1;
				if (this.line_mask != null)
				{
					this.line_mask.scaleX = 1;
					this.line_mask.scaleY = 1;
				}
				this.is_tip = false;
			}
		}
		
		private function drawStar_2( aGraphics:Graphics, aRadius:Number, 
									aRotation:Number ):void 
		{
			var angle:Number = 360 / 10;
			
			// Start at top point (unrotated)
			var degrees:Number = -90 + aRotation;
			for (var ix:int = 0; ix < 11; ix++)
			{
				var rad:Number;
				rad = (ix % 2 == 0) ? aRadius : aRadius/2;
				var xVal:Number = calcXOnCircle(rad, degrees);
				var yVal:Number = calcYOnCircle(rad, degrees);
				if(ix == 0)
				{
					aGraphics.moveTo(xVal, yVal);
				}
				else
				{
					aGraphics.lineTo(xVal, yVal);
				}
				degrees += angle;
			}
		}
		
	}
}
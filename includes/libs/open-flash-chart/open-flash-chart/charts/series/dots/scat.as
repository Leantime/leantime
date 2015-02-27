package charts.series.dots {
	
	import flash.display.Sprite;
	import flash.display.Graphics;
	import flash.display.BlendMode;
	import charts.series.Element;
	import caurina.transitions.Tweener;
	import caurina.transitions.Equations;
	import string.Utils;
	
	public class scat extends PointDotBase {
		
		public function scat( style:Object ) {
			
			// scatter charts have x, y (not value):
			style.value = style.y;

			super( -99, new Properties({}) );// style );

			// override the basics in PointDotBase:
			this._x = style.x;
			this._y = style.y;
			this.visible = true;

			if (style.alpha == null)
				style.alpha = 1;

			this.tooltip = this.replace_magic_values( style.tip );
			this.attach_events();
			
			// if style.x is null then user wants a gap in the line
			if (style.x == null)
			{
				this.visible = false;
			}
			else
			{
				var haloSize:Number = isNaN(style['halo-size']) ? 0 : style['halo-size'];
				var isHollow:Boolean = style['hollow'];
				
				if (isHollow)
				{
					// Hollow - set the fill to the background color/alpha
					if (style['background-colour'] != null)
					{
						var bgColor:Number = string.Utils.get_colour( style['background-colour'] );
					}
					else
					{
						bgColor = style.colour;
					}
					var bgAlpha:Number = isNaN(style['background-alpha']) ? 0 : style['background-alpha'];
					
					this.graphics.beginFill(bgColor, bgAlpha); 
				}
				else
				{
					// set the fill to be the same color and alpha as the line
					this.graphics.beginFill( style.colour, style.alpha );
				}

				switch (style['type'])
				{
					case 'dot':
						this.graphics.lineStyle( 0, 0, 0 );
						this.graphics.beginFill( style.colour, style.alpha );
						this.graphics.drawCircle( 0, 0, style['dot-size'] );
						this.graphics.endFill();
						
						var s:Sprite = new Sprite();
						s.graphics.lineStyle( 0, 0, 0 );
						s.graphics.beginFill( 0, 1 );
						s.graphics.drawCircle( 0, 0, style['dot-size'] + haloSize );
						s.blendMode = BlendMode.ERASE;
						
						this.line_mask = s;
						break;

					case 'anchor':
						this.graphics.lineStyle( style.width, style.colour, style.alpha );
						var rotation:Number = isNaN(style['rotation']) ? 0 : style['rotation'];
						var sides:Number = Math.max(3, isNaN(style['sides']) ? 3 : style['sides']);
						this.drawAnchor(this.graphics, this.radius, sides, rotation);
						// Check to see if part of the line needs to be erased
						//trace("haloSize = ", haloSize);
						if (haloSize > 0)
						{
							haloSize += this.radius;
							s = new Sprite();
							s.graphics.lineStyle( 0, 0, 0 );
							s.graphics.beginFill( 0, 1 );
							this.drawAnchor(s.graphics, haloSize, sides, rotation);
							s.blendMode = BlendMode.ERASE;
							s.graphics.endFill();
							this.line_mask = s;
						}
						break;
					
					case 'bow':
						this.graphics.lineStyle( style.width, style.colour, style.alpha );
						rotation = isNaN(style['rotation']) ? 0 : style['rotation'];
						
						this.drawBow(this.graphics, this.radius, rotation);
						// Check to see if part of the line needs to be erased
						if (haloSize > 0)
						{
							haloSize += this.radius;
							s = new Sprite();
							s.graphics.lineStyle( 0, 0, 0 );
							s.graphics.beginFill( 0, 1 );
							this.drawBow(s.graphics, haloSize, rotation);
							s.blendMode = BlendMode.ERASE;
							s.graphics.endFill();
							this.line_mask = s;
						}
						break;

					case 'star':
						this.graphics.lineStyle( style.width, style.colour, style.alpha );
						rotation = isNaN(style['rotation']) ? 0 : style['rotation'];
						
						this.drawStar_2(this.graphics, this.radius, rotation);
						// Check to see if part of the line needs to be erased
						if (haloSize > 0)
						{
							haloSize += this.radius;
							s = new Sprite();
							s.graphics.lineStyle( 0, 0, 0 );
							s.graphics.beginFill( 0, 1 );
							this.drawStar_2(s.graphics, haloSize, rotation);
							s.blendMode = BlendMode.ERASE;
							s.graphics.endFill();
							this.line_mask = s;
						}
						break;
						
					default:
						this.graphics.drawCircle( 0, 0, this.radius );
						this.graphics.drawCircle( 0, 0, this.radius - 1 );
						this.graphics.endFill();
				}
			}
			
		}
		/*
		protected function replace_magic_values( t:String ): String {
			
			t = t.replace('#x#', NumberUtils.formatNumber(this._x));
			t = t.replace('#y#', NumberUtils.formatNumber(this._y));
			t = t.replace('#size#', NumberUtils.formatNumber(this.radius));
			return t;
		}
		*/
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
		
		public override function resize( sc:ScreenCoordsBase ): void {
			
			//
			// Look: we have a real X value, so get its screen location:
			//
			this.x = sc.get_x_from_val( this._x );
			this.y = sc.get_y_from_val( this._y, this.right_axis );
			
			// Move the mask so it is in the proper place also
			// this all needs to be moved into the base class
			if (this.line_mask != null)
			{
				this.line_mask.x = this.x;
				this.line_mask.y = this.y;
			}
		}

		
		private function drawAnchor( aGraphics:Graphics, aRadius:Number, 
										aSides:Number, aRotation:Number ):void 
		{
			if (aSides < 3) aSides = 3;
			if (aSides > 360) aSides = 360;
			var angle:Number = 360 / aSides;
			for (var ix:int = 0; ix <= aSides; ix++)
			{
				// Move start point to vertical axis (-90 degrees)
				var degrees:Number = -90 + aRotation + (ix % aSides) * angle;
				var xVal:Number = calcXOnCircle(aRadius, degrees);
				var yVal:Number = calcYOnCircle(aRadius, degrees);
				
				if (ix == 0)
				{
					aGraphics.moveTo(xVal, yVal);
				}
				else
				{
					aGraphics.lineTo(xVal, yVal);
				}
			}
		}
		
		private function drawBow( aGraphics:Graphics, aRadius:Number, 
									aRotation:Number ):void 
		{
			var angle:Number = 60;

			// Start at center point
			aGraphics.moveTo(0, 0);
			
			// Upper right side point (unrotated)
			var degrees:Number = -90 + aRotation + angle;
			var xVal:Number = calcXOnCircle(aRadius, degrees);
			var yVal:Number = calcYOnCircle(aRadius, degrees);
			aGraphics.lineTo(xVal, yVal);

			// Lower right side point (unrotated)
			degrees += angle;
			xVal = calcXOnCircle(aRadius, degrees);
			yVal = calcYOnCircle(aRadius, degrees);
			aGraphics.lineTo(xVal, yVal);
			
			// Back to the center
			aGraphics.lineTo(xVal, yVal);
			
			// Upper left side point (unrotated)
			degrees = -90 + aRotation - angle;
			xVal = calcXOnCircle(aRadius, degrees);
			yVal = calcYOnCircle(aRadius, degrees);
			aGraphics.lineTo(xVal, yVal);
			
			// Lower Left side point (unrotated)
			degrees -= angle;
			xVal = calcXOnCircle(aRadius, degrees);
			yVal = calcYOnCircle(aRadius, degrees);
			aGraphics.lineTo(xVal, yVal);

			// Back to the center
			aGraphics.lineTo(xVal, yVal);
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
		
		private function drawStar( aGraphics:Graphics, aRadius:Number, 
									aRotation:Number ):void 
		{
			var angle:Number = 360 / 5;

			// Start at top point (unrotated)
			var degrees:Number = -90 + aRotation;
			for (var ix:int = 0; ix <= 5; ix++)
			{
				var xVal:Number = calcXOnCircle(aRadius, degrees);
				var yVal:Number = calcYOnCircle(aRadius, degrees);
				if (ix == 0)
				{
					aGraphics.moveTo(xVal, yVal);
				}
				else
				{
					aGraphics.lineTo(xVal, yVal);
				}
				// Move 2 points clockwise
				degrees += (2 * angle);
			}
		}
	}
}
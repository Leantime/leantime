package charts {
	
	import flash.events.Event;
	import flash.events.MouseEvent;
	import charts.series.Element;
	import charts.series.dots.scat;
	import string.Utils;
	import flash.geom.Point;
	import flash.display.Sprite;
	import flash.display.BlendMode;
	import charts.series.dots.DefaultDotProperties;
	
	
	public class ScatterLine extends ScatterBase
	{
		public var stepgraph:Number = 0;
		public static const STEP_HORIZONTAL:Number = 1;
		public static const STEP_VERTICAL:Number = 2;

		public function ScatterLine( json:Object )
		{
			super(json);
			//
			// so the mask child can punch a hole through the line
			//
			this.blendMode = BlendMode.LAYER;
			//
			
			this.style = {
				values:			[],
				width:			2,
				colour:			'#3030d0',
				text:			'',		// <-- default not display a key
				'font-size':	12,
				stepgraph:		0,
				axis:			'left'
			};
			
			// hack: keep this incase the merge kills it, we'll
			// remove the merge later (and this hack)
			var tmp:Object = json['dot-style'];
			
			object_helper.merge_2( json, style );
			
			this.default_style = new DefaultDotProperties(
				json['dot-style'], this.style.colour, this.style.axis);
				
			this.style.colour = string.Utils.get_colour( style.colour );
			
			this.line_width = style.width;
			this.colour		= this.style.colour;
			this.key		= style.text;
			this.font_size	= style['font-size'];
			//this.circle_size = style['dot-size'];
			
			switch (style['stepgraph']) {
				case 'horizontal':
					stepgraph = STEP_HORIZONTAL;
					break;
				case 'vertical':
					stepgraph = STEP_VERTICAL;
					break;
			}
	
			this.values = style.values;
			this.add_values();
		}
		

		
		// Draw points...
		public override function resize( sc:ScreenCoordsBase ): void {
			
			// move the dots:
			super.resize( sc );
			
			this.graphics.clear();
			this.graphics.lineStyle( this.style.width, this.style.colour );
			
			//if( this.style['line-style'].style != 'solid' )
			//	this.dash_line(sc);
			//else
			this.solid_line(sc);
				
		}
		
		//
		// This is cut and paste from LineBase
		//
		public function solid_line( sc:ScreenCoordsBase ): void {
			
			var first:Boolean = true;
			var last_x:Number = 0;
			var last_y:Number = 0;

			var areaClosed:Boolean = true;
			var isArea:Boolean = false;
			var areaBaseX:Number = NaN;
			var areaBaseY:Number = NaN;
			var areaColour:Number = this.colour;
			var areaAlpha:Number = 0.4;
			var areaStyle:Object = this.style['area-style'];
			if (areaStyle != null)
			{
				isArea = true;
				if (areaStyle.x != null)
				{
					areaBaseX = areaStyle.x;
				}
				if (areaStyle.y != null)
				{
					areaBaseY = areaStyle.y;
				}
				if (areaStyle.colour != null)
				{
					areaColour = string.Utils.get_colour( areaStyle.colour );
				}
				if (areaStyle.alpha != null)
				{
					areaAlpha = areaStyle.alpha;
				}
				if (!isNaN(areaBaseX)) 
				{
					// Convert X Value to screen position
					areaBaseX = sc.get_x_from_val(areaBaseX);
				}
				if (!isNaN(areaBaseY)) 
				{
					// Convert Y Value to screen position
					areaBaseY = sc.get_y_from_val(areaBaseY);  // TODO: Allow for right Y-Axis??
				}
			}
			
			for ( var i:Number = 0; i < this.numChildren; i++ ) {
				
				var tmp:Sprite = this.getChildAt(i) as Sprite;
				
				//
				// filter out the line masks
				//
				if( tmp is Element )
				{
					var e:Element = tmp as Element;
					
					// tell the point where it is on the screen
					// we will use this info to place the tooltip
					e.resize( sc );
					if (!e.visible)
					{
						// Creates a gap in the plot and closes out the current area if defined
						if ((isArea) && (i > 0))
						{
							// draw an invisible line back to the base and close the fill
							areaX = isNaN(areaBaseX) ? last_x : areaBaseX;
							areaY = isNaN(areaBaseY) ? last_y : areaBaseY;
							this.graphics.lineStyle( 0, areaColour, 0 );
							this.graphics.lineTo(areaX, areaY);
							this.graphics.endFill();
							areaClosed = true;
						}
						first = true;
					}
					else if( first )
					{
						if (isArea)
						{
							// draw an invisible line from the base to the point
							var areaX:Number = isNaN(areaBaseX) ? e.x : areaBaseX;
							var areaY:Number = isNaN(areaBaseY) ? e.y : areaBaseY;
							// Begin the fill for the area
							this.graphics.beginFill(areaColour, areaAlpha);
							this.graphics.lineStyle( 0, areaColour, 0 );
							this.graphics.moveTo(areaX, areaY);
							this.graphics.lineTo(e.x, e.y);
							areaClosed = false;
							// change the line style back to normal
							this.graphics.lineStyle( this.style.width, this.style.colour, 1.0 );
						}
						else
						{
							// just move to the point
							this.graphics.moveTo(e.x, e.y);
						}
						first = false;
					}
					else
					{
						if (this.stepgraph == STEP_HORIZONTAL)
							this.graphics.lineTo(e.x, last_y);
						else if (this.stepgraph == STEP_VERTICAL)
							this.graphics.lineTo(last_x, e.y);
					
						this.graphics.lineTo(e.x, e.y);
					}
					last_x = e.x;
					last_y = e.y;
				}
			}

			// Close out the area if defined
			if (isArea && !areaClosed)
			{
				// draw an invisible line back to the base and close the fill
				areaX = isNaN(areaBaseX) ? last_x : areaBaseX;
				areaY = isNaN(areaBaseY) ? last_y : areaBaseY;
				this.graphics.lineStyle( 0, areaColour, 0 );
				this.graphics.lineTo(areaX, areaY);
				this.graphics.endFill();
			}
		}
		
	}
}
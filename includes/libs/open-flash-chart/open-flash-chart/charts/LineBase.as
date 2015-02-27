package charts {
	
	import charts.series.Element;
	import charts.series.dots.PointDotBase;
	import flash.display.Graphics;
	import flash.display.Sprite;
	import flash.display.BlendMode;
	import string.Utils;
	// import charts.series.dots.PointDot;
	import charts.series.dots.dot_factory;
	
	
	public class LineBase extends Base
	{
		// JSON style:
		protected var style:Object;
		
		
		public function LineBase() {}
		
		//
		// called from the BaseLine object
		//
		protected override function get_element( index:Number, value:Object ): Element {

//			var s:Object = this.merge_us_with_value_object( value );
			//
			// the width of the hollow circle is the same as the width of the line
			//

			var tmp:Properties;
			if( value is Number )
				tmp = new Properties( { value:value }, this.style['--dot-style']);
			else
				tmp = new Properties( value, this.style['--dot-style']);
				
			return dot_factory.make( index, tmp );
		}
		
		
		// Draw lines...
		public override function resize( sc:ScreenCoordsBase ): void {
			this.x = this.y = 0;

			this.graphics.clear();
			this.graphics.lineStyle( this.style.width, this.style.colour );
			
			if( this.style['line-style'].style != 'solid' )
				this.dash_line(sc);
			else
				this.solid_line(sc);
		
		}
		
		public function solid_line( sc:ScreenCoordsBase ): void {
			
			var first:Boolean = true;
			var i:Number;
			var tmp:Sprite;
			var x:Number;
			var y:Number;
			
			for ( i=0; i < this.numChildren; i++ ) {

				tmp = this.getChildAt(i) as Sprite;
				
				//
				// filter out the line masks
				//
				if( tmp is Element )
				{
					var e:Element = tmp as Element;
					
					// tell the point where it is on the screen
					// we will use this info to place the tooltip
					e.resize( sc );
					if( first )
					{
						this.graphics.moveTo(e.x, e.y);
						x = e.x;
						y = e.y;
						first = false;
					}
					else
						this.graphics.lineTo(e.x, e.y);
				}
			}
			
			if ( this.style.loop ) {
				// close the line loop (radar charts)
				this.graphics.lineTo(x, y);
			}
		}
		
		// Dashed lines by Arseni
		public function dash_line( sc:ScreenCoordsBase ): void {
			
			var first:Boolean = true;
			
			var prev_x:int = 0;
			var prev_y:int = 0;
			var on_len_left:Number = 0;
			var off_len_left:Number = 0;
			var on_len:Number = this.style['line-style'].on; //Stroke Length
			var off_len:Number = this.style['line-style'].off; //Space Length
			var now_on:Boolean = true;
			
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
					if( first )
					{
						this.graphics.moveTo(e.x, e.y);
						on_len_left = on_len;
						off_len_left = off_len;
						now_on = true;
						first = false;
						prev_x = e.x;
						prev_y = e.y;
						var x_tmp_1:Number = prev_x;
						var x_tmp_2:Number;
						var y_tmp_1:Number = prev_y;
						var y_tmp_2:Number;						
					}
					else {
						var part_len:Number = Math.sqrt((e.x - prev_x) * (e.x - prev_x) + (e.y - prev_y) * (e.y - prev_y) );
						var sinus:Number = ((e.y - prev_y) / part_len); 
						var cosinus:Number = ((e.x - prev_x) / part_len); 
						var part_len_left:Number = part_len;
						var inside_part:Boolean = true;
							
						while (inside_part) {
							//Draw Lines And spaces one by one in loop
							if ( now_on ) {
								//Draw line
								//If whole stroke fits
								if (  on_len_left < part_len_left ) {
									//Fits - draw whole stroke
									x_tmp_2 = x_tmp_1 + on_len_left * cosinus;
									y_tmp_2 = y_tmp_1 + on_len_left * sinus;
									x_tmp_1 = x_tmp_2;
									y_tmp_1 = y_tmp_2;
									part_len_left = part_len_left - on_len_left;
									now_on = false;
									off_len_left = off_len;															
								} else {
									//Does not fit - draw part of the stroke
									x_tmp_2 = e.x;
									y_tmp_2 = e.y;
									x_tmp_1 = x_tmp_2;
									y_tmp_1 = y_tmp_2;
									on_len_left = on_len_left - part_len_left;
									inside_part = false;									
								}
								this.graphics.lineTo(x_tmp_2, y_tmp_2);								
							} else {
								//Draw space
								//If whole space fits
								if (  off_len_left < part_len_left ) {
									//Fits - draw whole space
									x_tmp_2 = x_tmp_1 + off_len_left * cosinus;
									y_tmp_2 = y_tmp_1 + off_len_left * sinus;
									x_tmp_1 = x_tmp_2;
									y_tmp_1 = y_tmp_2;
									part_len_left = part_len_left - off_len_left;								
									now_on = true;
									on_len_left = on_len;
								} else {
									//Does not fit - draw part of the space
									x_tmp_2 = e.x;									
									y_tmp_2 = e.y;									
									x_tmp_1 = x_tmp_2;
									y_tmp_1 = y_tmp_2;
									off_len_left = off_len_left - part_len_left;
									inside_part = false;																		
								}
								this.graphics.moveTo(x_tmp_2, y_tmp_2);								
							}
						}
					}
					prev_x = e.x;
					prev_y = e.y;
				}
			}
		}
		
		protected function merge_us_with_value_object( value:Object ): Object {
			
			var default_style:Object = {
				'dot-size':		this.style['dot-size'],
				colour:			this.style.colour,
				'halo-size':	this.style['halo-size'],
				tip:			this.style.tip,
				'on-click':		this.style['on-click'],
				'axis':			this.style.axis
			}
			
			if( value is Number )
				default_style.value = value;
			else
				object_helper.merge_2( value, default_style );
			
			// our parent colour is a number, but
			// we may have our own colour:
			if( default_style.colour is String )
				default_style.colour = Utils.get_colour( default_style.colour );
				
			// Minor hack, replace all #key# with this LINEs key text:
			default_style.tip = default_style.tip.replace('#key#', this.style.text);
			
			return default_style;
		}
		
		public override function get_colour(): Number {
			return this.style.colour;
		}
	}
}
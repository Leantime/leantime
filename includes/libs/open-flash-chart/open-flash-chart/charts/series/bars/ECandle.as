package charts.series.bars {
	
	import flash.display.Sprite;
	import flash.geom.Point;
	import charts.series.bars.Base;
	
	public class ECandle extends Base {
		protected var high:Number;
		protected var low:Number;
		protected var negative_colour:Number;

		
		public function ECandle( index:Number, props:Properties, group:Number ) {
			
			super(index, props, group);
			
			tr.aces( props.has('negative-colour'), props.get_colour('negative-colour'));
			
			if( props.has('negative-colour') )
				this.negative_colour = props.get_colour('negative-colour');
			else
				this.negative_colour = this.colour;
		}
		
		//
		// a candle chart has many values used to display each point
		//
		protected override function parse_value( props:Properties ):void {
			
			// set top (open) and bottom (close)
			super.parse_value( props );
			this.high = props.get('high');
			this.low = props.get('low');
		}
		
		protected override function replace_magic_values( t:String ): String {
			
			t = super.replace_magic_values( t );
			t = t.replace('#high#', NumberUtils.formatNumber( this.high ));
			t = t.replace('#open#', NumberUtils.formatNumber( this.top ));
			t = t.replace('#close#', NumberUtils.formatNumber( this.bottom ));
			t = t.replace('#low#', NumberUtils.formatNumber( this.low ));
			
			return t;
		}
		
		public override function resize( sc:ScreenCoordsBase ):void {
			
			// this moves everyting relative to the box (NOT the whiskers)
			var h:Object = this.resize_helper( sc as ScreenCoords );
			
			// 
			//var bar_high:Number = 0;
			//var bar_low:Number = height;
			
			// calculate the box position:
			var tmp:Number			= sc.get_y_from_val(Math.max(this.top, this.bottom), this.right_axis);
			var bar_high:Number		= sc.get_y_from_val(this.high, this.right_axis) - tmp;
			var bar_top:Number		= 0;
			var bar_bottom:Number	= sc.get_y_from_val(this.bottom, this.right_axis) - tmp;
			var bar_low:Number		= sc.get_y_from_val(this.low, this.right_axis) - tmp;
			
			//var height:Number = Math.abs( bar_bottom - bar_top );
			
			//
			// move the Sprite to the correct screen location:
			//
			//this.y = bar_high;
			//this.x = tmp.x;
			
			//
			// tell the tooltip where to show its self
			//
			this.tip_pos = new flash.geom.Point( this.x + (h.width / 2), this.y );
			
			var mid:Number = h.width / 2;
			this.graphics.clear();
			var c:Number = this.colour;
			if ( h.upside_down)
				c = this.negative_colour;
			
			this.top_line(c, mid, bar_high);
			
			if ( this.top == this.bottom )
				this.draw_doji(c, h.width, bar_top);
			else
				this.draw_box(c, bar_top, h.height, h.width, h.upside_down);
			
			this.bottom_line(c, mid, h.height, bar_low);
			// top line
			
			//
			// tell the tooltip where to show its self
			//
			this.tip_pos = new flash.geom.Point(
				this.x + (h.width / 2),
				this.y + bar_high );
		}
		
		private function top_line(colour:Number, mid:Number, height:Number): void {
			// top line
			this.graphics.beginFill( colour, 1.0 );
			this.graphics.moveTo( mid-1, 0 );
			this.graphics.lineTo( mid+1, 0 );
			this.graphics.lineTo( mid+1, height );
			this.graphics.lineTo( mid-1, height );
			this.graphics.endFill();
		}
		
		private function bottom_line(colour:Number, mid:Number, top:Number, bottom:Number):void {
			this.graphics.beginFill( colour, 1.0 );
			this.graphics.moveTo( mid-1, top );
			this.graphics.lineTo( mid+1, top );
			this.graphics.lineTo( mid+1, bottom );
			this.graphics.lineTo( mid-1, bottom );
			this.graphics.endFill();
		}
		
		//
		// http://en.wikipedia.org/wiki/Candlestick_chart
		//
		private function draw_doji(colour:Number, width:Number, pos:Number):void {
			// box
			this.graphics.beginFill( colour, 1.0 );
			this.graphics.moveTo( 0, pos-1 );
			this.graphics.lineTo( width, pos-1 );
			this.graphics.lineTo( width, pos+1 );
			this.graphics.lineTo( 0, pos+1 );
			this.graphics.endFill();
		}
		
	
		
		private function draw_box(colour:Number, top:Number, bottom:Number, width:Number, upside_down:Boolean):void {
			
			// box
			this.graphics.beginFill( colour, 1.0 );
			this.graphics.moveTo( 0, top );
			this.graphics.lineTo( width, top );
			this.graphics.lineTo( width, bottom );
			this.graphics.lineTo( 0, bottom );
			this.graphics.lineTo( 0, top );
			
			if ( upside_down) {
				// snip out the middle of the box:
				this.graphics.moveTo( 2, top+2 );
				this.graphics.lineTo( width-2, top+2 );
				this.graphics.lineTo( width-2, bottom-2 );
				this.graphics.lineTo( 2, bottom-2 );
				this.graphics.lineTo( 2, top+2 );
			}
			this.graphics.endFill();
			
			if ( upside_down ) {
				
				//
				// HACK: we fill an invisible rect over
				//       the hollow rect so the mouse over
				//       event fires correctly (even when the
				//       mouse is in the hollow part)
				//
				this.graphics.lineStyle( 0, 0, 0 );
				this.graphics.beginFill(0, 0);
				this.graphics.moveTo( 2, top-2 );
				this.graphics.lineTo( width-2, top-2 );
				this.graphics.lineTo( width-2, bottom-2 );
				this.graphics.lineTo( 2, bottom-2 );
				this.graphics.endFill();
			}
		}
			
	}
}
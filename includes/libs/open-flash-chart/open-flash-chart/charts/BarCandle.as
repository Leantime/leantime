package charts {
	import charts.Elements.Element;
	import charts.Elements.PointBarCandle;
	import string.Utils;
	
	public class BarCandle extends BarBase {
		//private var line_width:Number;
		
		public function BarCandle( lv:Array, num:Number, group:Number ) {
			super( lv, num, group, 'candle' );
		}
		
		public override function parse_bar( val:Object ):void {
			var vals:Array = val.split(",");
		
			//this.alpha = Number( vals[0] );
			this.line_width = Number( vals[1] );
			this.colour = Utils.get_colour(vals[2]);
			
			if( vals.length > 3 )
				this.key = vals[3].replace('#comma#',',');
				
			if( vals.length > 4 )
				this.font_size = Number( vals[4] );
		}
	
		//
		// the data looks like "[1,2,3,4],[2,3,4,5]"
		// this returns an array of strings like '1,2,3,4','2,3,4,5'
		// these are then parsed further in PointBarCandle
		//
		protected override function parse_list( values:String ):Array {
			var groups:Array=new Array();
			var tmp:String = '';
			var start:Boolean = false;

			for( var i:Number=0; i<values.length; i++ )
			{
				switch( values.charAt(i) )
				{
					case '[':
						start=true;
						break;
					case ']':
						start = false;
						groups.push( tmp );
						tmp = '';
						break;
					default:
						if( start )
							tmp += values.charAt(i);
						break;
				}
			}
			return groups;
		}
		
		
		//
		// called from the base object
		//
		protected override function get_element( x:Number, value:Object ): Element {
			return new PointBarCandle( x, value, this.line_width, this.colour, this.group );
		}
	}
}
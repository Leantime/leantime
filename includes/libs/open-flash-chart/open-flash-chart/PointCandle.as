package {
	public class PointCandle extends Point
	{
		public var width:Number;
		public var bar_bottom:Number;
		public var high:Number;
		public var open:Number;
		public var close:Number;
		public var low:Number;
		
		public function PointCandle( x:Number, high:Number, open:Number, close:Number, low:Number, tooltip:Number, width:Number ):void {
			super( x, high );
			
			this.width = width;
			this.high = high;
			this.open = open;
			this.close = close;
			this.low = low;
		}
		
		public override function make_tooltip(
			tip:String, key:String, val:Number, x_legend:String,
			x_axis_label:String, tip_set:String ):void {
				
		
			super.make_tooltip( tip, key, val, x_legend, x_axis_label, tip_set );
//			super.make_tooltip( tip, key, val.open, x_legend, x_axis_label, tip_set );
//			
//			var tmp:String = this.tooltip;
//			tmp = tmp.replace('#high#',NumberUtils.formatNumber(val.high));
//			tmp = tmp.replace('#open#',NumberUtils.formatNumber(val.open));
//			tmp = tmp.replace('#close#',NumberUtils.formatNumber(val.close));
//			tmp = tmp.replace('#low#',NumberUtils.formatNumber(val.low));
			
//			this.tooltip = tmp;
		}
		
		public override function get_tip_pos():Object {
			return {x:this.x+(this.width/2), y:this.y};
		}
	}
}
package charts {
	import charts.series.Element;
	import charts.series.bars.Glass;
	import string.Utils;
	
	public class BarGlass extends BarBase {

		
		public function BarGlass( json:Object, group:Number ) {
			
			super( json, group );
		}
		
		//
		// called from the base object
		//
		protected override function get_element( index:Number, value:Object ): Element {

			return new Glass( index, this.get_element_helper_prop( value ), this.group );
		}
	}
}
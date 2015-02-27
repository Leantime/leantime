package charts {
	import charts.series.Element;
	import charts.series.bars.Bar;
	import string.Utils;

	
	public class Bar extends BarBase {
		
		public function Bar( json:Object, group:Number ) {
			
			super( json, group );
		}
		
		//
		// called from the base object
		//
		protected override function get_element( index:Number, value:Object ): Element {

			return new charts.series.bars.Bar( index, this.get_element_helper_prop( value ), this.group );
		}
	}
}
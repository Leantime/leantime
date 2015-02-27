package charts {
	import charts.series.Element;
	import charts.series.bars.Bar3D;
	import string.Utils;
	
	
	public class Bar3D extends BarBase {
		
		public function Bar3D( json:Object, group:Number ) {
			super( json, group );
		}
		
		//
		// called from the base object
		//
		protected override function get_element( index:Number, value:Object ): Element {

			return new charts.series.bars.Bar3D( index, this.get_element_helper_prop( value ), this.group );
		}
	}
}
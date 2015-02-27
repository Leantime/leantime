package charts {
	import charts.series.Element;
	import charts.series.bars.CylinderOutline;

	public class BarCylinderOutline extends BarBase {

		public function BarCylinderOutline( json:Object, group:Number ) {

			super( json, group );
		}

       //
		// called from the base object
		//
		protected override function get_element( index:Number, value:Object ): Element {

			return new CylinderOutline( index, this.get_element_helper_prop( value ), this.group );
		}
	}
}
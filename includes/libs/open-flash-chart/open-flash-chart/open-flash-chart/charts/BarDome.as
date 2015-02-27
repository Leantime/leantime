package charts {
	import charts.series.Element;
	import charts.series.bars.Dome;

	public class BarDome extends BarBase {


		public function BarDome( json:Object, group:Number ) {

			super( json, group );
		}

		//
		// called from the base object
		//
		protected override function get_element( index:Number, value:Object ): Element {

			return new Dome( index, this.get_element_helper_prop( value ), this.group );
		}

	}
}
package charts {
	import charts.series.Element;
	import charts.series.bars.RoundGlass;

	public class BarRoundGlass extends BarBase {


		public function BarRoundGlass( json:Object, group:Number ) {

			super( json, group );
		}

		//
		// called from the base object
		//
		protected override function get_element( index:Number, value:Object ): Element {

			return new RoundGlass( index, this.get_element_helper_prop( value ), this.group );
		}
	}
}
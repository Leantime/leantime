package charts {
	import charts.series.Element;
	import charts.series.bars.Round;

	public class BarRound extends BarBase {

		public function BarRound( json:Object, group:Number ) {

			super( json, group );
		}

		//
		// called from the base object
		//
		protected override function get_element( index:Number, value:Object ): Element {

			return new Round( index, this.get_element_helper_prop( value ), this.group );
		}
	}
}
package charts {
	import charts.series.Element;
	import charts.series.bars.Cylinder;

	public class BarCylinder extends BarBase {


		public function BarCylinder( json:Object, group:Number ) {

			super( json, group );
		}

		//
		// called from the base object
		//
		protected override function get_element( index:Number, value:Object ): Element {
			
			return new Cylinder( index, this.get_element_helper_prop( value ), this.group );
		}
	}
}
package charts {
	import charts.series.Element;
	import charts.series.bars.Round3D;
       
       public class BarRound3D extends BarBase {

          
          public function BarRound3D( json:Object, group:Number ) {
             
             super( json, group );
          }
          
          //
		// called from the base object
		//
		protected override function get_element( index:Number, value:Object ): Element {

			return new charts.series.bars.Round3D( index, this.get_element_helper_prop( value ), this.group );
		}
	   }
    }
    package charts {
		
       import charts.series.Element;
	import charts.series.bars.PlasticFlat;
       import string.Utils;
       
       public class BarPlasticFlat extends BarBase {

          
          public function BarPlasticFlat( json:Object, group:Number ) {
             
             super( json, group );
          }
          
          //
		// called from the base object
		//
		protected override function get_element( index:Number, value:Object ): Element {

			return new PlasticFlat( index, this.get_element_helper_prop( value ), this.group );
		}
          
       }
    }
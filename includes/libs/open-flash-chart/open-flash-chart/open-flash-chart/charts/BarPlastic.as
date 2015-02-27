    package charts {
       import charts.series.Element;
	import charts.series.bars.Plastic;
       import string.Utils;
       
       public class BarPlastic extends BarBase {

          
          public function BarPlastic( json:Object, group:Number ) {
             
             super( json, group );
          }
          
          //
		// called from the base object
		//
		protected override function get_element( index:Number, value:Object ): Element {

			return new Plastic( index, this.get_element_helper_prop( value ), this.group );
		}
        
       }
    }
package charts {
	import charts.series.Element;
	import charts.series.bars.Outline;
	import string.Utils;
	
	public class BarOutline extends BarBase {
		private var outline_colour:Number;
		
		//TODO: remove
		protected var style:Object;
		
		
		public function BarOutline( json:Object, group:Number ) {
			
			//
			// specific value for outline
			//
			this.style = {
				'outline-colour':	"#000000"
			};
			
			object_helper.merge_2( json, this.style );
			
			super( json, group );
		}
		
		//
		// called from the base object
		//
		protected override function get_element( index:Number, value:Object ): Element {
			
			var root:Properties = new Properties( {
				'outline-colour':	this.style['outline-colour']
				} );
		
			var default_style:Properties = this.get_element_helper_prop( value );
			default_style.set_parent( root );
			
	/*
			if ( !default_style['outline-colour'] )
				default_style['outline-colour'] = this.style['outline-colour'];
			
			if( default_style['outline-colour'] is String )
				default_style['outline-colour'] = Utils.get_colour( default_style['outline-colour'] );
	*/

			return new Outline( index, default_style, this.group );
		}
	}
}
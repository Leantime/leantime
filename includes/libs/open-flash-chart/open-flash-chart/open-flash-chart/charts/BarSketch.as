package charts {
	import charts.series.Element;
	import charts.series.bars.Sketch;
	import string.Utils;
	
	public class BarSketch extends BarBase {
		private var outline_colour:Number;
		private var offset:Number;
		
		// TODO: remove
		protected var style:Object;
		
		public function BarSketch( json:Object, group:Number ) {
			
			//
			// these are specific values to the Sketch
			// and so we need to sort them out here
			//
			this.style = {
				'outline-colour':	"#000000",
				offset:				6
			};
			
			object_helper.merge_2( json, this.style );
			
			super( style, group );
		}
	
		//
		// called from the base object
		//
		protected override function get_element( index:Number, value:Object ): Element {
			
			var root:Properties = new Properties( {
				'outline-colour':	this.style['outline-colour'],
				offset:				this.style.offset
				} );
		
			var default_style:Properties = this.get_element_helper_prop( value );	
			default_style.set_parent( root );
	
/**
			// our parent colour is a number, but
			// we may have our own colour:
			if( default_style.colour is String )
				default_style.colour = Utils.get_colour( default_style.colour );
				
			if ( !default_style['outline-colour'] )
				default_style['outline-colour'] = this.style['outline-colour'];
				
			if( default_style['outline-colour'] is String )
				default_style['outline-colour'] = Utils.get_colour( default_style['outline-colour'] );
			
			if ( !default_style.offset )
				default_style.offset = this.style.offset;
**/
			return new Sketch( index, default_style, this.group );
		}
	}
}
package charts {
	//import caurina.transitions.Tweener;

	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import charts.series.Element;
	//import charts.series.dots.PointDot;
	import string.Utils;
	import flash.display.BlendMode;
	import charts.series.dots.Hollow;
	import charts.series.dots.dot_factory;
	
	public class LineHollow extends LineBase
	{
		
		public function LineHollow( json:Object )
		{
			//
			// so the mask child can punch a hole through the line
			//
			this.blendMode = BlendMode.LAYER;
			
			this.style = {
				values: 		[],
				width:			2,
				colour:			'#80a033',
				text:			'',
				'font-size':	10,
				'dot-size':		6,
				'halo-size':	2,
				tip:			'#val#',
				'line-style':	new LineStyle( json['line-style'] ),
				'axis':			'left'
			};
			
			this.style = object_helper.merge( json, this.style );
			
			this.style.colour = string.Utils.get_colour( this.style.colour );
			this.values = style.values;
			
			this.key = style.text;
			this.font_size = style['font-size'];
			
			
//			this.axis = which_axis_am_i_attached_to(data, num);
//			tr.ace( name );
//			tr.ace( 'axis : ' + this.axis );

			this.add_values();
			
		}
		
		//
		// called from the base object
		/*
		protected override function get_element( index:Number, value:Object ): charts.series.Element {
			
			var s:Object = this.merge_us_with_value_object( value );
			//
			// the width of the hollow circle is the same as the width of the line
			//
			s.width = this.style.width;
			if( s.type == null )
				s.type = 'hollow-dot';
			
			return dot_factory.make( index, s );
		}
		*/
	}
}
package charts {
	
	import charts.series.dots.scat;
	import charts.series.Element;
	import charts.series.dots.dot_factory;
	import string.Utils;
	import flash.geom.Point;
	import flash.display.Sprite;
	import charts.series.dots.DefaultDotProperties;
	
	public class ScatterBase extends Base {

		// TODO: move this into Base
		protected var props:Properties;
		protected var style:Object;
		private var on_show:Properties;
		private var dot_style:Properties;
		//
		
		protected var default_style:DefaultDotProperties;
		
		public function ScatterBase(json:Object) {
		
			//
			// merge into Line.as and Base.as
			//
			var root:Properties = new Properties({
				colour: 		'#3030d0',
				text: 			'',		// <-- default not display a key
				'font-size': 	12,
				tip:			'#val#',
				axis:			'left'
			});
			//
			this.props = new Properties(json, root);
			//
			this.dot_style = new DefaultDotProperties(json['dot-style'], this.props.get('colour'), this.props.get('axis'));
			//
			// LOOK for a scatter chart the default dot is NOT invisible!!
			//
		//	this.dot_style.set('type', 'solid-dot');
			//
			// LOOK default animation for scatter is explode, no cascade
			//
			var on_show_root:Properties = new Properties( {
				type:		"explode",
				cascade:	0,
				delay:		0.3
				});
			this.on_show = new Properties(json['on-show'], on_show_root);
			//this.on_show_start = true;
			//
			//
		}
		
		//
		// called from the base object
		//
		protected override function get_element( index:Number, value:Object ): Element {
			// we ignore the X value (index) passed to us,
			// the user has provided their own x value
			
			var default_style:Object = {
				width:			this.style.width,	// stroke
				colour:			this.style.colour,
				tip:			this.style.tip,
				'dot-size':		10
			};
			
			// Apply dot style defined at the plot level
			object_helper.merge_2( this.style['dot-style'], default_style );
			// Apply attributes defined at the value level
			object_helper.merge_2( value, default_style );
				
			// our parent colour is a number, but
			// we may have our own colour:
			if( default_style.colour is String )
				default_style.colour = Utils.get_colour( default_style.colour );
			
			//var tmp:Properties = new Properties( value, this.default_style);
			var tmp:Properties = new Properties(value, this.dot_style);
	
			// attach the animation bits:
			tmp.set('on-show', this.on_show);
			
			return dot_factory.make( index, tmp );
		}
		
		// Draw points...
		public override function resize( sc:ScreenCoordsBase ): void {
			
			var tmp:Sprite;
			for ( var i:Number = 0; i < this.numChildren; i++ ) {
				tmp = this.getChildAt(i) as Sprite;
				
				//
				// filter out the line masks
				//
				if( tmp is Element )
				{
					var e:Element = tmp as Element;
					e.resize( sc );
				}
			}
		}
	}
}
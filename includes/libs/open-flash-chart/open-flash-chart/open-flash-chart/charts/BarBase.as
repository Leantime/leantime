package charts {
	
	import charts.series.Element;
	import charts.Base;
	import string.Utils;
	import global.Global;

	
	public class BarBase extends Base
	{
		protected var group:Number;
		//protected var style:Object;
		protected var props:Properties;
		protected var on_show:Properties;
		
		public function BarBase( json:Object, group:Number )
		{
		
			var root:Properties = new Properties( {
				values:				[],
				colour:				'#3030d0',
				text:				'',		// <-- default not display a key
				'font-size':		12,
				tip:				'#val#<br>#x_label#',
				alpha:				0.6,
				'on-click':			false,
				'axis':				'left'
			} );
			
			this.props = new Properties(json, root);
			
		/*
			var on_show_root:Properties = new Properties( {
				type:		"none",		//"pop-up",
				cascade:	3,
				delay:		0
				});
			this.on_show = new Properties(json['on-show'], on_show_root);
		*/
			this.on_show = this.get_on_show(json['on-show']);
			
			this.colour		= this.props.get_colour('colour');
			this.key		= this.props.get('text');
			this.font_size	= this.props.get('font-size');

			// Minor hack, replace all #key# with this key text:
			this.props.set( 'tip', this.props.get('tip').replace('#key#', this.key) );
			
			
			//
			// bars are grouped, so 3 bar sets on one chart
			// will arrange them selves next to each other
			// at each value of X, this.group tell the bar
			// where it is in that grouping
			//
			this.group = group;
			
			this.values = this.props.get('values');
			this.add_values();
		}
		
		protected function get_on_show(json:Object): Properties {
			
			var on_show_root:Properties = new Properties( {
				type:		"none",		//"pop-up",
				cascade:	3,
				delay:		0
				});
				
			return new Properties(json, on_show_root);
		}
		
		
		//
		// hello people in the future! I was doing OK until I found some red wine. Now I can't figure stuff out,
		// like, do I pass in this.axis, or do I make it a member of each PointBarBase? I don't know. Hey, I know
		// I'll flip a coin and see what happens. It was heads. What does it mean? Mmmmm.... red wine....
		// Fuck it, I'm passing it in. Makes the resize method messy, but keeps the PointBarBase clean.
		//
		public override function resize( sc:ScreenCoordsBase ): void {
			
			for ( var i:Number = 0; i < this.numChildren; i++ )
			{
				var e:Element = this.getChildAt(i) as Element;
				e.resize( sc );
			}
		}
		
		
		public override function get_max_x():Number {
			
			var max_index:Number = Number.MIN_VALUE;
			
			for ( var i:Number = 0; i < this.numChildren; i++ ) {
				
				var e:Element = this.getChildAt(i) as Element;
				max_index = Math.max( max_index, e.index );
			}
			
			// 0 is a position, so count it:
			return max_index;
		}
		
		public override function get_min_x():Number {
			return 0;
		}
		
		//
		// override or don't call this if you need better help
		//
		protected function get_element_helper_prop( value:Object ): Properties {
			
			var default_style:Properties = new Properties({
				colour:		this.props.get('colour'),
				tip:		this.props.get('tip'),
				alpha:		this.props.get('alpha'),
				'on-click':	this.props.get('on-click'),
				axis:		this.props.get('axis'),
				'on-show':	this.on_show
			});
		
			var s:Properties;
			if( value is Number )
				s = new Properties({'top': value}, default_style);
			else
				s = new Properties(value, default_style);
		
			return s;
		}
		
		/*
				
			      +-----+
			      |  B  |
			+-----+     |   +-----+
			|  A  |     |   |  C  +- - -
			|     |     |   |     |  D
			+-----+-----+---+-----+- - -
			         1   2
			
		*/
			
		
		public override function closest( x:Number, y:Number ): Object {
			var shortest:Number = Number.MAX_VALUE;
			var ex:Element = null;
			
			for ( var i:Number = 0; i < this.numChildren; i++ )
			{
				var e:Element = this.getChildAt(i) as Element;

				e.is_tip = false;
				
				if( (x > e.x) && (x < e.x+e.width) )
				{
					// mouse is in position 1
					shortest = Math.min( Math.abs( x - e.x ), Math.abs( x - (e.x+e.width) ) );
					ex = e;
					break;
				}
				else
				{
					// mouse is in position 2
					// get distance to left side and right side
					var d1:Number = Math.abs( x - e.x );
					var d2:Number = Math.abs( x - (e.x+e.width) );
					var min:Number = Math.min( d1, d2 );
					if( min < shortest )
					{
						shortest = min;
						ex = e;
					}
				}
			}
			var dy:Number = Math.abs( y - ex.y );
			
			return { element:ex, distance_x:shortest, distance_y:dy };
		}
		
		public override function die():void {
			super.die();
			this.props.die();
		}
	}
}
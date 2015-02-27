package charts {

	import string.Utils;
	import charts.series.dots.DefaultDotProperties;
	
	public class Scatter extends ScatterBase
	{
		public function Scatter( json:Object )
		{
			super(json);
			
			this.style = {
				values:			[],
				width:			2,
				colour:			'#3030d0',
				text:			'',		// <-- default not display a key
				'font-size':	12,
				tip:			'[#x#,#y#] #size#',
				axis:			'left'
			};
			
			// hack: keep this incase the merge kills it, we'll
			// remove the merge later (and this hack)
			var tmp:Object = json['dot-style'];
			
			object_helper.merge_2( json, style );
			
			this.default_style = new DefaultDotProperties(
				json['dot-style'], this.style.colour, this.style.axis);
			
			this.line_width = style.width;
			this.colour		= string.Utils.get_colour( style.colour );
			this.key		= style.text;
			this.font_size	= style['font-size'];
			this.circle_size = style['dot-size'];
			
			this.values = style.values;

			this.add_values();
		}
	}
}
package charts {
	import flash.display.Sprite;
	import flash.geom.Point;
	import string.Utils;
	import charts.series.tags.Tag;
	
	public class Tags extends Base {
		
		private var style:Object;
		
		public function Tags( json:Object )
		{
			this.style = {
				values:				[],
				colour:				'#000000',
				text:				'[#x#, #y#]',  
				'align-x':			'center',  // center, left, right
				'align-y':			'above',   // above, below, center
				'pad-x':			4,
				'pad-y':			4,
				font:				'Verdana',
				bold:				false,
				'on-click':			null,
				rotate:				0,
				'font-size':		12,
				border:				false,
				underline:			false,
				alpha:				1
			};
			
			object_helper.merge_2( json, this.style );
			
			for each ( var v:Object in this.style.values )
			{
				var tmp:Tag = this.make_tag( v );
				this.addChild(tmp);
			}
		}
		
		private function make_tag( json:Object ):Tag
		{
			var tagStyle:Object = { };
			object_helper.merge_2( this.style, tagStyle );
			object_helper.merge_2( json, tagStyle );
			tagStyle.colour = string.Utils.get_colour(tagStyle.colour);
			
			return new Tag(tagStyle);
		}
		
		public override function resize( sc:ScreenCoordsBase ): void {
			for ( var i:Number = 0; i < this.numChildren; i++ ) {
				var tag:Tag = this.getChildAt(i) as Tag;
				tag.resize( sc );
			}
		}
	}
	
}
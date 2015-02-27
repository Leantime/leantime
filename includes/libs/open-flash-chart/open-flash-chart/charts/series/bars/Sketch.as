package charts.series.bars {
	
	import flash.display.Sprite;
	import charts.series.bars.Base;
	
	public class Sketch extends Base {
		private var outline:Number;
		private var offset:Number;
		
		public function Sketch( index:Number, props:Properties, group:Number ) {
			
			super(index, props, group);
			//super(index, {'top':props.get('top')}, props.get_colour('colour'), props.get('tip'), props.get('alpha'), group);
			this.outline = props.get_colour('outline-colour');
			this.offset = props.get('offset');
		}
		
		
		public override function resize( sc:ScreenCoordsBase ):void {
			
			var h:Object = this.resize_helper( sc as ScreenCoords );
			
			// how sketchy the bar is:
			var offset:Number = this.offset;
			var o2:Number = offset/2;
			
			// fill the bar
			// number of pen strokes:
			var strokes:Number = 6;
			// how wide each pen will need to be:
			var l_width:Number = h.width/strokes;
			
			this.graphics.clear();
			this.graphics.lineStyle( l_width+1, this.colour, 0.85, true, "none", "round", "miter", 0.8 );
			for( var i:Number=0; i<strokes; i++ )
			{
				this.graphics.moveTo( ((l_width*i)+(l_width/2))+(Math.random()*offset-o2), 2+(Math.random()*offset-o2) );
				this.graphics.lineTo( ((l_width*i)+(l_width/2))+(Math.random()*offset-o2), h.height-2+ (Math.random()*offset-o2) );
			}
			
			// outlines:
			this.graphics.lineStyle( 2, this.outline, 1 );
			// left upright
			this.graphics.moveTo( Math.random()*offset-o2, Math.random()*offset-o2 );
			this.graphics.lineTo( Math.random()*offset-o2, h.height+Math.random()*offset-o2 );
			
			// top
			this.graphics.moveTo( Math.random()*offset-o2, Math.random()*offset-o2 );
			this.graphics.lineTo( h.width+ (Math.random()*offset-o2), Math.random()*offset-o2 );
			
			// right upright
			this.graphics.moveTo( h.width+ (Math.random()*offset-o2), Math.random()*offset-o2 );
			this.graphics.lineTo( h.width+ (Math.random()*offset-o2), h.height+ (Math.random()*offset-o2) );
			
			// bottom
			this.graphics.moveTo( Math.random()*offset-o2, h.height+ (Math.random()*offset-o2) );
			this.graphics.lineTo( h.width+ (Math.random()*offset-o2), h.height+ (Math.random()*offset-o2) );
			
		}
	}
}
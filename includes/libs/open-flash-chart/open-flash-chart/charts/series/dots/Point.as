package charts.series.dots {
	import charts.series.dots.PointDotBase;
	import flash.display.Sprite;
	import flash.display.BlendMode;
	import string.Utils;
	
	public class Point extends PointDotBase {
		
		public function Point( index:Number, style:Properties )
		{
			super( index, style );

			var colour:Number = string.Utils.get_colour( style.get('colour') );
			
			this.graphics.lineStyle( 0, 0, 0 );
			this.graphics.beginFill( colour, 1 );
			this.graphics.drawCircle( 0, 0, style.get('dot-size') );
			this.visible = false;
			this.attach_events();
			
			var s:Sprite = new Sprite();
			s.graphics.lineStyle( 0, 0, 0 );
			s.graphics.beginFill( 0, 1 );
			s.graphics.drawCircle( 0, 0, style.get('dot-size')+style.get('halo-size') );
			s.blendMode = BlendMode.ERASE;
			s.visible = false;
			
			this.line_mask = s;
		}
		
		public override function set_tip( b:Boolean ):void {
			
			this.visible = b;
			this.line_mask.visible = b;
		}
	}
}
package charts.series.dots {
	
	import charts.series.dots.PointDotBase;
	import flash.display.BlendMode;
	import flash.display.Sprite;
	import string.Utils;
	
	public class Hollow extends PointDotBase {
		
		public function Hollow( index:Number, style:Properties ) {
			// tr.aces('h i', index);
			super( index, style );
			
			var colour:Number = string.Utils.get_colour( style.get('colour') );
			
			this.graphics.clear();
			//
			// fill a big circle
			//
			this.graphics.lineStyle( 0, 0, 0 );
			this.graphics.beginFill( colour, 1 );
			this.graphics.drawCircle( 0, 0, style.get('dot-size'));
			//
			// punch out the hollow circle:
			//
			this.graphics.drawCircle( 0, 0, style.get('dot-size')-style.get('width'));
			this.graphics.endFill();	// <-- LOOK
			//
			// HACK: we fill an invisible circle over
			//       the hollow circle so the mouse over
			//       event fires correctly (even when the
			//       mouse is in the hollow part)
			//
			this.graphics.lineStyle( 0, 0, 0 );
			this.graphics.beginFill(0, 0);
			this.graphics.drawCircle( 0, 0, style.get('dot-size') );
			this.graphics.endFill();
			//
			// MASK
			//
			var s:Sprite = new Sprite();
			s.graphics.lineStyle( 0, 0, 0 );
			s.graphics.beginFill( 0, 1 );
			s.graphics.drawCircle( 0, 0, style.get('dot-size') + style.get('halo-size') );
			s.blendMode = BlendMode.ERASE;
			
			this.line_mask = s;
			this.attach_events();
			
		}
	}
}


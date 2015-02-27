package charts.series.bars {
	
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import caurina.transitions.Tweener;
	import caurina.transitions.Equations;
	import flash.geom.Point;
	import charts.series.Element;
	
	public class Horizontal extends Element
	{
		private var right:Number;
		private var left:Number;
		//protected var width:Number;
		
		public var colour:Number;
		protected var group:Number;
		
		public function Horizontal( index:Number, style:Object, group:Number )
		{
			super();
			//
			// we use the index of this bar to find its Y position
			//
			this.index = index;
			//
			// horizontal bar: value = X Axis position
			// we'll use the ScreenCoords object to go [value -> x location]
			//
			
			this.left = style.left ? style.left : 0;
			this.right = style.right ? style.right : 0;
			
			this.colour = style.colour;
			this.group = group;
			this.visible = true;
			
			this.alpha = 0.5;
			
			this.tooltip = this.replace_magic_values( style.tip );
			
			this.addEventListener(MouseEvent.MOUSE_OVER, this.mouseOver);
			this.addEventListener(MouseEvent.MOUSE_OUT, this.mouseOut);
			
		}

		protected function replace_magic_values( t:String ): String {
			
			t = t.replace('#right#', NumberUtils.formatNumber( this.right ));
			t = t.replace('#left#', NumberUtils.formatNumber( this.left ));
			t = t.replace('#val#', NumberUtils.formatNumber( this.right - this.left ));
			
			return t;
		}
		
		public override function mouseOver(event:Event):void {
			Tweener.addTween(this, { alpha:1, time:0.6, transition:Equations.easeOutCirc } );
		}

		public override function mouseOut(event:Event):void {
			Tweener.addTween(this, { alpha:0.5, time:0.8, transition:Equations.easeOutElastic } );
		}
		
		public override function resize( sc:ScreenCoordsBase ):void {
			
			// is it OK to cast up like this?
			var sc2:ScreenCoords = sc as ScreenCoords;
			
			var tmp:Object = sc2.get_horiz_bar_coords( this.index, this.group );
			
			var left:Number  = sc.get_x_from_val( this.left );
			var right:Number = sc.get_x_from_val( this.right );
			var width:Number = right - left;
			
			this.graphics.clear();
			this.graphics.beginFill( this.colour, 1.0 );
			this.graphics.drawRect( 0, 0, width, tmp.width );
			this.graphics.endFill();
			
			this.x = left;
			this.y = tmp.y;
		}
		
		//
		// for tooltip closest - return the middle point
		//
		public override function get_mid_point():flash.geom.Point {
			
			//
			// bars mid point
			//
			return new flash.geom.Point( this.x + (this.width/2), this.y );
		}
		
		public override function get_tip_pos():Object {
			//
			// Hover the tip over the right of the bar
			//
			return {x:this.x+this.width-20, y:this.y};
		}
		
		public function get_max_x():Number {
			return this.right;
		}
	}
}
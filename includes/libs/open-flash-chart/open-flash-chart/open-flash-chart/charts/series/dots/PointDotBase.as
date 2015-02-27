package charts.series.dots {
	
	import flash.display.Sprite;
	import charts.series.Element;
	import flash.display.BlendMode;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import flash.geom.Point;
	import caurina.transitions.Tweener;
	import caurina.transitions.Equations;
	import string.DateUtils;
	
	public class PointDotBase extends Element {
		
		protected var radius:Number;
		protected var colour:Number;
		private var on_show_animate:Boolean;
		protected var on_show:Properties;
		
		public function PointDotBase( index:Number, props:Properties ) {
			
			super();
			this.is_tip = false;
			this.visible = true;
			this.on_show_animate = true;
			this.on_show = props.get('on-show');
			
			/*
			this.on_show = new Properties( {
				type:		"",
				cascade:	3,
				delay:		0
				});
			*/
			
			// line charts have a value and no X, scatter charts have
			// x, y (not value): radar charts have value, Y does not 
			// make sense.
			if( !props.has('y') )
				props.set('y', props.get('value'));
		
			this._y = props.get('y');
			
			// no X passed in so calculate it from the index
			if( !props.has('x') )
			{
				this.index = this._x = index;
			}
			else
			{
				// tr.aces( 'x', props.get('x') );
				this._x = props.get('x');
				this.index = Number.MIN_VALUE;
			}
			
			this.radius = props.get('dot-size');
			this.tooltip = this.replace_magic_values( props.get('tip') );
			
			if ( props.has('on-click') )
				this.set_on_click( props.get('on-click') );
			
			//
			// TODO: fix this hack
			//
			if ( props.has('axis') )
				if ( props.get('axis') == 'right' )
					this.right_axis = true;

		}
		
		public override function resize( sc:ScreenCoordsBase ): void {
			
			var x:Number;
			var y:Number;
			
			if ( this.index != Number.MIN_VALUE ) {
	
				var p:flash.geom.Point = sc.get_get_x_from_pos_and_y_from_val( this.index, this._y, this.right_axis );
				x = p.x;
				y = p.y;
			}
			else
			{

				//
				// Look: we have a real X value, so get its screen location:
				//
				x = sc.get_x_from_val( this._x );
				y = sc.get_y_from_val( this._y, this.right_axis );
			}
			
			// Move the mask so it is in the proper place also
			// this all needs to be moved into the base class
			if (this.line_mask != null)
			{
				this.line_mask.x = x;
				this.line_mask.y = y;
			}
			
			if ( this.on_show_animate )
				this.first_show(x, y, sc);
			else {
				//
				// move the Sprite to the correct screen location:
				//
				this.y = y;
				this.x = x;
			}
		}
		
		public function is_tweening(): Boolean {
			return Tweener.isTweening(this);
		}
		
		protected function first_show(x:Number, y:Number, sc:ScreenCoordsBase): void {
			
			this.on_show_animate = false;
			Tweener.removeTweens(this);
			
			// tr.aces('base.as', this.on_show.get('type') );
			var d:Number = x / this.stage.stageWidth;
			d *= this.on_show.get('cascade');
			d += this.on_show.get('delay');
		
			switch( this.on_show.get('type') ) {
				
				case 'pop-up':
					this.x = x;
					this.y = sc.get_y_bottom(this.right_axis);
					Tweener.addTween(this, { y:y, time:1.4, delay:d, transition:Equations.easeOutQuad } );
					
					if ( this.line_mask != null )
					{
						this.line_mask.x = x;
						this.line_mask.y = sc.get_y_bottom(this.right_axis);
						Tweener.addTween(this.line_mask, { y:y, time:1.4, delay:d, transition:Equations.easeOutQuad });
					}
					
					break;
					
				case 'explode':
					this.x = this.stage.stageWidth/2;
					this.y = this.stage.stageHeight/2;
					Tweener.addTween(this, { y:y, x:x, time:1.4, delay:d, transition:Equations.easeOutQuad } );
					
					if ( this.line_mask != null )
					{
						this.line_mask.x = this.stage.stageWidth/2;
						this.line_mask.y = this.stage.stageHeight/2;
						Tweener.addTween(this.line_mask, { y:y, x:x, time:1.4, delay:d, transition:Equations.easeOutQuad });
					}
					
					break;
				
				case 'mid-slide':
					this.x = x;
					this.y = this.stage.stageHeight / 2;
					this.alpha = 0.2;
					Tweener.addTween(this, { alpha:1, y:y, time:1.4, delay:d, transition:Equations.easeOutQuad });
					
					if ( this.line_mask != null )
					{
						this.line_mask.x = x;
						this.line_mask.y = this.stage.stageHeight / 2;
						Tweener.addTween(this.line_mask, { y:y, time:1.4, delay:d, transition:Equations.easeOutQuad });
					}
						
					break;
				
				/*
				 * the tooltips go a bit funny with this one
				 * TODO: investigate if this will work with area charts - need to move the bottom anchors
				case 'slide-in-up':
					this.x = 20;	// <-- left
					this.y = this.stage.stageHeight / 2;
					Tweener.addTween(
						this, 
						{ x:x, time:1.4, delay:d, transition:Equations.easeOutQuad, 
						onComplete:function():void {
							Tweener.addTween(this, 
							{ y:y, time:1.4, transition:Equations.easeOutQuad } ) }
						} );
					break;
				*/
				
				case 'drop':
					this.x = x;
					this.y = -height - 10;
					Tweener.addTween(this, { y:y, time:1.4, delay:d, transition:Equations.easeOutBounce } );
					
					if ( this.line_mask != null )
					{
						this.line_mask.x = x;
						this.line_mask.y = -height - 10;
						Tweener.addTween(this.line_mask, { y:y, time:1.4, delay:d, transition:Equations.easeOutQuad });
					}
					
					break;

				case 'fade-in':
					this.x = x;
					this.y = y;
					this.alpha = 0;
					Tweener.addTween(this, { alpha:1, time:1.2, delay:d, transition:Equations.easeOutQuad } );
					break;
				
				case 'shrink-in':
					this.x = x;
					this.y = y;
					this.scaleX = this.scaleY = 5;
					this.alpha = 0;
					Tweener.addTween(
						this,
						{
							scaleX:1, scaleY:1, alpha:1, time:1.2,
							delay:d, transition:Equations.easeOutQuad, 
							onComplete:function():void { tr.ace('Fin'); }
						} );
					
					break;
					
				default:
					this.y = y;
					this.x = x;
			}
		}
		
		public override function set_tip( b:Boolean ):void {
			//this.visible = b;
			if( b ) {
				this.scaleY = this.scaleX = 1.3;
				this.line_mask.scaleY = this.line_mask.scaleX = 1.3;
			}
			else {
				this.scaleY = this.scaleX = 1;
				this.line_mask.scaleY = this.line_mask.scaleX = 1;
			}
		}
		
		//
		// Dirty hack. Takes tooltip text, and replaces the #val# with the
		// tool_tip text, so noew you can do: "My Val = $#val#%", which turns into:
		// "My Val = $12.00%"
		//
		protected function replace_magic_values( t:String ): String {
			
			t = t.replace('#val#', NumberUtils.formatNumber( this._y ));
			
			// for scatter charts
			t = t.replace('#x#', NumberUtils.formatNumber(this._x));
			t = t.replace('#y#', NumberUtils.formatNumber(this._y));
			
			// debug the dots sizes
			t = t.replace('#size#', NumberUtils.formatNumber(this.radius));
			
			t = DateUtils.replace_magic_values(t, this._x);
			return t;
		}
		
		protected function calcXOnCircle(aRadius:Number, aDegrees:Number):Number
		{
			return aRadius * Math.cos(aDegrees / 180 * Math.PI);
		}
		
		protected function calcYOnCircle(aRadius:Number, aDegrees:Number):Number
		{
			return aRadius * Math.sin(aDegrees / 180 * Math.PI);
		}
		
	}
}


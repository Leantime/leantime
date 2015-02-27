package elements.menu {

	import flash.display.Sprite;
	import flash.events.MouseEvent;
	import elements.menu.menuItem;
	import caurina.transitions.Tweener;
	import caurina.transitions.Equations;
	import string.Utils;
	import flash.filters.DropShadowFilter;
	
	public class Menu extends Sprite {
		
		private var original_alpha:Number;
		private var props:Properties;
		private var first_showing:Boolean;
		private var hidden_pos:Number;
		
		public function Menu( chartID:String, json:Object ) {
			
			this.props = new DefaultMenuProperties(json);
			
			this.original_alpha = 0.4;
			this.alpha = 1;
			
			var pos:Number = 5;
			var height:Number = 0;
			this.first_showing = true;
			
			for each ( var val:Object in json.values )
			{
				var tmp:DefaultCameraIconProperties = new DefaultCameraIconProperties(val);
				var menu_item:menuItem = menu_item_factory.make(chartID, tmp);
				menu_item.x = 5;
				menu_item.y = pos;
				this.addChild(menu_item);
				height = menu_item.y + menu_item.height + 5;
				pos += menu_item.height + 5;
			}
			
			var width:Number = 0;
			
			for ( var i:Number = 0; i < this.numChildren; i++ )
				width = Math.max( width, this.getChildAt(i).width );
			
			this.draw(width+10, height);
			this.hidden_pos = height;
			
			/*
			var dropShadow:DropShadowFilter = new flash.filters.DropShadowFilter();
			dropShadow.blurX = 4;
			dropShadow.blurY = 4;
			dropShadow.distance = 4;
			dropShadow.angle = 45;
			dropShadow.quality = 2;
			dropShadow.alpha = 0.5;
			// apply shadow filter
			this.filters = [dropShadow];
			*/
			
			
			this.addEventListener(MouseEvent.MOUSE_OVER, mouseOverHandler);
			this.addEventListener(MouseEvent.MOUSE_OUT, mouseOutHandler);
		}
		
		private function draw(width:Number, height:Number): void {
			
			this.graphics.clear();
			
			var colour:Number = string.Utils.get_colour( this.props.get('colour') );
			var o_colour:Number = string.Utils.get_colour( this.props.get('outline-colour') );
			
			this.graphics.lineStyle( 1, o_colour );
			this.graphics.beginFill(colour, 1);
			this.graphics.moveTo( 0, -2 );
			this.graphics.lineTo( 0, height );
			this.graphics.lineTo( width-25, height );
			this.graphics.lineTo( width-20, height+10 );
			this.graphics.lineTo( width, height+10 );
			this.graphics.lineTo( width, -2 );
			this.graphics.endFill();
			
			// arrows
			this.graphics.lineStyle( 1, o_colour );
			this.graphics.moveTo( width-15, height+3 );
			this.graphics.lineTo( width-10, height+8 );
			this.graphics.lineTo( width-5, height+3 );
			
			this.graphics.moveTo( width-15, height );
			this.graphics.lineTo( width-10, height+5 );
			this.graphics.lineTo( width-5, height );
			
		}
		
		public function mouseOverHandler(event:MouseEvent):void {
			Tweener.removeTweens(this);
			Tweener.addTween(this, { y:0, time:0.4, transition:Equations.easeOutBounce } );
			Tweener.addTween(this, { alpha:1, time:0.4, transition:Equations.easeOutBounce } );
		}

		public function mouseOutHandler(event:MouseEvent):void {
			this.hide_menu();
		}
		
		private function hide_menu(): void
		{
			Tweener.removeTweens(this);
			Tweener.addTween(this, { y:-this.hidden_pos, time:0.4, transition:Equations.easeOutBounce } );
			Tweener.addTween(this, { alpha:this.original_alpha, time:0.4, transition:Equations.easeOutBounce } );
		}
		
		public function resize(): void {
			
			if ( this.first_showing ) {
				this.y = 0;
				this.first_showing = false;
				Tweener.removeTweens(this);
				Tweener.addTween(this, { time:3, onComplete:this.hide_menu } );
			}
			else {
				this.y = -(this.height) + 10;
			}
			this.x = this.stage.stageWidth - this.width - 5;
			
		}
	}
}
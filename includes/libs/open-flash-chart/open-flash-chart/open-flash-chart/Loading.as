/**
* ...
* @author Default
* @version 0.1
*/

package  {
	import flash.display.Sprite;
	import flash.text.TextField;
	import flash.text.TextFormat;
	import flash.events.Event;
	import flash.filters.DropShadowFilter;

	public class Loading extends Sprite {
		private var tf:TextField;
		
		public function Loading( text:String ) {
			
			this.tf = new TextField();
			this.tf.text = text;
			
			var fmt:TextFormat = new TextFormat();
			fmt.color = 0x000000;
			fmt.font = "Verdana";
			fmt.size = 12;
			fmt.align = "center";
			
			this.tf.setTextFormat(fmt);
			this.tf.autoSize = "left";
			this.tf.x = 5;
			this.tf.y = 5;
			
			//
			// HACK! For some reason the Stage.height is not
			// correct the very first time this object is created
			// so we wait untill the first frame before placing
			// the movie clip at the center of the Stage.
			//
			
			this.addEventListener( Event.ENTER_FRAME, this.onEnter );
				
			this.addChild( this.tf );
			
			this.graphics.lineStyle( 2, 0x808080, 1 );
			this.graphics.beginFill( 0xf0f0f0 );
			this.graphics.drawRoundRect(0, 0, this.tf.width + 10, this.tf.height + 10, 5, 5);
			
			var spin:Sprite = new Sprite();
			spin.x = this.tf.width + 40;
			spin.y = (this.tf.height + 10) / 2;
			
			var radius:Number = 15;
			var dots:Number = 6;
			var colours:Array = [0xF0F0F0,0xD0D0D0,0xB0B0B0,0x909090,0x707070,0x505050,0x303030];
			
			for( var i:Number=0; i<dots; i++ )
			{
				var deg:Number = (360/dots)*i;
				var radians:Number = deg * (Math.PI/180);
				var x:Number = radius * Math.cos(radians);
				var y:Number = radius * Math.sin(radians);
				
				spin.graphics.lineStyle(0, 0, 0);
				spin.graphics.beginFill( colours[i], 1 );
				spin.graphics.drawCircle( x, y, 4 );
			}
			
			this.addChild( spin );

			var dropShadow:DropShadowFilter = new DropShadowFilter();
			dropShadow.blurX = 4;
			dropShadow.blurY = 4;
			dropShadow.distance = 4;
			dropShadow.angle = 45;
			dropShadow.quality = 2;
			dropShadow.alpha = 0.5;
			// apply shadow filter
			this.filters = [dropShadow];
		
		/*
		
			
			
			spin.onEnterFrame = function ()
			{
				this._rotation += 5;
			}
		
			*/
		}
		
		private function onEnter(event:Event):void {
			
			if( this.stage ) {
				this.x = (this.stage.stageWidth/2)-((this.tf.width+10)/2);
				this.y = (this.stage.stageHeight/2)-((this.tf.height+10)/2);
				// this.removeEventListener( Event.ENTER_FRAME, this.onEnter );
				// tr.ace('ppp');
			}
			this.getChildAt(1).rotation += 5;
		}
	
	}
	
}

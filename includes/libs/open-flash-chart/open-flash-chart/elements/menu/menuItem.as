package elements.menu {

	import flash.display.Sprite;
	import flash.events.MouseEvent;
	import flash.external.ExternalInterface;
	import flash.text.TextField;
    import flash.text.TextFieldType;
	import flash.text.TextFormat;
	import flash.filters.GlowFilter;
	import string.Utils;

	public class menuItem extends Sprite {
		
		protected var chartId:String;
		protected var props:Properties;
		
		public function menuItem(chartId:String, props:Properties) {
			
			this.props = props;
			
			this.buttonMode = true;
			this.useHandCursor = true;
			this.chartId = chartId;
			 
			this.alpha = 0.5;
			
			var width:Number = this.add_elements();
			
			this.draw_bg(
				width +
				10 // 5px padding on either side
				);
			
			this.addEventListener(MouseEvent.CLICK, mouseClickHandler);
			this.addEventListener(MouseEvent.MOUSE_DOWN, mouseDownHandler);
			this.addEventListener(MouseEvent.MOUSE_OVER, mouseOverHandler);
			this.addEventListener(MouseEvent.MOUSE_OUT, mouseOutHandler);
		}
		
		protected function add_elements(): Number {
			var width:Number = this.add_text(this.props.get('text'), 5);
			return width;
		}
		
		private function draw_bg( width:Number ):void {
			this.graphics.beginFill(string.Utils.get_colour( this.props.get('background-colour') ));
			this.graphics.drawRoundRect(0, 0, width, 20, 5, 5 );
			this.graphics.endFill();
		}
		
		
		protected function add_text(text:String, left:Number): Number {
			var title:TextField = new TextField();
            title.x = left;
			title.y = 0;
			
			//this.text = 'Save chart';
			
			title.htmlText = text;
			
			var fmt:TextFormat = new TextFormat();
			fmt.color = string.Utils.get_colour( this.props.get('text-colour') );
			fmt.font = 'Verdana';
//			fmt.bold = this.css.font_weight == 'bold'?true:false;
			fmt.size = 10;// this.css.font_size;
			fmt.underline = true;
//			fmt.align = "center";
		
			title.setTextFormat(fmt);
			title.autoSize = "left";
			
			// so we don't get an I-beam cursor when we mouse
			// over the text - pass mouse messages onto the button
			title.mouseEnabled = false;
			
//			title.border = true;
			
			this.addChild(title);
			
			return title.width;
		}

		public function mouseClickHandler(event:MouseEvent):void {
			this.alpha = 0.0;
			tr.aces('Menu item clicked:', this.props.get('javascript-function')+'('+this.chartId+')');
			ExternalInterface.call(this.props.get('javascript-function'), this.chartId);
			this.alpha = 1.0;
		}

		public function mouseOverHandler(event:MouseEvent):void {
			this.alpha = 1;

			///Glow Filter
			var glow:GlowFilter = new GlowFilter();
			glow.color = string.Utils.get_colour( this.props.get('glow-colour') );
			glow.alpha = 0.8;
			glow.blurX = 4;
			glow.blurY = 4;
			glow.inner = false;
			
			this.filters = new Array(glow);
		}
		
		public function mouseDownHandler(event:MouseEvent):void {
			this.alpha = 1.0;
		}

		public function mouseOutHandler(event:MouseEvent):void {
			this.alpha = 0.5;
			this.filters = new Array();
		}
	}
}

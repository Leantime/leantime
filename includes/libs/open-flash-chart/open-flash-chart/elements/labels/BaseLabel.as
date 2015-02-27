/* */

package elements.labels {
	
	import flash.display.Sprite;
	import flash.display.Stage;
    import flash.text.TextField;
    import flash.text.TextFieldType;
	import flash.text.TextFormat;
	import flash.events.Event;
	import flash.text.TextFieldAutoSize;
	import string.Css;
	
	
	public class BaseLabel extends Sprite {
		public var text:String;
		protected var css:Css;
		public var style:String;
		protected var _height:Number;
		
		public function BaseLabel()	{}
		
		protected function build( text:String ):void {
			
			var title:TextField = new TextField();
            title.x = 0;
			title.y = 0;
			
			this.text = text;
			
			title.htmlText = this.text;
			
			var fmt:TextFormat = new TextFormat();
			fmt.color = this.css.color;
			//fmt.font = "Verdana";
			fmt.font = this.css.font_family?this.css.font_family:'Verdana';
			fmt.bold = this.css.font_weight == 'bold'?true:false;
			fmt.size = this.css.font_size;
			fmt.align = "center";
		
			title.setTextFormat(fmt);
			title.autoSize = "left";
			
			title.y = this.css.padding_top+this.css.margin_top;
			title.x = this.css.padding_left+this.css.margin_left;
			
//			title.border = true;
			
			if ( this.css.background_colour_set )
			{
				this.graphics.beginFill( this.css.background_colour, 1);
				this.graphics.drawRect(0,0,this.css.padding_left + title.width + this.css.padding_right, this.css.padding_top + title.height + this.css.padding_bottom );
				this.graphics.endFill();
			}

			this.addChild(title);
		}
		
		public function get_width():Number {
			return this.getChildAt(0).width;
		}
		
		public function die(): void {
			
			this.graphics.clear();
			while ( this.numChildren > 0 )
				this.removeChildAt(0);
		}
	}
}
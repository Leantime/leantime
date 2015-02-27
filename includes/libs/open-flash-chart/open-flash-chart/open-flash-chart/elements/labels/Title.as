/* */

package elements.labels {
	
	import flash.display.Sprite;
	import flash.display.Stage;
    import flash.text.TextField;
    import flash.text.TextFieldType;
	import flash.text.TextFormat;
	import flash.text.StyleSheet;
	import flash.events.Event;
	import flash.text.TextFieldAutoSize;
	import string.Css;
	import string.Utils;
	
	public class Title extends BaseLabel {
		public var colour:Number;
		public var size:Number;
		private var top_padding:Number = 0;
		
		public function Title( json:Object )
		{
			super();
				
			if( !json )
				return;
			
			// defaults:
			this.style = "font-size: 12px";
			
			object_helper.merge_2( json, this );
			
			this.css = new Css( this.style );
			this.build( this.text );
		}
		
		public function resize():void {
			if( this.text == null )
				return;
				
			this.getChildAt(0).width = this.stage.stageWidth;
			
			
			//
			// is the title aligned (text-align: xxx)?
			//
			var tmp:String = this.css.text_align;
			switch( tmp )
			{
				case 'left':
					this.x = this.css.margin_left;
					break;
						
				case 'right':
					this.x = this.stage.stageWidth - ( this.get_width() + this.css.margin_right );
					break;
						
				case 'center':
				default:
					this.x = (this.stage.stageWidth/2) - (this.get_width()/2);
					break;
			}
				
			this.y = this.css.margin_top;
		}
		
		public function get_height():Number {
			
			if ( this.text == null )
				return 0;
			else
				return this.css.padding_top+
					this.css.margin_top+
					this.getChildAt(0).height+
					this.css.padding_bottom+
					this.css.margin_bottom;
		}
	}
}
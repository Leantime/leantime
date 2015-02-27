/* */

package {
	
	import flash.display.Sprite;
	import flash.display.Stage;
    import flash.text.TextField;
    import flash.text.TextFieldType;
	import flash.text.TextFormat;
	import flash.events.Event;
	import flash.text.TextFieldAutoSize;
	//import string.Css;
	import flash.text.StyleSheet;
	import flash.events.TextEvent;

	
	
	public class ErrorMsg extends Sprite {
		
		public function ErrorMsg( msg:String ):void {
			
			var title:TextField = new TextField();
			title.text = msg;
			
			var fmt:TextFormat = new TextFormat();
			fmt.color = 0x000000;
			fmt.font = "Courier";
			fmt.size = 10;
			fmt.align = "left";
		
			title.setTextFormat(fmt);
			title.autoSize = "left";
			title.border = true;
			title.x = 5;
			title.y = 5;
			
			this.addChild(title);
		}
		
		public function add_html( html:String ): void {
			
			var txt:TextField = new TextField();
			
			var style:StyleSheet = new StyleSheet();

			var hover:Object = new Object();
			hover.fontWeight = "bold";
			hover.color = "#0000FF";
			
			var link:Object = new Object();
			link.fontWeight = "bold";
			link.textDecoration= "underline";
			link.color = "#0000A0";
			
			var active:Object = new Object();
			active.fontWeight = "bold";
			active.color = "#0000A0";

			var visited:Object = new Object();
			visited.fontWeight = "bold";
			visited.color = "#CC0099";
			visited.textDecoration= "underline";

			style.setStyle("a:link", link);
			style.setStyle("a:hover", hover);
			style.setStyle("a:active", active);
			style.setStyle(".visited", visited); //note Flash doesn't support a:visited
			
			txt.styleSheet = style;
			txt.htmlText = html;
			txt.autoSize = "left";
			txt.border = true;
			
			var t:TextField = this.getChildAt(0) as TextField;
			txt.y = t.y + t.height + 10;
			txt.x = 5;
			
			this.addChild( txt );
			
		}
	}
}
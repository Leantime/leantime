package elements.menu {

	import flash.display.Sprite;
	import flash.events.MouseEvent;
	import flash.external.ExternalInterface;
	
	import flash.text.TextField;
    import flash.text.TextFieldType;
	import flash.text.TextFormat;

	public class CameraIcon extends menuItem {
		
		public function CameraIcon(chartId:String, props:Properties) {
			super(chartId, props);
		}
		
		protected override function add_elements(): Number {
	
			this.draw_camera();
			var width:Number = this.add_text(this.props.get('text'), 35);
			
			return width+30;	// 30 is the icon width
		}
		
		private function draw_camera():void {
			
			var s:Sprite = new Sprite();
			
			s.graphics.beginFill(0x505050);
			s.graphics.drawRoundRect(2, 4, 26, 14, 2, 2);
			s.graphics.drawRect(20, 1, 5, 3);
			s.graphics.endFill();

			s.graphics.beginFill(0x202020);
			s.graphics.drawCircle(9, 11, 4.5);
			s.graphics.endFill();
			
			this.addChild(s);
			
		}
	}
}

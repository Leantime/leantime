package elements.axis {
	
	import flash.text.TextField;
	
	public class YTextField extends TextField {
		public var y_val:Number;
		
		//
		// mini class to hold the y value of the
		// Y Axis label (so we can position it later )
		//
		public function YTextField() {
			super();
			this.y_val = 0;
		}
	}
}
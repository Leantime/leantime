package elements.axis {
	import flash.text.TextField;
	
	public class YAxisLabelsRight extends YAxisLabelsBase {
		
		public function YAxisLabelsRight(json:Object) {
			
			this.lblText = "#val#";
			this.i_need_labels = true;
	
			super(json, 'y_axis_right');
		}

		// move y axis labels to the correct x pos
		public override function resize( left:Number, box:ScreenCoords ):void {
			var maxWidth:Number = this.get_width();
			var i:Number;
			var tf:YTextField;
			
			for( i=0; i<this.numChildren; i++ ) {
				// left align
				tf = this.getChildAt(i) as YTextField;
				tf.x = left; // - tf.width + maxWidth;
			}
			
			// now move it to the correct Y, vertical center align
			for ( i=0; i < this.numChildren; i++ ) {
				tf = this.getChildAt(i) as YTextField;
				if (tf.rotation != 0) {
					tf.y = box.get_y_from_val( tf.y_val, true ) + (tf.height / 2);
				}
				else {
					tf.y = box.get_y_from_val( tf.y_val, true ) - (tf.height / 2);
				}
				if (tf.y < 0 && box.top == 0) // Tried setting tf.height but that didnt work 
					tf.y = (tf.rotation != 0) ? tf.height : tf.textHeight - tf.height;
			}
		}
	}
}
/* */

package elements.axis {
	
	import flash.display.Sprite;
    import flash.text.TextField;
	import flash.geom.Rectangle;
	
	public class AxisLabel extends TextField {
		public var xAdj:Number = 0;
		public var yAdj:Number = 0;
		public var leftOverhang:Number = 0;
		public var rightOverhang:Number = 0;
		public var xVal:Number = NaN;
		public var yVal:Number = NaN;
		
		public function AxisLabel()	{}
		
		/**
		 * Rotate the label and align it to the X Axis tick
		 * 
		 * @param	rotation
		 */
		public function rotate_and_align( rotation:Number, align:String, parent:Sprite ): void
		{ 
			rotation = rotation % 360;
			if (rotation < 0) rotation += 360;
			
			var myright:Number = this.width * Math.cos(rotation * Math.PI / 180);
			var myleft:Number = this.height * Math.cos((90 - rotation) * Math.PI / 180);
			var mytop:Number = this.height * Math.sin((90 - rotation) * Math.PI / 180);
			var mybottom:Number = this.width * Math.sin(rotation * Math.PI / 180);
			
			if (((rotation % 90) == 0) || (align == "center"))
			{
				this.xAdj = (myleft - myright) / 2;
			}
			else
			{
				this.xAdj = (rotation < 180) ? myleft / 2 : -myright + (myleft / 2);
			}

			if (rotation > 90) {
				this.yAdj = -mytop;
			}
			if (rotation > 180) {
				this.yAdj = -mytop - mybottom;
			}
			if (rotation > 270) {
				this.yAdj = - mybottom;
			}
			this.rotation = rotation;

			var titleRect:Rectangle = this.getBounds(parent);
			this.leftOverhang = Math.abs(titleRect.x + this.xAdj);
			this.rightOverhang = Math.abs(titleRect.x + titleRect.width + this.xAdj);
      }
   }
}
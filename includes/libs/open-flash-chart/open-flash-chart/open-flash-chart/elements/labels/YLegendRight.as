package elements.labels {
	public class YLegendRight extends YLegendBase {
		
		public function YLegendRight( json:Object ) {
			super( json, 'y2' );
		}
		
		public override function resize():void {
			if ( this.numChildren == 0 )
				return;
			
			this.y = (this.stage.stageHeight/2)+(this.getChildAt(0).height/2);
			this.x = this.stage.stageWidth-this.getChildAt(0).width;
		}
	}
}
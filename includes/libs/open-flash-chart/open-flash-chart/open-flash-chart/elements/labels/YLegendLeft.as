package elements.labels {
	
	public class YLegendLeft extends YLegendBase {
		
		public function YLegendLeft( json:Object ) {
			super( json, 'y' );
		}
		
		public override function resize():void {
			if ( this.numChildren == 0 )
				return;
			
			this.y = (this.stage.stageHeight/2)+(this.getChildAt(0).height/2);
			this.x = 0;
		}
	}
}
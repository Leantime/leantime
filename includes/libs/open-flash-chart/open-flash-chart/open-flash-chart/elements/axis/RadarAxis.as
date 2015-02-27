package elements.axis {
	import flash.display.Sprite;
	import flash.geom.Point;
	import string.Utils;
	
	
	public class RadarAxis extends Sprite {
		
		private var style:Object;
		private var TO_RADIANS:Number = Math.PI / 180;
		
		private var colour:Number;
		private var grid_colour:Number;
		private var labels:RadarAxisLabels;
		private var spoke_labels:RadarSpokeLabels;
		
		function RadarAxis( json:Object )
		{
			// default values
			this.style = {
				stroke:			2,
				colour:			'#784016',
				'grid-colour':	'#F5E1AA',
				min:			0,
				max:			null,
				steps:			1
			};
			
			if( json != null )
				object_helper.merge_2( json, this.style );
				
			this.colour = Utils.get_colour( this.style.colour );
			this.grid_colour = Utils.get_colour( this.style['grid-colour'] );
			
			this.labels = new RadarAxisLabels( json.labels );
			this.addChild( this.labels );
			
			this.spoke_labels = new RadarSpokeLabels( json['spoke-labels'] );
			this.addChild( this.spoke_labels );
		}
		
		//
		// how many items in the X axis?
		//
		public function get_range():Range {
			return new Range( this.style.min, this.style.max, this.style.steps, false );
		}
		
		public function resize( sc:ScreenCoordsRadar ):void
		{
			this.x = 0;
			this.y = 0;
			this.graphics.clear();
			
			// this is going to change the radius
			this.spoke_labels.resize( sc );
			
			var count:Number = sc.get_angles();
			
			// draw the grid behind the axis
			this.draw_grid( sc, count );
			this.draw_axis( sc, count );
			
			this.labels.resize( sc );
		}
		
		private function draw_axis( sc:ScreenCoordsRadar, count:Number ): void {
			
			this.graphics.lineStyle(this.style.stroke, this.colour, 1, true);
			
			for ( var i:Number = 0; i < count; i++ ) {

				//
				// assume 0 is MIN
				//
				var p:flash.geom.Point = sc.get_get_x_from_pos_and_y_from_val( i, 0 );
				this.graphics.moveTo( p.x, p.y );
				
				var q:flash.geom.Point = sc.get_get_x_from_pos_and_y_from_val( i, sc.get_max() );
				this.graphics.lineTo( q.x, q.y );
			}
		}
		
		private function draw_grid( sc:ScreenCoordsRadar, count:Number ):void {
		
			this.graphics.lineStyle(1, this.grid_colour, 1, true);
			
			// floating point addition error:
			var max:Number = sc.get_max() + 0.00001;
			
			var r_step:Number = this.style.steps;
			var p:flash.geom.Point;
			
			//
			// start in the middle and move out drawing the grid,
			// don't draw at 0
			//
			for ( var r_pos:Number = r_step; r_pos <= max; r_pos+=r_step ) {
				
				p = sc.get_get_x_from_pos_and_y_from_val( 0, r_pos );
				this.graphics.moveTo( p.x, p.y );
				
				// draw from each spoke
				for ( var i:Number = 1; i < (count+1); i++ ) {
					
					p = sc.get_get_x_from_pos_and_y_from_val( i, r_pos );
					this.graphics.lineTo( p.x, p.y );
				}
			}
		}
		
		public function die(): void {
			
			this.style = null;
			this.labels.die();
			this.spoke_labels.die();
		
			this.graphics.clear();
			while ( this.numChildren > 0 )
				this.removeChildAt(0);
		}
	}
}
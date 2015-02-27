package charts.series.dots {
	
	public class dot_factory {
		
		public static function make( index:Number, style:Properties ):PointDotBase {
			
			// tr.aces( 'dot factory type', style.get('type'));
			
			switch( style.get('type') )
			{
				case 'star':
					return new star(index, style);
					break;
					
				case 'bow':
					return new bow(index, style);
					break;
				
				case 'anchor':
					return new anchor(index, style);
					break;
				
				case 'dot':
					return new Point(index, style);
					break;
				
				case 'solid-dot':
					return new PointDot(index, style);
					break;
					
				case 'hollow-dot':
					return new Hollow(index, style);
					break;
					
				default:
				//
				// copy out the bow tie and then remove
				//
					return new Point(index, style);
					// return new scat(style);
					break;
			}
		}
	}
}
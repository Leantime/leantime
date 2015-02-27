package charts.series.dots {

	public class DefaultDotProperties extends Properties
	{
		//
		// things that all dots share
		//
		public function DefaultDotProperties(json:Object, colour:String, axis:String) {
			// tr.ace_json(json);
			
			// the user JSON can override any of these:
			var parent:Properties = new Properties( {
				axis:			axis,
				'type':			'dot',
				'dot-size': 	5,
				'halo-size':	2,
				'colour':		colour,
				'tip':			'#val#',
				alpha:			1,
				// this is for anchors:
				rotation:		0,
				sides:			3,
				// this is for hollow dot:
				width:			1
				} );
				
				
				
			super( json, parent );
			
			tr.aces('4', this.get('axis'));
			// tr.aces('4', this.get('colour'));
			// tr.aces('4', this.get('type'));
		}
	}
}
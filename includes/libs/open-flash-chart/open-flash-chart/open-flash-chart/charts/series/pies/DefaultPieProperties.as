package charts.series.pies {

	public class DefaultPieProperties extends Properties
	{
	
		public function DefaultPieProperties(json:Object) {
			// tr.ace_json(json);
			
			// the user JSON can override any of these:
			var parent:Properties = new Properties( {
				alpha:				0.5,
				'start-angle':		90,
				'label-colour':		null,  // null means use colour of the slice
				'font-size':		10,
				'gradient-fill':	false,
				stroke:				1,
				colours:			["#900000", "#009000"],	// slices colours
				animate:			[{"type":"fade-in"}],
				tip:				'#val# of #total#',	// #percent#, #label#
				'no-labels':		false,
				'on-click':			null
				} );
				
				
				
			super( json, parent );
			
			tr.aces('4', this.get('start-angle'));
			// tr.aces('4', this.get('colour'));
			// tr.aces('4', this.get('type'));
		}
	}
}
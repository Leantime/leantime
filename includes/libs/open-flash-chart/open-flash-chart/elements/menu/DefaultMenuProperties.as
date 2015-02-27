package elements.menu {

	public class DefaultMenuProperties extends Properties
	{
		public function DefaultMenuProperties( json:Object ) {
			
			// the user JSON can override any of these:
			var parent:Properties = new Properties( {
				'colour':			'#E0E0E0',
				"outline-colour":	"#707070",
				'camera-text':		"Save chart"
				} );
			
			super( json, parent );
	
		}
	}
}
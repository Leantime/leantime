package charts {
	import charts.series.Element;
	import charts.series.bars.ECandle;
	import string.Utils;

	
	public class Candle extends BarBase {
		private var negative_colour:Number;
		
		public function Candle( json:Object, group:Number ) {
			
			super( json, group );
			
		tr.aces('---');
		tr.ace_json(json);
		tr.aces( 'neg', props.has('negative-colour'), props.get_colour('negative-colour'));
		
		}
		
		//
		// called from the base object
		//
		protected override function get_element( index:Number, value:Object ): Element {
		
			var default_style:Properties = this.get_element_helper_prop( value );	
			if(this.props.has('negative-colour'))
				default_style.set('negative-colour', this.props.get('negative-colour'));
			
			return new ECandle( index, default_style, this.group );
		}
	}
}
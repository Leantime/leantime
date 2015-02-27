package {
	import com.serialization.json.JSON;
	
	/**
	 * A simple function to inspect the JSON for items
	 * before we build the chart
	 */
	public class JsonInspector
	{
		
		public static function has_pie_chart( json:Object ): Boolean
		{
			
			var elements:Array = json['elements'] as Array;
			
			for( var i:Number = 0; i < elements.length; i++ )
			{
				// tr.ace( elements[i]['type'] );
				
				if ( elements[i]['type'] == 'pie' )
					return true;
			}
			
			return false;
		}
		
		public static function is_radar( json:Object ): Boolean
		{
			
			if ( json['radar_axis'] != null )
				return true;
			
			return false;
		}
	}
}
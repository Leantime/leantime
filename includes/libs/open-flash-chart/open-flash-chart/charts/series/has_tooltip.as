package charts.series {
	
	/**
	 * anything that wants to use our tooltips
	 * must implement this interface
	 */
	public interface has_tooltip {
		
		// get the tip string
		function get_tooltip():String;
		
		// this should return a Point
		function get_tip_pos():Object;
		
		// if true, show hover state,
		// if false the item should go
		// back to the ground state. Not hovered.
		function set_tip( b:Boolean ):void;
	}
}
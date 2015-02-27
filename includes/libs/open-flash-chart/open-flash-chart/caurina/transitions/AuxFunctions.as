package caurina.transitions {

	/**
	 * Generic, auxiliary functions
	 *
	 * @author		Zeh Fernando
	 * @version		1.0.0
	 * @private
	 */

	public class AuxFunctions {

		/**
		 * Gets the R (xx0000) bits from a number
		 *
		 * @param		p_num				Number		Color number (ie, 0xffff00)
		 * @return							Number		The R value
		 */
		public static function numberToR(p_num:Number):Number {
			// The initial & is meant to crop numbers bigger than 0xffffff
			return (p_num & 0xff0000) >> 16;
		}

		/**
		 * Gets the G (00xx00) bits from a number
		 *
		 * @param		p_num				Number		Color number (ie, 0xffff00)
		 * @return							Number		The G value
		 */
		public static function numberToG(p_num:Number):Number {
			return (p_num & 0xff00) >> 8;
		}

		/**
		 * Gets the B (0000xx) bits from a number
		 *
		 * @param		p_num				Number		Color number (ie, 0xffff00)
		 * @return							Number		The B value
		 */
		public static function numberToB(p_num:Number):Number {
			return (p_num & 0xff);
		}

		/**
		 * Checks whether a string is on an array
		 *
		 * @param		p_string			String		String to search for
		 * @param		p_array				Array		Array to be searched
		 * @return							Boolean		Whether the array contains the string or not
		 */
		public static function isInArray(p_string:String, p_array:Array):Boolean {
			var l:uint = p_array.length;
			for (var i:uint = 0; i < l; i++) {
				if (p_array[i] == p_string) return true;
			}
			return false;
		}

		/**
		 * Returns the number of properties an object has
		 *
		 * @param		p_object			Object		Target object with a number of properties
		 * @return							Number		Number of total properties the object has
		 */
		public static function getObjectLength(p_object:Object):uint {
			var totalProperties:uint = 0;
			for (var pName:String in p_object) totalProperties ++;
			return totalProperties;
		}
        
        /* Takes a variable number of objects as parameters and "adds" their properties, form left to right. If a latter object defines a property as null, it will be removed from the final object
    	* @param		args				Object(s)	A variable number of objects
    	* @return							Object		An object with the sum of all paremeters added as properties.
    	*/
    	public static function concatObjects(...args) : Object{
    		var finalObject : Object = {};
    		var currentObject : Object;
    		for (var i : int = 0; i < args.length; i++){
    			currentObject = args[i];
    			for (var prop : String in currentObject){
    				if (currentObject[prop] == null){
    				    // delete in case is null
    					delete finalObject[prop];
    				}else{
    					finalObject[prop] = currentObject[prop]
    				}
    			}
    		}
    		return finalObject;
    	}
	}
}
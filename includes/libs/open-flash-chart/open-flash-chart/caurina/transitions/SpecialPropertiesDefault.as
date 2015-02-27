package caurina.transitions {

	import flash.filters.BitmapFilter;
	import flash.filters.BlurFilter;
//	import flash.filters.GlowFilter;
	import flash.geom.ColorTransform;
	import flash.media.SoundTransform;

	/**
	 * SpecialPropertiesDefault
	 * List of default special properties for the Tweener class
	 * The function names are strange/inverted because it makes for easier debugging (alphabetic order). They're only for internal use (on this class) anyways.
	 *
	 * @author		Zeh Fernando, Nate Chatellier
	 * @version		1.0.1
	 * @private
	 */

	public class SpecialPropertiesDefault {
	
		/**
		 * There's no constructor.
		 */
		public function SpecialPropertiesDefault () {
			trace ("SpecialProperties is a static class and should not be instantiated.")
		}
	
		/**
		 * Registers all the special properties to the Tweener class, so the Tweener knows what to do with them.
		 */
		public static function init():void {

			// Normal properties
			Tweener.registerSpecialProperty("_frame", frame_get, frame_set);
			Tweener.registerSpecialProperty("_sound_volume", _sound_volume_get, _sound_volume_set);
			Tweener.registerSpecialProperty("_sound_pan", _sound_pan_get, _sound_pan_set);
			Tweener.registerSpecialProperty("_color_ra", _color_property_get, _color_property_set, ["redMultiplier"]);
			Tweener.registerSpecialProperty("_color_rb", _color_property_get, _color_property_set, ["redOffset"]);
			Tweener.registerSpecialProperty("_color_ga", _color_property_get, _color_property_set, ["greenMultiplier"]);
			Tweener.registerSpecialProperty("_color_gb", _color_property_get, _color_property_set, ["greenOffset"]);
			Tweener.registerSpecialProperty("_color_ba", _color_property_get, _color_property_set, ["blueMultiplier"]);
			Tweener.registerSpecialProperty("_color_bb", _color_property_get, _color_property_set, ["blueOffset"]);
			Tweener.registerSpecialProperty("_color_aa", _color_property_get, _color_property_set, ["alphaMultiplier"]);
			Tweener.registerSpecialProperty("_color_ab", _color_property_get, _color_property_set, ["alphaOffset"]);
			Tweener.registerSpecialProperty("_autoAlpha", _autoAlpha_get, _autoAlpha_set);

			// Normal splitter properties
			Tweener.registerSpecialPropertySplitter("_color", _color_splitter);
			Tweener.registerSpecialPropertySplitter("_colorTransform", _colorTransform_splitter);

			// Scale splitter properties
			Tweener.registerSpecialPropertySplitter("_scale", _scale_splitter);

			// Filter tweening properties - BlurFilter
			Tweener.registerSpecialProperty("_blur_blurX", _filter_property_get, _filter_property_set, [BlurFilter, "blurX"]);
			Tweener.registerSpecialProperty("_blur_blurY", _filter_property_get, _filter_property_set, [BlurFilter, "blurY"]);
			Tweener.registerSpecialProperty("_blur_quality", _filter_property_get, _filter_property_set, [BlurFilter, "quality"]);

			// Filter tweening splitter properties
			Tweener.registerSpecialPropertySplitter("_filter", _filter_splitter);

			// Bezier modifiers
			Tweener.registerSpecialPropertyModifier("_bezier", _bezier_modifier, _bezier_get);

		}
	

		// ==================================================================================================================================
		// PROPERTY GROUPING/SPLITTING functions --------------------------------------------------------------------------------------------


		// ----------------------------------------------------------------------------------------------------------------------------------
		// _color

		/**
		 * Splits the _color parameter into specific color variables
		 *
		 * @param		p_value				Number		The original _color value
		 * @return							Array		An array containing the .name and .value of all new properties
		 */
		public static function _color_splitter (p_value:*, p_parameters:Array):Array {
			var nArray:Array = new Array();
			if (p_value == null) {
				// No parameter passed, so just resets the color
				nArray.push({name:"_color_ra", value:1});
				nArray.push({name:"_color_rb", value:0});
				nArray.push({name:"_color_ga", value:1});
				nArray.push({name:"_color_gb", value:0});
				nArray.push({name:"_color_ba", value:1});
				nArray.push({name:"_color_bb", value:0});
			} else {
				// A color tinting is passed, so converts it to the object values
				nArray.push({name:"_color_ra", value:0});
				nArray.push({name:"_color_rb", value:AuxFunctions.numberToR(p_value)});
				nArray.push({name:"_color_ga", value:0});
				nArray.push({name:"_color_gb", value:AuxFunctions.numberToG(p_value)});
				nArray.push({name:"_color_ba", value:0});
				nArray.push({name:"_color_bb", value:AuxFunctions.numberToB(p_value)});
			}
			return nArray;
		}


		// ----------------------------------------------------------------------------------------------------------------------------------
		// _colorTransform

		/**
		 * Splits the _colorTransform parameter into specific color variables
		 *
		 * @param		p_value				Number		The original _colorTransform value
		 * @return							Array		An array containing the .name and .value of all new properties
		 */
		public static function _colorTransform_splitter (p_value:*, p_parameters:Array):Array {
			var nArray:Array = new Array();
			if (p_value == null) {
				// No parameter passed, so just resets the color
				nArray.push({name:"_color_ra", value:1});
				nArray.push({name:"_color_rb", value:0});
				nArray.push({name:"_color_ga", value:1});
				nArray.push({name:"_color_gb", value:0});
				nArray.push({name:"_color_ba", value:1});
				nArray.push({name:"_color_bb", value:0});
			} else {
				// A color tinting is passed, so converts it to the object values
				if (p_value.ra != undefined) nArray.push({name:"_color_ra", value:p_value.ra});
				if (p_value.rb != undefined) nArray.push({name:"_color_rb", value:p_value.rb});
				if (p_value.ga != undefined) nArray.push({name:"_color_ba", value:p_value.ba});
				if (p_value.gb != undefined) nArray.push({name:"_color_bb", value:p_value.bb});
				if (p_value.ba != undefined) nArray.push({name:"_color_ga", value:p_value.ga});
				if (p_value.bb != undefined) nArray.push({name:"_color_gb", value:p_value.gb});
				if (p_value.aa != undefined) nArray.push({name:"_color_aa", value:p_value.aa});
				if (p_value.ab != undefined) nArray.push({name:"_color_ab", value:p_value.ab});
			}
			return nArray;
		}


		// ----------------------------------------------------------------------------------------------------------------------------------
		// scale
		public static function _scale_splitter(p_value:Number, p_parameters:Array) : Array{
			var nArray:Array = new Array();
			nArray.push({name:"scaleX", value: p_value});
			nArray.push({name:"scaleY", value: p_value});
			return nArray;
		}


		// ----------------------------------------------------------------------------------------------------------------------------------
		// filters

		/**
		 * Splits the _filter, _blur, etc parameter into specific filter variables
		 *
		 * @param		p_value				BitmapFilter	A BitmapFilter instance
		 * @return							Array			An array containing the .name and .value of all new properties
		 */
		public static function _filter_splitter (p_value:BitmapFilter, p_parameters:Array):Array {
			var nArray:Array = new Array();
			if (p_value is BlurFilter) {
				nArray.push({name:"_blur_blurX",		value:BlurFilter(p_value).blurX});
				nArray.push({name:"_blur_blurY",		value:BlurFilter(p_value).blurY});
				nArray.push({name:"_blur_quality",		value:BlurFilter(p_value).quality});
			} else {
				// ?
				trace ("??");
			}
			return nArray;
		}

		// ==================================================================================================================================
		// NORMAL SPECIAL PROPERTY functions ------------------------------------------------------------------------------------------------


		// ----------------------------------------------------------------------------------------------------------------------------------
		// _frame
	
		/**
		 * Returns the current frame number from the movieclip timeline
		 *
		 * @param		p_obj				Object		MovieClip object
		 * @return							Number		The current frame
		 */
		public static function frame_get (p_obj:Object):Number {
			return p_obj.currentFrame;
		}
	
		/**
		 * Sets the timeline frame
		 *
		 * @param		p_obj				Object		MovieClip object
		 * @param		p_value				Number		New frame number
		 */
		public static function frame_set (p_obj:Object, p_value:Number):void {
			p_obj.gotoAndStop(Math.round(p_value));
		}
	
		
		// ----------------------------------------------------------------------------------------------------------------------------------
		// _sound_volume
	
		/**
		 * Returns the current sound volume
		 *
		 * @param		p_obj				Object		Sound object
		 * @return							Number		The current volume
		 */
		public static function _sound_volume_get (p_obj:Object):Number {
			return p_obj.soundTransform.volume;
		}
	
		/**
		 * Sets the sound volume
		 *
		 * @param		p_obj				Object		Sound object
		 * @param		p_value				Number		New volume
		 */
		public static function _sound_volume_set (p_obj:Object, p_value:Number):void {
			var sndTransform:SoundTransform = p_obj.soundTransform;
			sndTransform.volume = p_value;
			p_obj.soundTransform = sndTransform;
		}
	
	
		// ----------------------------------------------------------------------------------------------------------------------------------
		// _sound_pan
	
		/**
		 * Returns the current sound pan
		 *
		 * @param		p_obj				Object		Sound object
		 * @return							Number		The current pan
		 */
		public static function _sound_pan_get (p_obj:Object):Number {
			return p_obj.soundTransform.pan;
		}
	
		/**
		 * Sets the sound volume
		 *
		 * @param		p_obj				Object		Sound object
		 * @param		p_value				Number		New pan
		 */
		public static function _sound_pan_set (p_obj:Object, p_value:Number):void {
			var sndTransform:SoundTransform = p_obj.soundTransform;
			sndTransform.pan = p_value;
			p_obj.soundTransform = sndTransform;
		}


		// ----------------------------------------------------------------------------------------------------------------------------------
		// _color_*

		/**
		 * _color_*
		 * Generic function for the ra/rb/ga/gb/ba/bb/aa/ab components of the colorTransform object
		 */
		public static function _color_property_get (p_obj:Object, p_parameters:Array):Number {
			return p_obj.transform.colorTransform[p_parameters[0]];
		}
		public static function _color_property_set (p_obj:Object, p_value:Number, p_parameters:Array):void {
			var tf:ColorTransform = p_obj.transform.colorTransform;
			tf[p_parameters[0]] = p_value;
			p_obj.transform.colorTransform = tf;
		}


		// ----------------------------------------------------------------------------------------------------------------------------------
		// _autoAlpha
	
		/**
		 * Returns the current alpha
		 *
		 * @param		p_obj				Object		MovieClip or Textfield object
		 * @return							Number		The current alpha
		 */
		public static function _autoAlpha_get (p_obj:Object):Number {
			return p_obj.alpha;
		}
	
		/**
		 * Sets the current autoAlpha
		 *
		 * @param		p_obj				Object		MovieClip or Textfield object
		 * @param		p_value				Number		New alpha
		 */
		public static function _autoAlpha_set (p_obj:Object, p_value:Number):void {
			p_obj.alpha = p_value;
			p_obj.visible = p_value > 0;
		}


		// ----------------------------------------------------------------------------------------------------------------------------------
		// filters

		/**
		 * (filters)
		 * Generic function for the properties of filter objects
		 */
		public static function _filter_property_get (p_obj:Object, p_parameters:Array):Number {
			var f:Array = p_obj.filters;
			var i:uint;
			var filterClass:Object = p_parameters[0];
			var propertyName:String = p_parameters[1];
			for (i = 0; i < f.length; i++) {
				if (f[i] is BlurFilter && filterClass == BlurFilter) return (f[i][propertyName]);
			}
			
			// No value found for this property - no filter instance found using this class!
			// Must return default desired values
			var defaultValues:Object;
			switch (filterClass) {
				case BlurFilter:
					defaultValues = {blurX:0, blurY:0, quality:NaN};
					break;
			}
			// When returning NaN, the Tweener engine sets the starting value as being the same as the final value
			// When returning null, the Tweener engine doesn't tween it at all, just setting it to the final value
			return defaultValues[propertyName];
		}

		public static function _filter_property_set (p_obj:Object, p_value:Number, p_parameters:Array): void {
			var f:Array = p_obj.filters;
			var i:uint;
			var filterClass:Object = p_parameters[0];
			var propertyName:String = p_parameters[1];
			for (i = 0; i < f.length; i++) {
				if (f[i] is BlurFilter && filterClass == BlurFilter) {
					f[i][propertyName] = p_value;
					p_obj.filters = f;
					return;
				}
			}

			// The correct filter class wasn't found - create a new one
			if (f == null) f = new Array();
			var fi:BitmapFilter;
			switch (filterClass) {
				case BlurFilter:
					fi = new BlurFilter(0, 0);
					break;
			}
			fi[propertyName] = p_value;
			f.push(fi);
			p_obj.filters = f;
		}


		// ==================================================================================================================================
		// SPECIAL PROPERTY MODIFIER functions ----------------------------------------------------------------------------------------------


		// ----------------------------------------------------------------------------------------------------------------------------------
		// _bezier

		/**
		 * Given the parameter object passed to this special property, return an array listing the properties that should be modified, and their parameters
		 *
		 * @param		p_obj				Object		Parameter passed to this property
		 * @return							Array		Array listing name and parameter of each property
		 */
		public static function _bezier_modifier (p_obj:*):Array {
			var mList:Array = []; // List of properties to be modified
			var pList:Array; // List of parameters passed, normalized as an array
			if (p_obj is Array) {
				// Complex
				pList = p_obj;
			} else {
				pList = [p_obj];
			}

			var i:uint;
			var istr:String;
			var mListObj:Object = {}; // Object describing each property name and parameter

			for (i = 0; i < pList.length; i++) {
				for (istr in pList[i]) {
					if (mListObj[istr] == undefined) mListObj[istr] = [];
					mListObj[istr].push(pList[i][istr]);
				}
			}
			for (istr in mListObj) {
				mList.push({name:istr, parameters:mListObj[istr]});
			}
			return mList;
		}

		/**
		 * Given tweening specifications (beging, end, t), applies the property parameter to it, returning new t
		 *
		 * @param		b					Number		Beginning value of the property
		 * @param		e					Number		Ending (desired) value of the property
		 * @param		t					Number		Current t of this tweening (0-1), after applying the easing equation
		 * @param		p					Array		Array of parameters passed to this specific property
		 * @return							Number		New t, with the p parameters applied to it
		 */
		public static function _bezier_get (b:Number, e:Number, t:Number, p:Array):Number {
			// This is based on Robert Penner's code
			if (p.length == 1) {
				// Simple curve with just one bezier control point
				return b + t*(2*(1-t)*(p[0]-b) + t*(e - b));
			} else {
				// Array of bezier control points, must find the point between each pair of bezier points
				var ip:uint = Math.floor(t * p.length); // Position on the bezier list
				var it:Number = (t - (ip * (1 / p.length))) * p.length; // t inside this ip
				var p1:Number, p2:Number;
				if (ip == 0) {
					// First part: belongs to the first control point, find second midpoint
					p1 = b;
					p2 = (p[0]+p[1])/2;
				} else if (ip == p.length - 1) {
					// Last part: belongs to the last control point, find first midpoint
					p1 = (p[ip-1]+p[ip])/2;
					p2 = e;
				} else {
					// Any middle part: find both midpoints
					p1 = (p[ip-1]+p[ip])/2;
					p2 = (p[ip]+p[ip+1])/2;
				}
				return p1+it*(2*(1-it)*(p[ip]-p1) + it*(p2 - p1));
			}
		}

	}
}

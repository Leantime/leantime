package caurina.transitions {
    import caurina.transitions.AuxFunctions;
	/**
	 * The tween list object. Stores all of the properties and information that pertain to individual tweens.
	 *
	 * @author		Nate Chatellier, Zeh Fernando
	 * @version		1.0.4
	 * @private
	 */

	public class TweenListObj {
		
		public var scope					:Object;	// Object affected by this tweening
		public var properties				:Object;	// List of properties that are tweened (PropertyInfoObj instances)
			// .valueStart					:Number		// Initial value of the property
			// .valueComplete				:Number		// The value the property should have when completed
		public var auxProperties			:Object;	// Dynamic object containing properties used on this tweening
		public var timeStart				:Number;	// Time when this tweening should start
		public var timeComplete				:Number;	// Time when this tweening should end
		public var useFrames				:Boolean;	// Whether or not to use frames instead of time
		public var transition				:Function;	// Equation to control the transition animation
		public var onStart					:Function;	// Function to be executed on the object when the tween starts (once)
		public var onUpdate					:Function;	// Function to be executed on the object when the tween updates (several times)
		public var onComplete				:Function;	// Function to be executed on the object when the tween completes (once)
		public var onOverwrite				:Function;	// Function to be executed on the object when the tween is overwritten
		public var onError					:Function;	// Function to be executed if an error is thrown when tweener exectues a callback (onComplete, onUpdate etc)
		public var onStartParams			:Array;		// Array of parameters to be passed for the event
		public var onUpdateParams			:Array;		// Array of parameters to be passed for the event
		public var onCompleteParams			:Array;		// Array of parameters to be passed for the event
		public var onOverwriteParams		:Array;		// Array of parameters to be passed for the event
		public var rounded					:Boolean;	// Use rounded values when updating
		public var isPaused					:Boolean;	// Whether or not this tween is paused
		public var timePaused				:Number;	// Time when this tween was paused
		public var isCaller					:Boolean;	// Whether or not this tween is a "caller" tween
		public var count					:Number;	// Number of times this caller should be called
		public var timesCalled				:Number;	// How many times the caller has already been called ("caller" tweens only)
		public var waitFrames				:Boolean;	// Whether or not this caller should wait at least one frame for each call execution ("caller" tweens only)
		public var skipUpdates				:Number;	// How many updates should be skipped (default = 0; 1 = update-skip-update-skip...)
		public var updatesSkipped			:Number;	// How many updates have already been skipped
		public var hasStarted				:Boolean;	// Whether or not this tween has already started

		// ==================================================================================================================================
		// CONSTRUCTOR function -------------------------------------------------------------------------------------------------------------

		/**
		 * Initializes the basic TweenListObj.
		 * 
		 * @param	p_scope				Object		Object affected by this tweening
		 * @param	p_timeStart			Number		Time when this tweening should start
		 * @param	p_timeComplete		Number		Time when this tweening should end
		 * @param	p_useFrames			Boolean		Whether or not to use frames instead of time
		 * @param	p_transition		Function	Equation to control the transition animation
		 */
		function TweenListObj(p_scope:Object, p_timeStart:Number, p_timeComplete:Number, p_useFrames:Boolean, p_transition:Function) {
			scope			=	p_scope;
			timeStart		=	p_timeStart;
			timeComplete	=	p_timeComplete;
			useFrames		=	p_useFrames;
			transition		=	p_transition;

			// Other default information
			auxProperties	=	new Object();
			properties		=	new Object();
			isPaused		=	false;
			timePaused		=	undefined;
			isCaller		=	false;
			updatesSkipped	=	0;
			timesCalled		=	0;
			skipUpdates		=	0;
			hasStarted		=	false;
		}


		// ==================================================================================================================================
		// OTHER functions ------------------------------------------------------------------------------------------------------------------
	
		/**
		 * Clones this tweening and returns the new TweenListObj
		 *
		 * @param	omitEvents		Boolean			Whether or not events such as onStart (and its parameters) should be omitted
		 * @return					TweenListObj	A copy of this object
		 */
		public function clone(omitEvents:Boolean):TweenListObj {
			var nTween:TweenListObj = new TweenListObj(scope, timeStart, timeComplete, useFrames, transition);
			nTween.properties = new Array();
			for (var pName:String in properties) {
				nTween.properties[pName] = properties[pName].clone();
			}
			nTween.skipUpdates = skipUpdates;
			nTween.updatesSkipped = updatesSkipped;
			if (!omitEvents) {
				nTween.onStart = onStart;
				nTween.onUpdate = onUpdate;
				nTween.onComplete = onComplete;
				nTween.onOverwrite = onOverwrite;
				nTween.onError = onError;
				nTween.onStartParams = onStartParams;
				nTween.onUpdateParams = onUpdateParams;
				nTween.onCompleteParams = onCompleteParams;
				nTween.onOverwriteParams = onOverwriteParams;
			}
			nTween.rounded = rounded;
			nTween.isPaused = isPaused;
			nTween.timePaused = timePaused;
			nTween.isCaller = isCaller;
			nTween.count = count;
			nTween.timesCalled = timesCalled;
			nTween.waitFrames = waitFrames;
			nTween.hasStarted = hasStarted;

			return nTween;
		}

		/**
		 * Returns this object described as a String.
		 *
		 * @return					String		The description of this object.
		 */
		public function toString():String {
			var returnStr:String = "\n[TweenListObj ";
			returnStr += "scope:" + String(scope);
			returnStr += ", properties:";
			for (var i:uint = 0; i < properties.length; i++) {
				if (i > 0) returnStr += ",";
				returnStr += "[name:"+properties[i].name;
				returnStr += ",valueStart:"+properties[i].valueStart;
				returnStr += ",valueComplete:"+properties[i].valueComplete;
				returnStr += "]";
			} // END FOR
			returnStr += ", timeStart:" + String(timeStart);
			returnStr += ", timeComplete:" + String(timeComplete);
			returnStr += ", useFrames:" + String(useFrames);
			returnStr += ", transition:" + String(transition);

			if (skipUpdates)		returnStr += ", skipUpdates:"		+ String(skipUpdates);
			if (updatesSkipped)		returnStr += ", updatesSkipped:"	+ String(updatesSkipped);

			if (Boolean(onStart))			returnStr += ", onStart:"			+ String(onStart);
			if (Boolean(onUpdate))			returnStr += ", onUpdate:"			+ String(onUpdate);
			if (Boolean(onComplete))		returnStr += ", onComplete:"		+ String(onComplete);
			if (Boolean(onOverwrite))		returnStr += ", onOverwrite:"		+ String(onOverwrite);
			if (Boolean(onError))			returnStr += ", onError:"			+ String(onError);
			
			if (onStartParams)		returnStr += ", onStartParams:"		+ String(onStartParams);
			if (onUpdateParams)		returnStr += ", onUpdateParams:"	+ String(onUpdateParams);
			if (onCompleteParams)	returnStr += ", onCompleteParams:"	+ String(onCompleteParams);
			if (onOverwriteParams)	returnStr += ", onOverwriteParams:" + String(onOverwriteParams);

			if (rounded)			returnStr += ", rounded:"			+ String(rounded);
			if (isPaused)			returnStr += ", isPaused:"			+ String(isPaused);
			if (timePaused)			returnStr += ", timePaused:"		+ String(timePaused);
			if (isCaller)			returnStr += ", isCaller:"			+ String(isCaller);
			if (count)				returnStr += ", count:"				+ String(count);
			if (timesCalled)		returnStr += ", timesCalled:"		+ String(timesCalled);
			if (waitFrames)			returnStr += ", waitFrames:"		+ String(waitFrames);
			if (hasStarted)			returnStr += ", hasStarted:"		+ String(hasStarted);
			
			returnStr += "]\n";
			return returnStr;
		}
		
		/**
		 * Checks if p_obj "inherits" properties from other objects, as set by the "base" property. Will create a new object, leaving others intact.
		 * o_bj.base can be an object or an array of objects. Properties are collected from the first to the last element of the "base" filed, with higher
		 * indexes overwritting smaller ones. Does not modify any of the passed objects, but makes a shallow copy of all properties.
		 *
		 * @param		p_obj		Object				Object that should be tweened: a movieclip, textfield, etc.. OR an array of objects
		 * @return					Object				A new object with all properties from the p_obj and p_obj.base.
		 */

		public static function makePropertiesChain(p_obj : Object) : Object{
			// Is this object inheriting properties from another object?
			var baseObject : Object = p_obj.base;
			if(baseObject){
				// object inherits. Are we inheriting from an object or an array
				var chainedObject : Object = {};
				var chain : Object;
				if (baseObject is Array){
					// Inheritance chain is the base array
					chain = [];
					// make a shallow copy
					for (var k : Number = 0 ; k< baseObject.length; k++) chain.push(baseObject[k]);
				}else{
					// Only one object to be added to the array
					chain = [baseObject];
				}
				// add the final object to the array, so it's properties are added last
				chain.push(p_obj);
				var currChainObj : Object;
				// Loops through each object adding it's property to the final object
				var len : Number = chain.length;
				for(var i : Number = 0; i < len ; i ++){
					if(chain[i]["base"]){
						// deal with recursion: watch the order! "parent" base must be concatenated first!
						currChainObj = AuxFunctions.concatObjects( makePropertiesChain(chain[i]["base"] ), chain[i]);
					}else{
						currChainObj = chain[i] ;
					}
					chainedObject = AuxFunctions.concatObjects(chainedObject, currChainObj );
				}
				if( chainedObject["base"]){
				    delete chainedObject["base"];
				}
				return chainedObject;	
			}else{
				// No inheritance, just return the object it self
				return p_obj;
			}
		}
		

	}
	
	

	
}

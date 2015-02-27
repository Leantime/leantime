/**
 * Equations
 * Main equations for the Tweener class
 *
 * @author		Zeh Fernando, Nate Chatellier
 * @version		1.0.2
 */

/*
Disclaimer for Robert Penner's Easing Equations license:

TERMS OF USE - EASING EQUATIONS

Open source under the BSD License.

Copyright © 2001 Robert Penner
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
    * Neither the name of the author nor the names of contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

package caurina.transitions {
	
	public class Equations {
	
		/**
		 * There's no constructor.
		 * @private
		 */
		public function Equations () {
			trace ("Equations is a static class and should not be instantiated.")
		}
	
		/**
		 * Registers all the equations to the Tweener class, so they can be found by the direct string parameters.
		 * This method doesn't actually have to be used - equations can always be referenced by their full function
		 * names. But "registering" them make them available as their shorthand string names.
		 */
		public static function init():void {
			Tweener.registerTransition("easenone",			easeNone);
			Tweener.registerTransition("linear",			easeNone);		// mx.transitions.easing.None.easeNone
			
			Tweener.registerTransition("easeinquad",		easeInQuad);	// mx.transitions.easing.Regular.easeIn
			Tweener.registerTransition("easeoutquad",		easeOutQuad);	// mx.transitions.easing.Regular.easeOut
			Tweener.registerTransition("easeinoutquad",		easeInOutQuad);	// mx.transitions.easing.Regular.easeInOut
			Tweener.registerTransition("easeoutinquad",		easeOutInQuad);
			
			Tweener.registerTransition("easeincubic",		easeInCubic);
			Tweener.registerTransition("easeoutcubic",		easeOutCubic);
			Tweener.registerTransition("easeinoutcubic",	easeInOutCubic);
			Tweener.registerTransition("easeoutincubic",	easeOutInCubic);
			
			Tweener.registerTransition("easeinquart",		easeInQuart);
			Tweener.registerTransition("easeoutquart",		easeOutQuart);
			Tweener.registerTransition("easeinoutquart",	easeInOutQuart);
			Tweener.registerTransition("easeoutinquart",	easeOutInQuart);
			
			Tweener.registerTransition("easeinquint",		easeInQuint);
			Tweener.registerTransition("easeoutquint",		easeOutQuint);
			Tweener.registerTransition("easeinoutquint",	easeInOutQuint);
			Tweener.registerTransition("easeoutinquint",	easeOutInQuint);
			
			Tweener.registerTransition("easeinsine",		easeInSine);
			Tweener.registerTransition("easeoutsine",		easeOutSine);
			Tweener.registerTransition("easeinoutsine",		easeInOutSine);
			Tweener.registerTransition("easeoutinsine",		easeOutInSine);
			
			Tweener.registerTransition("easeincirc",		easeInCirc);
			Tweener.registerTransition("easeoutcirc",		easeOutCirc);
			Tweener.registerTransition("easeinoutcirc",		easeInOutCirc);
			Tweener.registerTransition("easeoutincirc",		easeOutInCirc);
			
			Tweener.registerTransition("easeinexpo",		easeInExpo);		// mx.transitions.easing.Strong.easeIn
			Tweener.registerTransition("easeoutexpo", 		easeOutExpo);		// mx.transitions.easing.Strong.easeOut
			Tweener.registerTransition("easeinoutexpo", 	easeInOutExpo);		// mx.transitions.easing.Strong.easeInOut
			Tweener.registerTransition("easeoutinexpo", 	easeOutInExpo);
			
			Tweener.registerTransition("easeinelastic", 	easeInElastic);		// mx.transitions.easing.Elastic.easeIn
			Tweener.registerTransition("easeoutelastic", 	easeOutElastic);	// mx.transitions.easing.Elastic.easeOut
			Tweener.registerTransition("easeinoutelastic", 	easeInOutElastic);	// mx.transitions.easing.Elastic.easeInOut
			Tweener.registerTransition("easeoutinelastic", 	easeOutInElastic);
			
			Tweener.registerTransition("easeinback", 		easeInBack);		// mx.transitions.easing.Back.easeIn
			Tweener.registerTransition("easeoutback", 		easeOutBack);		// mx.transitions.easing.Back.easeOut
			Tweener.registerTransition("easeinoutback", 	easeInOutBack);		// mx.transitions.easing.Back.easeInOut
			Tweener.registerTransition("easeoutinback", 	easeOutInBack);
			
			Tweener.registerTransition("easeinbounce", 		easeInBounce);		// mx.transitions.easing.Bounce.easeIn
			Tweener.registerTransition("easeoutbounce", 	easeOutBounce);		// mx.transitions.easing.Bounce.easeOut
			Tweener.registerTransition("easeinoutbounce", 	easeInOutBounce);	// mx.transitions.easing.Bounce.easeInOut
			Tweener.registerTransition("easeoutinbounce", 	easeOutInBounce);
		}

	// ==================================================================================================================================
	// TWEENING EQUATIONS functions -----------------------------------------------------------------------------------------------------
	// (the original equations are Robert Penner's work as mentioned on the disclaimer)

		/**
		 * Easing equation function for a simple linear tweening, with no easing.
		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeNone (t:Number, b:Number, c:Number, d:Number):Number {
			return c*t/d + b;
		}
	
		/**
		 * Easing equation function for a quadratic (t^2) easing in: accelerating from zero velocity.
		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeInQuad (t:Number, b:Number, c:Number, d:Number):Number {
			return c*(t/=d)*t + b;
		}
	
		/**
		 * Easing equation function for a quadratic (t^2) easing out: decelerating to zero velocity.
		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeOutQuad (t:Number, b:Number, c:Number, d:Number):Number {
			return -c *(t/=d)*(t-2) + b;
		}
	
		/**
		 * Easing equation function for a quadratic (t^2) easing in/out: acceleration until halfway, then deceleration.
		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeInOutQuad (t:Number, b:Number, c:Number, d:Number):Number {
			if ((t/=d/2) < 1) return c/2*t*t + b;
			return -c/2 * ((--t)*(t-2) - 1) + b;
		}
	
		/**
		 * Easing equation function for a quadratic (t^2) easing out/in: deceleration until halfway, then acceleration.
		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeOutInQuad (t:Number, b:Number, c:Number, d:Number):Number {
			if (t < d/2) return easeOutQuad (t*2, b, c/2, d);
			return easeInQuad((t*2)-d, b+c/2, c/2, d);
		}
	
		/**
		 * Easing equation function for a cubic (t^3) easing in: accelerating from zero velocity.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeInCubic (t:Number, b:Number, c:Number, d:Number):Number {
			return c*(t/=d)*t*t + b;
		}
	
		/**
		 * Easing equation function for a cubic (t^3) easing out: decelerating from zero velocity.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeOutCubic (t:Number, b:Number, c:Number, d:Number):Number {
			return c*((t=t/d-1)*t*t + 1) + b;
		}
	
		/**
		 * Easing equation function for a cubic (t^3) easing in/out: acceleration until halfway, then deceleration.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeInOutCubic (t:Number, b:Number, c:Number, d:Number):Number {
			if ((t/=d/2) < 1) return c/2*t*t*t + b;
			return c/2*((t-=2)*t*t + 2) + b;
		}
	
		/**
		 * Easing equation function for a cubic (t^3) easing out/in: deceleration until halfway, then acceleration.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeOutInCubic (t:Number, b:Number, c:Number, d:Number):Number {
			if (t < d/2) return easeOutCubic (t*2, b, c/2, d);
			return easeInCubic((t*2)-d, b+c/2, c/2, d);
		}
	
		/**
		 * Easing equation function for a quartic (t^4) easing in: accelerating from zero velocity.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeInQuart (t:Number, b:Number, c:Number, d:Number):Number {
			return c*(t/=d)*t*t*t + b;
		}
	
		/**
		 * Easing equation function for a quartic (t^4) easing out: decelerating from zero velocity.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeOutQuart (t:Number, b:Number, c:Number, d:Number):Number {
			return -c * ((t=t/d-1)*t*t*t - 1) + b;
		}
	
		/**
		 * Easing equation function for a quartic (t^4) easing in/out: acceleration until halfway, then deceleration.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeInOutQuart (t:Number, b:Number, c:Number, d:Number):Number {
			if ((t/=d/2) < 1) return c/2*t*t*t*t + b;
			return -c/2 * ((t-=2)*t*t*t - 2) + b;
		}
	
		/**
		 * Easing equation function for a quartic (t^4) easing out/in: deceleration until halfway, then acceleration.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeOutInQuart (t:Number, b:Number, c:Number, d:Number):Number {
			if (t < d/2) return easeOutQuart (t*2, b, c/2, d);
			return easeInQuart((t*2)-d, b+c/2, c/2, d);
		}
	
		/**
		 * Easing equation function for a quintic (t^5) easing in: accelerating from zero velocity.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeInQuint (t:Number, b:Number, c:Number, d:Number):Number {
			return c*(t/=d)*t*t*t*t + b;
		}
	
		/**
		 * Easing equation function for a quintic (t^5) easing out: decelerating from zero velocity.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeOutQuint (t:Number, b:Number, c:Number, d:Number):Number {
			return c*((t=t/d-1)*t*t*t*t + 1) + b;
		}
	
		/**
		 * Easing equation function for a quintic (t^5) easing in/out: acceleration until halfway, then deceleration.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeInOutQuint (t:Number, b:Number, c:Number, d:Number):Number {
			if ((t/=d/2) < 1) return c/2*t*t*t*t*t + b;
			return c/2*((t-=2)*t*t*t*t + 2) + b;
		}
	
		/**
		 * Easing equation function for a quintic (t^5) easing out/in: deceleration until halfway, then acceleration.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeOutInQuint (t:Number, b:Number, c:Number, d:Number):Number {
			if (t < d/2) return easeOutQuint (t*2, b, c/2, d);
			return easeInQuint((t*2)-d, b+c/2, c/2, d);
		}
	
		/**
		 * Easing equation function for a sinusoidal (sin(t)) easing in: accelerating from zero velocity.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeInSine (t:Number, b:Number, c:Number, d:Number):Number {
			return -c * Math.cos(t/d * (Math.PI/2)) + c + b;
		}
	
		/**
		 * Easing equation function for a sinusoidal (sin(t)) easing out: decelerating from zero velocity.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeOutSine (t:Number, b:Number, c:Number, d:Number):Number {
			return c * Math.sin(t/d * (Math.PI/2)) + b;
		}
	
		/**
		 * Easing equation function for a sinusoidal (sin(t)) easing in/out: acceleration until halfway, then deceleration.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeInOutSine (t:Number, b:Number, c:Number, d:Number):Number {
			return -c/2 * (Math.cos(Math.PI*t/d) - 1) + b;
		}
	
		/**
		 * Easing equation function for a sinusoidal (sin(t)) easing out/in: deceleration until halfway, then acceleration.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeOutInSine (t:Number, b:Number, c:Number, d:Number):Number {
			if (t < d/2) return easeOutSine (t*2, b, c/2, d);
			return easeInSine((t*2)-d, b+c/2, c/2, d);
		}
	
		/**
		 * Easing equation function for an exponential (2^t) easing in: accelerating from zero velocity.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeInExpo (t:Number, b:Number, c:Number, d:Number):Number {
			return (t==0) ? b : c * Math.pow(2, 10 * (t/d - 1)) + b - c * 0.001;
		}
	
		/**
		 * Easing equation function for an exponential (2^t) easing out: decelerating from zero velocity.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeOutExpo (t:Number, b:Number, c:Number, d:Number):Number {
			return (t==d) ? b+c : c * 1.001 * (-Math.pow(2, -10 * t/d) + 1) + b;
		}
	
		/**
		 * Easing equation function for an exponential (2^t) easing in/out: acceleration until halfway, then deceleration.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeInOutExpo (t:Number, b:Number, c:Number, d:Number):Number {
			if (t==0) return b;
			if (t==d) return b+c;
			if ((t/=d/2) < 1) return c/2 * Math.pow(2, 10 * (t - 1)) + b - c * 0.0005;
			return c/2 * 1.0005 * (-Math.pow(2, -10 * --t) + 2) + b;
		}
	
		/**
		 * Easing equation function for an exponential (2^t) easing out/in: deceleration until halfway, then acceleration.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeOutInExpo (t:Number, b:Number, c:Number, d:Number):Number {
			if (t < d/2) return easeOutExpo (t*2, b, c/2, d);
			return easeInExpo((t*2)-d, b+c/2, c/2, d);
		}
	
		/**
		 * Easing equation function for a circular (sqrt(1-t^2)) easing in: accelerating from zero velocity.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeInCirc (t:Number, b:Number, c:Number, d:Number):Number {
			return -c * (Math.sqrt(1 - (t/=d)*t) - 1) + b;
		}
	
		/**
		 * Easing equation function for a circular (sqrt(1-t^2)) easing out: decelerating from zero velocity.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeOutCirc (t:Number, b:Number, c:Number, d:Number):Number {
			return c * Math.sqrt(1 - (t=t/d-1)*t) + b;
		}
	
		/**
		 * Easing equation function for a circular (sqrt(1-t^2)) easing in/out: acceleration until halfway, then deceleration.
 		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeInOutCirc (t:Number, b:Number, c:Number, d:Number):Number {
			if ((t/=d/2) < 1) return -c/2 * (Math.sqrt(1 - t*t) - 1) + b;
			return c/2 * (Math.sqrt(1 - (t-=2)*t) + 1) + b;
		}
	
		/**
		 * Easing equation function for a circular (sqrt(1-t^2)) easing out/in: deceleration until halfway, then acceleration.
		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeOutInCirc (t:Number, b:Number, c:Number, d:Number):Number {
			if (t < d/2) return easeOutCirc (t*2, b, c/2, d);
			return easeInCirc((t*2)-d, b+c/2, c/2, d);
		}
	
		/**
		 * Easing equation function for an elastic (exponentially decaying sine wave) easing in: accelerating from zero velocity.
		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @param a		Amplitude.
		 * @param p		Period.
		 * @return		The correct value.
		 */
		public static function easeInElastic (t:Number, b:Number, c:Number, d:Number, a:Number = Number.NaN, p:Number = Number.NaN):Number {
			if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
			var s:Number;
			if (!a || a < Math.abs(c)) { a=c; s=p/4; }
			else s = p/(2*Math.PI) * Math.asin (c/a);
			return -(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
		}
	
		/**
		 * Easing equation function for an elastic (exponentially decaying sine wave) easing out: decelerating from zero velocity.
		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @param a		Amplitude.
		 * @param p		Period.
		 * @return		The correct value.
		 */
		public static function easeOutElastic (t:Number, b:Number, c:Number, d:Number, a:Number = Number.NaN, p:Number = Number.NaN):Number {
			if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
			var s:Number;
			if (!a || a < Math.abs(c)) { a=c; s=p/4; }
			else s = p/(2*Math.PI) * Math.asin (c/a);
			return (a*Math.pow(2,-10*t) * Math.sin( (t*d-s)*(2*Math.PI)/p ) + c + b);
		}
	
		/**
		 * Easing equation function for an elastic (exponentially decaying sine wave) easing in/out: acceleration until halfway, then deceleration.
		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @param a		Amplitude.
		 * @param p		Period.
		 * @return		The correct value.
		 */
		public static function easeInOutElastic (t:Number, b:Number, c:Number, d:Number, a:Number = Number.NaN, p:Number = Number.NaN):Number {
			if (t==0) return b;  if ((t/=d/2)==2) return b+c;  if (!p) p=d*(.3*1.5);
			var s:Number;
			if (!a || a < Math.abs(c)) { a=c; s=p/4; }
			else s = p/(2*Math.PI) * Math.asin (c/a);
			if (t < 1) return -.5*(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
			return a*Math.pow(2,-10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )*.5 + c + b;
		}
	
		/**
		 * Easing equation function for an elastic (exponentially decaying sine wave) easing out/in: deceleration until halfway, then acceleration.
		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @param a		Amplitude.
		 * @param p		Period.
		 * @return		The correct value.
		 */
		public static function easeOutInElastic (t:Number, b:Number, c:Number, d:Number, a:Number = Number.NaN, p:Number = Number.NaN):Number {
			if (t < d/2) return easeOutElastic (t*2, b, c/2, d, a, p);
			return easeInElastic((t*2)-d, b+c/2, c/2, d, a, p);
		}
	
		/**
		 * Easing equation function for a back (overshooting cubic easing: (s+1)*t^3 - s*t^2) easing in: accelerating from zero velocity.
		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @param s		Overshoot ammount: higher s means greater overshoot (0 produces cubic easing with no overshoot, and the default value of 1.70158 produces an overshoot of 10 percent).
		 * @return		The correct value.
		 */
		public static function easeInBack (t:Number, b:Number, c:Number, d:Number, s:Number = Number.NaN):Number {
			if (!s) s = 1.70158;
			return c*(t/=d)*t*((s+1)*t - s) + b;
		}
	
		/**
		 * Easing equation function for a back (overshooting cubic easing: (s+1)*t^3 - s*t^2) easing out: decelerating from zero velocity.
		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @param s		Overshoot ammount: higher s means greater overshoot (0 produces cubic easing with no overshoot, and the default value of 1.70158 produces an overshoot of 10 percent).
		 * @return		The correct value.
		 */
		public static function easeOutBack (t:Number, b:Number, c:Number, d:Number, s:Number = Number.NaN):Number {
			if (!s) s = 1.70158;
			return c*((t=t/d-1)*t*((s+1)*t + s) + 1) + b;
		}
	
		/**
		 * Easing equation function for a back (overshooting cubic easing: (s+1)*t^3 - s*t^2) easing in/out: acceleration until halfway, then deceleration.
		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @param s		Overshoot ammount: higher s means greater overshoot (0 produces cubic easing with no overshoot, and the default value of 1.70158 produces an overshoot of 10 percent).
		 * @return		The correct value.
		 */
		public static function easeInOutBack (t:Number, b:Number, c:Number, d:Number, s:Number = Number.NaN):Number {
			if (!s) s = 1.70158;
			if ((t/=d/2) < 1) return c/2*(t*t*(((s*=(1.525))+1)*t - s)) + b;
			return c/2*((t-=2)*t*(((s*=(1.525))+1)*t + s) + 2) + b;
		}
	
		/**
		 * Easing equation function for a back (overshooting cubic easing: (s+1)*t^3 - s*t^2) easing out/in: deceleration until halfway, then acceleration.
		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @param s		Overshoot ammount: higher s means greater overshoot (0 produces cubic easing with no overshoot, and the default value of 1.70158 produces an overshoot of 10 percent).
		 * @return		The correct value.
		 */
		public static function easeOutInBack (t:Number, b:Number, c:Number, d:Number, s:Number = Number.NaN):Number {
			if (t < d/2) return easeOutBack (t*2, b, c/2, d, s);
			return easeInBack((t*2)-d, b+c/2, c/2, d, s);
		}
	
		/**
		 * Easing equation function for a bounce (exponentially decaying parabolic bounce) easing in: accelerating from zero velocity.
		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeInBounce (t:Number, b:Number, c:Number, d:Number):Number {
			return c - easeOutBounce (d-t, 0, c, d) + b;
		}
	
		/**
		 * Easing equation function for a bounce (exponentially decaying parabolic bounce) easing out: decelerating from zero velocity.
		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeOutBounce (t:Number, b:Number, c:Number, d:Number):Number {
			if ((t/=d) < (1/2.75)) {
				return c*(7.5625*t*t) + b;
			} else if (t < (2/2.75)) {
				return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
			} else if (t < (2.5/2.75)) {
				return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
			} else {
				return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
			}
		}
	
		/**
		 * Easing equation function for a bounce (exponentially decaying parabolic bounce) easing in/out: acceleration until halfway, then deceleration.
		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeInOutBounce (t:Number, b:Number, c:Number, d:Number):Number {
			if (t < d/2) return easeInBounce (t*2, 0, c, d) * .5 + b;
			else return easeOutBounce (t*2-d, 0, c, d) * .5 + c*.5 + b;
		}
	
		/**
		 * Easing equation function for a bounce (exponentially decaying parabolic bounce) easing out/in: deceleration until halfway, then acceleration.
		 *
		 * @param t		Current time (in frames or seconds).
		 * @param b		Starting value.
		 * @param c		Change needed in value.
		 * @param d		Expected easing duration (in frames or seconds).
		 * @return		The correct value.
		 */
		public static function easeOutInBounce (t:Number, b:Number, c:Number, d:Number):Number {
			if (t < d/2) return easeOutBounce (t*2, b, c/2, d);
			return easeInBounce((t*2)-d, b+c/2, c/2, d);
		}
	}
}

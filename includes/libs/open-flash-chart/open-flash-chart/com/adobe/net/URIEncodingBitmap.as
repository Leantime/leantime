/*
	Adobe Systems Incorporated(r) Source Code License Agreement
	Copyright(c) 2005 Adobe Systems Incorporated. All rights reserved.
	
	Please read this Source Code License Agreement carefully before using
	the source code.
	
	Adobe Systems Incorporated grants to you a perpetual, worldwide, non-exclusive, 
	no-charge, royalty-free, irrevocable copyright license, to reproduce,
	prepare derivative works of, publicly display, publicly perform, and
	distribute this source code and such derivative works in source or 
	object code form without any attribution requirements.  
	
	The name "Adobe Systems Incorporated" must not be used to endorse or promote products
	derived from the source code without prior written permission.
	
	You agree to indemnify, hold harmless and defend Adobe Systems Incorporated from and
	against any loss, damage, claims or lawsuits, including attorney's 
	fees that arise or result from your use or distribution of the source 
	code.
	
	THIS SOURCE CODE IS PROVIDED "AS IS" AND "WITH ALL FAULTS", WITHOUT 
	ANY TECHNICAL SUPPORT OR ANY EXPRESSED OR IMPLIED WARRANTIES, INCLUDING,
	BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
	FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  ALSO, THERE IS NO WARRANTY OF 
	NON-INFRINGEMENT, TITLE OR QUIET ENJOYMENT.  IN NO EVENT SHALL MACROMEDIA
	OR ITS SUPPLIERS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
	EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, 
	PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
	OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, 
	WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR 
	OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOURCE CODE, EVEN IF
	ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

package com.adobe.net
{
	import flash.utils.ByteArray;
	
	/**
	 * This class implements an efficient lookup table for URI
	 * character escaping.  This class is only needed if you
	 * create a derived class of URI to handle custom URI
	 * syntax.  This class is used internally by URI.
	 * 
	 * @langversion ActionScript 3.0
	 * @playerversion Flash 9.0* 
	 */
	public class URIEncodingBitmap extends ByteArray
	{
		/**
		 * Constructor.  Creates an encoding bitmap using the given
		 * string of characters as the set of characters that need
		 * to be URI escaped.
		 * 
		 * @langversion ActionScript 3.0
		 * @playerversion Flash 9.0
		 */
		public function URIEncodingBitmap(charsToEscape:String) : void
		{
			var i:int;
			var data:ByteArray = new ByteArray();
			
			// Initialize our 128 bits (16 bytes) to zero
			for (i = 0; i < 16; i++)
				this.writeByte(0);
				
			data.writeUTFBytes(charsToEscape);
			data.position = 0;
			
			while (data.bytesAvailable)
			{
				var c:int = data.readByte();
				
				if (c > 0x7f)
					continue;  // only escape low bytes
					
				var enc:int;
				this.position = (c >> 3);
				enc = this.readByte();
				enc |= 1 << (c & 0x7);
				this.position = (c >> 3);
				this.writeByte(enc);
			}
		}
		
		/**
		 * Based on the data table contained in this object, check
		 * if the given character should be escaped.
		 * 
		 * @param char	the character to be escaped.  Only the first
		 * character in the string is used.  Any other characters
		 * are ignored.
		 * 
		 * @return	the integer value of the raw UTF8 character.  For
		 * example, if '%' is given, the return value is 37 (0x25).
		 * If the character given does not need to be escaped, the
		 * return value is zero.
		 * 
		 * @langversion ActionScript 3.0
		 * @playerversion Flash 9.0 
		 */
		public function ShouldEscape(char:String) : int
		{
			var data:ByteArray = new ByteArray();
			var c:int, mask:int;
			
			// write the character into a ByteArray so
			// we can pull it out as a raw byte value.
			data.writeUTFBytes(char);
			data.position = 0;
			c = data.readByte();
			
			if (c & 0x80)
			{
				// don't escape high byte characters.  It can make international
				// URI's unreadable.  We just want to escape characters that would
				// make URI syntax ambiguous.
				return 0;
			}
			else if ((c < 0x1f) || (c == 0x7f))
			{
				// control characters must be escaped.
				return c;
			}
			
			this.position = (c >> 3);
			mask = this.readByte();
			
			if (mask & (1 << (c & 0x7)))
			{
				// we need to escape this, return the numeric value
				// of the character
				return c;
			}
			else
			{
				return 0;
			}
		}
	}
}
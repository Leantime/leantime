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
FOR A PARTICULAR PURPOSE ARE DISCLAIMED. ALSO, THERE IS NO WARRANTY OF
NON-INFRINGEMENT, TITLE OR QUIET ENJOYMENT. IN NO EVENT SHALL MACROMEDIA
OR ITS SUPPLIERS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOURCE CODE, EVEN IF
ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

package com.adobe.crypto
{
	import com.adobe.utils.IntUtil;
	import flash.utils.ByteArray;
	import mx.utils.Base64Encoder;
	
	/**
	 *  US Secure Hash Algorithm 1 (SHA1)
	 *
	 *  Implementation based on algorithm description at 
	 *  http://www.faqs.org/rfcs/rfc3174.html
	 */
	public class SHA1
	{
		/**
		 *  Performs the SHA1 hash algorithm on a string.
		 *
		 *  @param s		The string to hash
		 *  @return			A string containing the hash value of s
		 *  @langversion	ActionScript 3.0
		 *  @playerversion	9.0
		 *  @tiptext
		 */
		public static function hash( s:String ):String
		{
			var blocks:Array = createBlocksFromString( s );
			var byteArray:ByteArray = hashBlocks( blocks );
			
			return IntUtil.toHex( byteArray.readInt(), true )
					+ IntUtil.toHex( byteArray.readInt(), true )
					+ IntUtil.toHex( byteArray.readInt(), true )
					+ IntUtil.toHex( byteArray.readInt(), true )
					+ IntUtil.toHex( byteArray.readInt(), true );
		}
		
		/**
		 *  Performs the SHA1 hash algorithm on a ByteArray.
		 *
		 *  @param data		The ByteArray data to hash
		 *  @return			A string containing the hash value of data
		 *  @langversion	ActionScript 3.0
		 *  @playerversion	9.0
		 */
		public static function hashBytes( data:ByteArray ):String
		{
			var blocks:Array = SHA1.createBlocksFromByteArray( data );
			var byteArray:ByteArray = hashBlocks(blocks);
			
			return IntUtil.toHex( byteArray.readInt(), true )
					+ IntUtil.toHex( byteArray.readInt(), true )
					+ IntUtil.toHex( byteArray.readInt(), true )
					+ IntUtil.toHex( byteArray.readInt(), true )
					+ IntUtil.toHex( byteArray.readInt(), true );
		}
		
		/**
		 *  Performs the SHA1 hash algorithm on a string, then does
		 *  Base64 encoding on the result.
		 *
		 *  @param s		The string to hash
		 *  @return			The base64 encoded hash value of s
		 *  @langversion	ActionScript 3.0
		 *  @playerversion	9.0
		 *  @tiptext
		 */
		public static function hashToBase64( s:String ):String
		{
			var blocks:Array = SHA1.createBlocksFromString( s );
			var byteArray:ByteArray = hashBlocks(blocks);

			// ByteArray.toString() returns the contents as a UTF-8 string,
			// which we can't use because certain byte sequences might trigger
			// a UTF-8 conversion.  Instead, we convert the bytes to characters
			// one by one.
			var charsInByteArray:String = "";
			byteArray.position = 0;
			for (var j:int = 0; j < byteArray.length; j++)
			{
				var byte:uint = byteArray.readUnsignedByte();
				charsInByteArray += String.fromCharCode(byte);
			}

			var encoder:Base64Encoder = new Base64Encoder();
			encoder.encode(charsInByteArray);
			return encoder.flush();
		}
		
		private static function hashBlocks( blocks:Array ):ByteArray
		{
			// initialize the h's
			var h0:int = 0x67452301;
			var h1:int = 0xefcdab89;
			var h2:int = 0x98badcfe;
			var h3:int = 0x10325476;
			var h4:int = 0xc3d2e1f0;
			
			var len:int = blocks.length;
			var w:Array = new Array( 80 );
			
			// loop over all of the blocks
			for ( var i:int = 0; i < len; i += 16 ) {
			
				// 6.1.c
				var a:int = h0;
				var b:int = h1;
				var c:int = h2;
				var d:int = h3;
				var e:int = h4;
				
				// 80 steps to process each block
				// TODO: unroll for faster execution, or 4 loops of
				// 20 each to avoid the k and f function calls
				for ( var t:int = 0; t < 80; t++ ) {
					
					if ( t < 16 ) {
						// 6.1.a
						w[ t ] = blocks[ i + t ];
					} else {
						// 6.1.b
						w[ t ] = IntUtil.rol( w[ t - 3 ] ^ w[ t - 8 ] ^ w[ t - 14 ] ^ w[ t - 16 ], 1 );
					}
					
					// 6.1.d
					var temp:int = IntUtil.rol( a, 5 ) + f( t, b, c, d ) + e + int( w[ t ] ) + k( t );
					
					e = d;
					d = c;
					c = IntUtil.rol( b, 30 );
					b = a;
					a = temp;
				}
				
				// 6.1.e
				h0 += a;
				h1 += b;
				h2 += c;
				h3 += d;
				h4 += e;		
			}
			
			var byteArray:ByteArray = new ByteArray();
			byteArray.writeInt(h0);
			byteArray.writeInt(h1);
			byteArray.writeInt(h2);
			byteArray.writeInt(h3);
			byteArray.writeInt(h4);
			byteArray.position = 0;
			return byteArray;
		}

		/**
		 *  Performs the logical function based on t
		 */
		private static function f( t:int, b:int, c:int, d:int ):int {
			if ( t < 20 ) {
				return ( b & c ) | ( ~b & d );
			} else if ( t < 40 ) {
				return b ^ c ^ d;
			} else if ( t < 60 ) {
				return ( b & c ) | ( b & d ) | ( c & d );
			}
			return b ^ c ^ d;
		}
		
		/**
		 *  Determines the constant value based on t
		 */
		private static function k( t:int ):int {
			if ( t < 20 ) {
				return 0x5a827999;
			} else if ( t < 40 ) {
				return 0x6ed9eba1;
			} else if ( t < 60 ) {
				return 0x8f1bbcdc;
			}
			return 0xca62c1d6;
		}
					
		/**
		 *  Converts a ByteArray to a sequence of 16-word blocks
		 *  that we'll do the processing on.  Appends padding
		 *  and length in the process.
		 *
		 *  @param data		The data to split into blocks
		 *  @return			An array containing the blocks into which data was split
		 */
		private static function createBlocksFromByteArray( data:ByteArray ):Array
		{
			var oldPosition:int = data.position;
			data.position = 0;
			
			var blocks:Array = new Array();
			var len:int = data.length * 8;
			var mask:int = 0xFF; // ignore hi byte of characters > 0xFF
			for( var i:int = 0; i < len; i += 8 )
			{
				blocks[ i >> 5 ] |= ( data.readByte() & mask ) << ( 24 - i % 32 );
			}
			
			// append padding and length
			blocks[ len >> 5 ] |= 0x80 << ( 24 - len % 32 );
			blocks[ ( ( ( len + 64 ) >> 9 ) << 4 ) + 15 ] = len;
			
			data.position = oldPosition;
			
			return blocks;
		}
					
		/**
		 *  Converts a string to a sequence of 16-word blocks
		 *  that we'll do the processing on.  Appends padding
		 *  and length in the process.
		 *
		 *  @param s	The string to split into blocks
		 *  @return		An array containing the blocks that s was split into.
		 */
		private static function createBlocksFromString( s:String ):Array
		{
			var blocks:Array = new Array();
			var len:int = s.length * 8;
			var mask:int = 0xFF; // ignore hi byte of characters > 0xFF
			for( var i:int = 0; i < len; i += 8 ) {
				blocks[ i >> 5 ] |= ( s.charCodeAt( i / 8 ) & mask ) << ( 24 - i % 32 );
			}
			
			// append padding and length
			blocks[ len >> 5 ] |= 0x80 << ( 24 - len % 32 );
			blocks[ ( ( ( len + 64 ) >> 9 ) << 4 ) + 15 ] = len;
			return blocks;
		}
		
	}
}
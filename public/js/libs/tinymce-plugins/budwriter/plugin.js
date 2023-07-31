(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
        module.exports = after

        function after(count, callback, err_cb) {
            var bail = false
            err_cb = err_cb || noop
            proxy.count = count

            return (count === 0) ? callback() : proxy

            function proxy(err, result) {
                if (proxy.count <= 0) {
                    throw new Error('after called too many times')
                }
                --proxy.count

                // after first error, rest are passed to err_cb
                if (err) {
                    bail = true
                    callback(err)
                    // future error callbacks will go to error handler
                    callback = err_cb
                } else if (proxy.count === 0 && !bail) {
                    callback(null, result)
                }
            }
        }

        function noop() {}

    },{}],2:[function(require,module,exports){
        /**
         * An abstraction for slicing an arraybuffer even when
         * ArrayBuffer.prototype.slice is not supported
         *
         * @api public
         */

        module.exports = function(arraybuffer, start, end) {
            var bytes = arraybuffer.byteLength;
            start = start || 0;
            end = end || bytes;

            if (arraybuffer.slice) { return arraybuffer.slice(start, end); }

            if (start < 0) { start += bytes; }
            if (end < 0) { end += bytes; }
            if (end > bytes) { end = bytes; }

            if (start >= bytes || start >= end || bytes === 0) {
                return new ArrayBuffer(0);
            }

            var abv = new Uint8Array(arraybuffer);
            var result = new Uint8Array(end - start);
            for (var i = start, ii = 0; i < end; i++, ii++) {
                result[ii] = abv[i];
            }
            return result.buffer;
        };

    },{}],3:[function(require,module,exports){

        /**
         * Expose `Backoff`.
         */

        module.exports = Backoff;

        /**
         * Initialize backoff timer with `opts`.
         *
         * - `min` initial timeout in milliseconds [100]
         * - `max` max timeout [10000]
         * - `jitter` [0]
         * - `factor` [2]
         *
         * @param {Object} opts
         * @api public
         */

        function Backoff(opts) {
            opts = opts || {};
            this.ms = opts.min || 100;
            this.max = opts.max || 10000;
            this.factor = opts.factor || 2;
            this.jitter = opts.jitter > 0 && opts.jitter <= 1 ? opts.jitter : 0;
            this.attempts = 0;
        }

        /**
         * Return the backoff duration.
         *
         * @return {Number}
         * @api public
         */

        Backoff.prototype.duration = function(){
            var ms = this.ms * Math.pow(this.factor, this.attempts++);
            if (this.jitter) {
                var rand =  Math.random();
                var deviation = Math.floor(rand * this.jitter * ms);
                ms = (Math.floor(rand * 10) & 1) == 0  ? ms - deviation : ms + deviation;
            }
            return Math.min(ms, this.max) | 0;
        };

        /**
         * Reset the number of attempts.
         *
         * @api public
         */

        Backoff.prototype.reset = function(){
            this.attempts = 0;
        };

        /**
         * Set the minimum duration
         *
         * @api public
         */

        Backoff.prototype.setMin = function(min){
            this.ms = min;
        };

        /**
         * Set the maximum duration
         *
         * @api public
         */

        Backoff.prototype.setMax = function(max){
            this.max = max;
        };

        /**
         * Set the jitter
         *
         * @api public
         */

        Backoff.prototype.setJitter = function(jitter){
            this.jitter = jitter;
        };


    },{}],4:[function(require,module,exports){
        /*
 * base64-arraybuffer
 * https://github.com/niklasvh/base64-arraybuffer
 *
 * Copyright (c) 2012 Niklas von Hertzen
 * Licensed under the MIT license.
 */
        (function(){
            "use strict";

            var chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";

            // Use a lookup table to find the index.
            var lookup = new Uint8Array(256);
            for (var i = 0; i < chars.length; i++) {
                lookup[chars.charCodeAt(i)] = i;
            }

            exports.encode = function(arraybuffer) {
                var bytes = new Uint8Array(arraybuffer),
                    i, len = bytes.length, base64 = "";

                for (i = 0; i < len; i+=3) {
                    base64 += chars[bytes[i] >> 2];
                    base64 += chars[((bytes[i] & 3) << 4) | (bytes[i + 1] >> 4)];
                    base64 += chars[((bytes[i + 1] & 15) << 2) | (bytes[i + 2] >> 6)];
                    base64 += chars[bytes[i + 2] & 63];
                }

                if ((len % 3) === 2) {
                    base64 = base64.substring(0, base64.length - 1) + "=";
                } else if (len % 3 === 1) {
                    base64 = base64.substring(0, base64.length - 2) + "==";
                }

                return base64;
            };

            exports.decode =  function(base64) {
                var bufferLength = base64.length * 0.75,
                    len = base64.length, i, p = 0,
                    encoded1, encoded2, encoded3, encoded4;

                if (base64[base64.length - 1] === "=") {
                    bufferLength--;
                    if (base64[base64.length - 2] === "=") {
                        bufferLength--;
                    }
                }

                var arraybuffer = new ArrayBuffer(bufferLength),
                    bytes = new Uint8Array(arraybuffer);

                for (i = 0; i < len; i+=4) {
                    encoded1 = lookup[base64.charCodeAt(i)];
                    encoded2 = lookup[base64.charCodeAt(i+1)];
                    encoded3 = lookup[base64.charCodeAt(i+2)];
                    encoded4 = lookup[base64.charCodeAt(i+3)];

                    bytes[p++] = (encoded1 << 2) | (encoded2 >> 4);
                    bytes[p++] = ((encoded2 & 15) << 4) | (encoded3 >> 2);
                    bytes[p++] = ((encoded3 & 3) << 6) | (encoded4 & 63);
                }

                return arraybuffer;
            };
        })();

    },{}],5:[function(require,module,exports){
        'use strict'

        exports.byteLength = byteLength
        exports.toByteArray = toByteArray
        exports.fromByteArray = fromByteArray

        var lookup = []
        var revLookup = []
        var Arr = typeof Uint8Array !== 'undefined' ? Uint8Array : Array

        var code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'
        for (var i = 0, len = code.length; i < len; ++i) {
            lookup[i] = code[i]
            revLookup[code.charCodeAt(i)] = i
        }

// Support decoding URL-safe base64 strings, as Node.js does.
// See: https://en.wikipedia.org/wiki/Base64#URL_applications
        revLookup['-'.charCodeAt(0)] = 62
        revLookup['_'.charCodeAt(0)] = 63

        function getLens (b64) {
            var len = b64.length

            if (len % 4 > 0) {
                throw new Error('Invalid string. Length must be a multiple of 4')
            }

            // Trim off extra bytes after placeholder bytes are found
            // See: https://github.com/beatgammit/base64-js/issues/42
            var validLen = b64.indexOf('=')
            if (validLen === -1) validLen = len

            var placeHoldersLen = validLen === len
                ? 0
                : 4 - (validLen % 4)

            return [validLen, placeHoldersLen]
        }

// base64 is 4/3 + up to two characters of the original data
        function byteLength (b64) {
            var lens = getLens(b64)
            var validLen = lens[0]
            var placeHoldersLen = lens[1]
            return ((validLen + placeHoldersLen) * 3 / 4) - placeHoldersLen
        }

        function _byteLength (b64, validLen, placeHoldersLen) {
            return ((validLen + placeHoldersLen) * 3 / 4) - placeHoldersLen
        }

        function toByteArray (b64) {
            var tmp
            var lens = getLens(b64)
            var validLen = lens[0]
            var placeHoldersLen = lens[1]

            var arr = new Arr(_byteLength(b64, validLen, placeHoldersLen))

            var curByte = 0

            // if there are placeholders, only get up to the last complete 4 chars
            var len = placeHoldersLen > 0
                ? validLen - 4
                : validLen

            var i
            for (i = 0; i < len; i += 4) {
                tmp =
                    (revLookup[b64.charCodeAt(i)] << 18) |
                    (revLookup[b64.charCodeAt(i + 1)] << 12) |
                    (revLookup[b64.charCodeAt(i + 2)] << 6) |
                    revLookup[b64.charCodeAt(i + 3)]
                arr[curByte++] = (tmp >> 16) & 0xFF
                arr[curByte++] = (tmp >> 8) & 0xFF
                arr[curByte++] = tmp & 0xFF
            }

            if (placeHoldersLen === 2) {
                tmp =
                    (revLookup[b64.charCodeAt(i)] << 2) |
                    (revLookup[b64.charCodeAt(i + 1)] >> 4)
                arr[curByte++] = tmp & 0xFF
            }

            if (placeHoldersLen === 1) {
                tmp =
                    (revLookup[b64.charCodeAt(i)] << 10) |
                    (revLookup[b64.charCodeAt(i + 1)] << 4) |
                    (revLookup[b64.charCodeAt(i + 2)] >> 2)
                arr[curByte++] = (tmp >> 8) & 0xFF
                arr[curByte++] = tmp & 0xFF
            }

            return arr
        }

        function tripletToBase64 (num) {
            return lookup[num >> 18 & 0x3F] +
                lookup[num >> 12 & 0x3F] +
                lookup[num >> 6 & 0x3F] +
                lookup[num & 0x3F]
        }

        function encodeChunk (uint8, start, end) {
            var tmp
            var output = []
            for (var i = start; i < end; i += 3) {
                tmp =
                    ((uint8[i] << 16) & 0xFF0000) +
                    ((uint8[i + 1] << 8) & 0xFF00) +
                    (uint8[i + 2] & 0xFF)
                output.push(tripletToBase64(tmp))
            }
            return output.join('')
        }

        function fromByteArray (uint8) {
            var tmp
            var len = uint8.length
            var extraBytes = len % 3 // if we have 1 byte left, pad 2 bytes
            var parts = []
            var maxChunkLength = 16383 // must be multiple of 3

            // go through the array every three bytes, we'll deal with trailing stuff later
            for (var i = 0, len2 = len - extraBytes; i < len2; i += maxChunkLength) {
                parts.push(encodeChunk(
                    uint8, i, (i + maxChunkLength) > len2 ? len2 : (i + maxChunkLength)
                ))
            }

            // pad the end with zeros, but make sure to not forget the extra bytes
            if (extraBytes === 1) {
                tmp = uint8[len - 1]
                parts.push(
                    lookup[tmp >> 2] +
                    lookup[(tmp << 4) & 0x3F] +
                    '=='
                )
            } else if (extraBytes === 2) {
                tmp = (uint8[len - 2] << 8) + uint8[len - 1]
                parts.push(
                    lookup[tmp >> 10] +
                    lookup[(tmp >> 4) & 0x3F] +
                    lookup[(tmp << 2) & 0x3F] +
                    '='
                )
            }

            return parts.join('')
        }

    },{}],6:[function(require,module,exports){
        /**
         * Create a blob builder even when vendor prefixes exist
         */

        var BlobBuilder = typeof BlobBuilder !== 'undefined' ? BlobBuilder :
            typeof WebKitBlobBuilder !== 'undefined' ? WebKitBlobBuilder :
                typeof MSBlobBuilder !== 'undefined' ? MSBlobBuilder :
                    typeof MozBlobBuilder !== 'undefined' ? MozBlobBuilder :
                        false;

        /**
         * Check if Blob constructor is supported
         */

        var blobSupported = (function() {
            try {
                var a = new Blob(['hi']);
                return a.size === 2;
            } catch(e) {
                return false;
            }
        })();

        /**
         * Check if Blob constructor supports ArrayBufferViews
         * Fails in Safari 6, so we need to map to ArrayBuffers there.
         */

        var blobSupportsArrayBufferView = blobSupported && (function() {
            try {
                var b = new Blob([new Uint8Array([1,2])]);
                return b.size === 2;
            } catch(e) {
                return false;
            }
        })();

        /**
         * Check if BlobBuilder is supported
         */

        var blobBuilderSupported = BlobBuilder
            && BlobBuilder.prototype.append
            && BlobBuilder.prototype.getBlob;

        /**
         * Helper function that maps ArrayBufferViews to ArrayBuffers
         * Used by BlobBuilder constructor and old browsers that didn't
         * support it in the Blob constructor.
         */

        function mapArrayBufferViews(ary) {
            return ary.map(function(chunk) {
                if (chunk.buffer instanceof ArrayBuffer) {
                    var buf = chunk.buffer;

                    // if this is a subarray, make a copy so we only
                    // include the subarray region from the underlying buffer
                    if (chunk.byteLength !== buf.byteLength) {
                        var copy = new Uint8Array(chunk.byteLength);
                        copy.set(new Uint8Array(buf, chunk.byteOffset, chunk.byteLength));
                        buf = copy.buffer;
                    }

                    return buf;
                }

                return chunk;
            });
        }

        function BlobBuilderConstructor(ary, options) {
            options = options || {};

            var bb = new BlobBuilder();
            mapArrayBufferViews(ary).forEach(function(part) {
                bb.append(part);
            });

            return (options.type) ? bb.getBlob(options.type) : bb.getBlob();
        };

        function BlobConstructor(ary, options) {
            return new Blob(mapArrayBufferViews(ary), options || {});
        };

        if (typeof Blob !== 'undefined') {
            BlobBuilderConstructor.prototype = Blob.prototype;
            BlobConstructor.prototype = Blob.prototype;
        }

        module.exports = (function() {
            if (blobSupported) {
                return blobSupportsArrayBufferView ? Blob : BlobConstructor;
            } else if (blobBuilderSupported) {
                return BlobBuilderConstructor;
            } else {
                return undefined;
            }
        })();

    },{}],7:[function(require,module,exports){

    },{}],8:[function(require,module,exports){
        (function (Buffer){
            /*!
 * The buffer module from node.js, for the browser.
 *
 * @author   Feross Aboukhadijeh <https://feross.org>
 * @license  MIT
 */
            /* eslint-disable no-proto */

            'use strict'

            var base64 = require('base64-js')
            var ieee754 = require('ieee754')

            exports.Buffer = Buffer
            exports.SlowBuffer = SlowBuffer
            exports.INSPECT_MAX_BYTES = 50

            var K_MAX_LENGTH = 0x7fffffff
            exports.kMaxLength = K_MAX_LENGTH

            /**
             * If `Buffer.TYPED_ARRAY_SUPPORT`:
             *   === true    Use Uint8Array implementation (fastest)
             *   === false   Print warning and recommend using `buffer` v4.x which has an Object
             *               implementation (most compatible, even IE6)
             *
             * Browsers that support typed arrays are IE 10+, Firefox 4+, Chrome 7+, Safari 5.1+,
             * Opera 11.6+, iOS 4.2+.
             *
             * We report that the browser does not support typed arrays if the are not subclassable
             * using __proto__. Firefox 4-29 lacks support for adding new properties to `Uint8Array`
             * (See: https://bugzilla.mozilla.org/show_bug.cgi?id=695438). IE 10 lacks support
             * for __proto__ and has a buggy typed array implementation.
             */
            Buffer.TYPED_ARRAY_SUPPORT = typedArraySupport()

            if (!Buffer.TYPED_ARRAY_SUPPORT && typeof console !== 'undefined' &&
                typeof console.error === 'function') {
                console.error(
                    'This browser lacks typed array (Uint8Array) support which is required by ' +
                    '`buffer` v5.x. Use `buffer` v4.x if you require old browser support.'
                )
            }

            function typedArraySupport () {
                // Can typed array instances can be augmented?
                try {
                    var arr = new Uint8Array(1)
                    arr.__proto__ = { __proto__: Uint8Array.prototype, foo: function () { return 42 } }
                    return arr.foo() === 42
                } catch (e) {
                    return false
                }
            }

            Object.defineProperty(Buffer.prototype, 'parent', {
                enumerable: true,
                get: function () {
                    if (!Buffer.isBuffer(this)) return undefined
                    return this.buffer
                }
            })

            Object.defineProperty(Buffer.prototype, 'offset', {
                enumerable: true,
                get: function () {
                    if (!Buffer.isBuffer(this)) return undefined
                    return this.byteOffset
                }
            })

            function createBuffer (length) {
                if (length > K_MAX_LENGTH) {
                    throw new RangeError('The value "' + length + '" is invalid for option "size"')
                }
                // Return an augmented `Uint8Array` instance
                var buf = new Uint8Array(length)
                buf.__proto__ = Buffer.prototype
                return buf
            }

            /**
             * The Buffer constructor returns instances of `Uint8Array` that have their
             * prototype changed to `Buffer.prototype`. Furthermore, `Buffer` is a subclass of
             * `Uint8Array`, so the returned instances will have all the node `Buffer` methods
             * and the `Uint8Array` methods. Square bracket notation works as expected -- it
             * returns a single octet.
             *
             * The `Uint8Array` prototype remains unmodified.
             */

            function Buffer (arg, encodingOrOffset, length) {
                // Common case.
                if (typeof arg === 'number') {
                    if (typeof encodingOrOffset === 'string') {
                        throw new TypeError(
                            'The "string" argument must be of type string. Received type number'
                        )
                    }
                    return allocUnsafe(arg)
                }
                return from(arg, encodingOrOffset, length)
            }

// Fix subarray() in ES2016. See: https://github.com/feross/buffer/pull/97
            if (typeof Symbol !== 'undefined' && Symbol.species != null &&
                Buffer[Symbol.species] === Buffer) {
                Object.defineProperty(Buffer, Symbol.species, {
                    value: null,
                    configurable: true,
                    enumerable: false,
                    writable: false
                })
            }

            Buffer.poolSize = 8192 // not used by this implementation

            function from (value, encodingOrOffset, length) {
                if (typeof value === 'string') {
                    return fromString(value, encodingOrOffset)
                }

                if (ArrayBuffer.isView(value)) {
                    return fromArrayLike(value)
                }

                if (value == null) {
                    throw TypeError(
                        'The first argument must be one of type string, Buffer, ArrayBuffer, Array, ' +
                        'or Array-like Object. Received type ' + (typeof value)
                    )
                }

                if (isInstance(value, ArrayBuffer) ||
                    (value && isInstance(value.buffer, ArrayBuffer))) {
                    return fromArrayBuffer(value, encodingOrOffset, length)
                }

                if (typeof value === 'number') {
                    throw new TypeError(
                        'The "value" argument must not be of type number. Received type number'
                    )
                }

                var valueOf = value.valueOf && value.valueOf()
                if (valueOf != null && valueOf !== value) {
                    return Buffer.from(valueOf, encodingOrOffset, length)
                }

                var b = fromObject(value)
                if (b) return b

                if (typeof Symbol !== 'undefined' && Symbol.toPrimitive != null &&
                    typeof value[Symbol.toPrimitive] === 'function') {
                    return Buffer.from(
                        value[Symbol.toPrimitive]('string'), encodingOrOffset, length
                    )
                }

                throw new TypeError(
                    'The first argument must be one of type string, Buffer, ArrayBuffer, Array, ' +
                    'or Array-like Object. Received type ' + (typeof value)
                )
            }

            /**
             * Functionally equivalent to Buffer(arg, encoding) but throws a TypeError
             * if value is a number.
             * Buffer.from(str[, encoding])
             * Buffer.from(array)
             * Buffer.from(buffer)
             * Buffer.from(arrayBuffer[, byteOffset[, length]])
             **/
            Buffer.from = function (value, encodingOrOffset, length) {
                return from(value, encodingOrOffset, length)
            }

// Note: Change prototype *after* Buffer.from is defined to workaround Chrome bug:
// https://github.com/feross/buffer/pull/148
            Buffer.prototype.__proto__ = Uint8Array.prototype
            Buffer.__proto__ = Uint8Array

            function assertSize (size) {
                if (typeof size !== 'number') {
                    throw new TypeError('"size" argument must be of type number')
                } else if (size < 0) {
                    throw new RangeError('The value "' + size + '" is invalid for option "size"')
                }
            }

            function alloc (size, fill, encoding) {
                assertSize(size)
                if (size <= 0) {
                    return createBuffer(size)
                }
                if (fill !== undefined) {
                    // Only pay attention to encoding if it's a string. This
                    // prevents accidentally sending in a number that would
                    // be interpretted as a start offset.
                    return typeof encoding === 'string'
                        ? createBuffer(size).fill(fill, encoding)
                        : createBuffer(size).fill(fill)
                }
                return createBuffer(size)
            }

            /**
             * Creates a new filled Buffer instance.
             * alloc(size[, fill[, encoding]])
             **/
            Buffer.alloc = function (size, fill, encoding) {
                return alloc(size, fill, encoding)
            }

            function allocUnsafe (size) {
                assertSize(size)
                return createBuffer(size < 0 ? 0 : checked(size) | 0)
            }

            /**
             * Equivalent to Buffer(num), by default creates a non-zero-filled Buffer instance.
             * */
            Buffer.allocUnsafe = function (size) {
                return allocUnsafe(size)
            }
            /**
             * Equivalent to SlowBuffer(num), by default creates a non-zero-filled Buffer instance.
             */
            Buffer.allocUnsafeSlow = function (size) {
                return allocUnsafe(size)
            }

            function fromString (string, encoding) {
                if (typeof encoding !== 'string' || encoding === '') {
                    encoding = 'utf8'
                }

                if (!Buffer.isEncoding(encoding)) {
                    throw new TypeError('Unknown encoding: ' + encoding)
                }

                var length = byteLength(string, encoding) | 0
                var buf = createBuffer(length)

                var actual = buf.write(string, encoding)

                if (actual !== length) {
                    // Writing a hex string, for example, that contains invalid characters will
                    // cause everything after the first invalid character to be ignored. (e.g.
                    // 'abxxcd' will be treated as 'ab')
                    buf = buf.slice(0, actual)
                }

                return buf
            }

            function fromArrayLike (array) {
                var length = array.length < 0 ? 0 : checked(array.length) | 0
                var buf = createBuffer(length)
                for (var i = 0; i < length; i += 1) {
                    buf[i] = array[i] & 255
                }
                return buf
            }

            function fromArrayBuffer (array, byteOffset, length) {
                if (byteOffset < 0 || array.byteLength < byteOffset) {
                    throw new RangeError('"offset" is outside of buffer bounds')
                }

                if (array.byteLength < byteOffset + (length || 0)) {
                    throw new RangeError('"length" is outside of buffer bounds')
                }

                var buf
                if (byteOffset === undefined && length === undefined) {
                    buf = new Uint8Array(array)
                } else if (length === undefined) {
                    buf = new Uint8Array(array, byteOffset)
                } else {
                    buf = new Uint8Array(array, byteOffset, length)
                }

                // Return an augmented `Uint8Array` instance
                buf.__proto__ = Buffer.prototype
                return buf
            }

            function fromObject (obj) {
                if (Buffer.isBuffer(obj)) {
                    var len = checked(obj.length) | 0
                    var buf = createBuffer(len)

                    if (buf.length === 0) {
                        return buf
                    }

                    obj.copy(buf, 0, 0, len)
                    return buf
                }

                if (obj.length !== undefined) {
                    if (typeof obj.length !== 'number' || numberIsNaN(obj.length)) {
                        return createBuffer(0)
                    }
                    return fromArrayLike(obj)
                }

                if (obj.type === 'Buffer' && Array.isArray(obj.data)) {
                    return fromArrayLike(obj.data)
                }
            }

            function checked (length) {
                // Note: cannot use `length < K_MAX_LENGTH` here because that fails when
                // length is NaN (which is otherwise coerced to zero.)
                if (length >= K_MAX_LENGTH) {
                    throw new RangeError('Attempt to allocate Buffer larger than maximum ' +
                        'size: 0x' + K_MAX_LENGTH.toString(16) + ' bytes')
                }
                return length | 0
            }

            function SlowBuffer (length) {
                if (+length != length) { // eslint-disable-line eqeqeq
                    length = 0
                }
                return Buffer.alloc(+length)
            }

            Buffer.isBuffer = function isBuffer (b) {
                return b != null && b._isBuffer === true &&
                    b !== Buffer.prototype // so Buffer.isBuffer(Buffer.prototype) will be false
            }

            Buffer.compare = function compare (a, b) {
                if (isInstance(a, Uint8Array)) a = Buffer.from(a, a.offset, a.byteLength)
                if (isInstance(b, Uint8Array)) b = Buffer.from(b, b.offset, b.byteLength)
                if (!Buffer.isBuffer(a) || !Buffer.isBuffer(b)) {
                    throw new TypeError(
                        'The "buf1", "buf2" arguments must be one of type Buffer or Uint8Array'
                    )
                }

                if (a === b) return 0

                var x = a.length
                var y = b.length

                for (var i = 0, len = Math.min(x, y); i < len; ++i) {
                    if (a[i] !== b[i]) {
                        x = a[i]
                        y = b[i]
                        break
                    }
                }

                if (x < y) return -1
                if (y < x) return 1
                return 0
            }

            Buffer.isEncoding = function isEncoding (encoding) {
                switch (String(encoding).toLowerCase()) {
                    case 'hex':
                    case 'utf8':
                    case 'utf-8':
                    case 'ascii':
                    case 'latin1':
                    case 'binary':
                    case 'base64':
                    case 'ucs2':
                    case 'ucs-2':
                    case 'utf16le':
                    case 'utf-16le':
                        return true
                    default:
                        return false
                }
            }

            Buffer.concat = function concat (list, length) {
                if (!Array.isArray(list)) {
                    throw new TypeError('"list" argument must be an Array of Buffers')
                }

                if (list.length === 0) {
                    return Buffer.alloc(0)
                }

                var i
                if (length === undefined) {
                    length = 0
                    for (i = 0; i < list.length; ++i) {
                        length += list[i].length
                    }
                }

                var buffer = Buffer.allocUnsafe(length)
                var pos = 0
                for (i = 0; i < list.length; ++i) {
                    var buf = list[i]
                    if (isInstance(buf, Uint8Array)) {
                        buf = Buffer.from(buf)
                    }
                    if (!Buffer.isBuffer(buf)) {
                        throw new TypeError('"list" argument must be an Array of Buffers')
                    }
                    buf.copy(buffer, pos)
                    pos += buf.length
                }
                return buffer
            }

            function byteLength (string, encoding) {
                if (Buffer.isBuffer(string)) {
                    return string.length
                }
                if (ArrayBuffer.isView(string) || isInstance(string, ArrayBuffer)) {
                    return string.byteLength
                }
                if (typeof string !== 'string') {
                    throw new TypeError(
                        'The "string" argument must be one of type string, Buffer, or ArrayBuffer. ' +
                        'Received type ' + typeof string
                    )
                }

                var len = string.length
                var mustMatch = (arguments.length > 2 && arguments[2] === true)
                if (!mustMatch && len === 0) return 0

                // Use a for loop to avoid recursion
                var loweredCase = false
                for (;;) {
                    switch (encoding) {
                        case 'ascii':
                        case 'latin1':
                        case 'binary':
                            return len
                        case 'utf8':
                        case 'utf-8':
                            return utf8ToBytes(string).length
                        case 'ucs2':
                        case 'ucs-2':
                        case 'utf16le':
                        case 'utf-16le':
                            return len * 2
                        case 'hex':
                            return len >>> 1
                        case 'base64':
                            return base64ToBytes(string).length
                        default:
                            if (loweredCase) {
                                return mustMatch ? -1 : utf8ToBytes(string).length // assume utf8
                            }
                            encoding = ('' + encoding).toLowerCase()
                            loweredCase = true
                    }
                }
            }
            Buffer.byteLength = byteLength

            function slowToString (encoding, start, end) {
                var loweredCase = false

                // No need to verify that "this.length <= MAX_UINT32" since it's a read-only
                // property of a typed array.

                // This behaves neither like String nor Uint8Array in that we set start/end
                // to their upper/lower bounds if the value passed is out of range.
                // undefined is handled specially as per ECMA-262 6th Edition,
                // Section 13.3.3.7 Runtime Semantics: KeyedBindingInitialization.
                if (start === undefined || start < 0) {
                    start = 0
                }
                // Return early if start > this.length. Done here to prevent potential uint32
                // coercion fail below.
                if (start > this.length) {
                    return ''
                }

                if (end === undefined || end > this.length) {
                    end = this.length
                }

                if (end <= 0) {
                    return ''
                }

                // Force coersion to uint32. This will also coerce falsey/NaN values to 0.
                end >>>= 0
                start >>>= 0

                if (end <= start) {
                    return ''
                }

                if (!encoding) encoding = 'utf8'

                while (true) {
                    switch (encoding) {
                        case 'hex':
                            return hexSlice(this, start, end)

                        case 'utf8':
                        case 'utf-8':
                            return utf8Slice(this, start, end)

                        case 'ascii':
                            return asciiSlice(this, start, end)

                        case 'latin1':
                        case 'binary':
                            return latin1Slice(this, start, end)

                        case 'base64':
                            return base64Slice(this, start, end)

                        case 'ucs2':
                        case 'ucs-2':
                        case 'utf16le':
                        case 'utf-16le':
                            return utf16leSlice(this, start, end)

                        default:
                            if (loweredCase) throw new TypeError('Unknown encoding: ' + encoding)
                            encoding = (encoding + '').toLowerCase()
                            loweredCase = true
                    }
                }
            }

// This property is used by `Buffer.isBuffer` (and the `is-buffer` npm package)
// to detect a Buffer instance. It's not possible to use `instanceof Buffer`
// reliably in a browserify context because there could be multiple different
// copies of the 'buffer' package in use. This method works even for Buffer
// instances that were created from another copy of the `buffer` package.
// See: https://github.com/feross/buffer/issues/154
            Buffer.prototype._isBuffer = true

            function swap (b, n, m) {
                var i = b[n]
                b[n] = b[m]
                b[m] = i
            }

            Buffer.prototype.swap16 = function swap16 () {
                var len = this.length
                if (len % 2 !== 0) {
                    throw new RangeError('Buffer size must be a multiple of 16-bits')
                }
                for (var i = 0; i < len; i += 2) {
                    swap(this, i, i + 1)
                }
                return this
            }

            Buffer.prototype.swap32 = function swap32 () {
                var len = this.length
                if (len % 4 !== 0) {
                    throw new RangeError('Buffer size must be a multiple of 32-bits')
                }
                for (var i = 0; i < len; i += 4) {
                    swap(this, i, i + 3)
                    swap(this, i + 1, i + 2)
                }
                return this
            }

            Buffer.prototype.swap64 = function swap64 () {
                var len = this.length
                if (len % 8 !== 0) {
                    throw new RangeError('Buffer size must be a multiple of 64-bits')
                }
                for (var i = 0; i < len; i += 8) {
                    swap(this, i, i + 7)
                    swap(this, i + 1, i + 6)
                    swap(this, i + 2, i + 5)
                    swap(this, i + 3, i + 4)
                }
                return this
            }

            Buffer.prototype.toString = function toString () {
                var length = this.length
                if (length === 0) return ''
                if (arguments.length === 0) return utf8Slice(this, 0, length)
                return slowToString.apply(this, arguments)
            }

            Buffer.prototype.toLocaleString = Buffer.prototype.toString

            Buffer.prototype.equals = function equals (b) {
                if (!Buffer.isBuffer(b)) throw new TypeError('Argument must be a Buffer')
                if (this === b) return true
                return Buffer.compare(this, b) === 0
            }

            Buffer.prototype.inspect = function inspect () {
                var str = ''
                var max = exports.INSPECT_MAX_BYTES
                str = this.toString('hex', 0, max).replace(/(.{2})/g, '$1 ').trim()
                if (this.length > max) str += ' ... '
                return '<Buffer ' + str + '>'
            }

            Buffer.prototype.compare = function compare (target, start, end, thisStart, thisEnd) {
                if (isInstance(target, Uint8Array)) {
                    target = Buffer.from(target, target.offset, target.byteLength)
                }
                if (!Buffer.isBuffer(target)) {
                    throw new TypeError(
                        'The "target" argument must be one of type Buffer or Uint8Array. ' +
                        'Received type ' + (typeof target)
                    )
                }

                if (start === undefined) {
                    start = 0
                }
                if (end === undefined) {
                    end = target ? target.length : 0
                }
                if (thisStart === undefined) {
                    thisStart = 0
                }
                if (thisEnd === undefined) {
                    thisEnd = this.length
                }

                if (start < 0 || end > target.length || thisStart < 0 || thisEnd > this.length) {
                    throw new RangeError('out of range index')
                }

                if (thisStart >= thisEnd && start >= end) {
                    return 0
                }
                if (thisStart >= thisEnd) {
                    return -1
                }
                if (start >= end) {
                    return 1
                }

                start >>>= 0
                end >>>= 0
                thisStart >>>= 0
                thisEnd >>>= 0

                if (this === target) return 0

                var x = thisEnd - thisStart
                var y = end - start
                var len = Math.min(x, y)

                var thisCopy = this.slice(thisStart, thisEnd)
                var targetCopy = target.slice(start, end)

                for (var i = 0; i < len; ++i) {
                    if (thisCopy[i] !== targetCopy[i]) {
                        x = thisCopy[i]
                        y = targetCopy[i]
                        break
                    }
                }

                if (x < y) return -1
                if (y < x) return 1
                return 0
            }

// Finds either the first index of `val` in `buffer` at offset >= `byteOffset`,
// OR the last index of `val` in `buffer` at offset <= `byteOffset`.
//
// Arguments:
// - buffer - a Buffer to search
// - val - a string, Buffer, or number
// - byteOffset - an index into `buffer`; will be clamped to an int32
// - encoding - an optional encoding, relevant is val is a string
// - dir - true for indexOf, false for lastIndexOf
            function bidirectionalIndexOf (buffer, val, byteOffset, encoding, dir) {
                // Empty buffer means no match
                if (buffer.length === 0) return -1

                // Normalize byteOffset
                if (typeof byteOffset === 'string') {
                    encoding = byteOffset
                    byteOffset = 0
                } else if (byteOffset > 0x7fffffff) {
                    byteOffset = 0x7fffffff
                } else if (byteOffset < -0x80000000) {
                    byteOffset = -0x80000000
                }
                byteOffset = +byteOffset // Coerce to Number.
                if (numberIsNaN(byteOffset)) {
                    // byteOffset: it it's undefined, null, NaN, "foo", etc, search whole buffer
                    byteOffset = dir ? 0 : (buffer.length - 1)
                }

                // Normalize byteOffset: negative offsets start from the end of the buffer
                if (byteOffset < 0) byteOffset = buffer.length + byteOffset
                if (byteOffset >= buffer.length) {
                    if (dir) return -1
                    else byteOffset = buffer.length - 1
                } else if (byteOffset < 0) {
                    if (dir) byteOffset = 0
                    else return -1
                }

                // Normalize val
                if (typeof val === 'string') {
                    val = Buffer.from(val, encoding)
                }

                // Finally, search either indexOf (if dir is true) or lastIndexOf
                if (Buffer.isBuffer(val)) {
                    // Special case: looking for empty string/buffer always fails
                    if (val.length === 0) {
                        return -1
                    }
                    return arrayIndexOf(buffer, val, byteOffset, encoding, dir)
                } else if (typeof val === 'number') {
                    val = val & 0xFF // Search for a byte value [0-255]
                    if (typeof Uint8Array.prototype.indexOf === 'function') {
                        if (dir) {
                            return Uint8Array.prototype.indexOf.call(buffer, val, byteOffset)
                        } else {
                            return Uint8Array.prototype.lastIndexOf.call(buffer, val, byteOffset)
                        }
                    }
                    return arrayIndexOf(buffer, [ val ], byteOffset, encoding, dir)
                }

                throw new TypeError('val must be string, number or Buffer')
            }

            function arrayIndexOf (arr, val, byteOffset, encoding, dir) {
                var indexSize = 1
                var arrLength = arr.length
                var valLength = val.length

                if (encoding !== undefined) {
                    encoding = String(encoding).toLowerCase()
                    if (encoding === 'ucs2' || encoding === 'ucs-2' ||
                        encoding === 'utf16le' || encoding === 'utf-16le') {
                        if (arr.length < 2 || val.length < 2) {
                            return -1
                        }
                        indexSize = 2
                        arrLength /= 2
                        valLength /= 2
                        byteOffset /= 2
                    }
                }

                function read (buf, i) {
                    if (indexSize === 1) {
                        return buf[i]
                    } else {
                        return buf.readUInt16BE(i * indexSize)
                    }
                }

                var i
                if (dir) {
                    var foundIndex = -1
                    for (i = byteOffset; i < arrLength; i++) {
                        if (read(arr, i) === read(val, foundIndex === -1 ? 0 : i - foundIndex)) {
                            if (foundIndex === -1) foundIndex = i
                            if (i - foundIndex + 1 === valLength) return foundIndex * indexSize
                        } else {
                            if (foundIndex !== -1) i -= i - foundIndex
                            foundIndex = -1
                        }
                    }
                } else {
                    if (byteOffset + valLength > arrLength) byteOffset = arrLength - valLength
                    for (i = byteOffset; i >= 0; i--) {
                        var found = true
                        for (var j = 0; j < valLength; j++) {
                            if (read(arr, i + j) !== read(val, j)) {
                                found = false
                                break
                            }
                        }
                        if (found) return i
                    }
                }

                return -1
            }

            Buffer.prototype.includes = function includes (val, byteOffset, encoding) {
                return this.indexOf(val, byteOffset, encoding) !== -1
            }

            Buffer.prototype.indexOf = function indexOf (val, byteOffset, encoding) {
                return bidirectionalIndexOf(this, val, byteOffset, encoding, true)
            }

            Buffer.prototype.lastIndexOf = function lastIndexOf (val, byteOffset, encoding) {
                return bidirectionalIndexOf(this, val, byteOffset, encoding, false)
            }

            function hexWrite (buf, string, offset, length) {
                offset = Number(offset) || 0
                var remaining = buf.length - offset
                if (!length) {
                    length = remaining
                } else {
                    length = Number(length)
                    if (length > remaining) {
                        length = remaining
                    }
                }

                var strLen = string.length

                if (length > strLen / 2) {
                    length = strLen / 2
                }
                for (var i = 0; i < length; ++i) {
                    var parsed = parseInt(string.substr(i * 2, 2), 16)
                    if (numberIsNaN(parsed)) return i
                    buf[offset + i] = parsed
                }
                return i
            }

            function utf8Write (buf, string, offset, length) {
                return blitBuffer(utf8ToBytes(string, buf.length - offset), buf, offset, length)
            }

            function asciiWrite (buf, string, offset, length) {
                return blitBuffer(asciiToBytes(string), buf, offset, length)
            }

            function latin1Write (buf, string, offset, length) {
                return asciiWrite(buf, string, offset, length)
            }

            function base64Write (buf, string, offset, length) {
                return blitBuffer(base64ToBytes(string), buf, offset, length)
            }

            function ucs2Write (buf, string, offset, length) {
                return blitBuffer(utf16leToBytes(string, buf.length - offset), buf, offset, length)
            }

            Buffer.prototype.write = function write (string, offset, length, encoding) {
                // Buffer#write(string)
                if (offset === undefined) {
                    encoding = 'utf8'
                    length = this.length
                    offset = 0
                    // Buffer#write(string, encoding)
                } else if (length === undefined && typeof offset === 'string') {
                    encoding = offset
                    length = this.length
                    offset = 0
                    // Buffer#write(string, offset[, length][, encoding])
                } else if (isFinite(offset)) {
                    offset = offset >>> 0
                    if (isFinite(length)) {
                        length = length >>> 0
                        if (encoding === undefined) encoding = 'utf8'
                    } else {
                        encoding = length
                        length = undefined
                    }
                } else {
                    throw new Error(
                        'Buffer.write(string, encoding, offset[, length]) is no longer supported'
                    )
                }

                var remaining = this.length - offset
                if (length === undefined || length > remaining) length = remaining

                if ((string.length > 0 && (length < 0 || offset < 0)) || offset > this.length) {
                    throw new RangeError('Attempt to write outside buffer bounds')
                }

                if (!encoding) encoding = 'utf8'

                var loweredCase = false
                for (;;) {
                    switch (encoding) {
                        case 'hex':
                            return hexWrite(this, string, offset, length)

                        case 'utf8':
                        case 'utf-8':
                            return utf8Write(this, string, offset, length)

                        case 'ascii':
                            return asciiWrite(this, string, offset, length)

                        case 'latin1':
                        case 'binary':
                            return latin1Write(this, string, offset, length)

                        case 'base64':
                            // Warning: maxLength not taken into account in base64Write
                            return base64Write(this, string, offset, length)

                        case 'ucs2':
                        case 'ucs-2':
                        case 'utf16le':
                        case 'utf-16le':
                            return ucs2Write(this, string, offset, length)

                        default:
                            if (loweredCase) throw new TypeError('Unknown encoding: ' + encoding)
                            encoding = ('' + encoding).toLowerCase()
                            loweredCase = true
                    }
                }
            }

            Buffer.prototype.toJSON = function toJSON () {
                return {
                    type: 'Buffer',
                    data: Array.prototype.slice.call(this._arr || this, 0)
                }
            }

            function base64Slice (buf, start, end) {
                if (start === 0 && end === buf.length) {
                    return base64.fromByteArray(buf)
                } else {
                    return base64.fromByteArray(buf.slice(start, end))
                }
            }

            function utf8Slice (buf, start, end) {
                end = Math.min(buf.length, end)
                var res = []

                var i = start
                while (i < end) {
                    var firstByte = buf[i]
                    var codePoint = null
                    var bytesPerSequence = (firstByte > 0xEF) ? 4
                        : (firstByte > 0xDF) ? 3
                            : (firstByte > 0xBF) ? 2
                                : 1

                    if (i + bytesPerSequence <= end) {
                        var secondByte, thirdByte, fourthByte, tempCodePoint

                        switch (bytesPerSequence) {
                            case 1:
                                if (firstByte < 0x80) {
                                    codePoint = firstByte
                                }
                                break
                            case 2:
                                secondByte = buf[i + 1]
                                if ((secondByte & 0xC0) === 0x80) {
                                    tempCodePoint = (firstByte & 0x1F) << 0x6 | (secondByte & 0x3F)
                                    if (tempCodePoint > 0x7F) {
                                        codePoint = tempCodePoint
                                    }
                                }
                                break
                            case 3:
                                secondByte = buf[i + 1]
                                thirdByte = buf[i + 2]
                                if ((secondByte & 0xC0) === 0x80 && (thirdByte & 0xC0) === 0x80) {
                                    tempCodePoint = (firstByte & 0xF) << 0xC | (secondByte & 0x3F) << 0x6 | (thirdByte & 0x3F)
                                    if (tempCodePoint > 0x7FF && (tempCodePoint < 0xD800 || tempCodePoint > 0xDFFF)) {
                                        codePoint = tempCodePoint
                                    }
                                }
                                break
                            case 4:
                                secondByte = buf[i + 1]
                                thirdByte = buf[i + 2]
                                fourthByte = buf[i + 3]
                                if ((secondByte & 0xC0) === 0x80 && (thirdByte & 0xC0) === 0x80 && (fourthByte & 0xC0) === 0x80) {
                                    tempCodePoint = (firstByte & 0xF) << 0x12 | (secondByte & 0x3F) << 0xC | (thirdByte & 0x3F) << 0x6 | (fourthByte & 0x3F)
                                    if (tempCodePoint > 0xFFFF && tempCodePoint < 0x110000) {
                                        codePoint = tempCodePoint
                                    }
                                }
                        }
                    }

                    if (codePoint === null) {
                        // we did not generate a valid codePoint so insert a
                        // replacement char (U+FFFD) and advance only 1 byte
                        codePoint = 0xFFFD
                        bytesPerSequence = 1
                    } else if (codePoint > 0xFFFF) {
                        // encode to utf16 (surrogate pair dance)
                        codePoint -= 0x10000
                        res.push(codePoint >>> 10 & 0x3FF | 0xD800)
                        codePoint = 0xDC00 | codePoint & 0x3FF
                    }

                    res.push(codePoint)
                    i += bytesPerSequence
                }

                return decodeCodePointsArray(res)
            }

// Based on http://stackoverflow.com/a/22747272/680742, the browser with
// the lowest limit is Chrome, with 0x10000 args.
// We go 1 magnitude less, for safety
            var MAX_ARGUMENTS_LENGTH = 0x1000

            function decodeCodePointsArray (codePoints) {
                var len = codePoints.length
                if (len <= MAX_ARGUMENTS_LENGTH) {
                    return String.fromCharCode.apply(String, codePoints) // avoid extra slice()
                }

                // Decode in chunks to avoid "call stack size exceeded".
                var res = ''
                var i = 0
                while (i < len) {
                    res += String.fromCharCode.apply(
                        String,
                        codePoints.slice(i, i += MAX_ARGUMENTS_LENGTH)
                    )
                }
                return res
            }

            function asciiSlice (buf, start, end) {
                var ret = ''
                end = Math.min(buf.length, end)

                for (var i = start; i < end; ++i) {
                    ret += String.fromCharCode(buf[i] & 0x7F)
                }
                return ret
            }

            function latin1Slice (buf, start, end) {
                var ret = ''
                end = Math.min(buf.length, end)

                for (var i = start; i < end; ++i) {
                    ret += String.fromCharCode(buf[i])
                }
                return ret
            }

            function hexSlice (buf, start, end) {
                var len = buf.length

                if (!start || start < 0) start = 0
                if (!end || end < 0 || end > len) end = len

                var out = ''
                for (var i = start; i < end; ++i) {
                    out += toHex(buf[i])
                }
                return out
            }

            function utf16leSlice (buf, start, end) {
                var bytes = buf.slice(start, end)
                var res = ''
                for (var i = 0; i < bytes.length; i += 2) {
                    res += String.fromCharCode(bytes[i] + (bytes[i + 1] * 256))
                }
                return res
            }

            Buffer.prototype.slice = function slice (start, end) {
                var len = this.length
                start = ~~start
                end = end === undefined ? len : ~~end

                if (start < 0) {
                    start += len
                    if (start < 0) start = 0
                } else if (start > len) {
                    start = len
                }

                if (end < 0) {
                    end += len
                    if (end < 0) end = 0
                } else if (end > len) {
                    end = len
                }

                if (end < start) end = start

                var newBuf = this.subarray(start, end)
                // Return an augmented `Uint8Array` instance
                newBuf.__proto__ = Buffer.prototype
                return newBuf
            }

            /*
 * Need to make sure that buffer isn't trying to write out of bounds.
 */
            function checkOffset (offset, ext, length) {
                if ((offset % 1) !== 0 || offset < 0) throw new RangeError('offset is not uint')
                if (offset + ext > length) throw new RangeError('Trying to access beyond buffer length')
            }

            Buffer.prototype.readUIntLE = function readUIntLE (offset, byteLength, noAssert) {
                offset = offset >>> 0
                byteLength = byteLength >>> 0
                if (!noAssert) checkOffset(offset, byteLength, this.length)

                var val = this[offset]
                var mul = 1
                var i = 0
                while (++i < byteLength && (mul *= 0x100)) {
                    val += this[offset + i] * mul
                }

                return val
            }

            Buffer.prototype.readUIntBE = function readUIntBE (offset, byteLength, noAssert) {
                offset = offset >>> 0
                byteLength = byteLength >>> 0
                if (!noAssert) {
                    checkOffset(offset, byteLength, this.length)
                }

                var val = this[offset + --byteLength]
                var mul = 1
                while (byteLength > 0 && (mul *= 0x100)) {
                    val += this[offset + --byteLength] * mul
                }

                return val
            }

            Buffer.prototype.readUInt8 = function readUInt8 (offset, noAssert) {
                offset = offset >>> 0
                if (!noAssert) checkOffset(offset, 1, this.length)
                return this[offset]
            }

            Buffer.prototype.readUInt16LE = function readUInt16LE (offset, noAssert) {
                offset = offset >>> 0
                if (!noAssert) checkOffset(offset, 2, this.length)
                return this[offset] | (this[offset + 1] << 8)
            }

            Buffer.prototype.readUInt16BE = function readUInt16BE (offset, noAssert) {
                offset = offset >>> 0
                if (!noAssert) checkOffset(offset, 2, this.length)
                return (this[offset] << 8) | this[offset + 1]
            }

            Buffer.prototype.readUInt32LE = function readUInt32LE (offset, noAssert) {
                offset = offset >>> 0
                if (!noAssert) checkOffset(offset, 4, this.length)

                return ((this[offset]) |
                        (this[offset + 1] << 8) |
                        (this[offset + 2] << 16)) +
                    (this[offset + 3] * 0x1000000)
            }

            Buffer.prototype.readUInt32BE = function readUInt32BE (offset, noAssert) {
                offset = offset >>> 0
                if (!noAssert) checkOffset(offset, 4, this.length)

                return (this[offset] * 0x1000000) +
                    ((this[offset + 1] << 16) |
                        (this[offset + 2] << 8) |
                        this[offset + 3])
            }

            Buffer.prototype.readIntLE = function readIntLE (offset, byteLength, noAssert) {
                offset = offset >>> 0
                byteLength = byteLength >>> 0
                if (!noAssert) checkOffset(offset, byteLength, this.length)

                var val = this[offset]
                var mul = 1
                var i = 0
                while (++i < byteLength && (mul *= 0x100)) {
                    val += this[offset + i] * mul
                }
                mul *= 0x80

                if (val >= mul) val -= Math.pow(2, 8 * byteLength)

                return val
            }

            Buffer.prototype.readIntBE = function readIntBE (offset, byteLength, noAssert) {
                offset = offset >>> 0
                byteLength = byteLength >>> 0
                if (!noAssert) checkOffset(offset, byteLength, this.length)

                var i = byteLength
                var mul = 1
                var val = this[offset + --i]
                while (i > 0 && (mul *= 0x100)) {
                    val += this[offset + --i] * mul
                }
                mul *= 0x80

                if (val >= mul) val -= Math.pow(2, 8 * byteLength)

                return val
            }

            Buffer.prototype.readInt8 = function readInt8 (offset, noAssert) {
                offset = offset >>> 0
                if (!noAssert) checkOffset(offset, 1, this.length)
                if (!(this[offset] & 0x80)) return (this[offset])
                return ((0xff - this[offset] + 1) * -1)
            }

            Buffer.prototype.readInt16LE = function readInt16LE (offset, noAssert) {
                offset = offset >>> 0
                if (!noAssert) checkOffset(offset, 2, this.length)
                var val = this[offset] | (this[offset + 1] << 8)
                return (val & 0x8000) ? val | 0xFFFF0000 : val
            }

            Buffer.prototype.readInt16BE = function readInt16BE (offset, noAssert) {
                offset = offset >>> 0
                if (!noAssert) checkOffset(offset, 2, this.length)
                var val = this[offset + 1] | (this[offset] << 8)
                return (val & 0x8000) ? val | 0xFFFF0000 : val
            }

            Buffer.prototype.readInt32LE = function readInt32LE (offset, noAssert) {
                offset = offset >>> 0
                if (!noAssert) checkOffset(offset, 4, this.length)

                return (this[offset]) |
                    (this[offset + 1] << 8) |
                    (this[offset + 2] << 16) |
                    (this[offset + 3] << 24)
            }

            Buffer.prototype.readInt32BE = function readInt32BE (offset, noAssert) {
                offset = offset >>> 0
                if (!noAssert) checkOffset(offset, 4, this.length)

                return (this[offset] << 24) |
                    (this[offset + 1] << 16) |
                    (this[offset + 2] << 8) |
                    (this[offset + 3])
            }

            Buffer.prototype.readFloatLE = function readFloatLE (offset, noAssert) {
                offset = offset >>> 0
                if (!noAssert) checkOffset(offset, 4, this.length)
                return ieee754.read(this, offset, true, 23, 4)
            }

            Buffer.prototype.readFloatBE = function readFloatBE (offset, noAssert) {
                offset = offset >>> 0
                if (!noAssert) checkOffset(offset, 4, this.length)
                return ieee754.read(this, offset, false, 23, 4)
            }

            Buffer.prototype.readDoubleLE = function readDoubleLE (offset, noAssert) {
                offset = offset >>> 0
                if (!noAssert) checkOffset(offset, 8, this.length)
                return ieee754.read(this, offset, true, 52, 8)
            }

            Buffer.prototype.readDoubleBE = function readDoubleBE (offset, noAssert) {
                offset = offset >>> 0
                if (!noAssert) checkOffset(offset, 8, this.length)
                return ieee754.read(this, offset, false, 52, 8)
            }

            function checkInt (buf, value, offset, ext, max, min) {
                if (!Buffer.isBuffer(buf)) throw new TypeError('"buffer" argument must be a Buffer instance')
                if (value > max || value < min) throw new RangeError('"value" argument is out of bounds')
                if (offset + ext > buf.length) throw new RangeError('Index out of range')
            }

            Buffer.prototype.writeUIntLE = function writeUIntLE (value, offset, byteLength, noAssert) {
                value = +value
                offset = offset >>> 0
                byteLength = byteLength >>> 0
                if (!noAssert) {
                    var maxBytes = Math.pow(2, 8 * byteLength) - 1
                    checkInt(this, value, offset, byteLength, maxBytes, 0)
                }

                var mul = 1
                var i = 0
                this[offset] = value & 0xFF
                while (++i < byteLength && (mul *= 0x100)) {
                    this[offset + i] = (value / mul) & 0xFF
                }

                return offset + byteLength
            }

            Buffer.prototype.writeUIntBE = function writeUIntBE (value, offset, byteLength, noAssert) {
                value = +value
                offset = offset >>> 0
                byteLength = byteLength >>> 0
                if (!noAssert) {
                    var maxBytes = Math.pow(2, 8 * byteLength) - 1
                    checkInt(this, value, offset, byteLength, maxBytes, 0)
                }

                var i = byteLength - 1
                var mul = 1
                this[offset + i] = value & 0xFF
                while (--i >= 0 && (mul *= 0x100)) {
                    this[offset + i] = (value / mul) & 0xFF
                }

                return offset + byteLength
            }

            Buffer.prototype.writeUInt8 = function writeUInt8 (value, offset, noAssert) {
                value = +value
                offset = offset >>> 0
                if (!noAssert) checkInt(this, value, offset, 1, 0xff, 0)
                this[offset] = (value & 0xff)
                return offset + 1
            }

            Buffer.prototype.writeUInt16LE = function writeUInt16LE (value, offset, noAssert) {
                value = +value
                offset = offset >>> 0
                if (!noAssert) checkInt(this, value, offset, 2, 0xffff, 0)
                this[offset] = (value & 0xff)
                this[offset + 1] = (value >>> 8)
                return offset + 2
            }

            Buffer.prototype.writeUInt16BE = function writeUInt16BE (value, offset, noAssert) {
                value = +value
                offset = offset >>> 0
                if (!noAssert) checkInt(this, value, offset, 2, 0xffff, 0)
                this[offset] = (value >>> 8)
                this[offset + 1] = (value & 0xff)
                return offset + 2
            }

            Buffer.prototype.writeUInt32LE = function writeUInt32LE (value, offset, noAssert) {
                value = +value
                offset = offset >>> 0
                if (!noAssert) checkInt(this, value, offset, 4, 0xffffffff, 0)
                this[offset + 3] = (value >>> 24)
                this[offset + 2] = (value >>> 16)
                this[offset + 1] = (value >>> 8)
                this[offset] = (value & 0xff)
                return offset + 4
            }

            Buffer.prototype.writeUInt32BE = function writeUInt32BE (value, offset, noAssert) {
                value = +value
                offset = offset >>> 0
                if (!noAssert) checkInt(this, value, offset, 4, 0xffffffff, 0)
                this[offset] = (value >>> 24)
                this[offset + 1] = (value >>> 16)
                this[offset + 2] = (value >>> 8)
                this[offset + 3] = (value & 0xff)
                return offset + 4
            }

            Buffer.prototype.writeIntLE = function writeIntLE (value, offset, byteLength, noAssert) {
                value = +value
                offset = offset >>> 0
                if (!noAssert) {
                    var limit = Math.pow(2, (8 * byteLength) - 1)

                    checkInt(this, value, offset, byteLength, limit - 1, -limit)
                }

                var i = 0
                var mul = 1
                var sub = 0
                this[offset] = value & 0xFF
                while (++i < byteLength && (mul *= 0x100)) {
                    if (value < 0 && sub === 0 && this[offset + i - 1] !== 0) {
                        sub = 1
                    }
                    this[offset + i] = ((value / mul) >> 0) - sub & 0xFF
                }

                return offset + byteLength
            }

            Buffer.prototype.writeIntBE = function writeIntBE (value, offset, byteLength, noAssert) {
                value = +value
                offset = offset >>> 0
                if (!noAssert) {
                    var limit = Math.pow(2, (8 * byteLength) - 1)

                    checkInt(this, value, offset, byteLength, limit - 1, -limit)
                }

                var i = byteLength - 1
                var mul = 1
                var sub = 0
                this[offset + i] = value & 0xFF
                while (--i >= 0 && (mul *= 0x100)) {
                    if (value < 0 && sub === 0 && this[offset + i + 1] !== 0) {
                        sub = 1
                    }
                    this[offset + i] = ((value / mul) >> 0) - sub & 0xFF
                }

                return offset + byteLength
            }

            Buffer.prototype.writeInt8 = function writeInt8 (value, offset, noAssert) {
                value = +value
                offset = offset >>> 0
                if (!noAssert) checkInt(this, value, offset, 1, 0x7f, -0x80)
                if (value < 0) value = 0xff + value + 1
                this[offset] = (value & 0xff)
                return offset + 1
            }

            Buffer.prototype.writeInt16LE = function writeInt16LE (value, offset, noAssert) {
                value = +value
                offset = offset >>> 0
                if (!noAssert) checkInt(this, value, offset, 2, 0x7fff, -0x8000)
                this[offset] = (value & 0xff)
                this[offset + 1] = (value >>> 8)
                return offset + 2
            }

            Buffer.prototype.writeInt16BE = function writeInt16BE (value, offset, noAssert) {
                value = +value
                offset = offset >>> 0
                if (!noAssert) checkInt(this, value, offset, 2, 0x7fff, -0x8000)
                this[offset] = (value >>> 8)
                this[offset + 1] = (value & 0xff)
                return offset + 2
            }

            Buffer.prototype.writeInt32LE = function writeInt32LE (value, offset, noAssert) {
                value = +value
                offset = offset >>> 0
                if (!noAssert) checkInt(this, value, offset, 4, 0x7fffffff, -0x80000000)
                this[offset] = (value & 0xff)
                this[offset + 1] = (value >>> 8)
                this[offset + 2] = (value >>> 16)
                this[offset + 3] = (value >>> 24)
                return offset + 4
            }

            Buffer.prototype.writeInt32BE = function writeInt32BE (value, offset, noAssert) {
                value = +value
                offset = offset >>> 0
                if (!noAssert) checkInt(this, value, offset, 4, 0x7fffffff, -0x80000000)
                if (value < 0) value = 0xffffffff + value + 1
                this[offset] = (value >>> 24)
                this[offset + 1] = (value >>> 16)
                this[offset + 2] = (value >>> 8)
                this[offset + 3] = (value & 0xff)
                return offset + 4
            }

            function checkIEEE754 (buf, value, offset, ext, max, min) {
                if (offset + ext > buf.length) throw new RangeError('Index out of range')
                if (offset < 0) throw new RangeError('Index out of range')
            }

            function writeFloat (buf, value, offset, littleEndian, noAssert) {
                value = +value
                offset = offset >>> 0
                if (!noAssert) {
                    checkIEEE754(buf, value, offset, 4, 3.4028234663852886e+38, -3.4028234663852886e+38)
                }
                ieee754.write(buf, value, offset, littleEndian, 23, 4)
                return offset + 4
            }

            Buffer.prototype.writeFloatLE = function writeFloatLE (value, offset, noAssert) {
                return writeFloat(this, value, offset, true, noAssert)
            }

            Buffer.prototype.writeFloatBE = function writeFloatBE (value, offset, noAssert) {
                return writeFloat(this, value, offset, false, noAssert)
            }

            function writeDouble (buf, value, offset, littleEndian, noAssert) {
                value = +value
                offset = offset >>> 0
                if (!noAssert) {
                    checkIEEE754(buf, value, offset, 8, 1.7976931348623157E+308, -1.7976931348623157E+308)
                }
                ieee754.write(buf, value, offset, littleEndian, 52, 8)
                return offset + 8
            }

            Buffer.prototype.writeDoubleLE = function writeDoubleLE (value, offset, noAssert) {
                return writeDouble(this, value, offset, true, noAssert)
            }

            Buffer.prototype.writeDoubleBE = function writeDoubleBE (value, offset, noAssert) {
                return writeDouble(this, value, offset, false, noAssert)
            }

// copy(targetBuffer, targetStart=0, sourceStart=0, sourceEnd=buffer.length)
            Buffer.prototype.copy = function copy (target, targetStart, start, end) {
                if (!Buffer.isBuffer(target)) throw new TypeError('argument should be a Buffer')
                if (!start) start = 0
                if (!end && end !== 0) end = this.length
                if (targetStart >= target.length) targetStart = target.length
                if (!targetStart) targetStart = 0
                if (end > 0 && end < start) end = start

                // Copy 0 bytes; we're done
                if (end === start) return 0
                if (target.length === 0 || this.length === 0) return 0

                // Fatal error conditions
                if (targetStart < 0) {
                    throw new RangeError('targetStart out of bounds')
                }
                if (start < 0 || start >= this.length) throw new RangeError('Index out of range')
                if (end < 0) throw new RangeError('sourceEnd out of bounds')

                // Are we oob?
                if (end > this.length) end = this.length
                if (target.length - targetStart < end - start) {
                    end = target.length - targetStart + start
                }

                var len = end - start

                if (this === target && typeof Uint8Array.prototype.copyWithin === 'function') {
                    // Use built-in when available, missing from IE11
                    this.copyWithin(targetStart, start, end)
                } else if (this === target && start < targetStart && targetStart < end) {
                    // descending copy from end
                    for (var i = len - 1; i >= 0; --i) {
                        target[i + targetStart] = this[i + start]
                    }
                } else {
                    Uint8Array.prototype.set.call(
                        target,
                        this.subarray(start, end),
                        targetStart
                    )
                }

                return len
            }

// Usage:
//    buffer.fill(number[, offset[, end]])
//    buffer.fill(buffer[, offset[, end]])
//    buffer.fill(string[, offset[, end]][, encoding])
            Buffer.prototype.fill = function fill (val, start, end, encoding) {
                // Handle string cases:
                if (typeof val === 'string') {
                    if (typeof start === 'string') {
                        encoding = start
                        start = 0
                        end = this.length
                    } else if (typeof end === 'string') {
                        encoding = end
                        end = this.length
                    }
                    if (encoding !== undefined && typeof encoding !== 'string') {
                        throw new TypeError('encoding must be a string')
                    }
                    if (typeof encoding === 'string' && !Buffer.isEncoding(encoding)) {
                        throw new TypeError('Unknown encoding: ' + encoding)
                    }
                    if (val.length === 1) {
                        var code = val.charCodeAt(0)
                        if ((encoding === 'utf8' && code < 128) ||
                            encoding === 'latin1') {
                            // Fast path: If `val` fits into a single byte, use that numeric value.
                            val = code
                        }
                    }
                } else if (typeof val === 'number') {
                    val = val & 255
                }

                // Invalid ranges are not set to a default, so can range check early.
                if (start < 0 || this.length < start || this.length < end) {
                    throw new RangeError('Out of range index')
                }

                if (end <= start) {
                    return this
                }

                start = start >>> 0
                end = end === undefined ? this.length : end >>> 0

                if (!val) val = 0

                var i
                if (typeof val === 'number') {
                    for (i = start; i < end; ++i) {
                        this[i] = val
                    }
                } else {
                    var bytes = Buffer.isBuffer(val)
                        ? val
                        : Buffer.from(val, encoding)
                    var len = bytes.length
                    if (len === 0) {
                        throw new TypeError('The value "' + val +
                            '" is invalid for argument "value"')
                    }
                    for (i = 0; i < end - start; ++i) {
                        this[i + start] = bytes[i % len]
                    }
                }

                return this
            }

// HELPER FUNCTIONS
// ================

            var INVALID_BASE64_RE = /[^+/0-9A-Za-z-_]/g

            function base64clean (str) {
                // Node takes equal signs as end of the Base64 encoding
                str = str.split('=')[0]
                // Node strips out invalid characters like \n and \t from the string, base64-js does not
                str = str.trim().replace(INVALID_BASE64_RE, '')
                // Node converts strings with length < 2 to ''
                if (str.length < 2) return ''
                // Node allows for non-padded base64 strings (missing trailing ===), base64-js does not
                while (str.length % 4 !== 0) {
                    str = str + '='
                }
                return str
            }

            function toHex (n) {
                if (n < 16) return '0' + n.toString(16)
                return n.toString(16)
            }

            function utf8ToBytes (string, units) {
                units = units || Infinity
                var codePoint
                var length = string.length
                var leadSurrogate = null
                var bytes = []

                for (var i = 0; i < length; ++i) {
                    codePoint = string.charCodeAt(i)

                    // is surrogate component
                    if (codePoint > 0xD7FF && codePoint < 0xE000) {
                        // last char was a lead
                        if (!leadSurrogate) {
                            // no lead yet
                            if (codePoint > 0xDBFF) {
                                // unexpected trail
                                if ((units -= 3) > -1) bytes.push(0xEF, 0xBF, 0xBD)
                                continue
                            } else if (i + 1 === length) {
                                // unpaired lead
                                if ((units -= 3) > -1) bytes.push(0xEF, 0xBF, 0xBD)
                                continue
                            }

                            // valid lead
                            leadSurrogate = codePoint

                            continue
                        }

                        // 2 leads in a row
                        if (codePoint < 0xDC00) {
                            if ((units -= 3) > -1) bytes.push(0xEF, 0xBF, 0xBD)
                            leadSurrogate = codePoint
                            continue
                        }

                        // valid surrogate pair
                        codePoint = (leadSurrogate - 0xD800 << 10 | codePoint - 0xDC00) + 0x10000
                    } else if (leadSurrogate) {
                        // valid bmp char, but last char was a lead
                        if ((units -= 3) > -1) bytes.push(0xEF, 0xBF, 0xBD)
                    }

                    leadSurrogate = null

                    // encode utf8
                    if (codePoint < 0x80) {
                        if ((units -= 1) < 0) break
                        bytes.push(codePoint)
                    } else if (codePoint < 0x800) {
                        if ((units -= 2) < 0) break
                        bytes.push(
                            codePoint >> 0x6 | 0xC0,
                            codePoint & 0x3F | 0x80
                        )
                    } else if (codePoint < 0x10000) {
                        if ((units -= 3) < 0) break
                        bytes.push(
                            codePoint >> 0xC | 0xE0,
                            codePoint >> 0x6 & 0x3F | 0x80,
                            codePoint & 0x3F | 0x80
                        )
                    } else if (codePoint < 0x110000) {
                        if ((units -= 4) < 0) break
                        bytes.push(
                            codePoint >> 0x12 | 0xF0,
                            codePoint >> 0xC & 0x3F | 0x80,
                            codePoint >> 0x6 & 0x3F | 0x80,
                            codePoint & 0x3F | 0x80
                        )
                    } else {
                        throw new Error('Invalid code point')
                    }
                }

                return bytes
            }

            function asciiToBytes (str) {
                var byteArray = []
                for (var i = 0; i < str.length; ++i) {
                    // Node's code seems to be doing this and not & 0x7F..
                    byteArray.push(str.charCodeAt(i) & 0xFF)
                }
                return byteArray
            }

            function utf16leToBytes (str, units) {
                var c, hi, lo
                var byteArray = []
                for (var i = 0; i < str.length; ++i) {
                    if ((units -= 2) < 0) break

                    c = str.charCodeAt(i)
                    hi = c >> 8
                    lo = c % 256
                    byteArray.push(lo)
                    byteArray.push(hi)
                }

                return byteArray
            }

            function base64ToBytes (str) {
                return base64.toByteArray(base64clean(str))
            }

            function blitBuffer (src, dst, offset, length) {
                for (var i = 0; i < length; ++i) {
                    if ((i + offset >= dst.length) || (i >= src.length)) break
                    dst[i + offset] = src[i]
                }
                return i
            }

// ArrayBuffer or Uint8Array objects from other contexts (i.e. iframes) do not pass
// the `instanceof` check but they should be treated as of that type.
// See: https://github.com/feross/buffer/issues/166
            function isInstance (obj, type) {
                return obj instanceof type ||
                    (obj != null && obj.constructor != null && obj.constructor.name != null &&
                        obj.constructor.name === type.name)
            }
            function numberIsNaN (obj) {
                // For IE11 support
                return obj !== obj // eslint-disable-line no-self-compare
            }

        }).call(this,require("buffer").Buffer)
    },{"base64-js":5,"buffer":8,"ieee754":30}],9:[function(require,module,exports){
        /**
         * Slice reference.
         */

        var slice = [].slice;

        /**
         * Bind `obj` to `fn`.
         *
         * @param {Object} obj
         * @param {Function|String} fn or string
         * @return {Function}
         * @api public
         */

        module.exports = function(obj, fn){
            if ('string' == typeof fn) fn = obj[fn];
            if ('function' != typeof fn) throw new Error('bind() requires a function');
            var args = slice.call(arguments, 2);
            return function(){
                return fn.apply(obj, args.concat(slice.call(arguments)));
            }
        };

    },{}],10:[function(require,module,exports){

        /**
         * Expose `Emitter`.
         */

        if (typeof module !== 'undefined') {
            module.exports = Emitter;
        }

        /**
         * Initialize a new `Emitter`.
         *
         * @api public
         */

        function Emitter(obj) {
            if (obj) return mixin(obj);
        };

        /**
         * Mixin the emitter properties.
         *
         * @param {Object} obj
         * @return {Object}
         * @api private
         */

        function mixin(obj) {
            for (var key in Emitter.prototype) {
                obj[key] = Emitter.prototype[key];
            }
            return obj;
        }

        /**
         * Listen on the given `event` with `fn`.
         *
         * @param {String} event
         * @param {Function} fn
         * @return {Emitter}
         * @api public
         */

        Emitter.prototype.on =
            Emitter.prototype.addEventListener = function(event, fn){
                this._callbacks = this._callbacks || {};
                (this._callbacks['$' + event] = this._callbacks['$' + event] || [])
                    .push(fn);
                return this;
            };

        /**
         * Adds an `event` listener that will be invoked a single
         * time then automatically removed.
         *
         * @param {String} event
         * @param {Function} fn
         * @return {Emitter}
         * @api public
         */

        Emitter.prototype.once = function(event, fn){
            function on() {
                this.off(event, on);
                fn.apply(this, arguments);
            }

            on.fn = fn;
            this.on(event, on);
            return this;
        };

        /**
         * Remove the given callback for `event` or all
         * registered callbacks.
         *
         * @param {String} event
         * @param {Function} fn
         * @return {Emitter}
         * @api public
         */

        Emitter.prototype.off =
            Emitter.prototype.removeListener =
                Emitter.prototype.removeAllListeners =
                    Emitter.prototype.removeEventListener = function(event, fn){
                        this._callbacks = this._callbacks || {};

                        // all
                        if (0 == arguments.length) {
                            this._callbacks = {};
                            return this;
                        }

                        // specific event
                        var callbacks = this._callbacks['$' + event];
                        if (!callbacks) return this;

                        // remove all handlers
                        if (1 == arguments.length) {
                            delete this._callbacks['$' + event];
                            return this;
                        }

                        // remove specific handler
                        var cb;
                        for (var i = 0; i < callbacks.length; i++) {
                            cb = callbacks[i];
                            if (cb === fn || cb.fn === fn) {
                                callbacks.splice(i, 1);
                                break;
                            }
                        }

                        // Remove event specific arrays for event types that no
                        // one is subscribed for to avoid memory leak.
                        if (callbacks.length === 0) {
                            delete this._callbacks['$' + event];
                        }

                        return this;
                    };

        /**
         * Emit `event` with the given args.
         *
         * @param {String} event
         * @param {Mixed} ...
         * @return {Emitter}
         */

        Emitter.prototype.emit = function(event){
            this._callbacks = this._callbacks || {};

            var args = new Array(arguments.length - 1)
                , callbacks = this._callbacks['$' + event];

            for (var i = 1; i < arguments.length; i++) {
                args[i - 1] = arguments[i];
            }

            if (callbacks) {
                callbacks = callbacks.slice(0);
                for (var i = 0, len = callbacks.length; i < len; ++i) {
                    callbacks[i].apply(this, args);
                }
            }

            return this;
        };

        /**
         * Return array of callbacks for `event`.
         *
         * @param {String} event
         * @return {Array}
         * @api public
         */

        Emitter.prototype.listeners = function(event){
            this._callbacks = this._callbacks || {};
            return this._callbacks['$' + event] || [];
        };

        /**
         * Check if this emitter has `event` handlers.
         *
         * @param {String} event
         * @return {Boolean}
         * @api public
         */

        Emitter.prototype.hasListeners = function(event){
            return !! this.listeners(event).length;
        };

    },{}],11:[function(require,module,exports){

        module.exports = function(a, b){
            var fn = function(){};
            fn.prototype = b.prototype;
            a.prototype = new fn;
            a.prototype.constructor = a;
        };
    },{}],12:[function(require,module,exports){
        module.exports = (function () {
            if (typeof self !== 'undefined') {
                return self;
            } else if (typeof window !== 'undefined') {
                return window;
            } else {
                return Function('return this')(); // eslint-disable-line no-new-func
            }
        })();

    },{}],13:[function(require,module,exports){

        module.exports = require('./socket');

        /**
         * Exports parser
         *
         * @api public
         *
         */
        module.exports.parser = require('engine.io-parser');

    },{"./socket":14,"engine.io-parser":24}],14:[function(require,module,exports){
        /**
         * Module dependencies.
         */

        var transports = require('./transports/index');
        var Emitter = require('component-emitter');
        var debug = require('debug')('engine.io-client:socket');
        var index = require('indexof');
        var parser = require('engine.io-parser');
        var parseuri = require('parseuri');
        var parseqs = require('parseqs');

        /**
         * Module exports.
         */

        module.exports = Socket;

        /**
         * Socket constructor.
         *
         * @param {String|Object} uri or options
         * @param {Object} options
         * @api public
         */

        function Socket (uri, opts) {
            if (!(this instanceof Socket)) return new Socket(uri, opts);

            opts = opts || {};

            if (uri && 'object' === typeof uri) {
                opts = uri;
                uri = null;
            }

            if (uri) {
                uri = parseuri(uri);
                opts.hostname = uri.host;
                opts.secure = uri.protocol === 'https' || uri.protocol === 'wss';
                opts.port = uri.port;
                if (uri.query) opts.query = uri.query;
            } else if (opts.host) {
                opts.hostname = parseuri(opts.host).host;
            }

            this.secure = null != opts.secure ? opts.secure
                : (typeof location !== 'undefined' && 'https:' === location.protocol);

            if (opts.hostname && !opts.port) {
                // if no port is specified manually, use the protocol default
                opts.port = this.secure ? '443' : '80';
            }

            this.agent = opts.agent || false;
            this.hostname = opts.hostname ||
                (typeof location !== 'undefined' ? location.hostname : 'localhost');
            this.port = opts.port || (typeof location !== 'undefined' && location.port
                ? location.port
                : (this.secure ? 443 : 80));
            this.query = opts.query || {};
            if ('string' === typeof this.query) this.query = parseqs.decode(this.query);
            this.upgrade = false !== opts.upgrade;
            this.path = (opts.path || '/engine.io').replace(/\/$/, '') + '/';
            this.forceJSONP = !!opts.forceJSONP;
            this.jsonp = false !== opts.jsonp;
            this.forceBase64 = !!opts.forceBase64;
            this.enablesXDR = !!opts.enablesXDR;
            this.withCredentials = false !== opts.withCredentials;
            this.timestampParam = opts.timestampParam || 't';
            this.timestampRequests = opts.timestampRequests;
            this.transports = opts.transports || ['polling', 'websocket'];
            this.transportOptions = opts.transportOptions || {};
            this.readyState = '';
            this.writeBuffer = [];
            this.prevBufferLen = 0;
            this.policyPort = opts.policyPort || 843;
            this.rememberUpgrade = opts.rememberUpgrade || false;
            this.binaryType = null;
            this.onlyBinaryUpgrades = opts.onlyBinaryUpgrades;
            this.perMessageDeflate = false !== opts.perMessageDeflate ? (opts.perMessageDeflate || {}) : false;

            if (true === this.perMessageDeflate) this.perMessageDeflate = {};
            if (this.perMessageDeflate && null == this.perMessageDeflate.threshold) {
                this.perMessageDeflate.threshold = 1024;
            }

            // SSL options for Node.js client
            this.pfx = opts.pfx || null;
            this.key = opts.key || null;
            this.passphrase = opts.passphrase || null;
            this.cert = opts.cert || null;
            this.ca = opts.ca || null;
            this.ciphers = opts.ciphers || null;
            this.rejectUnauthorized = opts.rejectUnauthorized === undefined ? true : opts.rejectUnauthorized;
            this.forceNode = !!opts.forceNode;

            // detect ReactNative environment
            this.isReactNative = (typeof navigator !== 'undefined' && typeof navigator.product === 'string' && navigator.product.toLowerCase() === 'reactnative');

            // other options for Node.js or ReactNative client
            if (typeof self === 'undefined' || this.isReactNative) {
                if (opts.extraHeaders && Object.keys(opts.extraHeaders).length > 0) {
                    this.extraHeaders = opts.extraHeaders;
                }

                if (opts.localAddress) {
                    this.localAddress = opts.localAddress;
                }
            }

            // set on handshake
            this.id = null;
            this.upgrades = null;
            this.pingInterval = null;
            this.pingTimeout = null;

            // set on heartbeat
            this.pingIntervalTimer = null;
            this.pingTimeoutTimer = null;

            this.open();
        }

        Socket.priorWebsocketSuccess = false;

        /**
         * Mix in `Emitter`.
         */

        Emitter(Socket.prototype);

        /**
         * Protocol version.
         *
         * @api public
         */

        Socket.protocol = parser.protocol; // this is an int

        /**
         * Expose deps for legacy compatibility
         * and standalone browser access.
         */

        Socket.Socket = Socket;
        Socket.Transport = require('./transport');
        Socket.transports = require('./transports/index');
        Socket.parser = require('engine.io-parser');

        /**
         * Creates transport of the given type.
         *
         * @param {String} transport name
         * @return {Transport}
         * @api private
         */

        Socket.prototype.createTransport = function (name) {
            debug('creating transport "%s"', name);
            var query = clone(this.query);

            // append engine.io protocol identifier
            query.EIO = parser.protocol;

            // transport name
            query.transport = name;

            // per-transport options
            var options = this.transportOptions[name] || {};

            // session id if we already have one
            if (this.id) query.sid = this.id;

            var transport = new transports[name]({
                query: query,
                socket: this,
                agent: options.agent || this.agent,
                hostname: options.hostname || this.hostname,
                port: options.port || this.port,
                secure: options.secure || this.secure,
                path: options.path || this.path,
                forceJSONP: options.forceJSONP || this.forceJSONP,
                jsonp: options.jsonp || this.jsonp,
                forceBase64: options.forceBase64 || this.forceBase64,
                enablesXDR: options.enablesXDR || this.enablesXDR,
                withCredentials: options.withCredentials || this.withCredentials,
                timestampRequests: options.timestampRequests || this.timestampRequests,
                timestampParam: options.timestampParam || this.timestampParam,
                policyPort: options.policyPort || this.policyPort,
                pfx: options.pfx || this.pfx,
                key: options.key || this.key,
                passphrase: options.passphrase || this.passphrase,
                cert: options.cert || this.cert,
                ca: options.ca || this.ca,
                ciphers: options.ciphers || this.ciphers,
                rejectUnauthorized: options.rejectUnauthorized || this.rejectUnauthorized,
                perMessageDeflate: options.perMessageDeflate || this.perMessageDeflate,
                extraHeaders: options.extraHeaders || this.extraHeaders,
                forceNode: options.forceNode || this.forceNode,
                localAddress: options.localAddress || this.localAddress,
                requestTimeout: options.requestTimeout || this.requestTimeout,
                protocols: options.protocols || void (0),
                isReactNative: this.isReactNative
            });

            return transport;
        };

        function clone (obj) {
            var o = {};
            for (var i in obj) {
                if (obj.hasOwnProperty(i)) {
                    o[i] = obj[i];
                }
            }
            return o;
        }

        /**
         * Initializes transport to use and starts probe.
         *
         * @api private
         */
        Socket.prototype.open = function () {
            var transport;
            if (this.rememberUpgrade && Socket.priorWebsocketSuccess && this.transports.indexOf('websocket') !== -1) {
                transport = 'websocket';
            } else if (0 === this.transports.length) {
                // Emit error on next tick so it can be listened to
                var self = this;
                setTimeout(function () {
                    self.emit('error', 'No transports available');
                }, 0);
                return;
            } else {
                transport = this.transports[0];
            }
            this.readyState = 'opening';

            // Retry with the next transport if the transport is disabled (jsonp: false)
            try {
                transport = this.createTransport(transport);
            } catch (e) {
                this.transports.shift();
                this.open();
                return;
            }

            transport.open();
            this.setTransport(transport);
        };

        /**
         * Sets the current transport. Disables the existing one (if any).
         *
         * @api private
         */

        Socket.prototype.setTransport = function (transport) {
            debug('setting transport %s', transport.name);
            var self = this;

            if (this.transport) {
                debug('clearing existing transport %s', this.transport.name);
                this.transport.removeAllListeners();
            }

            // set up transport
            this.transport = transport;

            // set up transport listeners
            transport
                .on('drain', function () {
                    self.onDrain();
                })
                .on('packet', function (packet) {
                    self.onPacket(packet);
                })
                .on('error', function (e) {
                    self.onError(e);
                })
                .on('close', function () {
                    self.onClose('transport close');
                });
        };

        /**
         * Probes a transport.
         *
         * @param {String} transport name
         * @api private
         */

        Socket.prototype.probe = function (name) {
            debug('probing transport "%s"', name);
            var transport = this.createTransport(name, { probe: 1 });
            var failed = false;
            var self = this;

            Socket.priorWebsocketSuccess = false;

            function onTransportOpen () {
                if (self.onlyBinaryUpgrades) {
                    var upgradeLosesBinary = !this.supportsBinary && self.transport.supportsBinary;
                    failed = failed || upgradeLosesBinary;
                }
                if (failed) return;

                debug('probe transport "%s" opened', name);
                transport.send([{ type: 'ping', data: 'probe' }]);
                transport.once('packet', function (msg) {
                    if (failed) return;
                    if ('pong' === msg.type && 'probe' === msg.data) {
                        debug('probe transport "%s" pong', name);
                        self.upgrading = true;
                        self.emit('upgrading', transport);
                        if (!transport) return;
                        Socket.priorWebsocketSuccess = 'websocket' === transport.name;

                        debug('pausing current transport "%s"', self.transport.name);
                        self.transport.pause(function () {
                            if (failed) return;
                            if ('closed' === self.readyState) return;
                            debug('changing transport and sending upgrade packet');

                            cleanup();

                            self.setTransport(transport);
                            transport.send([{ type: 'upgrade' }]);
                            self.emit('upgrade', transport);
                            transport = null;
                            self.upgrading = false;
                            self.flush();
                        });
                    } else {
                        debug('probe transport "%s" failed', name);
                        var err = new Error('probe error');
                        err.transport = transport.name;
                        self.emit('upgradeError', err);
                    }
                });
            }

            function freezeTransport () {
                if (failed) return;

                // Any callback called by transport should be ignored since now
                failed = true;

                cleanup();

                transport.close();
                transport = null;
            }

            // Handle any error that happens while probing
            function onerror (err) {
                var error = new Error('probe error: ' + err);
                error.transport = transport.name;

                freezeTransport();

                debug('probe transport "%s" failed because of error: %s', name, err);

                self.emit('upgradeError', error);
            }

            function onTransportClose () {
                onerror('transport closed');
            }

            // When the socket is closed while we're probing
            function onclose () {
                onerror('socket closed');
            }

            // When the socket is upgraded while we're probing
            function onupgrade (to) {
                if (transport && to.name !== transport.name) {
                    debug('"%s" works - aborting "%s"', to.name, transport.name);
                    freezeTransport();
                }
            }

            // Remove all listeners on the transport and on self
            function cleanup () {
                transport.removeListener('open', onTransportOpen);
                transport.removeListener('error', onerror);
                transport.removeListener('close', onTransportClose);
                self.removeListener('close', onclose);
                self.removeListener('upgrading', onupgrade);
            }

            transport.once('open', onTransportOpen);
            transport.once('error', onerror);
            transport.once('close', onTransportClose);

            this.once('close', onclose);
            this.once('upgrading', onupgrade);

            transport.open();
        };

        /**
         * Called when connection is deemed open.
         *
         * @api public
         */

        Socket.prototype.onOpen = function () {
            debug('socket open');
            this.readyState = 'open';
            Socket.priorWebsocketSuccess = 'websocket' === this.transport.name;
            this.emit('open');
            this.flush();

            // we check for `readyState` in case an `open`
            // listener already closed the socket
            if ('open' === this.readyState && this.upgrade && this.transport.pause) {
                debug('starting upgrade probes');
                for (var i = 0, l = this.upgrades.length; i < l; i++) {
                    this.probe(this.upgrades[i]);
                }
            }
        };

        /**
         * Handles a packet.
         *
         * @api private
         */

        Socket.prototype.onPacket = function (packet) {
            if ('opening' === this.readyState || 'open' === this.readyState ||
                'closing' === this.readyState) {
                debug('socket receive: type "%s", data "%s"', packet.type, packet.data);

                this.emit('packet', packet);

                // Socket is live - any packet counts
                this.emit('heartbeat');

                switch (packet.type) {
                    case 'open':
                        this.onHandshake(JSON.parse(packet.data));
                        break;

                    case 'pong':
                        this.setPing();
                        this.emit('pong');
                        break;

                    case 'error':
                        var err = new Error('server error');
                        err.code = packet.data;
                        this.onError(err);
                        break;

                    case 'message':
                        this.emit('data', packet.data);
                        this.emit('message', packet.data);
                        break;
                }
            } else {
                debug('packet received with socket readyState "%s"', this.readyState);
            }
        };

        /**
         * Called upon handshake completion.
         *
         * @param {Object} handshake obj
         * @api private
         */

        Socket.prototype.onHandshake = function (data) {
            this.emit('handshake', data);
            this.id = data.sid;
            this.transport.query.sid = data.sid;
            this.upgrades = this.filterUpgrades(data.upgrades);
            this.pingInterval = data.pingInterval;
            this.pingTimeout = data.pingTimeout;
            this.onOpen();
            // In case open handler closes socket
            if ('closed' === this.readyState) return;
            this.setPing();

            // Prolong liveness of socket on heartbeat
            this.removeListener('heartbeat', this.onHeartbeat);
            this.on('heartbeat', this.onHeartbeat);
        };

        /**
         * Resets ping timeout.
         *
         * @api private
         */

        Socket.prototype.onHeartbeat = function (timeout) {
            clearTimeout(this.pingTimeoutTimer);
            var self = this;
            self.pingTimeoutTimer = setTimeout(function () {
                if ('closed' === self.readyState) return;
                self.onClose('ping timeout');
            }, timeout || (self.pingInterval + self.pingTimeout));
        };

        /**
         * Pings server every `this.pingInterval` and expects response
         * within `this.pingTimeout` or closes connection.
         *
         * @api private
         */

        Socket.prototype.setPing = function () {
            var self = this;
            clearTimeout(self.pingIntervalTimer);
            self.pingIntervalTimer = setTimeout(function () {
                debug('writing ping packet - expecting pong within %sms', self.pingTimeout);
                self.ping();
                self.onHeartbeat(self.pingTimeout);
            }, self.pingInterval);
        };

        /**
         * Sends a ping packet.
         *
         * @api private
         */

        Socket.prototype.ping = function () {
            var self = this;
            this.sendPacket('ping', function () {
                self.emit('ping');
            });
        };

        /**
         * Called on `drain` event
         *
         * @api private
         */

        Socket.prototype.onDrain = function () {
            this.writeBuffer.splice(0, this.prevBufferLen);

            // setting prevBufferLen = 0 is very important
            // for example, when upgrading, upgrade packet is sent over,
            // and a nonzero prevBufferLen could cause problems on `drain`
            this.prevBufferLen = 0;

            if (0 === this.writeBuffer.length) {
                this.emit('drain');
            } else {
                this.flush();
            }
        };

        /**
         * Flush write buffers.
         *
         * @api private
         */

        Socket.prototype.flush = function () {
            if ('closed' !== this.readyState && this.transport.writable &&
                !this.upgrading && this.writeBuffer.length) {
                debug('flushing %d packets in socket', this.writeBuffer.length);
                this.transport.send(this.writeBuffer);
                // keep track of current length of writeBuffer
                // splice writeBuffer and callbackBuffer on `drain`
                this.prevBufferLen = this.writeBuffer.length;
                this.emit('flush');
            }
        };

        /**
         * Sends a message.
         *
         * @param {String} message.
         * @param {Function} callback function.
         * @param {Object} options.
         * @return {Socket} for chaining.
         * @api public
         */

        Socket.prototype.write =
            Socket.prototype.send = function (msg, options, fn) {
                this.sendPacket('message', msg, options, fn);
                return this;
            };

        /**
         * Sends a packet.
         *
         * @param {String} packet type.
         * @param {String} data.
         * @param {Object} options.
         * @param {Function} callback function.
         * @api private
         */

        Socket.prototype.sendPacket = function (type, data, options, fn) {
            if ('function' === typeof data) {
                fn = data;
                data = undefined;
            }

            if ('function' === typeof options) {
                fn = options;
                options = null;
            }

            if ('closing' === this.readyState || 'closed' === this.readyState) {
                return;
            }

            options = options || {};
            options.compress = false !== options.compress;

            var packet = {
                type: type,
                data: data,
                options: options
            };
            this.emit('packetCreate', packet);
            this.writeBuffer.push(packet);
            if (fn) this.once('flush', fn);
            this.flush();
        };

        /**
         * Closes the connection.
         *
         * @api private
         */

        Socket.prototype.close = function () {
            if ('opening' === this.readyState || 'open' === this.readyState) {
                this.readyState = 'closing';

                var self = this;

                if (this.writeBuffer.length) {
                    this.once('drain', function () {
                        if (this.upgrading) {
                            waitForUpgrade();
                        } else {
                            close();
                        }
                    });
                } else if (this.upgrading) {
                    waitForUpgrade();
                } else {
                    close();
                }
            }

            function close () {
                self.onClose('forced close');
                debug('socket closing - telling transport to close');
                self.transport.close();
            }

            function cleanupAndClose () {
                self.removeListener('upgrade', cleanupAndClose);
                self.removeListener('upgradeError', cleanupAndClose);
                close();
            }

            function waitForUpgrade () {
                // wait for upgrade to finish since we can't send packets while pausing a transport
                self.once('upgrade', cleanupAndClose);
                self.once('upgradeError', cleanupAndClose);
            }

            return this;
        };

        /**
         * Called upon transport error
         *
         * @api private
         */

        Socket.prototype.onError = function (err) {
            debug('socket error %j', err);
            Socket.priorWebsocketSuccess = false;
            this.emit('error', err);
            this.onClose('transport error', err);
        };

        /**
         * Called upon transport close.
         *
         * @api private
         */

        Socket.prototype.onClose = function (reason, desc) {
            if ('opening' === this.readyState || 'open' === this.readyState || 'closing' === this.readyState) {
                debug('socket close with reason: "%s"', reason);
                var self = this;

                // clear timers
                clearTimeout(this.pingIntervalTimer);
                clearTimeout(this.pingTimeoutTimer);

                // stop event from firing again for transport
                this.transport.removeAllListeners('close');

                // ensure transport won't stay open
                this.transport.close();

                // ignore further transport communication
                this.transport.removeAllListeners();

                // set ready state
                this.readyState = 'closed';

                // clear session id
                this.id = null;

                // emit close event
                this.emit('close', reason, desc);

                // clean buffers after, so users can still
                // grab the buffers on `close` event
                self.writeBuffer = [];
                self.prevBufferLen = 0;
            }
        };

        /**
         * Filters upgrades, returning only those matching client transports.
         *
         * @param {Array} server upgrades
         * @api private
         *
         */

        Socket.prototype.filterUpgrades = function (upgrades) {
            var filteredUpgrades = [];
            for (var i = 0, j = upgrades.length; i < j; i++) {
                if (~index(this.transports, upgrades[i])) filteredUpgrades.push(upgrades[i]);
            }
            return filteredUpgrades;
        };

    },{"./transport":15,"./transports/index":16,"component-emitter":10,"debug":22,"engine.io-parser":24,"indexof":31,"parseqs":33,"parseuri":34}],15:[function(require,module,exports){
        /**
         * Module dependencies.
         */

        var parser = require('engine.io-parser');
        var Emitter = require('component-emitter');

        /**
         * Module exports.
         */

        module.exports = Transport;

        /**
         * Transport abstract constructor.
         *
         * @param {Object} options.
         * @api private
         */

        function Transport (opts) {
            this.path = opts.path;
            this.hostname = opts.hostname;
            this.port = opts.port;
            this.secure = opts.secure;
            this.query = opts.query;
            this.timestampParam = opts.timestampParam;
            this.timestampRequests = opts.timestampRequests;
            this.readyState = '';
            this.agent = opts.agent || false;
            this.socket = opts.socket;
            this.enablesXDR = opts.enablesXDR;
            this.withCredentials = opts.withCredentials;

            // SSL options for Node.js client
            this.pfx = opts.pfx;
            this.key = opts.key;
            this.passphrase = opts.passphrase;
            this.cert = opts.cert;
            this.ca = opts.ca;
            this.ciphers = opts.ciphers;
            this.rejectUnauthorized = opts.rejectUnauthorized;
            this.forceNode = opts.forceNode;

            // results of ReactNative environment detection
            this.isReactNative = opts.isReactNative;

            // other options for Node.js client
            this.extraHeaders = opts.extraHeaders;
            this.localAddress = opts.localAddress;
        }

        /**
         * Mix in `Emitter`.
         */

        Emitter(Transport.prototype);

        /**
         * Emits an error.
         *
         * @param {String} str
         * @return {Transport} for chaining
         * @api public
         */

        Transport.prototype.onError = function (msg, desc) {
            var err = new Error(msg);
            err.type = 'TransportError';
            err.description = desc;
            this.emit('error', err);
            return this;
        };

        /**
         * Opens the transport.
         *
         * @api public
         */

        Transport.prototype.open = function () {
            if ('closed' === this.readyState || '' === this.readyState) {
                this.readyState = 'opening';
                this.doOpen();
            }

            return this;
        };

        /**
         * Closes the transport.
         *
         * @api private
         */

        Transport.prototype.close = function () {
            if ('opening' === this.readyState || 'open' === this.readyState) {
                this.doClose();
                this.onClose();
            }

            return this;
        };

        /**
         * Sends multiple packets.
         *
         * @param {Array} packets
         * @api private
         */

        Transport.prototype.send = function (packets) {
            if ('open' === this.readyState) {
                this.write(packets);
            } else {
                throw new Error('Transport not open');
            }
        };

        /**
         * Called upon open
         *
         * @api private
         */

        Transport.prototype.onOpen = function () {
            this.readyState = 'open';
            this.writable = true;
            this.emit('open');
        };

        /**
         * Called with data.
         *
         * @param {String} data
         * @api private
         */

        Transport.prototype.onData = function (data) {
            var packet = parser.decodePacket(data, this.socket.binaryType);
            this.onPacket(packet);
        };

        /**
         * Called with a decoded packet.
         */

        Transport.prototype.onPacket = function (packet) {
            this.emit('packet', packet);
        };

        /**
         * Called upon close.
         *
         * @api private
         */

        Transport.prototype.onClose = function () {
            this.readyState = 'closed';
            this.emit('close');
        };

    },{"component-emitter":10,"engine.io-parser":24}],16:[function(require,module,exports){
        /**
         * Module dependencies
         */

        var XMLHttpRequest = require('xmlhttprequest-ssl');
        var XHR = require('./polling-xhr');
        var JSONP = require('./polling-jsonp');
        var websocket = require('./websocket');

        /**
         * Export transports.
         */

        exports.polling = polling;
        exports.websocket = websocket;

        /**
         * Polling transport polymorphic constructor.
         * Decides on xhr vs jsonp based on feature detection.
         *
         * @api private
         */

        function polling (opts) {
            var xhr;
            var xd = false;
            var xs = false;
            var jsonp = false !== opts.jsonp;

            if (typeof location !== 'undefined') {
                var isSSL = 'https:' === location.protocol;
                var port = location.port;

                // some user agents have empty `location.port`
                if (!port) {
                    port = isSSL ? 443 : 80;
                }

                xd = opts.hostname !== location.hostname || port !== opts.port;
                xs = opts.secure !== isSSL;
            }

            opts.xdomain = xd;
            opts.xscheme = xs;
            xhr = new XMLHttpRequest(opts);

            if ('open' in xhr && !opts.forceJSONP) {
                return new XHR(opts);
            } else {
                if (!jsonp) throw new Error('JSONP disabled');
                return new JSONP(opts);
            }
        }

    },{"./polling-jsonp":17,"./polling-xhr":18,"./websocket":20,"xmlhttprequest-ssl":21}],17:[function(require,module,exports){
        /**
         * Module requirements.
         */

        var Polling = require('./polling');
        var inherit = require('component-inherit');
        var globalThis = require('../globalThis');

        /**
         * Module exports.
         */

        module.exports = JSONPPolling;

        /**
         * Cached regular expressions.
         */

        var rNewline = /\n/g;
        var rEscapedNewline = /\\n/g;

        /**
         * Global JSONP callbacks.
         */

        var callbacks;

        /**
         * Noop.
         */

        function empty () { }

        /**
         * JSONP Polling constructor.
         *
         * @param {Object} opts.
         * @api public
         */

        function JSONPPolling (opts) {
            Polling.call(this, opts);

            this.query = this.query || {};

            // define global callbacks array if not present
            // we do this here (lazily) to avoid unneeded global pollution
            if (!callbacks) {
                // we need to consider multiple engines in the same page
                callbacks = globalThis.___eio = (globalThis.___eio || []);
            }

            // callback identifier
            this.index = callbacks.length;

            // add callback to jsonp global
            var self = this;
            callbacks.push(function (msg) {
                self.onData(msg);
            });

            // append to query string
            this.query.j = this.index;

            // prevent spurious errors from being emitted when the window is unloaded
            if (typeof addEventListener === 'function') {
                addEventListener('beforeunload', function () {
                    if (self.script) self.script.onerror = empty;
                }, false);
            }
        }

        /**
         * Inherits from Polling.
         */

        inherit(JSONPPolling, Polling);

        /*
 * JSONP only supports binary as base64 encoded strings
 */

        JSONPPolling.prototype.supportsBinary = false;

        /**
         * Closes the socket.
         *
         * @api private
         */

        JSONPPolling.prototype.doClose = function () {
            if (this.script) {
                this.script.parentNode.removeChild(this.script);
                this.script = null;
            }

            if (this.form) {
                this.form.parentNode.removeChild(this.form);
                this.form = null;
                this.iframe = null;
            }

            Polling.prototype.doClose.call(this);
        };

        /**
         * Starts a poll cycle.
         *
         * @api private
         */

        JSONPPolling.prototype.doPoll = function () {
            var self = this;
            var script = document.createElement('script');

            if (this.script) {
                this.script.parentNode.removeChild(this.script);
                this.script = null;
            }

            script.async = true;
            script.src = this.uri();
            script.onerror = function (e) {
                self.onError('jsonp poll error', e);
            };

            var insertAt = document.getElementsByTagName('script')[0];
            if (insertAt) {
                insertAt.parentNode.insertBefore(script, insertAt);
            } else {
                (document.head || document.body).appendChild(script);
            }
            this.script = script;

            var isUAgecko = 'undefined' !== typeof navigator && /gecko/i.test(navigator.userAgent);

            if (isUAgecko) {
                setTimeout(function () {
                    var iframe = document.createElement('iframe');
                    document.body.appendChild(iframe);
                    document.body.removeChild(iframe);
                }, 100);
            }
        };

        /**
         * Writes with a hidden iframe.
         *
         * @param {String} data to send
         * @param {Function} called upon flush.
         * @api private
         */

        JSONPPolling.prototype.doWrite = function (data, fn) {
            var self = this;

            if (!this.form) {
                var form = document.createElement('form');
                var area = document.createElement('textarea');
                var id = this.iframeId = 'eio_iframe_' + this.index;
                var iframe;

                form.className = 'socketio';
                form.style.position = 'absolute';
                form.style.top = '-1000px';
                form.style.left = '-1000px';
                form.target = id;
                form.method = 'POST';
                form.setAttribute('accept-charset', 'utf-8');
                area.name = 'd';
                form.appendChild(area);
                document.body.appendChild(form);

                this.form = form;
                this.area = area;
            }

            this.form.action = this.uri();

            function complete () {
                initIframe();
                fn();
            }

            function initIframe () {
                if (self.iframe) {
                    try {
                        self.form.removeChild(self.iframe);
                    } catch (e) {
                        self.onError('jsonp polling iframe removal error', e);
                    }
                }

                try {
                    // ie6 dynamic iframes with target="" support (thanks Chris Lambacher)
                    var html = '<iframe src="javascript:0" name="' + self.iframeId + '">';
                    iframe = document.createElement(html);
                } catch (e) {
                    iframe = document.createElement('iframe');
                    iframe.name = self.iframeId;
                    iframe.src = 'javascript:0';
                }

                iframe.id = self.iframeId;

                self.form.appendChild(iframe);
                self.iframe = iframe;
            }

            initIframe();

            // escape \n to prevent it from being converted into \r\n by some UAs
            // double escaping is required for escaped new lines because unescaping of new lines can be done safely on server-side
            data = data.replace(rEscapedNewline, '\\\n');
            this.area.value = data.replace(rNewline, '\\n');

            try {
                this.form.submit();
            } catch (e) {}

            if (this.iframe.attachEvent) {
                this.iframe.onreadystatechange = function () {
                    if (self.iframe.readyState === 'complete') {
                        complete();
                    }
                };
            } else {
                this.iframe.onload = complete;
            }
        };

    },{"../globalThis":12,"./polling":19,"component-inherit":11}],18:[function(require,module,exports){
        /* global attachEvent */

        /**
         * Module requirements.
         */

        var XMLHttpRequest = require('xmlhttprequest-ssl');
        var Polling = require('./polling');
        var Emitter = require('component-emitter');
        var inherit = require('component-inherit');
        var debug = require('debug')('engine.io-client:polling-xhr');
        var globalThis = require('../globalThis');

        /**
         * Module exports.
         */

        module.exports = XHR;
        module.exports.Request = Request;

        /**
         * Empty function
         */

        function empty () {}

        /**
         * XHR Polling constructor.
         *
         * @param {Object} opts
         * @api public
         */

        function XHR (opts) {
            Polling.call(this, opts);
            this.requestTimeout = opts.requestTimeout;
            this.extraHeaders = opts.extraHeaders;

            if (typeof location !== 'undefined') {
                var isSSL = 'https:' === location.protocol;
                var port = location.port;

                // some user agents have empty `location.port`
                if (!port) {
                    port = isSSL ? 443 : 80;
                }

                this.xd = (typeof location !== 'undefined' && opts.hostname !== location.hostname) ||
                    port !== opts.port;
                this.xs = opts.secure !== isSSL;
            }
        }

        /**
         * Inherits from Polling.
         */

        inherit(XHR, Polling);

        /**
         * XHR supports binary
         */

        XHR.prototype.supportsBinary = true;

        /**
         * Creates a request.
         *
         * @param {String} method
         * @api private
         */

        XHR.prototype.request = function (opts) {
            opts = opts || {};
            opts.uri = this.uri();
            opts.xd = this.xd;
            opts.xs = this.xs;
            opts.agent = this.agent || false;
            opts.supportsBinary = this.supportsBinary;
            opts.enablesXDR = this.enablesXDR;
            opts.withCredentials = this.withCredentials;

            // SSL options for Node.js client
            opts.pfx = this.pfx;
            opts.key = this.key;
            opts.passphrase = this.passphrase;
            opts.cert = this.cert;
            opts.ca = this.ca;
            opts.ciphers = this.ciphers;
            opts.rejectUnauthorized = this.rejectUnauthorized;
            opts.requestTimeout = this.requestTimeout;

            // other options for Node.js client
            opts.extraHeaders = this.extraHeaders;

            return new Request(opts);
        };

        /**
         * Sends data.
         *
         * @param {String} data to send.
         * @param {Function} called upon flush.
         * @api private
         */

        XHR.prototype.doWrite = function (data, fn) {
            var isBinary = typeof data !== 'string' && data !== undefined;
            var req = this.request({ method: 'POST', data: data, isBinary: isBinary });
            var self = this;
            req.on('success', fn);
            req.on('error', function (err) {
                self.onError('xhr post error', err);
            });
            this.sendXhr = req;
        };

        /**
         * Starts a poll cycle.
         *
         * @api private
         */

        XHR.prototype.doPoll = function () {
            debug('xhr poll');
            var req = this.request();
            var self = this;
            req.on('data', function (data) {
                self.onData(data);
            });
            req.on('error', function (err) {
                self.onError('xhr poll error', err);
            });
            this.pollXhr = req;
        };

        /**
         * Request constructor
         *
         * @param {Object} options
         * @api public
         */

        function Request (opts) {
            this.method = opts.method || 'GET';
            this.uri = opts.uri;
            this.xd = !!opts.xd;
            this.xs = !!opts.xs;
            this.async = false !== opts.async;
            this.data = undefined !== opts.data ? opts.data : null;
            this.agent = opts.agent;
            this.isBinary = opts.isBinary;
            this.supportsBinary = opts.supportsBinary;
            this.enablesXDR = opts.enablesXDR;
            this.withCredentials = opts.withCredentials;
            this.requestTimeout = opts.requestTimeout;

            // SSL options for Node.js client
            this.pfx = opts.pfx;
            this.key = opts.key;
            this.passphrase = opts.passphrase;
            this.cert = opts.cert;
            this.ca = opts.ca;
            this.ciphers = opts.ciphers;
            this.rejectUnauthorized = opts.rejectUnauthorized;

            // other options for Node.js client
            this.extraHeaders = opts.extraHeaders;

            this.create();
        }

        /**
         * Mix in `Emitter`.
         */

        Emitter(Request.prototype);

        /**
         * Creates the XHR object and sends the request.
         *
         * @api private
         */

        Request.prototype.create = function () {
            var opts = { agent: this.agent, xdomain: this.xd, xscheme: this.xs, enablesXDR: this.enablesXDR };

            // SSL options for Node.js client
            opts.pfx = this.pfx;
            opts.key = this.key;
            opts.passphrase = this.passphrase;
            opts.cert = this.cert;
            opts.ca = this.ca;
            opts.ciphers = this.ciphers;
            opts.rejectUnauthorized = this.rejectUnauthorized;

            var xhr = this.xhr = new XMLHttpRequest(opts);
            var self = this;

            try {
                debug('xhr open %s: %s', this.method, this.uri);
                xhr.open(this.method, this.uri, this.async);
                try {
                    if (this.extraHeaders) {
                        xhr.setDisableHeaderCheck && xhr.setDisableHeaderCheck(true);
                        for (var i in this.extraHeaders) {
                            if (this.extraHeaders.hasOwnProperty(i)) {
                                xhr.setRequestHeader(i, this.extraHeaders[i]);
                            }
                        }
                    }
                } catch (e) {}

                if ('POST' === this.method) {
                    try {
                        if (this.isBinary) {
                            xhr.setRequestHeader('Content-type', 'application/octet-stream');
                        } else {
                            xhr.setRequestHeader('Content-type', 'text/plain;charset=UTF-8');
                        }
                    } catch (e) {}
                }

                try {
                    xhr.setRequestHeader('Accept', '*/*');
                } catch (e) {}

                // ie6 check
                if ('withCredentials' in xhr) {
                    xhr.withCredentials = this.withCredentials;
                }

                if (this.requestTimeout) {
                    xhr.timeout = this.requestTimeout;
                }

                if (this.hasXDR()) {
                    xhr.onload = function () {
                        self.onLoad();
                    };
                    xhr.onerror = function () {
                        self.onError(xhr.responseText);
                    };
                } else {
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === 2) {
                            try {
                                var contentType = xhr.getResponseHeader('Content-Type');
                                if (self.supportsBinary && contentType === 'application/octet-stream' || contentType === 'application/octet-stream; charset=UTF-8') {
                                    xhr.responseType = 'arraybuffer';
                                }
                            } catch (e) {}
                        }
                        if (4 !== xhr.readyState) return;
                        if (200 === xhr.status || 1223 === xhr.status) {
                            self.onLoad();
                        } else {
                            // make sure the `error` event handler that's user-set
                            // does not throw in the same tick and gets caught here
                            setTimeout(function () {
                                self.onError(typeof xhr.status === 'number' ? xhr.status : 0);
                            }, 0);
                        }
                    };
                }

                debug('xhr data %s', this.data);
                xhr.send(this.data);
            } catch (e) {
                // Need to defer since .create() is called directly fhrom the constructor
                // and thus the 'error' event can only be only bound *after* this exception
                // occurs.  Therefore, also, we cannot throw here at all.
                setTimeout(function () {
                    self.onError(e);
                }, 0);
                return;
            }

            if (typeof document !== 'undefined') {
                this.index = Request.requestsCount++;
                Request.requests[this.index] = this;
            }
        };

        /**
         * Called upon successful response.
         *
         * @api private
         */

        Request.prototype.onSuccess = function () {
            this.emit('success');
            this.cleanup();
        };

        /**
         * Called if we have data.
         *
         * @api private
         */

        Request.prototype.onData = function (data) {
            this.emit('data', data);
            this.onSuccess();
        };

        /**
         * Called upon error.
         *
         * @api private
         */

        Request.prototype.onError = function (err) {
            this.emit('error', err);
            this.cleanup(true);
        };

        /**
         * Cleans up house.
         *
         * @api private
         */

        Request.prototype.cleanup = function (fromError) {
            if ('undefined' === typeof this.xhr || null === this.xhr) {
                return;
            }
            // xmlhttprequest
            if (this.hasXDR()) {
                this.xhr.onload = this.xhr.onerror = empty;
            } else {
                this.xhr.onreadystatechange = empty;
            }

            if (fromError) {
                try {
                    this.xhr.abort();
                } catch (e) {}
            }

            if (typeof document !== 'undefined') {
                delete Request.requests[this.index];
            }

            this.xhr = null;
        };

        /**
         * Called upon load.
         *
         * @api private
         */

        Request.prototype.onLoad = function () {
            var data;
            try {
                var contentType;
                try {
                    contentType = this.xhr.getResponseHeader('Content-Type');
                } catch (e) {}
                if (contentType === 'application/octet-stream' || contentType === 'application/octet-stream; charset=UTF-8') {
                    data = this.xhr.response || this.xhr.responseText;
                } else {
                    data = this.xhr.responseText;
                }
            } catch (e) {
                this.onError(e);
            }
            if (null != data) {
                this.onData(data);
            }
        };

        /**
         * Check if it has XDomainRequest.
         *
         * @api private
         */

        Request.prototype.hasXDR = function () {
            return typeof XDomainRequest !== 'undefined' && !this.xs && this.enablesXDR;
        };

        /**
         * Aborts the request.
         *
         * @api public
         */

        Request.prototype.abort = function () {
            this.cleanup();
        };

        /**
         * Aborts pending requests when unloading the window. This is needed to prevent
         * memory leaks (e.g. when using IE) and to ensure that no spurious error is
         * emitted.
         */

        Request.requestsCount = 0;
        Request.requests = {};

        if (typeof document !== 'undefined') {
            if (typeof attachEvent === 'function') {
                attachEvent('onunload', unloadHandler);
            } else if (typeof addEventListener === 'function') {
                var terminationEvent = 'onpagehide' in globalThis ? 'pagehide' : 'unload';
                addEventListener(terminationEvent, unloadHandler, false);
            }
        }

        function unloadHandler () {
            for (var i in Request.requests) {
                if (Request.requests.hasOwnProperty(i)) {
                    Request.requests[i].abort();
                }
            }
        }

    },{"../globalThis":12,"./polling":19,"component-emitter":10,"component-inherit":11,"debug":22,"xmlhttprequest-ssl":21}],19:[function(require,module,exports){
        /**
         * Module dependencies.
         */

        var Transport = require('../transport');
        var parseqs = require('parseqs');
        var parser = require('engine.io-parser');
        var inherit = require('component-inherit');
        var yeast = require('yeast');
        var debug = require('debug')('engine.io-client:polling');

        /**
         * Module exports.
         */

        module.exports = Polling;

        /**
         * Is XHR2 supported?
         */

        var hasXHR2 = (function () {
            var XMLHttpRequest = require('xmlhttprequest-ssl');
            var xhr = new XMLHttpRequest({ xdomain: false });
            return null != xhr.responseType;
        })();

        /**
         * Polling interface.
         *
         * @param {Object} opts
         * @api private
         */

        function Polling (opts) {
            var forceBase64 = (opts && opts.forceBase64);
            if (!hasXHR2 || forceBase64) {
                this.supportsBinary = false;
            }
            Transport.call(this, opts);
        }

        /**
         * Inherits from Transport.
         */

        inherit(Polling, Transport);

        /**
         * Transport name.
         */

        Polling.prototype.name = 'polling';

        /**
         * Opens the socket (triggers polling). We write a PING message to determine
         * when the transport is open.
         *
         * @api private
         */

        Polling.prototype.doOpen = function () {
            this.poll();
        };

        /**
         * Pauses polling.
         *
         * @param {Function} callback upon buffers are flushed and transport is paused
         * @api private
         */

        Polling.prototype.pause = function (onPause) {
            var self = this;

            this.readyState = 'pausing';

            function pause () {
                debug('paused');
                self.readyState = 'paused';
                onPause();
            }

            if (this.polling || !this.writable) {
                var total = 0;

                if (this.polling) {
                    debug('we are currently polling - waiting to pause');
                    total++;
                    this.once('pollComplete', function () {
                        debug('pre-pause polling complete');
                        --total || pause();
                    });
                }

                if (!this.writable) {
                    debug('we are currently writing - waiting to pause');
                    total++;
                    this.once('drain', function () {
                        debug('pre-pause writing complete');
                        --total || pause();
                    });
                }
            } else {
                pause();
            }
        };

        /**
         * Starts polling cycle.
         *
         * @api public
         */

        Polling.prototype.poll = function () {
            debug('polling');
            this.polling = true;
            this.doPoll();
            this.emit('poll');
        };

        /**
         * Overloads onData to detect payloads.
         *
         * @api private
         */

        Polling.prototype.onData = function (data) {
            var self = this;
            debug('polling got data %s', data);
            var callback = function (packet, index, total) {
                // if its the first message we consider the transport open
                if ('opening' === self.readyState) {
                    self.onOpen();
                }

                // if its a close packet, we close the ongoing requests
                if ('close' === packet.type) {
                    self.onClose();
                    return false;
                }

                // otherwise bypass onData and handle the message
                self.onPacket(packet);
            };

            // decode payload
            parser.decodePayload(data, this.socket.binaryType, callback);

            // if an event did not trigger closing
            if ('closed' !== this.readyState) {
                // if we got data we're not polling
                this.polling = false;
                this.emit('pollComplete');

                if ('open' === this.readyState) {
                    this.poll();
                } else {
                    debug('ignoring poll - transport state "%s"', this.readyState);
                }
            }
        };

        /**
         * For polling, send a close packet.
         *
         * @api private
         */

        Polling.prototype.doClose = function () {
            var self = this;

            function close () {
                debug('writing close packet');
                self.write([{ type: 'close' }]);
            }

            if ('open' === this.readyState) {
                debug('transport open - closing');
                close();
            } else {
                // in case we're trying to close while
                // handshaking is in progress (GH-164)
                debug('transport not open - deferring close');
                this.once('open', close);
            }
        };

        /**
         * Writes a packets payload.
         *
         * @param {Array} data packets
         * @param {Function} drain callback
         * @api private
         */

        Polling.prototype.write = function (packets) {
            var self = this;
            this.writable = false;
            var callbackfn = function () {
                self.writable = true;
                self.emit('drain');
            };

            parser.encodePayload(packets, this.supportsBinary, function (data) {
                self.doWrite(data, callbackfn);
            });
        };

        /**
         * Generates uri for connection.
         *
         * @api private
         */

        Polling.prototype.uri = function () {
            var query = this.query || {};
            var schema = this.secure ? 'https' : 'http';
            var port = '';

            // cache busting is forced
            if (false !== this.timestampRequests) {
                query[this.timestampParam] = yeast();
            }

            if (!this.supportsBinary && !query.sid) {
                query.b64 = 1;
            }

            query = parseqs.encode(query);

            // avoid port if default for schema
            if (this.port && (('https' === schema && Number(this.port) !== 443) ||
                ('http' === schema && Number(this.port) !== 80))) {
                port = ':' + this.port;
            }

            // prepend ? to query
            if (query.length) {
                query = '?' + query;
            }

            var ipv6 = this.hostname.indexOf(':') !== -1;
            return schema + '://' + (ipv6 ? '[' + this.hostname + ']' : this.hostname) + port + this.path + query;
        };

    },{"../transport":15,"component-inherit":11,"debug":22,"engine.io-parser":24,"parseqs":33,"xmlhttprequest-ssl":21,"yeast":52}],20:[function(require,module,exports){
        (function (Buffer){
            /**
             * Module dependencies.
             */

            var Transport = require('../transport');
            var parser = require('engine.io-parser');
            var parseqs = require('parseqs');
            var inherit = require('component-inherit');
            var yeast = require('yeast');
            var debug = require('debug')('engine.io-client:websocket');

            var BrowserWebSocket, NodeWebSocket;

            if (typeof WebSocket !== 'undefined') {
                BrowserWebSocket = WebSocket;
            } else if (typeof self !== 'undefined') {
                BrowserWebSocket = self.WebSocket || self.MozWebSocket;
            }

            if (typeof window === 'undefined') {
                try {
                    NodeWebSocket = require('ws');
                } catch (e) { }
            }

            /**
             * Get either the `WebSocket` or `MozWebSocket` globals
             * in the browser or try to resolve WebSocket-compatible
             * interface exposed by `ws` for Node-like environment.
             */

            var WebSocketImpl = BrowserWebSocket || NodeWebSocket;

            /**
             * Module exports.
             */

            module.exports = WS;

            /**
             * WebSocket transport constructor.
             *
             * @api {Object} connection options
             * @api public
             */

            function WS (opts) {
                var forceBase64 = (opts && opts.forceBase64);
                if (forceBase64) {
                    this.supportsBinary = false;
                }
                this.perMessageDeflate = opts.perMessageDeflate;
                this.usingBrowserWebSocket = BrowserWebSocket && !opts.forceNode;
                this.protocols = opts.protocols;
                if (!this.usingBrowserWebSocket) {
                    WebSocketImpl = NodeWebSocket;
                }
                Transport.call(this, opts);
            }

            /**
             * Inherits from Transport.
             */

            inherit(WS, Transport);

            /**
             * Transport name.
             *
             * @api public
             */

            WS.prototype.name = 'websocket';

            /*
 * WebSockets support binary
 */

            WS.prototype.supportsBinary = true;

            /**
             * Opens socket.
             *
             * @api private
             */

            WS.prototype.doOpen = function () {
                if (!this.check()) {
                    // let probe timeout
                    return;
                }

                var uri = this.uri();
                var protocols = this.protocols;

                var opts = {};

                if (!this.isReactNative) {
                    opts.agent = this.agent;
                    opts.perMessageDeflate = this.perMessageDeflate;

                    // SSL options for Node.js client
                    opts.pfx = this.pfx;
                    opts.key = this.key;
                    opts.passphrase = this.passphrase;
                    opts.cert = this.cert;
                    opts.ca = this.ca;
                    opts.ciphers = this.ciphers;
                    opts.rejectUnauthorized = this.rejectUnauthorized;
                }

                if (this.extraHeaders) {
                    opts.headers = this.extraHeaders;
                }
                if (this.localAddress) {
                    opts.localAddress = this.localAddress;
                }

                try {
                    this.ws =
                        this.usingBrowserWebSocket && !this.isReactNative
                            ? protocols
                                ? new WebSocketImpl(uri, protocols)
                                : new WebSocketImpl(uri)
                            : new WebSocketImpl(uri, protocols, opts);
                } catch (err) {
                    return this.emit('error', err);
                }

                if (this.ws.binaryType === undefined) {
                    this.supportsBinary = false;
                }

                if (this.ws.supports && this.ws.supports.binary) {
                    this.supportsBinary = true;
                    this.ws.binaryType = 'nodebuffer';
                } else {
                    this.ws.binaryType = 'arraybuffer';
                }

                this.addEventListeners();
            };

            /**
             * Adds event listeners to the socket
             *
             * @api private
             */

            WS.prototype.addEventListeners = function () {
                var self = this;

                this.ws.onopen = function () {
                    self.onOpen();
                };
                this.ws.onclose = function () {
                    self.onClose();
                };
                this.ws.onmessage = function (ev) {
                    self.onData(ev.data);
                };
                this.ws.onerror = function (e) {
                    self.onError('websocket error', e);
                };
            };

            /**
             * Writes data to socket.
             *
             * @param {Array} array of packets.
             * @api private
             */

            WS.prototype.write = function (packets) {
                var self = this;
                this.writable = false;

                // encodePacket efficient as it uses WS framing
                // no need for encodePayload
                var total = packets.length;
                for (var i = 0, l = total; i < l; i++) {
                    (function (packet) {
                        parser.encodePacket(packet, self.supportsBinary, function (data) {
                            if (!self.usingBrowserWebSocket) {
                                // always create a new object (GH-437)
                                var opts = {};
                                if (packet.options) {
                                    opts.compress = packet.options.compress;
                                }

                                if (self.perMessageDeflate) {
                                    var len = 'string' === typeof data ? Buffer.byteLength(data) : data.length;
                                    if (len < self.perMessageDeflate.threshold) {
                                        opts.compress = false;
                                    }
                                }
                            }

                            // Sometimes the websocket has already been closed but the browser didn't
                            // have a chance of informing us about it yet, in that case send will
                            // throw an error
                            try {
                                if (self.usingBrowserWebSocket) {
                                    // TypeError is thrown when passing the second argument on Safari
                                    self.ws.send(data);
                                } else {
                                    self.ws.send(data, opts);
                                }
                            } catch (e) {
                                debug('websocket closed before onclose event');
                            }

                            --total || done();
                        });
                    })(packets[i]);
                }

                function done () {
                    self.emit('flush');

                    // fake drain
                    // defer to next tick to allow Socket to clear writeBuffer
                    setTimeout(function () {
                        self.writable = true;
                        self.emit('drain');
                    }, 0);
                }
            };

            /**
             * Called upon close
             *
             * @api private
             */

            WS.prototype.onClose = function () {
                Transport.prototype.onClose.call(this);
            };

            /**
             * Closes socket.
             *
             * @api private
             */

            WS.prototype.doClose = function () {
                if (typeof this.ws !== 'undefined') {
                    this.ws.close();
                }
            };

            /**
             * Generates uri for connection.
             *
             * @api private
             */

            WS.prototype.uri = function () {
                var query = this.query || {};
                var schema = this.secure ? 'wss' : 'ws';
                var port = '';

                // avoid port if default for schema
                if (this.port && (('wss' === schema && Number(this.port) !== 443) ||
                    ('ws' === schema && Number(this.port) !== 80))) {
                    port = ':' + this.port;
                }

                // append timestamp to URI
                if (this.timestampRequests) {
                    query[this.timestampParam] = yeast();
                }

                // communicate binary support capabilities
                if (!this.supportsBinary) {
                    query.b64 = 1;
                }

                query = parseqs.encode(query);

                // prepend ? to query
                if (query.length) {
                    query = '?' + query;
                }

                var ipv6 = this.hostname.indexOf(':') !== -1;
                return schema + '://' + (ipv6 ? '[' + this.hostname + ']' : this.hostname) + port + this.path + query;
            };

            /**
             * Feature detection for WebSocket.
             *
             * @return {Boolean} whether this transport is available.
             * @api public
             */

            WS.prototype.check = function () {
                return !!WebSocketImpl && !('__initialize' in WebSocketImpl && this.name === WS.prototype.name);
            };

        }).call(this,require("buffer").Buffer)
    },{"../transport":15,"buffer":8,"component-inherit":11,"debug":22,"engine.io-parser":24,"parseqs":33,"ws":7,"yeast":52}],21:[function(require,module,exports){
// browser shim for xmlhttprequest module

        var hasCORS = require('has-cors');
        var globalThis = require('./globalThis');

        module.exports = function (opts) {
            var xdomain = opts.xdomain;

            // scheme must be same when usign XDomainRequest
            // http://blogs.msdn.com/b/ieinternals/archive/2010/05/13/xdomainrequest-restrictions-limitations-and-workarounds.aspx
            var xscheme = opts.xscheme;

            // XDomainRequest has a flow of not sending cookie, therefore it should be disabled as a default.
            // https://github.com/Automattic/engine.io-client/pull/217
            var enablesXDR = opts.enablesXDR;

            // XMLHttpRequest can be disabled on IE
            try {
                if ('undefined' !== typeof XMLHttpRequest && (!xdomain || hasCORS)) {
                    return new XMLHttpRequest();
                }
            } catch (e) { }

            // Use XDomainRequest for IE8 if enablesXDR is true
            // because loading bar keeps flashing when using jsonp-polling
            // https://github.com/yujiosaka/socke.io-ie8-loading-example
            try {
                if ('undefined' !== typeof XDomainRequest && !xscheme && enablesXDR) {
                    return new XDomainRequest();
                }
            } catch (e) { }

            if (!xdomain) {
                try {
                    return new globalThis[['Active'].concat('Object').join('X')]('Microsoft.XMLHTTP');
                } catch (e) { }
            }
        };

    },{"./globalThis":12,"has-cors":29}],22:[function(require,module,exports){
        (function (process){
            /* eslint-env browser */

            /**
             * This is the web browser implementation of `debug()`.
             */

            exports.log = log;
            exports.formatArgs = formatArgs;
            exports.save = save;
            exports.load = load;
            exports.useColors = useColors;
            exports.storage = localstorage();

            /**
             * Colors.
             */

            exports.colors = [
                '#0000CC',
                '#0000FF',
                '#0033CC',
                '#0033FF',
                '#0066CC',
                '#0066FF',
                '#0099CC',
                '#0099FF',
                '#00CC00',
                '#00CC33',
                '#00CC66',
                '#00CC99',
                '#00CCCC',
                '#00CCFF',
                '#3300CC',
                '#3300FF',
                '#3333CC',
                '#3333FF',
                '#3366CC',
                '#3366FF',
                '#3399CC',
                '#3399FF',
                '#33CC00',
                '#33CC33',
                '#33CC66',
                '#33CC99',
                '#33CCCC',
                '#33CCFF',
                '#6600CC',
                '#6600FF',
                '#6633CC',
                '#6633FF',
                '#66CC00',
                '#66CC33',
                '#9900CC',
                '#9900FF',
                '#9933CC',
                '#9933FF',
                '#99CC00',
                '#99CC33',
                '#CC0000',
                '#CC0033',
                '#CC0066',
                '#CC0099',
                '#CC00CC',
                '#CC00FF',
                '#CC3300',
                '#CC3333',
                '#CC3366',
                '#CC3399',
                '#CC33CC',
                '#CC33FF',
                '#CC6600',
                '#CC6633',
                '#CC9900',
                '#CC9933',
                '#CCCC00',
                '#CCCC33',
                '#FF0000',
                '#FF0033',
                '#FF0066',
                '#FF0099',
                '#FF00CC',
                '#FF00FF',
                '#FF3300',
                '#FF3333',
                '#FF3366',
                '#FF3399',
                '#FF33CC',
                '#FF33FF',
                '#FF6600',
                '#FF6633',
                '#FF9900',
                '#FF9933',
                '#FFCC00',
                '#FFCC33'
            ];

            /**
             * Currently only WebKit-based Web Inspectors, Firefox >= v31,
             * and the Firebug extension (any Firefox version) are known
             * to support "%c" CSS customizations.
             *
             * TODO: add a `localStorage` variable to explicitly enable/disable colors
             */

// eslint-disable-next-line complexity
            function useColors() {
                // NB: In an Electron preload script, document will be defined but not fully
                // initialized. Since we know we're in Chrome, we'll just detect this case
                // explicitly
                if (typeof window !== 'undefined' && window.process && (window.process.type === 'renderer' || window.process.__nwjs)) {
                    return true;
                }

                // Internet Explorer and Edge do not support colors.
                if (typeof navigator !== 'undefined' && navigator.userAgent && navigator.userAgent.toLowerCase().match(/(edge|trident)\/(\d+)/)) {
                    return false;
                }

                // Is webkit? http://stackoverflow.com/a/16459606/376773
                // document is undefined in react-native: https://github.com/facebook/react-native/pull/1632
                return (typeof document !== 'undefined' && document.documentElement && document.documentElement.style && document.documentElement.style.WebkitAppearance) ||
                    // Is firebug? http://stackoverflow.com/a/398120/376773
                    (typeof window !== 'undefined' && window.console && (window.console.firebug || (window.console.exception && window.console.table))) ||
                    // Is firefox >= v31?
                    // https://developer.mozilla.org/en-US/docs/Tools/Web_Console#Styling_messages
                    (typeof navigator !== 'undefined' && navigator.userAgent && navigator.userAgent.toLowerCase().match(/firefox\/(\d+)/) && parseInt(RegExp.$1, 10) >= 31) ||
                    // Double check webkit in userAgent just in case we are in a worker
                    (typeof navigator !== 'undefined' && navigator.userAgent && navigator.userAgent.toLowerCase().match(/applewebkit\/(\d+)/));
            }

            /**
             * Colorize log arguments if enabled.
             *
             * @api public
             */

            function formatArgs(args) {
                args[0] = (this.useColors ? '%c' : '') +
                    this.namespace +
                    (this.useColors ? ' %c' : ' ') +
                    args[0] +
                    (this.useColors ? '%c ' : ' ') +
                    '+' + module.exports.humanize(this.diff);

                if (!this.useColors) {
                    return;
                }

                const c = 'color: ' + this.color;
                args.splice(1, 0, c, 'color: inherit');

                // The final "%c" is somewhat tricky, because there could be other
                // arguments passed either before or after the %c, so we need to
                // figure out the correct index to insert the CSS into
                let index = 0;
                let lastC = 0;
                args[0].replace(/%[a-zA-Z%]/g, match => {
                    if (match === '%%') {
                        return;
                    }
                    index++;
                    if (match === '%c') {
                        // We only are interested in the *last* %c
                        // (the user may have provided their own)
                        lastC = index;
                    }
                });

                args.splice(lastC, 0, c);
            }

            /**
             * Invokes `console.log()` when available.
             * No-op when `console.log` is not a "function".
             *
             * @api public
             */
            function log(...args) {
                // This hackery is required for IE8/9, where
                // the `console.log` function doesn't have 'apply'
                return typeof console === 'object' &&
                    console.log &&
                    console.log(...args);
            }

            /**
             * Save `namespaces`.
             *
             * @param {String} namespaces
             * @api private
             */
            function save(namespaces) {
                try {
                    if (namespaces) {
                        exports.storage.setItem('debug', namespaces);
                    } else {
                        exports.storage.removeItem('debug');
                    }
                } catch (error) {
                    // Swallow
                    // XXX (@Qix-) should we be logging these?
                }
            }

            /**
             * Load `namespaces`.
             *
             * @return {String} returns the previously persisted debug modes
             * @api private
             */
            function load() {
                let r;
                try {
                    r = exports.storage.getItem('debug');
                } catch (error) {
                    // Swallow
                    // XXX (@Qix-) should we be logging these?
                }

                // If debug isn't set in LS, and we're in Electron, try to load $DEBUG
                if (!r && typeof process !== 'undefined' && 'env' in process) {
                    r = process.env.DEBUG;
                }

                return r;
            }

            /**
             * Localstorage attempts to return the localstorage.
             *
             * This is necessary because safari throws
             * when a user disables cookies/localstorage
             * and you attempt to access it.
             *
             * @return {LocalStorage}
             * @api private
             */

            function localstorage() {
                try {
                    // TVMLKit (Apple TV JS Runtime) does not have a window object, just localStorage in the global context
                    // The Browser also has localStorage in the global context.
                    return localStorage;
                } catch (error) {
                    // Swallow
                    // XXX (@Qix-) should we be logging these?
                }
            }

            module.exports = require('./common')(exports);

            const {formatters} = module.exports;

            /**
             * Map %j to `JSON.stringify()`, since no Web Inspectors do that by default.
             */

            formatters.j = function (v) {
                try {
                    return JSON.stringify(v);
                } catch (error) {
                    return '[UnexpectedJSONParseError]: ' + error.message;
                }
            };

        }).call(this,require('_process'))
    },{"./common":23,"_process":35}],23:[function(require,module,exports){

        /**
         * This is the common logic for both the Node.js and web browser
         * implementations of `debug()`.
         */

        function setup(env) {
            createDebug.debug = createDebug;
            createDebug.default = createDebug;
            createDebug.coerce = coerce;
            createDebug.disable = disable;
            createDebug.enable = enable;
            createDebug.enabled = enabled;
            createDebug.humanize = require('ms');

            Object.keys(env).forEach(key => {
                createDebug[key] = env[key];
            });

            /**
             * Active `debug` instances.
             */
            createDebug.instances = [];

            /**
             * The currently active debug mode names, and names to skip.
             */

            createDebug.names = [];
            createDebug.skips = [];

            /**
             * Map of special "%n" handling functions, for the debug "format" argument.
             *
             * Valid key names are a single, lower or upper-case letter, i.e. "n" and "N".
             */
            createDebug.formatters = {};

            /**
             * Selects a color for a debug namespace
             * @param {String} namespace The namespace string for the for the debug instance to be colored
             * @return {Number|String} An ANSI color code for the given namespace
             * @api private
             */
            function selectColor(namespace) {
                let hash = 0;

                for (let i = 0; i < namespace.length; i++) {
                    hash = ((hash << 5) - hash) + namespace.charCodeAt(i);
                    hash |= 0; // Convert to 32bit integer
                }

                return createDebug.colors[Math.abs(hash) % createDebug.colors.length];
            }
            createDebug.selectColor = selectColor;

            /**
             * Create a debugger with the given `namespace`.
             *
             * @param {String} namespace
             * @return {Function}
             * @api public
             */
            function createDebug(namespace) {
                let prevTime;

                function debug(...args) {
                    // Disabled?
                    if (!debug.enabled) {
                        return;
                    }

                    const self = debug;

                    // Set `diff` timestamp
                    const curr = Number(new Date());
                    const ms = curr - (prevTime || curr);
                    self.diff = ms;
                    self.prev = prevTime;
                    self.curr = curr;
                    prevTime = curr;

                    args[0] = createDebug.coerce(args[0]);

                    if (typeof args[0] !== 'string') {
                        // Anything else let's inspect with %O
                        args.unshift('%O');
                    }

                    // Apply any `formatters` transformations
                    let index = 0;
                    args[0] = args[0].replace(/%([a-zA-Z%])/g, (match, format) => {
                        // If we encounter an escaped % then don't increase the array index
                        if (match === '%%') {
                            return match;
                        }
                        index++;
                        const formatter = createDebug.formatters[format];
                        if (typeof formatter === 'function') {
                            const val = args[index];
                            match = formatter.call(self, val);

                            // Now we need to remove `args[index]` since it's inlined in the `format`
                            args.splice(index, 1);
                            index--;
                        }
                        return match;
                    });

                    // Apply env-specific formatting (colors, etc.)
                    createDebug.formatArgs.call(self, args);

                    const logFn = self.log || createDebug.log;
                    logFn.apply(self, args);
                }

                debug.namespace = namespace;
                debug.enabled = createDebug.enabled(namespace);
                debug.useColors = createDebug.useColors();
                debug.color = selectColor(namespace);
                debug.destroy = destroy;
                debug.extend = extend;
                // Debug.formatArgs = formatArgs;
                // debug.rawLog = rawLog;

                // env-specific initialization logic for debug instances
                if (typeof createDebug.init === 'function') {
                    createDebug.init(debug);
                }

                createDebug.instances.push(debug);

                return debug;
            }

            function destroy() {
                const index = createDebug.instances.indexOf(this);
                if (index !== -1) {
                    createDebug.instances.splice(index, 1);
                    return true;
                }
                return false;
            }

            function extend(namespace, delimiter) {
                const newDebug = createDebug(this.namespace + (typeof delimiter === 'undefined' ? ':' : delimiter) + namespace);
                newDebug.log = this.log;
                return newDebug;
            }

            /**
             * Enables a debug mode by namespaces. This can include modes
             * separated by a colon and wildcards.
             *
             * @param {String} namespaces
             * @api public
             */
            function enable(namespaces) {
                createDebug.save(namespaces);

                createDebug.names = [];
                createDebug.skips = [];

                let i;
                const split = (typeof namespaces === 'string' ? namespaces : '').split(/[\s,]+/);
                const len = split.length;

                for (i = 0; i < len; i++) {
                    if (!split[i]) {
                        // ignore empty strings
                        continue;
                    }

                    namespaces = split[i].replace(/\*/g, '.*?');

                    if (namespaces[0] === '-') {
                        createDebug.skips.push(new RegExp('^' + namespaces.substr(1) + '$'));
                    } else {
                        createDebug.names.push(new RegExp('^' + namespaces + '$'));
                    }
                }

                for (i = 0; i < createDebug.instances.length; i++) {
                    const instance = createDebug.instances[i];
                    instance.enabled = createDebug.enabled(instance.namespace);
                }
            }

            /**
             * Disable debug output.
             *
             * @return {String} namespaces
             * @api public
             */
            function disable() {
                const namespaces = [
                    ...createDebug.names.map(toNamespace),
                    ...createDebug.skips.map(toNamespace).map(namespace => '-' + namespace)
                ].join(',');
                createDebug.enable('');
                return namespaces;
            }

            /**
             * Returns true if the given mode name is enabled, false otherwise.
             *
             * @param {String} name
             * @return {Boolean}
             * @api public
             */
            function enabled(name) {
                if (name[name.length - 1] === '*') {
                    return true;
                }

                let i;
                let len;

                for (i = 0, len = createDebug.skips.length; i < len; i++) {
                    if (createDebug.skips[i].test(name)) {
                        return false;
                    }
                }

                for (i = 0, len = createDebug.names.length; i < len; i++) {
                    if (createDebug.names[i].test(name)) {
                        return true;
                    }
                }

                return false;
            }

            /**
             * Convert regexp to namespace
             *
             * @param {RegExp} regxep
             * @return {String} namespace
             * @api private
             */
            function toNamespace(regexp) {
                return regexp.toString()
                    .substring(2, regexp.toString().length - 2)
                    .replace(/\.\*\?$/, '*');
            }

            /**
             * Coerce `val`.
             *
             * @param {Mixed} val
             * @return {Mixed}
             * @api private
             */
            function coerce(val) {
                if (val instanceof Error) {
                    return val.stack || val.message;
                }
                return val;
            }

            createDebug.enable(createDebug.load());

            return createDebug;
        }

        module.exports = setup;

    },{"ms":32}],24:[function(require,module,exports){
        /**
         * Module dependencies.
         */

        var keys = require('./keys');
        var hasBinary = require('has-binary2');
        var sliceBuffer = require('arraybuffer.slice');
        var after = require('after');
        var utf8 = require('./utf8');

        var base64encoder;
        if (typeof ArrayBuffer !== 'undefined') {
            base64encoder = require('base64-arraybuffer');
        }

        /**
         * Check if we are running an android browser. That requires us to use
         * ArrayBuffer with polling transports...
         *
         * http://ghinda.net/jpeg-blob-ajax-android/
         */

        var isAndroid = typeof navigator !== 'undefined' && /Android/i.test(navigator.userAgent);

        /**
         * Check if we are running in PhantomJS.
         * Uploading a Blob with PhantomJS does not work correctly, as reported here:
         * https://github.com/ariya/phantomjs/issues/11395
         * @type boolean
         */
        var isPhantomJS = typeof navigator !== 'undefined' && /PhantomJS/i.test(navigator.userAgent);

        /**
         * When true, avoids using Blobs to encode payloads.
         * @type boolean
         */
        var dontSendBlobs = isAndroid || isPhantomJS;

        /**
         * Current protocol version.
         */

        exports.protocol = 3;

        /**
         * Packet types.
         */

        var packets = exports.packets = {
            open:     0    // non-ws
            , close:    1    // non-ws
            , ping:     2
            , pong:     3
            , message:  4
            , upgrade:  5
            , noop:     6
        };

        var packetslist = keys(packets);

        /**
         * Premade error packet.
         */

        var err = { type: 'error', data: 'parser error' };

        /**
         * Create a blob api even for blob builder when vendor prefixes exist
         */

        var Blob = require('blob');

        /**
         * Encodes a packet.
         *
         *     <packet type id> [ <data> ]
         *
         * Example:
         *
         *     5hello world
         *     3
         *     4
         *
         * Binary is encoded in an identical principle
         *
         * @api private
         */

        exports.encodePacket = function (packet, supportsBinary, utf8encode, callback) {
            if (typeof supportsBinary === 'function') {
                callback = supportsBinary;
                supportsBinary = false;
            }

            if (typeof utf8encode === 'function') {
                callback = utf8encode;
                utf8encode = null;
            }

            var data = (packet.data === undefined)
                ? undefined
                : packet.data.buffer || packet.data;

            if (typeof ArrayBuffer !== 'undefined' && data instanceof ArrayBuffer) {
                return encodeArrayBuffer(packet, supportsBinary, callback);
            } else if (typeof Blob !== 'undefined' && data instanceof Blob) {
                return encodeBlob(packet, supportsBinary, callback);
            }

            // might be an object with { base64: true, data: dataAsBase64String }
            if (data && data.base64) {
                return encodeBase64Object(packet, callback);
            }

            // Sending data as a utf-8 string
            var encoded = packets[packet.type];

            // data fragment is optional
            if (undefined !== packet.data) {
                encoded += utf8encode ? utf8.encode(String(packet.data), { strict: false }) : String(packet.data);
            }

            return callback('' + encoded);

        };

        function encodeBase64Object(packet, callback) {
            // packet data is an object { base64: true, data: dataAsBase64String }
            var message = 'b' + exports.packets[packet.type] + packet.data.data;
            return callback(message);
        }

        /**
         * Encode packet helpers for binary types
         */

        function encodeArrayBuffer(packet, supportsBinary, callback) {
            if (!supportsBinary) {
                return exports.encodeBase64Packet(packet, callback);
            }

            var data = packet.data;
            var contentArray = new Uint8Array(data);
            var resultBuffer = new Uint8Array(1 + data.byteLength);

            resultBuffer[0] = packets[packet.type];
            for (var i = 0; i < contentArray.length; i++) {
                resultBuffer[i+1] = contentArray[i];
            }

            return callback(resultBuffer.buffer);
        }

        function encodeBlobAsArrayBuffer(packet, supportsBinary, callback) {
            if (!supportsBinary) {
                return exports.encodeBase64Packet(packet, callback);
            }

            var fr = new FileReader();
            fr.onload = function() {
                exports.encodePacket({ type: packet.type, data: fr.result }, supportsBinary, true, callback);
            };
            return fr.readAsArrayBuffer(packet.data);
        }

        function encodeBlob(packet, supportsBinary, callback) {
            if (!supportsBinary) {
                return exports.encodeBase64Packet(packet, callback);
            }

            if (dontSendBlobs) {
                return encodeBlobAsArrayBuffer(packet, supportsBinary, callback);
            }

            var length = new Uint8Array(1);
            length[0] = packets[packet.type];
            var blob = new Blob([length.buffer, packet.data]);

            return callback(blob);
        }

        /**
         * Encodes a packet with binary data in a base64 string
         *
         * @param {Object} packet, has `type` and `data`
         * @return {String} base64 encoded message
         */

        exports.encodeBase64Packet = function(packet, callback) {
            var message = 'b' + exports.packets[packet.type];
            if (typeof Blob !== 'undefined' && packet.data instanceof Blob) {
                var fr = new FileReader();
                fr.onload = function() {
                    var b64 = fr.result.split(',')[1];
                    callback(message + b64);
                };
                return fr.readAsDataURL(packet.data);
            }

            var b64data;
            try {
                b64data = String.fromCharCode.apply(null, new Uint8Array(packet.data));
            } catch (e) {
                // iPhone Safari doesn't let you apply with typed arrays
                var typed = new Uint8Array(packet.data);
                var basic = new Array(typed.length);
                for (var i = 0; i < typed.length; i++) {
                    basic[i] = typed[i];
                }
                b64data = String.fromCharCode.apply(null, basic);
            }
            message += btoa(b64data);
            return callback(message);
        };

        /**
         * Decodes a packet. Changes format to Blob if requested.
         *
         * @return {Object} with `type` and `data` (if any)
         * @api private
         */

        exports.decodePacket = function (data, binaryType, utf8decode) {
            if (data === undefined) {
                return err;
            }
            // String data
            if (typeof data === 'string') {
                if (data.charAt(0) === 'b') {
                    return exports.decodeBase64Packet(data.substr(1), binaryType);
                }

                if (utf8decode) {
                    data = tryDecode(data);
                    if (data === false) {
                        return err;
                    }
                }
                var type = data.charAt(0);

                if (Number(type) != type || !packetslist[type]) {
                    return err;
                }

                if (data.length > 1) {
                    return { type: packetslist[type], data: data.substring(1) };
                } else {
                    return { type: packetslist[type] };
                }
            }

            var asArray = new Uint8Array(data);
            var type = asArray[0];
            var rest = sliceBuffer(data, 1);
            if (Blob && binaryType === 'blob') {
                rest = new Blob([rest]);
            }
            return { type: packetslist[type], data: rest };
        };

        function tryDecode(data) {
            try {
                data = utf8.decode(data, { strict: false });
            } catch (e) {
                return false;
            }
            return data;
        }

        /**
         * Decodes a packet encoded in a base64 string
         *
         * @param {String} base64 encoded message
         * @return {Object} with `type` and `data` (if any)
         */

        exports.decodeBase64Packet = function(msg, binaryType) {
            var type = packetslist[msg.charAt(0)];
            if (!base64encoder) {
                return { type: type, data: { base64: true, data: msg.substr(1) } };
            }

            var data = base64encoder.decode(msg.substr(1));

            if (binaryType === 'blob' && Blob) {
                data = new Blob([data]);
            }

            return { type: type, data: data };
        };

        /**
         * Encodes multiple messages (payload).
         *
         *     <length>:data
         *
         * Example:
         *
         *     11:hello world2:hi
         *
         * If any contents are binary, they will be encoded as base64 strings. Base64
         * encoded strings are marked with a b before the length specifier
         *
         * @param {Array} packets
         * @api private
         */

        exports.encodePayload = function (packets, supportsBinary, callback) {
            if (typeof supportsBinary === 'function') {
                callback = supportsBinary;
                supportsBinary = null;
            }

            var isBinary = hasBinary(packets);

            if (supportsBinary && isBinary) {
                if (Blob && !dontSendBlobs) {
                    return exports.encodePayloadAsBlob(packets, callback);
                }

                return exports.encodePayloadAsArrayBuffer(packets, callback);
            }

            if (!packets.length) {
                return callback('0:');
            }

            function setLengthHeader(message) {
                return message.length + ':' + message;
            }

            function encodeOne(packet, doneCallback) {
                exports.encodePacket(packet, !isBinary ? false : supportsBinary, false, function(message) {
                    doneCallback(null, setLengthHeader(message));
                });
            }

            map(packets, encodeOne, function(err, results) {
                return callback(results.join(''));
            });
        };

        /**
         * Async array map using after
         */

        function map(ary, each, done) {
            var result = new Array(ary.length);
            var next = after(ary.length, done);

            var eachWithIndex = function(i, el, cb) {
                each(el, function(error, msg) {
                    result[i] = msg;
                    cb(error, result);
                });
            };

            for (var i = 0; i < ary.length; i++) {
                eachWithIndex(i, ary[i], next);
            }
        }

        /*
 * Decodes data when a payload is maybe expected. Possible binary contents are
 * decoded from their base64 representation
 *
 * @param {String} data, callback method
 * @api public
 */

        exports.decodePayload = function (data, binaryType, callback) {
            if (typeof data !== 'string') {
                return exports.decodePayloadAsBinary(data, binaryType, callback);
            }

            if (typeof binaryType === 'function') {
                callback = binaryType;
                binaryType = null;
            }

            var packet;
            if (data === '') {
                // parser error - ignoring payload
                return callback(err, 0, 1);
            }

            var length = '', n, msg;

            for (var i = 0, l = data.length; i < l; i++) {
                var chr = data.charAt(i);

                if (chr !== ':') {
                    length += chr;
                    continue;
                }

                if (length === '' || (length != (n = Number(length)))) {
                    // parser error - ignoring payload
                    return callback(err, 0, 1);
                }

                msg = data.substr(i + 1, n);

                if (length != msg.length) {
                    // parser error - ignoring payload
                    return callback(err, 0, 1);
                }

                if (msg.length) {
                    packet = exports.decodePacket(msg, binaryType, false);

                    if (err.type === packet.type && err.data === packet.data) {
                        // parser error in individual packet - ignoring payload
                        return callback(err, 0, 1);
                    }

                    var ret = callback(packet, i + n, l);
                    if (false === ret) return;
                }

                // advance cursor
                i += n;
                length = '';
            }

            if (length !== '') {
                // parser error - ignoring payload
                return callback(err, 0, 1);
            }

        };

        /**
         * Encodes multiple messages (payload) as binary.
         *
         * <1 = binary, 0 = string><number from 0-9><number from 0-9>[...]<number
         * 255><data>
         *
         * Example:
         * 1 3 255 1 2 3, if the binary contents are interpreted as 8 bit integers
         *
         * @param {Array} packets
         * @return {ArrayBuffer} encoded payload
         * @api private
         */

        exports.encodePayloadAsArrayBuffer = function(packets, callback) {
            if (!packets.length) {
                return callback(new ArrayBuffer(0));
            }

            function encodeOne(packet, doneCallback) {
                exports.encodePacket(packet, true, true, function(data) {
                    return doneCallback(null, data);
                });
            }

            map(packets, encodeOne, function(err, encodedPackets) {
                var totalLength = encodedPackets.reduce(function(acc, p) {
                    var len;
                    if (typeof p === 'string'){
                        len = p.length;
                    } else {
                        len = p.byteLength;
                    }
                    return acc + len.toString().length + len + 2; // string/binary identifier + separator = 2
                }, 0);

                var resultArray = new Uint8Array(totalLength);

                var bufferIndex = 0;
                encodedPackets.forEach(function(p) {
                    var isString = typeof p === 'string';
                    var ab = p;
                    if (isString) {
                        var view = new Uint8Array(p.length);
                        for (var i = 0; i < p.length; i++) {
                            view[i] = p.charCodeAt(i);
                        }
                        ab = view.buffer;
                    }

                    if (isString) { // not true binary
                        resultArray[bufferIndex++] = 0;
                    } else { // true binary
                        resultArray[bufferIndex++] = 1;
                    }

                    var lenStr = ab.byteLength.toString();
                    for (var i = 0; i < lenStr.length; i++) {
                        resultArray[bufferIndex++] = parseInt(lenStr[i]);
                    }
                    resultArray[bufferIndex++] = 255;

                    var view = new Uint8Array(ab);
                    for (var i = 0; i < view.length; i++) {
                        resultArray[bufferIndex++] = view[i];
                    }
                });

                return callback(resultArray.buffer);
            });
        };

        /**
         * Encode as Blob
         */

        exports.encodePayloadAsBlob = function(packets, callback) {
            function encodeOne(packet, doneCallback) {
                exports.encodePacket(packet, true, true, function(encoded) {
                    var binaryIdentifier = new Uint8Array(1);
                    binaryIdentifier[0] = 1;
                    if (typeof encoded === 'string') {
                        var view = new Uint8Array(encoded.length);
                        for (var i = 0; i < encoded.length; i++) {
                            view[i] = encoded.charCodeAt(i);
                        }
                        encoded = view.buffer;
                        binaryIdentifier[0] = 0;
                    }

                    var len = (encoded instanceof ArrayBuffer)
                        ? encoded.byteLength
                        : encoded.size;

                    var lenStr = len.toString();
                    var lengthAry = new Uint8Array(lenStr.length + 1);
                    for (var i = 0; i < lenStr.length; i++) {
                        lengthAry[i] = parseInt(lenStr[i]);
                    }
                    lengthAry[lenStr.length] = 255;

                    if (Blob) {
                        var blob = new Blob([binaryIdentifier.buffer, lengthAry.buffer, encoded]);
                        doneCallback(null, blob);
                    }
                });
            }

            map(packets, encodeOne, function(err, results) {
                return callback(new Blob(results));
            });
        };

        /*
 * Decodes data when a payload is maybe expected. Strings are decoded by
 * interpreting each byte as a key code for entries marked to start with 0. See
 * description of encodePayloadAsBinary
 *
 * @param {ArrayBuffer} data, callback method
 * @api public
 */

        exports.decodePayloadAsBinary = function (data, binaryType, callback) {
            if (typeof binaryType === 'function') {
                callback = binaryType;
                binaryType = null;
            }

            var bufferTail = data;
            var buffers = [];

            while (bufferTail.byteLength > 0) {
                var tailArray = new Uint8Array(bufferTail);
                var isString = tailArray[0] === 0;
                var msgLength = '';

                for (var i = 1; ; i++) {
                    if (tailArray[i] === 255) break;

                    // 310 = char length of Number.MAX_VALUE
                    if (msgLength.length > 310) {
                        return callback(err, 0, 1);
                    }

                    msgLength += tailArray[i];
                }

                bufferTail = sliceBuffer(bufferTail, 2 + msgLength.length);
                msgLength = parseInt(msgLength);

                var msg = sliceBuffer(bufferTail, 0, msgLength);
                if (isString) {
                    try {
                        msg = String.fromCharCode.apply(null, new Uint8Array(msg));
                    } catch (e) {
                        // iPhone Safari doesn't let you apply to typed arrays
                        var typed = new Uint8Array(msg);
                        msg = '';
                        for (var i = 0; i < typed.length; i++) {
                            msg += String.fromCharCode(typed[i]);
                        }
                    }
                }

                buffers.push(msg);
                bufferTail = sliceBuffer(bufferTail, msgLength);
            }

            var total = buffers.length;
            buffers.forEach(function(buffer, i) {
                callback(exports.decodePacket(buffer, binaryType, true), i, total);
            });
        };

    },{"./keys":25,"./utf8":26,"after":1,"arraybuffer.slice":2,"base64-arraybuffer":4,"blob":6,"has-binary2":27}],25:[function(require,module,exports){

        /**
         * Gets the keys for an object.
         *
         * @return {Array} keys
         * @api private
         */

        module.exports = Object.keys || function keys (obj){
            var arr = [];
            var has = Object.prototype.hasOwnProperty;

            for (var i in obj) {
                if (has.call(obj, i)) {
                    arr.push(i);
                }
            }
            return arr;
        };

    },{}],26:[function(require,module,exports){
        /*! https://mths.be/utf8js v2.1.2 by @mathias */

        var stringFromCharCode = String.fromCharCode;

// Taken from https://mths.be/punycode
        function ucs2decode(string) {
            var output = [];
            var counter = 0;
            var length = string.length;
            var value;
            var extra;
            while (counter < length) {
                value = string.charCodeAt(counter++);
                if (value >= 0xD800 && value <= 0xDBFF && counter < length) {
                    // high surrogate, and there is a next character
                    extra = string.charCodeAt(counter++);
                    if ((extra & 0xFC00) == 0xDC00) { // low surrogate
                        output.push(((value & 0x3FF) << 10) + (extra & 0x3FF) + 0x10000);
                    } else {
                        // unmatched surrogate; only append this code unit, in case the next
                        // code unit is the high surrogate of a surrogate pair
                        output.push(value);
                        counter--;
                    }
                } else {
                    output.push(value);
                }
            }
            return output;
        }

// Taken from https://mths.be/punycode
        function ucs2encode(array) {
            var length = array.length;
            var index = -1;
            var value;
            var output = '';
            while (++index < length) {
                value = array[index];
                if (value > 0xFFFF) {
                    value -= 0x10000;
                    output += stringFromCharCode(value >>> 10 & 0x3FF | 0xD800);
                    value = 0xDC00 | value & 0x3FF;
                }
                output += stringFromCharCode(value);
            }
            return output;
        }

        function checkScalarValue(codePoint, strict) {
            if (codePoint >= 0xD800 && codePoint <= 0xDFFF) {
                if (strict) {
                    throw Error(
                        'Lone surrogate U+' + codePoint.toString(16).toUpperCase() +
                        ' is not a scalar value'
                    );
                }
                return false;
            }
            return true;
        }
        /*--------------------------------------------------------------------------*/

        function createByte(codePoint, shift) {
            return stringFromCharCode(((codePoint >> shift) & 0x3F) | 0x80);
        }

        function encodeCodePoint(codePoint, strict) {
            if ((codePoint & 0xFFFFFF80) == 0) { // 1-byte sequence
                return stringFromCharCode(codePoint);
            }
            var symbol = '';
            if ((codePoint & 0xFFFFF800) == 0) { // 2-byte sequence
                symbol = stringFromCharCode(((codePoint >> 6) & 0x1F) | 0xC0);
            }
            else if ((codePoint & 0xFFFF0000) == 0) { // 3-byte sequence
                if (!checkScalarValue(codePoint, strict)) {
                    codePoint = 0xFFFD;
                }
                symbol = stringFromCharCode(((codePoint >> 12) & 0x0F) | 0xE0);
                symbol += createByte(codePoint, 6);
            }
            else if ((codePoint & 0xFFE00000) == 0) { // 4-byte sequence
                symbol = stringFromCharCode(((codePoint >> 18) & 0x07) | 0xF0);
                symbol += createByte(codePoint, 12);
                symbol += createByte(codePoint, 6);
            }
            symbol += stringFromCharCode((codePoint & 0x3F) | 0x80);
            return symbol;
        }

        function utf8encode(string, opts) {
            opts = opts || {};
            var strict = false !== opts.strict;

            var codePoints = ucs2decode(string);
            var length = codePoints.length;
            var index = -1;
            var codePoint;
            var byteString = '';
            while (++index < length) {
                codePoint = codePoints[index];
                byteString += encodeCodePoint(codePoint, strict);
            }
            return byteString;
        }

        /*--------------------------------------------------------------------------*/

        function readContinuationByte() {
            if (byteIndex >= byteCount) {
                throw Error('Invalid byte index');
            }

            var continuationByte = byteArray[byteIndex] & 0xFF;
            byteIndex++;

            if ((continuationByte & 0xC0) == 0x80) {
                return continuationByte & 0x3F;
            }

            // If we end up here, it’s not a continuation byte
            throw Error('Invalid continuation byte');
        }

        function decodeSymbol(strict) {
            var byte1;
            var byte2;
            var byte3;
            var byte4;
            var codePoint;

            if (byteIndex > byteCount) {
                throw Error('Invalid byte index');
            }

            if (byteIndex == byteCount) {
                return false;
            }

            // Read first byte
            byte1 = byteArray[byteIndex] & 0xFF;
            byteIndex++;

            // 1-byte sequence (no continuation bytes)
            if ((byte1 & 0x80) == 0) {
                return byte1;
            }

            // 2-byte sequence
            if ((byte1 & 0xE0) == 0xC0) {
                byte2 = readContinuationByte();
                codePoint = ((byte1 & 0x1F) << 6) | byte2;
                if (codePoint >= 0x80) {
                    return codePoint;
                } else {
                    throw Error('Invalid continuation byte');
                }
            }

            // 3-byte sequence (may include unpaired surrogates)
            if ((byte1 & 0xF0) == 0xE0) {
                byte2 = readContinuationByte();
                byte3 = readContinuationByte();
                codePoint = ((byte1 & 0x0F) << 12) | (byte2 << 6) | byte3;
                if (codePoint >= 0x0800) {
                    return checkScalarValue(codePoint, strict) ? codePoint : 0xFFFD;
                } else {
                    throw Error('Invalid continuation byte');
                }
            }

            // 4-byte sequence
            if ((byte1 & 0xF8) == 0xF0) {
                byte2 = readContinuationByte();
                byte3 = readContinuationByte();
                byte4 = readContinuationByte();
                codePoint = ((byte1 & 0x07) << 0x12) | (byte2 << 0x0C) |
                    (byte3 << 0x06) | byte4;
                if (codePoint >= 0x010000 && codePoint <= 0x10FFFF) {
                    return codePoint;
                }
            }

            throw Error('Invalid UTF-8 detected');
        }

        var byteArray;
        var byteCount;
        var byteIndex;
        function utf8decode(byteString, opts) {
            opts = opts || {};
            var strict = false !== opts.strict;

            byteArray = ucs2decode(byteString);
            byteCount = byteArray.length;
            byteIndex = 0;
            var codePoints = [];
            var tmp;
            while ((tmp = decodeSymbol(strict)) !== false) {
                codePoints.push(tmp);
            }
            return ucs2encode(codePoints);
        }

        module.exports = {
            version: '2.1.2',
            encode: utf8encode,
            decode: utf8decode
        };

    },{}],27:[function(require,module,exports){
        (function (Buffer){
            /* global Blob File */

            /*
 * Module requirements.
 */

            var isArray = require('isarray');

            var toString = Object.prototype.toString;
            var withNativeBlob = typeof Blob === 'function' ||
                typeof Blob !== 'undefined' && toString.call(Blob) === '[object BlobConstructor]';
            var withNativeFile = typeof File === 'function' ||
                typeof File !== 'undefined' && toString.call(File) === '[object FileConstructor]';

            /**
             * Module exports.
             */

            module.exports = hasBinary;

            /**
             * Checks for binary data.
             *
             * Supports Buffer, ArrayBuffer, Blob and File.
             *
             * @param {Object} anything
             * @api public
             */

            function hasBinary (obj) {
                if (!obj || typeof obj !== 'object') {
                    return false;
                }

                if (isArray(obj)) {
                    for (var i = 0, l = obj.length; i < l; i++) {
                        if (hasBinary(obj[i])) {
                            return true;
                        }
                    }
                    return false;
                }

                if ((typeof Buffer === 'function' && Buffer.isBuffer && Buffer.isBuffer(obj)) ||
                    (typeof ArrayBuffer === 'function' && obj instanceof ArrayBuffer) ||
                    (withNativeBlob && obj instanceof Blob) ||
                    (withNativeFile && obj instanceof File)
                ) {
                    return true;
                }

                // see: https://github.com/Automattic/has-binary/pull/4
                if (obj.toJSON && typeof obj.toJSON === 'function' && arguments.length === 1) {
                    return hasBinary(obj.toJSON(), true);
                }

                for (var key in obj) {
                    if (Object.prototype.hasOwnProperty.call(obj, key) && hasBinary(obj[key])) {
                        return true;
                    }
                }

                return false;
            }

        }).call(this,require("buffer").Buffer)
    },{"buffer":8,"isarray":28}],28:[function(require,module,exports){
        var toString = {}.toString;

        module.exports = Array.isArray || function (arr) {
            return toString.call(arr) == '[object Array]';
        };

    },{}],29:[function(require,module,exports){

        /**
         * Module exports.
         *
         * Logic borrowed from Modernizr:
         *
         *   - https://github.com/Modernizr/Modernizr/blob/master/feature-detects/cors.js
         */

        try {
            module.exports = typeof XMLHttpRequest !== 'undefined' &&
                'withCredentials' in new XMLHttpRequest();
        } catch (err) {
            // if XMLHttp support is disabled in IE then it will throw
            // when trying to create
            module.exports = false;
        }

    },{}],30:[function(require,module,exports){
        exports.read = function (buffer, offset, isLE, mLen, nBytes) {
            var e, m
            var eLen = (nBytes * 8) - mLen - 1
            var eMax = (1 << eLen) - 1
            var eBias = eMax >> 1
            var nBits = -7
            var i = isLE ? (nBytes - 1) : 0
            var d = isLE ? -1 : 1
            var s = buffer[offset + i]

            i += d

            e = s & ((1 << (-nBits)) - 1)
            s >>= (-nBits)
            nBits += eLen
            for (; nBits > 0; e = (e * 256) + buffer[offset + i], i += d, nBits -= 8) {}

            m = e & ((1 << (-nBits)) - 1)
            e >>= (-nBits)
            nBits += mLen
            for (; nBits > 0; m = (m * 256) + buffer[offset + i], i += d, nBits -= 8) {}

            if (e === 0) {
                e = 1 - eBias
            } else if (e === eMax) {
                return m ? NaN : ((s ? -1 : 1) * Infinity)
            } else {
                m = m + Math.pow(2, mLen)
                e = e - eBias
            }
            return (s ? -1 : 1) * m * Math.pow(2, e - mLen)
        }

        exports.write = function (buffer, value, offset, isLE, mLen, nBytes) {
            var e, m, c
            var eLen = (nBytes * 8) - mLen - 1
            var eMax = (1 << eLen) - 1
            var eBias = eMax >> 1
            var rt = (mLen === 23 ? Math.pow(2, -24) - Math.pow(2, -77) : 0)
            var i = isLE ? 0 : (nBytes - 1)
            var d = isLE ? 1 : -1
            var s = value < 0 || (value === 0 && 1 / value < 0) ? 1 : 0

            value = Math.abs(value)

            if (isNaN(value) || value === Infinity) {
                m = isNaN(value) ? 1 : 0
                e = eMax
            } else {
                e = Math.floor(Math.log(value) / Math.LN2)
                if (value * (c = Math.pow(2, -e)) < 1) {
                    e--
                    c *= 2
                }
                if (e + eBias >= 1) {
                    value += rt / c
                } else {
                    value += rt * Math.pow(2, 1 - eBias)
                }
                if (value * c >= 2) {
                    e++
                    c /= 2
                }

                if (e + eBias >= eMax) {
                    m = 0
                    e = eMax
                } else if (e + eBias >= 1) {
                    m = ((value * c) - 1) * Math.pow(2, mLen)
                    e = e + eBias
                } else {
                    m = value * Math.pow(2, eBias - 1) * Math.pow(2, mLen)
                    e = 0
                }
            }

            for (; mLen >= 8; buffer[offset + i] = m & 0xff, i += d, m /= 256, mLen -= 8) {}

            e = (e << mLen) | m
            eLen += mLen
            for (; eLen > 0; buffer[offset + i] = e & 0xff, i += d, e /= 256, eLen -= 8) {}

            buffer[offset + i - d] |= s * 128
        }

    },{}],31:[function(require,module,exports){

        var indexOf = [].indexOf;

        module.exports = function(arr, obj){
            if (indexOf) return arr.indexOf(obj);
            for (var i = 0; i < arr.length; ++i) {
                if (arr[i] === obj) return i;
            }
            return -1;
        };
    },{}],32:[function(require,module,exports){
        /**
         * Helpers.
         */

        var s = 1000;
        var m = s * 60;
        var h = m * 60;
        var d = h * 24;
        var w = d * 7;
        var y = d * 365.25;

        /**
         * Parse or format the given `val`.
         *
         * Options:
         *
         *  - `long` verbose formatting [false]
         *
         * @param {String|Number} val
         * @param {Object} [options]
         * @throws {Error} throw an error if val is not a non-empty string or a number
         * @return {String|Number}
         * @api public
         */

        module.exports = function(val, options) {
            options = options || {};
            var type = typeof val;
            if (type === 'string' && val.length > 0) {
                return parse(val);
            } else if (type === 'number' && isFinite(val)) {
                return options.long ? fmtLong(val) : fmtShort(val);
            }
            throw new Error(
                'val is not a non-empty string or a valid number. val=' +
                JSON.stringify(val)
            );
        };

        /**
         * Parse the given `str` and return milliseconds.
         *
         * @param {String} str
         * @return {Number}
         * @api private
         */

        function parse(str) {
            str = String(str);
            if (str.length > 100) {
                return;
            }
            var match = /^(-?(?:\d+)?\.?\d+) *(milliseconds?|msecs?|ms|seconds?|secs?|s|minutes?|mins?|m|hours?|hrs?|h|days?|d|weeks?|w|years?|yrs?|y)?$/i.exec(
                str
            );
            if (!match) {
                return;
            }
            var n = parseFloat(match[1]);
            var type = (match[2] || 'ms').toLowerCase();
            switch (type) {
                case 'years':
                case 'year':
                case 'yrs':
                case 'yr':
                case 'y':
                    return n * y;
                case 'weeks':
                case 'week':
                case 'w':
                    return n * w;
                case 'days':
                case 'day':
                case 'd':
                    return n * d;
                case 'hours':
                case 'hour':
                case 'hrs':
                case 'hr':
                case 'h':
                    return n * h;
                case 'minutes':
                case 'minute':
                case 'mins':
                case 'min':
                case 'm':
                    return n * m;
                case 'seconds':
                case 'second':
                case 'secs':
                case 'sec':
                case 's':
                    return n * s;
                case 'milliseconds':
                case 'millisecond':
                case 'msecs':
                case 'msec':
                case 'ms':
                    return n;
                default:
                    return undefined;
            }
        }

        /**
         * Short format for `ms`.
         *
         * @param {Number} ms
         * @return {String}
         * @api private
         */

        function fmtShort(ms) {
            var msAbs = Math.abs(ms);
            if (msAbs >= d) {
                return Math.round(ms / d) + 'd';
            }
            if (msAbs >= h) {
                return Math.round(ms / h) + 'h';
            }
            if (msAbs >= m) {
                return Math.round(ms / m) + 'm';
            }
            if (msAbs >= s) {
                return Math.round(ms / s) + 's';
            }
            return ms + 'ms';
        }

        /**
         * Long format for `ms`.
         *
         * @param {Number} ms
         * @return {String}
         * @api private
         */

        function fmtLong(ms) {
            var msAbs = Math.abs(ms);
            if (msAbs >= d) {
                return plural(ms, msAbs, d, 'day');
            }
            if (msAbs >= h) {
                return plural(ms, msAbs, h, 'hour');
            }
            if (msAbs >= m) {
                return plural(ms, msAbs, m, 'minute');
            }
            if (msAbs >= s) {
                return plural(ms, msAbs, s, 'second');
            }
            return ms + ' ms';
        }

        /**
         * Pluralization helper.
         */

        function plural(ms, msAbs, n, name) {
            var isPlural = msAbs >= n * 1.5;
            return Math.round(ms / n) + ' ' + name + (isPlural ? 's' : '');
        }

    },{}],33:[function(require,module,exports){
        /**
         * Compiles a querystring
         * Returns string representation of the object
         *
         * @param {Object}
         * @api private
         */

        exports.encode = function (obj) {
            var str = '';

            for (var i in obj) {
                if (obj.hasOwnProperty(i)) {
                    if (str.length) str += '&';
                    str += encodeURIComponent(i) + '=' + encodeURIComponent(obj[i]);
                }
            }

            return str;
        };

        /**
         * Parses a simple querystring into an object
         *
         * @param {String} qs
         * @api private
         */

        exports.decode = function(qs){
            var qry = {};
            var pairs = qs.split('&');
            for (var i = 0, l = pairs.length; i < l; i++) {
                var pair = pairs[i].split('=');
                qry[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
            }
            return qry;
        };

    },{}],34:[function(require,module,exports){
        /**
         * Parses an URI
         *
         * @author Steven Levithan <stevenlevithan.com> (MIT license)
         * @api private
         */

        var re = /^(?:(?![^:@]+:[^:@\/]*@)(http|https|ws|wss):\/\/)?((?:(([^:@]*)(?::([^:@]*))?)?@)?((?:[a-f0-9]{0,4}:){2,7}[a-f0-9]{0,4}|[^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/;

        var parts = [
            'source', 'protocol', 'authority', 'userInfo', 'user', 'password', 'host', 'port', 'relative', 'path', 'directory', 'file', 'query', 'anchor'
        ];

        module.exports = function parseuri(str) {
            var src = str,
                b = str.indexOf('['),
                e = str.indexOf(']');

            if (b != -1 && e != -1) {
                str = str.substring(0, b) + str.substring(b, e).replace(/:/g, ';') + str.substring(e, str.length);
            }

            var m = re.exec(str || ''),
                uri = {},
                i = 14;

            while (i--) {
                uri[parts[i]] = m[i] || '';
            }

            if (b != -1 && e != -1) {
                uri.source = src;
                uri.host = uri.host.substring(1, uri.host.length - 1).replace(/;/g, ':');
                uri.authority = uri.authority.replace('[', '').replace(']', '').replace(/;/g, ':');
                uri.ipv6uri = true;
            }

            return uri;
        };

    },{}],35:[function(require,module,exports){
// shim for using process in browser
        var process = module.exports = {};

// cached from whatever global is present so that test runners that stub it
// don't break things.  But we need to wrap it in a try catch in case it is
// wrapped in strict mode code which doesn't define any globals.  It's inside a
// function because try/catches deoptimize in certain engines.

        var cachedSetTimeout;
        var cachedClearTimeout;

        function defaultSetTimout() {
            throw new Error('setTimeout has not been defined');
        }
        function defaultClearTimeout () {
            throw new Error('clearTimeout has not been defined');
        }
        (function () {
            try {
                if (typeof setTimeout === 'function') {
                    cachedSetTimeout = setTimeout;
                } else {
                    cachedSetTimeout = defaultSetTimout;
                }
            } catch (e) {
                cachedSetTimeout = defaultSetTimout;
            }
            try {
                if (typeof clearTimeout === 'function') {
                    cachedClearTimeout = clearTimeout;
                } else {
                    cachedClearTimeout = defaultClearTimeout;
                }
            } catch (e) {
                cachedClearTimeout = defaultClearTimeout;
            }
        } ())
        function runTimeout(fun) {
            if (cachedSetTimeout === setTimeout) {
                //normal enviroments in sane situations
                return setTimeout(fun, 0);
            }
            // if setTimeout wasn't available but was latter defined
            if ((cachedSetTimeout === defaultSetTimout || !cachedSetTimeout) && setTimeout) {
                cachedSetTimeout = setTimeout;
                return setTimeout(fun, 0);
            }
            try {
                // when when somebody has screwed with setTimeout but no I.E. maddness
                return cachedSetTimeout(fun, 0);
            } catch(e){
                try {
                    // When we are in I.E. but the script has been evaled so I.E. doesn't trust the global object when called normally
                    return cachedSetTimeout.call(null, fun, 0);
                } catch(e){
                    // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error
                    return cachedSetTimeout.call(this, fun, 0);
                }
            }


        }
        function runClearTimeout(marker) {
            if (cachedClearTimeout === clearTimeout) {
                //normal enviroments in sane situations
                return clearTimeout(marker);
            }
            // if clearTimeout wasn't available but was latter defined
            if ((cachedClearTimeout === defaultClearTimeout || !cachedClearTimeout) && clearTimeout) {
                cachedClearTimeout = clearTimeout;
                return clearTimeout(marker);
            }
            try {
                // when when somebody has screwed with setTimeout but no I.E. maddness
                return cachedClearTimeout(marker);
            } catch (e){
                try {
                    // When we are in I.E. but the script has been evaled so I.E. doesn't  trust the global object when called normally
                    return cachedClearTimeout.call(null, marker);
                } catch (e){
                    // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error.
                    // Some versions of I.E. have different rules for clearTimeout vs setTimeout
                    return cachedClearTimeout.call(this, marker);
                }
            }



        }
        var queue = [];
        var draining = false;
        var currentQueue;
        var queueIndex = -1;

        function cleanUpNextTick() {
            if (!draining || !currentQueue) {
                return;
            }
            draining = false;
            if (currentQueue.length) {
                queue = currentQueue.concat(queue);
            } else {
                queueIndex = -1;
            }
            if (queue.length) {
                drainQueue();
            }
        }

        function drainQueue() {
            if (draining) {
                return;
            }
            var timeout = runTimeout(cleanUpNextTick);
            draining = true;

            var len = queue.length;
            while(len) {
                currentQueue = queue;
                queue = [];
                while (++queueIndex < len) {
                    if (currentQueue) {
                        currentQueue[queueIndex].run();
                    }
                }
                queueIndex = -1;
                len = queue.length;
            }
            currentQueue = null;
            draining = false;
            runClearTimeout(timeout);
        }

        process.nextTick = function (fun) {
            var args = new Array(arguments.length - 1);
            if (arguments.length > 1) {
                for (var i = 1; i < arguments.length; i++) {
                    args[i - 1] = arguments[i];
                }
            }
            queue.push(new Item(fun, args));
            if (queue.length === 1 && !draining) {
                runTimeout(drainQueue);
            }
        };

// v8 likes predictible objects
        function Item(fun, array) {
            this.fun = fun;
            this.array = array;
        }
        Item.prototype.run = function () {
            this.fun.apply(null, this.array);
        };
        process.title = 'browser';
        process.browser = true;
        process.env = {};
        process.argv = [];
        process.version = ''; // empty string to avoid regexp issues
        process.versions = {};

        function noop() {}

        process.on = noop;
        process.addListener = noop;
        process.once = noop;
        process.off = noop;
        process.removeListener = noop;
        process.removeAllListeners = noop;
        process.emit = noop;
        process.prependListener = noop;
        process.prependOnceListener = noop;

        process.listeners = function (name) { return [] }

        process.binding = function (name) {
            throw new Error('process.binding is not supported');
        };

        process.cwd = function () { return '/' };
        process.chdir = function (dir) {
            throw new Error('process.chdir is not supported');
        };
        process.umask = function() { return 0; };

    },{}],36:[function(require,module,exports){

        /**
         * Module dependencies.
         */

        var url = require('./url');
        var parser = require('socket.io-parser');
        var Manager = require('./manager');
        var debug = require('debug')('socket.io-client');

        /**
         * Module exports.
         */

        module.exports = exports = lookup;

        /**
         * Managers cache.
         */

        var cache = exports.managers = {};

        /**
         * Looks up an existing `Manager` for multiplexing.
         * If the user summons:
         *
         *   `io('http://localhost/a');`
         *   `io('http://localhost/b');`
         *
         * We reuse the existing instance based on same scheme/port/host,
         * and we initialize sockets for each namespace.
         *
         * @api public
         */

        function lookup (uri, opts) {
            if (typeof uri === 'object') {
                opts = uri;
                uri = undefined;
            }

            opts = opts || {};

            var parsed = url(uri);
            var source = parsed.source;
            var id = parsed.id;
            var path = parsed.path;
            var sameNamespace = cache[id] && path in cache[id].nsps;
            var newConnection = opts.forceNew || opts['force new connection'] ||
                false === opts.multiplex || sameNamespace;

            var io;

            if (newConnection) {
                debug('ignoring socket cache for %s', source);
                io = Manager(source, opts);
            } else {
                if (!cache[id]) {
                    debug('new io instance for %s', source);
                    cache[id] = Manager(source, opts);
                }
                io = cache[id];
            }
            if (parsed.query && !opts.query) {
                opts.query = parsed.query;
            }
            return io.socket(parsed.path, opts);
        }

        /**
         * Protocol version.
         *
         * @api public
         */

        exports.protocol = parser.protocol;

        /**
         * `connect`.
         *
         * @param {String} uri
         * @api public
         */

        exports.connect = lookup;

        /**
         * Expose constructors for standalone build.
         *
         * @api public
         */

        exports.Manager = require('./manager');
        exports.Socket = require('./socket');

    },{"./manager":37,"./socket":39,"./url":40,"debug":42,"socket.io-parser":46}],37:[function(require,module,exports){

        /**
         * Module dependencies.
         */

        var eio = require('engine.io-client');
        var Socket = require('./socket');
        var Emitter = require('component-emitter');
        var parser = require('socket.io-parser');
        var on = require('./on');
        var bind = require('component-bind');
        var debug = require('debug')('socket.io-client:manager');
        var indexOf = require('indexof');
        var Backoff = require('backo2');

        /**
         * IE6+ hasOwnProperty
         */

        var has = Object.prototype.hasOwnProperty;

        /**
         * Module exports
         */

        module.exports = Manager;

        /**
         * `Manager` constructor.
         *
         * @param {String} engine instance or engine uri/opts
         * @param {Object} options
         * @api public
         */

        function Manager (uri, opts) {
            if (!(this instanceof Manager)) return new Manager(uri, opts);
            if (uri && ('object' === typeof uri)) {
                opts = uri;
                uri = undefined;
            }
            opts = opts || {};

            opts.path = opts.path || '/socket.io';
            this.nsps = {};
            this.subs = [];
            this.opts = opts;
            this.reconnection(opts.reconnection !== false);
            this.reconnectionAttempts(opts.reconnectionAttempts || Infinity);
            this.reconnectionDelay(opts.reconnectionDelay || 1000);
            this.reconnectionDelayMax(opts.reconnectionDelayMax || 5000);
            this.randomizationFactor(opts.randomizationFactor || 0.5);
            this.backoff = new Backoff({
                min: this.reconnectionDelay(),
                max: this.reconnectionDelayMax(),
                jitter: this.randomizationFactor()
            });
            this.timeout(null == opts.timeout ? 20000 : opts.timeout);
            this.readyState = 'closed';
            this.uri = uri;
            this.connecting = [];
            this.lastPing = null;
            this.encoding = false;
            this.packetBuffer = [];
            var _parser = opts.parser || parser;
            this.encoder = new _parser.Encoder();
            this.decoder = new _parser.Decoder();
            this.autoConnect = opts.autoConnect !== false;
            if (this.autoConnect) this.open();
        }

        /**
         * Propagate given event to sockets and emit on `this`
         *
         * @api private
         */

        Manager.prototype.emitAll = function () {
            this.emit.apply(this, arguments);
            for (var nsp in this.nsps) {
                if (has.call(this.nsps, nsp)) {
                    this.nsps[nsp].emit.apply(this.nsps[nsp], arguments);
                }
            }
        };

        /**
         * Update `socket.id` of all sockets
         *
         * @api private
         */

        Manager.prototype.updateSocketIds = function () {
            for (var nsp in this.nsps) {
                if (has.call(this.nsps, nsp)) {
                    this.nsps[nsp].id = this.generateId(nsp);
                }
            }
        };

        /**
         * generate `socket.id` for the given `nsp`
         *
         * @param {String} nsp
         * @return {String}
         * @api private
         */

        Manager.prototype.generateId = function (nsp) {
            return (nsp === '/' ? '' : (nsp + '#')) + this.engine.id;
        };

        /**
         * Mix in `Emitter`.
         */

        Emitter(Manager.prototype);

        /**
         * Sets the `reconnection` config.
         *
         * @param {Boolean} true/false if it should automatically reconnect
         * @return {Manager} self or value
         * @api public
         */

        Manager.prototype.reconnection = function (v) {
            if (!arguments.length) return this._reconnection;
            this._reconnection = !!v;
            return this;
        };

        /**
         * Sets the reconnection attempts config.
         *
         * @param {Number} max reconnection attempts before giving up
         * @return {Manager} self or value
         * @api public
         */

        Manager.prototype.reconnectionAttempts = function (v) {
            if (!arguments.length) return this._reconnectionAttempts;
            this._reconnectionAttempts = v;
            return this;
        };

        /**
         * Sets the delay between reconnections.
         *
         * @param {Number} delay
         * @return {Manager} self or value
         * @api public
         */

        Manager.prototype.reconnectionDelay = function (v) {
            if (!arguments.length) return this._reconnectionDelay;
            this._reconnectionDelay = v;
            this.backoff && this.backoff.setMin(v);
            return this;
        };

        Manager.prototype.randomizationFactor = function (v) {
            if (!arguments.length) return this._randomizationFactor;
            this._randomizationFactor = v;
            this.backoff && this.backoff.setJitter(v);
            return this;
        };

        /**
         * Sets the maximum delay between reconnections.
         *
         * @param {Number} delay
         * @return {Manager} self or value
         * @api public
         */

        Manager.prototype.reconnectionDelayMax = function (v) {
            if (!arguments.length) return this._reconnectionDelayMax;
            this._reconnectionDelayMax = v;
            this.backoff && this.backoff.setMax(v);
            return this;
        };

        /**
         * Sets the connection timeout. `false` to disable
         *
         * @return {Manager} self or value
         * @api public
         */

        Manager.prototype.timeout = function (v) {
            if (!arguments.length) return this._timeout;
            this._timeout = v;
            return this;
        };

        /**
         * Starts trying to reconnect if reconnection is enabled and we have not
         * started reconnecting yet
         *
         * @api private
         */

        Manager.prototype.maybeReconnectOnOpen = function () {
            // Only try to reconnect if it's the first time we're connecting
            if (!this.reconnecting && this._reconnection && this.backoff.attempts === 0) {
                // keeps reconnection from firing twice for the same reconnection loop
                this.reconnect();
            }
        };

        /**
         * Sets the current transport `socket`.
         *
         * @param {Function} optional, callback
         * @return {Manager} self
         * @api public
         */

        Manager.prototype.open =
            Manager.prototype.connect = function (fn, opts) {
                debug('readyState %s', this.readyState);
                if (~this.readyState.indexOf('open')) return this;

                debug('opening %s', this.uri);
                this.engine = eio(this.uri, this.opts);
                var socket = this.engine;
                var self = this;
                this.readyState = 'opening';
                this.skipReconnect = false;

                // emit `open`
                var openSub = on(socket, 'open', function () {
                    self.onopen();
                    fn && fn();
                });

                // emit `connect_error`
                var errorSub = on(socket, 'error', function (data) {
                    debug('connect_error');
                    self.cleanup();
                    self.readyState = 'closed';
                    self.emitAll('connect_error', data);
                    if (fn) {
                        var err = new Error('Connection error');
                        err.data = data;
                        fn(err);
                    } else {
                        // Only do this if there is no fn to handle the error
                        self.maybeReconnectOnOpen();
                    }
                });

                // emit `connect_timeout`
                if (false !== this._timeout) {
                    var timeout = this._timeout;
                    debug('connect attempt will timeout after %d', timeout);

                    // set timer
                    var timer = setTimeout(function () {
                        debug('connect attempt timed out after %d', timeout);
                        openSub.destroy();
                        socket.close();
                        socket.emit('error', 'timeout');
                        self.emitAll('connect_timeout', timeout);
                    }, timeout);

                    this.subs.push({
                        destroy: function () {
                            clearTimeout(timer);
                        }
                    });
                }

                this.subs.push(openSub);
                this.subs.push(errorSub);

                return this;
            };

        /**
         * Called upon transport open.
         *
         * @api private
         */

        Manager.prototype.onopen = function () {
            debug('open');

            // clear old subs
            this.cleanup();

            // mark as open
            this.readyState = 'open';
            this.emit('open');

            // add new subs
            var socket = this.engine;
            this.subs.push(on(socket, 'data', bind(this, 'ondata')));
            this.subs.push(on(socket, 'ping', bind(this, 'onping')));
            this.subs.push(on(socket, 'pong', bind(this, 'onpong')));
            this.subs.push(on(socket, 'error', bind(this, 'onerror')));
            this.subs.push(on(socket, 'close', bind(this, 'onclose')));
            this.subs.push(on(this.decoder, 'decoded', bind(this, 'ondecoded')));
        };

        /**
         * Called upon a ping.
         *
         * @api private
         */

        Manager.prototype.onping = function () {
            this.lastPing = new Date();
            this.emitAll('ping');
        };

        /**
         * Called upon a packet.
         *
         * @api private
         */

        Manager.prototype.onpong = function () {
            this.emitAll('pong', new Date() - this.lastPing);
        };

        /**
         * Called with data.
         *
         * @api private
         */

        Manager.prototype.ondata = function (data) {
            this.decoder.add(data);
        };

        /**
         * Called when parser fully decodes a packet.
         *
         * @api private
         */

        Manager.prototype.ondecoded = function (packet) {
            this.emit('packet', packet);
        };

        /**
         * Called upon socket error.
         *
         * @api private
         */

        Manager.prototype.onerror = function (err) {
            debug('error', err);
            this.emitAll('error', err);
        };

        /**
         * Creates a new socket for the given `nsp`.
         *
         * @return {Socket}
         * @api public
         */

        Manager.prototype.socket = function (nsp, opts) {
            var socket = this.nsps[nsp];
            if (!socket) {
                socket = new Socket(this, nsp, opts);
                this.nsps[nsp] = socket;
                var self = this;
                socket.on('connecting', onConnecting);
                socket.on('connect', function () {
                    socket.id = self.generateId(nsp);
                });

                if (this.autoConnect) {
                    // manually call here since connecting event is fired before listening
                    onConnecting();
                }
            }

            function onConnecting () {
                if (!~indexOf(self.connecting, socket)) {
                    self.connecting.push(socket);
                }
            }

            return socket;
        };

        /**
         * Called upon a socket close.
         *
         * @param {Socket} socket
         */

        Manager.prototype.destroy = function (socket) {
            var index = indexOf(this.connecting, socket);
            if (~index) this.connecting.splice(index, 1);
            if (this.connecting.length) return;

            this.close();
        };

        /**
         * Writes a packet.
         *
         * @param {Object} packet
         * @api private
         */

        Manager.prototype.packet = function (packet) {
            debug('writing packet %j', packet);
            var self = this;
            if (packet.query && packet.type === 0) packet.nsp += '?' + packet.query;

            if (!self.encoding) {
                // encode, then write to engine with result
                self.encoding = true;
                this.encoder.encode(packet, function (encodedPackets) {
                    for (var i = 0; i < encodedPackets.length; i++) {
                        self.engine.write(encodedPackets[i], packet.options);
                    }
                    self.encoding = false;
                    self.processPacketQueue();
                });
            } else { // add packet to the queue
                self.packetBuffer.push(packet);
            }
        };

        /**
         * If packet buffer is non-empty, begins encoding the
         * next packet in line.
         *
         * @api private
         */

        Manager.prototype.processPacketQueue = function () {
            if (this.packetBuffer.length > 0 && !this.encoding) {
                var pack = this.packetBuffer.shift();
                this.packet(pack);
            }
        };

        /**
         * Clean up transport subscriptions and packet buffer.
         *
         * @api private
         */

        Manager.prototype.cleanup = function () {
            debug('cleanup');

            var subsLength = this.subs.length;
            for (var i = 0; i < subsLength; i++) {
                var sub = this.subs.shift();
                sub.destroy();
            }

            this.packetBuffer = [];
            this.encoding = false;
            this.lastPing = null;

            this.decoder.destroy();
        };

        /**
         * Close the current socket.
         *
         * @api private
         */

        Manager.prototype.close =
            Manager.prototype.disconnect = function () {
                debug('disconnect');
                this.skipReconnect = true;
                this.reconnecting = false;
                if ('opening' === this.readyState) {
                    // `onclose` will not fire because
                    // an open event never happened
                    this.cleanup();
                }
                this.backoff.reset();
                this.readyState = 'closed';
                if (this.engine) this.engine.close();
            };

        /**
         * Called upon engine close.
         *
         * @api private
         */

        Manager.prototype.onclose = function (reason) {
            debug('onclose');

            this.cleanup();
            this.backoff.reset();
            this.readyState = 'closed';
            this.emit('close', reason);

            if (this._reconnection && !this.skipReconnect) {
                this.reconnect();
            }
        };

        /**
         * Attempt a reconnection.
         *
         * @api private
         */

        Manager.prototype.reconnect = function () {
            if (this.reconnecting || this.skipReconnect) return this;

            var self = this;

            if (this.backoff.attempts >= this._reconnectionAttempts) {
                debug('reconnect failed');
                this.backoff.reset();
                this.emitAll('reconnect_failed');
                this.reconnecting = false;
            } else {
                var delay = this.backoff.duration();
                debug('will wait %dms before reconnect attempt', delay);

                this.reconnecting = true;
                var timer = setTimeout(function () {
                    if (self.skipReconnect) return;

                    debug('attempting reconnect');
                    self.emitAll('reconnect_attempt', self.backoff.attempts);
                    self.emitAll('reconnecting', self.backoff.attempts);

                    // check again for the case socket closed in above events
                    if (self.skipReconnect) return;

                    self.open(function (err) {
                        if (err) {
                            debug('reconnect attempt error');
                            self.reconnecting = false;
                            self.reconnect();
                            self.emitAll('reconnect_error', err.data);
                        } else {
                            debug('reconnect success');
                            self.onreconnect();
                        }
                    });
                }, delay);

                this.subs.push({
                    destroy: function () {
                        clearTimeout(timer);
                    }
                });
            }
        };

        /**
         * Called upon successful reconnect.
         *
         * @api private
         */

        Manager.prototype.onreconnect = function () {
            var attempt = this.backoff.attempts;
            this.reconnecting = false;
            this.backoff.reset();
            this.updateSocketIds();
            this.emitAll('reconnect', attempt);
        };

    },{"./on":38,"./socket":39,"backo2":3,"component-bind":9,"component-emitter":41,"debug":42,"engine.io-client":13,"indexof":31,"socket.io-parser":46}],38:[function(require,module,exports){

        /**
         * Module exports.
         */

        module.exports = on;

        /**
         * Helper for subscriptions.
         *
         * @param {Object|EventEmitter} obj with `Emitter` mixin or `EventEmitter`
         * @param {String} event name
         * @param {Function} callback
         * @api public
         */

        function on (obj, ev, fn) {
            obj.on(ev, fn);
            return {
                destroy: function () {
                    obj.removeListener(ev, fn);
                }
            };
        }

    },{}],39:[function(require,module,exports){

        /**
         * Module dependencies.
         */

        var parser = require('socket.io-parser');
        var Emitter = require('component-emitter');
        var toArray = require('to-array');
        var on = require('./on');
        var bind = require('component-bind');
        var debug = require('debug')('socket.io-client:socket');
        var parseqs = require('parseqs');
        var hasBin = require('has-binary2');

        /**
         * Module exports.
         */

        module.exports = exports = Socket;

        /**
         * Internal events (blacklisted).
         * These events can't be emitted by the user.
         *
         * @api private
         */

        var events = {
            connect: 1,
            connect_error: 1,
            connect_timeout: 1,
            connecting: 1,
            disconnect: 1,
            error: 1,
            reconnect: 1,
            reconnect_attempt: 1,
            reconnect_failed: 1,
            reconnect_error: 1,
            reconnecting: 1,
            ping: 1,
            pong: 1
        };

        /**
         * Shortcut to `Emitter#emit`.
         */

        var emit = Emitter.prototype.emit;

        /**
         * `Socket` constructor.
         *
         * @api public
         */

        function Socket (io, nsp, opts) {
            this.io = io;
            this.nsp = nsp;
            this.json = this; // compat
            this.ids = 0;
            this.acks = {};
            this.receiveBuffer = [];
            this.sendBuffer = [];
            this.connected = false;
            this.disconnected = true;
            this.flags = {};
            if (opts && opts.query) {
                this.query = opts.query;
            }
            if (this.io.autoConnect) this.open();
        }

        /**
         * Mix in `Emitter`.
         */

        Emitter(Socket.prototype);

        /**
         * Subscribe to open, close and packet events
         *
         * @api private
         */

        Socket.prototype.subEvents = function () {
            if (this.subs) return;

            var io = this.io;
            this.subs = [
                on(io, 'open', bind(this, 'onopen')),
                on(io, 'packet', bind(this, 'onpacket')),
                on(io, 'close', bind(this, 'onclose'))
            ];
        };

        /**
         * "Opens" the socket.
         *
         * @api public
         */

        Socket.prototype.open =
            Socket.prototype.connect = function () {
                if (this.connected) return this;

                this.subEvents();
                this.io.open(); // ensure open
                if ('open' === this.io.readyState) this.onopen();
                this.emit('connecting');
                return this;
            };

        /**
         * Sends a `message` event.
         *
         * @return {Socket} self
         * @api public
         */

        Socket.prototype.send = function () {
            var args = toArray(arguments);
            args.unshift('message');
            this.emit.apply(this, args);
            return this;
        };

        /**
         * Override `emit`.
         * If the event is in `events`, it's emitted normally.
         *
         * @param {String} event name
         * @return {Socket} self
         * @api public
         */

        Socket.prototype.emit = function (ev) {
            if (events.hasOwnProperty(ev)) {
                emit.apply(this, arguments);
                return this;
            }

            var args = toArray(arguments);
            var packet = {
                type: (this.flags.binary !== undefined ? this.flags.binary : hasBin(args)) ? parser.BINARY_EVENT : parser.EVENT,
                data: args
            };

            packet.options = {};
            packet.options.compress = !this.flags || false !== this.flags.compress;

            // event ack callback
            if ('function' === typeof args[args.length - 1]) {
                debug('emitting packet with ack id %d', this.ids);
                this.acks[this.ids] = args.pop();
                packet.id = this.ids++;
            }

            if (this.connected) {
                this.packet(packet);
            } else {
                this.sendBuffer.push(packet);
            }

            this.flags = {};

            return this;
        };

        /**
         * Sends a packet.
         *
         * @param {Object} packet
         * @api private
         */

        Socket.prototype.packet = function (packet) {
            packet.nsp = this.nsp;
            this.io.packet(packet);
        };

        /**
         * Called upon engine `open`.
         *
         * @api private
         */

        Socket.prototype.onopen = function () {
            debug('transport is open - connecting');

            // write connect packet if necessary
            if ('/' !== this.nsp) {
                if (this.query) {
                    var query = typeof this.query === 'object' ? parseqs.encode(this.query) : this.query;
                    debug('sending connect packet with query %s', query);
                    this.packet({type: parser.CONNECT, query: query});
                } else {
                    this.packet({type: parser.CONNECT});
                }
            }
        };

        /**
         * Called upon engine `close`.
         *
         * @param {String} reason
         * @api private
         */

        Socket.prototype.onclose = function (reason) {
            debug('close (%s)', reason);
            this.connected = false;
            this.disconnected = true;
            delete this.id;
            this.emit('disconnect', reason);
        };

        /**
         * Called with socket packet.
         *
         * @param {Object} packet
         * @api private
         */

        Socket.prototype.onpacket = function (packet) {
            var sameNamespace = packet.nsp === this.nsp;
            var rootNamespaceError = packet.type === parser.ERROR && packet.nsp === '/';

            if (!sameNamespace && !rootNamespaceError) return;

            switch (packet.type) {
                case parser.CONNECT:
                    this.onconnect();
                    break;

                case parser.EVENT:
                    this.onevent(packet);
                    break;

                case parser.BINARY_EVENT:
                    this.onevent(packet);
                    break;

                case parser.ACK:
                    this.onack(packet);
                    break;

                case parser.BINARY_ACK:
                    this.onack(packet);
                    break;

                case parser.DISCONNECT:
                    this.ondisconnect();
                    break;

                case parser.ERROR:
                    this.emit('error', packet.data);
                    break;
            }
        };

        /**
         * Called upon a server event.
         *
         * @param {Object} packet
         * @api private
         */

        Socket.prototype.onevent = function (packet) {
            var args = packet.data || [];
            debug('emitting event %j', args);

            if (null != packet.id) {
                debug('attaching ack callback to event');
                args.push(this.ack(packet.id));
            }

            if (this.connected) {
                emit.apply(this, args);
            } else {
                this.receiveBuffer.push(args);
            }
        };

        /**
         * Produces an ack callback to emit with an event.
         *
         * @api private
         */

        Socket.prototype.ack = function (id) {
            var self = this;
            var sent = false;
            return function () {
                // prevent double callbacks
                if (sent) return;
                sent = true;
                var args = toArray(arguments);
                debug('sending ack %j', args);

                self.packet({
                    type: hasBin(args) ? parser.BINARY_ACK : parser.ACK,
                    id: id,
                    data: args
                });
            };
        };

        /**
         * Called upon a server acknowlegement.
         *
         * @param {Object} packet
         * @api private
         */

        Socket.prototype.onack = function (packet) {
            var ack = this.acks[packet.id];
            if ('function' === typeof ack) {
                debug('calling ack %s with %j', packet.id, packet.data);
                ack.apply(this, packet.data);
                delete this.acks[packet.id];
            } else {
                debug('bad ack %s', packet.id);
            }
        };

        /**
         * Called upon server connect.
         *
         * @api private
         */

        Socket.prototype.onconnect = function () {
            this.connected = true;
            this.disconnected = false;
            this.emit('connect');
            this.emitBuffered();
        };

        /**
         * Emit buffered events (received and emitted).
         *
         * @api private
         */

        Socket.prototype.emitBuffered = function () {
            var i;
            for (i = 0; i < this.receiveBuffer.length; i++) {
                emit.apply(this, this.receiveBuffer[i]);
            }
            this.receiveBuffer = [];

            for (i = 0; i < this.sendBuffer.length; i++) {
                this.packet(this.sendBuffer[i]);
            }
            this.sendBuffer = [];
        };

        /**
         * Called upon server disconnect.
         *
         * @api private
         */

        Socket.prototype.ondisconnect = function () {
            debug('server disconnect (%s)', this.nsp);
            this.destroy();
            this.onclose('io server disconnect');
        };

        /**
         * Called upon forced client/server side disconnections,
         * this method ensures the manager stops tracking us and
         * that reconnections don't get triggered for this.
         *
         * @api private.
         */

        Socket.prototype.destroy = function () {
            if (this.subs) {
                // clean subscriptions to avoid reconnections
                for (var i = 0; i < this.subs.length; i++) {
                    this.subs[i].destroy();
                }
                this.subs = null;
            }

            this.io.destroy(this);
        };

        /**
         * Disconnects the socket manually.
         *
         * @return {Socket} self
         * @api public
         */

        Socket.prototype.close =
            Socket.prototype.disconnect = function () {
                if (this.connected) {
                    debug('performing disconnect (%s)', this.nsp);
                    this.packet({ type: parser.DISCONNECT });
                }

                // remove socket from pool
                this.destroy();

                if (this.connected) {
                    // fire events
                    this.onclose('io client disconnect');
                }
                return this;
            };

        /**
         * Sets the compress flag.
         *
         * @param {Boolean} if `true`, compresses the sending data
         * @return {Socket} self
         * @api public
         */

        Socket.prototype.compress = function (compress) {
            this.flags.compress = compress;
            return this;
        };

        /**
         * Sets the binary flag
         *
         * @param {Boolean} whether the emitted data contains binary
         * @return {Socket} self
         * @api public
         */

        Socket.prototype.binary = function (binary) {
            this.flags.binary = binary;
            return this;
        };

    },{"./on":38,"component-bind":9,"component-emitter":41,"debug":42,"has-binary2":27,"parseqs":33,"socket.io-parser":46,"to-array":51}],40:[function(require,module,exports){

        /**
         * Module dependencies.
         */

        var parseuri = require('parseuri');
        var debug = require('debug')('socket.io-client:url');

        /**
         * Module exports.
         */

        module.exports = url;

        /**
         * URL parser.
         *
         * @param {String} url
         * @param {Object} An object meant to mimic window.location.
         *                 Defaults to window.location.
         * @api public
         */

        function url (uri, loc) {
            var obj = uri;

            // default to window.location
            loc = loc || (typeof location !== 'undefined' && location);
            if (null == uri) uri = loc.protocol + '//' + loc.host;

            // relative path support
            if ('string' === typeof uri) {
                if ('/' === uri.charAt(0)) {
                    if ('/' === uri.charAt(1)) {
                        uri = loc.protocol + uri;
                    } else {
                        uri = loc.host + uri;
                    }
                }

                if (!/^(https?|wss?):\/\//.test(uri)) {
                    debug('protocol-less url %s', uri);
                    if ('undefined' !== typeof loc) {
                        uri = loc.protocol + '//' + uri;
                    } else {
                        uri = 'https://' + uri;
                    }
                }

                // parse
                debug('parse %s', uri);
                obj = parseuri(uri);
            }

            // make sure we treat `localhost:80` and `localhost` equally
            if (!obj.port) {
                if (/^(http|ws)$/.test(obj.protocol)) {
                    obj.port = '80';
                } else if (/^(http|ws)s$/.test(obj.protocol)) {
                    obj.port = '443';
                }
            }

            obj.path = obj.path || '/';

            var ipv6 = obj.host.indexOf(':') !== -1;
            var host = ipv6 ? '[' + obj.host + ']' : obj.host;

            // define unique id
            obj.id = obj.protocol + '://' + host + ':' + obj.port;
            // define href
            obj.href = obj.protocol + '://' + host + (loc && loc.port === obj.port ? '' : (':' + obj.port));

            return obj;
        }

    },{"debug":42,"parseuri":34}],41:[function(require,module,exports){

        /**
         * Expose `Emitter`.
         */

        if (typeof module !== 'undefined') {
            module.exports = Emitter;
        }

        /**
         * Initialize a new `Emitter`.
         *
         * @api public
         */

        function Emitter(obj) {
            if (obj) return mixin(obj);
        };

        /**
         * Mixin the emitter properties.
         *
         * @param {Object} obj
         * @return {Object}
         * @api private
         */

        function mixin(obj) {
            for (var key in Emitter.prototype) {
                obj[key] = Emitter.prototype[key];
            }
            return obj;
        }

        /**
         * Listen on the given `event` with `fn`.
         *
         * @param {String} event
         * @param {Function} fn
         * @return {Emitter}
         * @api public
         */

        Emitter.prototype.on =
            Emitter.prototype.addEventListener = function(event, fn){
                this._callbacks = this._callbacks || {};
                (this._callbacks['$' + event] = this._callbacks['$' + event] || [])
                    .push(fn);
                return this;
            };

        /**
         * Adds an `event` listener that will be invoked a single
         * time then automatically removed.
         *
         * @param {String} event
         * @param {Function} fn
         * @return {Emitter}
         * @api public
         */

        Emitter.prototype.once = function(event, fn){
            function on() {
                this.off(event, on);
                fn.apply(this, arguments);
            }

            on.fn = fn;
            this.on(event, on);
            return this;
        };

        /**
         * Remove the given callback for `event` or all
         * registered callbacks.
         *
         * @param {String} event
         * @param {Function} fn
         * @return {Emitter}
         * @api public
         */

        Emitter.prototype.off =
            Emitter.prototype.removeListener =
                Emitter.prototype.removeAllListeners =
                    Emitter.prototype.removeEventListener = function(event, fn){
                        this._callbacks = this._callbacks || {};

                        // all
                        if (0 == arguments.length) {
                            this._callbacks = {};
                            return this;
                        }

                        // specific event
                        var callbacks = this._callbacks['$' + event];
                        if (!callbacks) return this;

                        // remove all handlers
                        if (1 == arguments.length) {
                            delete this._callbacks['$' + event];
                            return this;
                        }

                        // remove specific handler
                        var cb;
                        for (var i = 0; i < callbacks.length; i++) {
                            cb = callbacks[i];
                            if (cb === fn || cb.fn === fn) {
                                callbacks.splice(i, 1);
                                break;
                            }
                        }
                        return this;
                    };

        /**
         * Emit `event` with the given args.
         *
         * @param {String} event
         * @param {Mixed} ...
         * @return {Emitter}
         */

        Emitter.prototype.emit = function(event){
            this._callbacks = this._callbacks || {};
            var args = [].slice.call(arguments, 1)
                , callbacks = this._callbacks['$' + event];

            if (callbacks) {
                callbacks = callbacks.slice(0);
                for (var i = 0, len = callbacks.length; i < len; ++i) {
                    callbacks[i].apply(this, args);
                }
            }

            return this;
        };

        /**
         * Return array of callbacks for `event`.
         *
         * @param {String} event
         * @return {Array}
         * @api public
         */

        Emitter.prototype.listeners = function(event){
            this._callbacks = this._callbacks || {};
            return this._callbacks['$' + event] || [];
        };

        /**
         * Check if this emitter has `event` handlers.
         *
         * @param {String} event
         * @return {Boolean}
         * @api public
         */

        Emitter.prototype.hasListeners = function(event){
            return !! this.listeners(event).length;
        };

    },{}],42:[function(require,module,exports){
        arguments[4][22][0].apply(exports,arguments)
    },{"./common":43,"_process":35,"dup":22}],43:[function(require,module,exports){
        arguments[4][23][0].apply(exports,arguments)
    },{"dup":23,"ms":32}],44:[function(require,module,exports){
        arguments[4][28][0].apply(exports,arguments)
    },{"dup":28}],45:[function(require,module,exports){
        /*global Blob,File*/

        /**
         * Module requirements
         */

        var isArray = require('isarray');
        var isBuf = require('./is-buffer');
        var toString = Object.prototype.toString;
        var withNativeBlob = typeof Blob === 'function' || (typeof Blob !== 'undefined' && toString.call(Blob) === '[object BlobConstructor]');
        var withNativeFile = typeof File === 'function' || (typeof File !== 'undefined' && toString.call(File) === '[object FileConstructor]');

        /**
         * Replaces every Buffer | ArrayBuffer in packet with a numbered placeholder.
         * Anything with blobs or files should be fed through removeBlobs before coming
         * here.
         *
         * @param {Object} packet - socket.io event packet
         * @return {Object} with deconstructed packet and list of buffers
         * @api public
         */

        exports.deconstructPacket = function(packet) {
            var buffers = [];
            var packetData = packet.data;
            var pack = packet;
            pack.data = _deconstructPacket(packetData, buffers);
            pack.attachments = buffers.length; // number of binary 'attachments'
            return {packet: pack, buffers: buffers};
        };

        function _deconstructPacket(data, buffers) {
            if (!data) return data;

            if (isBuf(data)) {
                var placeholder = { _placeholder: true, num: buffers.length };
                buffers.push(data);
                return placeholder;
            } else if (isArray(data)) {
                var newData = new Array(data.length);
                for (var i = 0; i < data.length; i++) {
                    newData[i] = _deconstructPacket(data[i], buffers);
                }
                return newData;
            } else if (typeof data === 'object' && !(data instanceof Date)) {
                var newData = {};
                for (var key in data) {
                    newData[key] = _deconstructPacket(data[key], buffers);
                }
                return newData;
            }
            return data;
        }

        /**
         * Reconstructs a binary packet from its placeholder packet and buffers
         *
         * @param {Object} packet - event packet with placeholders
         * @param {Array} buffers - binary buffers to put in placeholder positions
         * @return {Object} reconstructed packet
         * @api public
         */

        exports.reconstructPacket = function(packet, buffers) {
            packet.data = _reconstructPacket(packet.data, buffers);
            packet.attachments = undefined; // no longer useful
            return packet;
        };

        function _reconstructPacket(data, buffers) {
            if (!data) return data;

            if (data && data._placeholder) {
                return buffers[data.num]; // appropriate buffer (should be natural order anyway)
            } else if (isArray(data)) {
                for (var i = 0; i < data.length; i++) {
                    data[i] = _reconstructPacket(data[i], buffers);
                }
            } else if (typeof data === 'object') {
                for (var key in data) {
                    data[key] = _reconstructPacket(data[key], buffers);
                }
            }

            return data;
        }

        /**
         * Asynchronously removes Blobs or Files from data via
         * FileReader's readAsArrayBuffer method. Used before encoding
         * data as msgpack. Calls callback with the blobless data.
         *
         * @param {Object} data
         * @param {Function} callback
         * @api private
         */

        exports.removeBlobs = function(data, callback) {
            function _removeBlobs(obj, curKey, containingObject) {
                if (!obj) return obj;

                // convert any blob
                if ((withNativeBlob && obj instanceof Blob) ||
                    (withNativeFile && obj instanceof File)) {
                    pendingBlobs++;

                    // async filereader
                    var fileReader = new FileReader();
                    fileReader.onload = function() { // this.result == arraybuffer
                        if (containingObject) {
                            containingObject[curKey] = this.result;
                        }
                        else {
                            bloblessData = this.result;
                        }

                        // if nothing pending its callback time
                        if(! --pendingBlobs) {
                            callback(bloblessData);
                        }
                    };

                    fileReader.readAsArrayBuffer(obj); // blob -> arraybuffer
                } else if (isArray(obj)) { // handle array
                    for (var i = 0; i < obj.length; i++) {
                        _removeBlobs(obj[i], i, obj);
                    }
                } else if (typeof obj === 'object' && !isBuf(obj)) { // and object
                    for (var key in obj) {
                        _removeBlobs(obj[key], key, obj);
                    }
                }
            }

            var pendingBlobs = 0;
            var bloblessData = data;
            _removeBlobs(bloblessData);
            if (!pendingBlobs) {
                callback(bloblessData);
            }
        };

    },{"./is-buffer":47,"isarray":44}],46:[function(require,module,exports){

        /**
         * Module dependencies.
         */

        var debug = require('debug')('socket.io-parser');
        var Emitter = require('component-emitter');
        var binary = require('./binary');
        var isArray = require('isarray');
        var isBuf = require('./is-buffer');

        /**
         * Protocol version.
         *
         * @api public
         */

        exports.protocol = 4;

        /**
         * Packet types.
         *
         * @api public
         */

        exports.types = [
            'CONNECT',
            'DISCONNECT',
            'EVENT',
            'ACK',
            'ERROR',
            'BINARY_EVENT',
            'BINARY_ACK'
        ];

        /**
         * Packet type `connect`.
         *
         * @api public
         */

        exports.CONNECT = 0;

        /**
         * Packet type `disconnect`.
         *
         * @api public
         */

        exports.DISCONNECT = 1;

        /**
         * Packet type `event`.
         *
         * @api public
         */

        exports.EVENT = 2;

        /**
         * Packet type `ack`.
         *
         * @api public
         */

        exports.ACK = 3;

        /**
         * Packet type `error`.
         *
         * @api public
         */

        exports.ERROR = 4;

        /**
         * Packet type 'binary event'
         *
         * @api public
         */

        exports.BINARY_EVENT = 5;

        /**
         * Packet type `binary ack`. For acks with binary arguments.
         *
         * @api public
         */

        exports.BINARY_ACK = 6;

        /**
         * Encoder constructor.
         *
         * @api public
         */

        exports.Encoder = Encoder;

        /**
         * Decoder constructor.
         *
         * @api public
         */

        exports.Decoder = Decoder;

        /**
         * A socket.io Encoder instance
         *
         * @api public
         */

        function Encoder() {}

        var ERROR_PACKET = exports.ERROR + '"encode error"';

        /**
         * Encode a packet as a single string if non-binary, or as a
         * buffer sequence, depending on packet type.
         *
         * @param {Object} obj - packet object
         * @param {Function} callback - function to handle encodings (likely engine.write)
         * @return Calls callback with Array of encodings
         * @api public
         */

        Encoder.prototype.encode = function(obj, callback){
            debug('encoding packet %j', obj);

            if (exports.BINARY_EVENT === obj.type || exports.BINARY_ACK === obj.type) {
                encodeAsBinary(obj, callback);
            } else {
                var encoding = encodeAsString(obj);
                callback([encoding]);
            }
        };

        /**
         * Encode packet as string.
         *
         * @param {Object} packet
         * @return {String} encoded
         * @api private
         */

        function encodeAsString(obj) {

            // first is type
            var str = '' + obj.type;

            // attachments if we have them
            if (exports.BINARY_EVENT === obj.type || exports.BINARY_ACK === obj.type) {
                str += obj.attachments + '-';
            }

            // if we have a namespace other than `/`
            // we append it followed by a comma `,`
            if (obj.nsp && '/' !== obj.nsp) {
                str += obj.nsp + ',';
            }

            // immediately followed by the id
            if (null != obj.id) {
                str += obj.id;
            }

            // json data
            if (null != obj.data) {
                var payload = tryStringify(obj.data);
                if (payload !== false) {
                    str += payload;
                } else {
                    return ERROR_PACKET;
                }
            }

            debug('encoded %j as %s', obj, str);
            return str;
        }

        function tryStringify(str) {
            try {
                return JSON.stringify(str);
            } catch(e){
                return false;
            }
        }

        /**
         * Encode packet as 'buffer sequence' by removing blobs, and
         * deconstructing packet into object with placeholders and
         * a list of buffers.
         *
         * @param {Object} packet
         * @return {Buffer} encoded
         * @api private
         */

        function encodeAsBinary(obj, callback) {

            function writeEncoding(bloblessData) {
                var deconstruction = binary.deconstructPacket(bloblessData);
                var pack = encodeAsString(deconstruction.packet);
                var buffers = deconstruction.buffers;

                buffers.unshift(pack); // add packet info to beginning of data list
                callback(buffers); // write all the buffers
            }

            binary.removeBlobs(obj, writeEncoding);
        }

        /**
         * A socket.io Decoder instance
         *
         * @return {Object} decoder
         * @api public
         */

        function Decoder() {
            this.reconstructor = null;
        }

        /**
         * Mix in `Emitter` with Decoder.
         */

        Emitter(Decoder.prototype);

        /**
         * Decodes an encoded packet string into packet JSON.
         *
         * @param {String} obj - encoded packet
         * @return {Object} packet
         * @api public
         */

        Decoder.prototype.add = function(obj) {
            var packet;
            if (typeof obj === 'string') {
                packet = decodeString(obj);
                if (exports.BINARY_EVENT === packet.type || exports.BINARY_ACK === packet.type) { // binary packet's json
                    this.reconstructor = new BinaryReconstructor(packet);

                    // no attachments, labeled binary but no binary data to follow
                    if (this.reconstructor.reconPack.attachments === 0) {
                        this.emit('decoded', packet);
                    }
                } else { // non-binary full packet
                    this.emit('decoded', packet);
                }
            } else if (isBuf(obj) || obj.base64) { // raw binary data
                if (!this.reconstructor) {
                    throw new Error('got binary data when not reconstructing a packet');
                } else {
                    packet = this.reconstructor.takeBinaryData(obj);
                    if (packet) { // received final buffer
                        this.reconstructor = null;
                        this.emit('decoded', packet);
                    }
                }
            } else {
                throw new Error('Unknown type: ' + obj);
            }
        };

        /**
         * Decode a packet String (JSON data)
         *
         * @param {String} str
         * @return {Object} packet
         * @api private
         */

        function decodeString(str) {
            var i = 0;
            // look up type
            var p = {
                type: Number(str.charAt(0))
            };

            if (null == exports.types[p.type]) {
                return error('unknown packet type ' + p.type);
            }

            // look up attachments if type binary
            if (exports.BINARY_EVENT === p.type || exports.BINARY_ACK === p.type) {
                var buf = '';
                while (str.charAt(++i) !== '-') {
                    buf += str.charAt(i);
                    if (i == str.length) break;
                }
                if (buf != Number(buf) || str.charAt(i) !== '-') {
                    throw new Error('Illegal attachments');
                }
                p.attachments = Number(buf);
            }

            // look up namespace (if any)
            if ('/' === str.charAt(i + 1)) {
                p.nsp = '';
                while (++i) {
                    var c = str.charAt(i);
                    if (',' === c) break;
                    p.nsp += c;
                    if (i === str.length) break;
                }
            } else {
                p.nsp = '/';
            }

            // look up id
            var next = str.charAt(i + 1);
            if ('' !== next && Number(next) == next) {
                p.id = '';
                while (++i) {
                    var c = str.charAt(i);
                    if (null == c || Number(c) != c) {
                        --i;
                        break;
                    }
                    p.id += str.charAt(i);
                    if (i === str.length) break;
                }
                p.id = Number(p.id);
            }

            // look up json data
            if (str.charAt(++i)) {
                var payload = tryParse(str.substr(i));
                var isPayloadValid = payload !== false && (p.type === exports.ERROR || isArray(payload));
                if (isPayloadValid) {
                    p.data = payload;
                } else {
                    return error('invalid payload');
                }
            }

            debug('decoded %s as %j', str, p);
            return p;
        }

        function tryParse(str) {
            try {
                return JSON.parse(str);
            } catch(e){
                return false;
            }
        }

        /**
         * Deallocates a parser's resources
         *
         * @api public
         */

        Decoder.prototype.destroy = function() {
            if (this.reconstructor) {
                this.reconstructor.finishedReconstruction();
            }
        };

        /**
         * A manager of a binary event's 'buffer sequence'. Should
         * be constructed whenever a packet of type BINARY_EVENT is
         * decoded.
         *
         * @param {Object} packet
         * @return {BinaryReconstructor} initialized reconstructor
         * @api private
         */

        function BinaryReconstructor(packet) {
            this.reconPack = packet;
            this.buffers = [];
        }

        /**
         * Method to be called when binary data received from connection
         * after a BINARY_EVENT packet.
         *
         * @param {Buffer | ArrayBuffer} binData - the raw binary data received
         * @return {null | Object} returns null if more binary data is expected or
         *   a reconstructed packet object if all buffers have been received.
         * @api private
         */

        BinaryReconstructor.prototype.takeBinaryData = function(binData) {
            this.buffers.push(binData);
            if (this.buffers.length === this.reconPack.attachments) { // done with buffer list
                var packet = binary.reconstructPacket(this.reconPack, this.buffers);
                this.finishedReconstruction();
                return packet;
            }
            return null;
        };

        /**
         * Cleans up binary packet reconstruction variables.
         *
         * @api private
         */

        BinaryReconstructor.prototype.finishedReconstruction = function() {
            this.reconPack = null;
            this.buffers = [];
        };

        function error(msg) {
            return {
                type: exports.ERROR,
                data: 'parser error: ' + msg
            };
        }

    },{"./binary":45,"./is-buffer":47,"component-emitter":41,"debug":48,"isarray":44}],47:[function(require,module,exports){
        (function (Buffer){

            module.exports = isBuf;

            var withNativeBuffer = typeof Buffer === 'function' && typeof Buffer.isBuffer === 'function';
            var withNativeArrayBuffer = typeof ArrayBuffer === 'function';

            var isView = function (obj) {
                return typeof ArrayBuffer.isView === 'function' ? ArrayBuffer.isView(obj) : (obj.buffer instanceof ArrayBuffer);
            };

            /**
             * Returns true if obj is a buffer or an arraybuffer.
             *
             * @api private
             */

            function isBuf(obj) {
                return (withNativeBuffer && Buffer.isBuffer(obj)) ||
                    (withNativeArrayBuffer && (obj instanceof ArrayBuffer || isView(obj)));
            }

        }).call(this,require("buffer").Buffer)
    },{"buffer":8}],48:[function(require,module,exports){
        (function (process){
            /**
             * This is the web browser implementation of `debug()`.
             *
             * Expose `debug()` as the module.
             */

            exports = module.exports = require('./debug');
            exports.log = log;
            exports.formatArgs = formatArgs;
            exports.save = save;
            exports.load = load;
            exports.useColors = useColors;
            exports.storage = 'undefined' != typeof chrome
            && 'undefined' != typeof chrome.storage
                ? chrome.storage.local
                : localstorage();

            /**
             * Colors.
             */

            exports.colors = [
                '#0000CC', '#0000FF', '#0033CC', '#0033FF', '#0066CC', '#0066FF', '#0099CC',
                '#0099FF', '#00CC00', '#00CC33', '#00CC66', '#00CC99', '#00CCCC', '#00CCFF',
                '#3300CC', '#3300FF', '#3333CC', '#3333FF', '#3366CC', '#3366FF', '#3399CC',
                '#3399FF', '#33CC00', '#33CC33', '#33CC66', '#33CC99', '#33CCCC', '#33CCFF',
                '#6600CC', '#6600FF', '#6633CC', '#6633FF', '#66CC00', '#66CC33', '#9900CC',
                '#9900FF', '#9933CC', '#9933FF', '#99CC00', '#99CC33', '#CC0000', '#CC0033',
                '#CC0066', '#CC0099', '#CC00CC', '#CC00FF', '#CC3300', '#CC3333', '#CC3366',
                '#CC3399', '#CC33CC', '#CC33FF', '#CC6600', '#CC6633', '#CC9900', '#CC9933',
                '#CCCC00', '#CCCC33', '#FF0000', '#FF0033', '#FF0066', '#FF0099', '#FF00CC',
                '#FF00FF', '#FF3300', '#FF3333', '#FF3366', '#FF3399', '#FF33CC', '#FF33FF',
                '#FF6600', '#FF6633', '#FF9900', '#FF9933', '#FFCC00', '#FFCC33'
            ];

            /**
             * Currently only WebKit-based Web Inspectors, Firefox >= v31,
             * and the Firebug extension (any Firefox version) are known
             * to support "%c" CSS customizations.
             *
             * TODO: add a `localStorage` variable to explicitly enable/disable colors
             */

            function useColors() {
                // NB: In an Electron preload script, document will be defined but not fully
                // initialized. Since we know we're in Chrome, we'll just detect this case
                // explicitly
                if (typeof window !== 'undefined' && window.process && window.process.type === 'renderer') {
                    return true;
                }

                // Internet Explorer and Edge do not support colors.
                if (typeof navigator !== 'undefined' && navigator.userAgent && navigator.userAgent.toLowerCase().match(/(edge|trident)\/(\d+)/)) {
                    return false;
                }

                // is webkit? http://stackoverflow.com/a/16459606/376773
                // document is undefined in react-native: https://github.com/facebook/react-native/pull/1632
                return (typeof document !== 'undefined' && document.documentElement && document.documentElement.style && document.documentElement.style.WebkitAppearance) ||
                    // is firebug? http://stackoverflow.com/a/398120/376773
                    (typeof window !== 'undefined' && window.console && (window.console.firebug || (window.console.exception && window.console.table))) ||
                    // is firefox >= v31?
                    // https://developer.mozilla.org/en-US/docs/Tools/Web_Console#Styling_messages
                    (typeof navigator !== 'undefined' && navigator.userAgent && navigator.userAgent.toLowerCase().match(/firefox\/(\d+)/) && parseInt(RegExp.$1, 10) >= 31) ||
                    // double check webkit in userAgent just in case we are in a worker
                    (typeof navigator !== 'undefined' && navigator.userAgent && navigator.userAgent.toLowerCase().match(/applewebkit\/(\d+)/));
            }

            /**
             * Map %j to `JSON.stringify()`, since no Web Inspectors do that by default.
             */

            exports.formatters.j = function(v) {
                try {
                    return JSON.stringify(v);
                } catch (err) {
                    return '[UnexpectedJSONParseError]: ' + err.message;
                }
            };


            /**
             * Colorize log arguments if enabled.
             *
             * @api public
             */

            function formatArgs(args) {
                var useColors = this.useColors;

                args[0] = (useColors ? '%c' : '')
                    + this.namespace
                    + (useColors ? ' %c' : ' ')
                    + args[0]
                    + (useColors ? '%c ' : ' ')
                    + '+' + exports.humanize(this.diff);

                if (!useColors) return;

                var c = 'color: ' + this.color;
                args.splice(1, 0, c, 'color: inherit')

                // the final "%c" is somewhat tricky, because there could be other
                // arguments passed either before or after the %c, so we need to
                // figure out the correct index to insert the CSS into
                var index = 0;
                var lastC = 0;
                args[0].replace(/%[a-zA-Z%]/g, function(match) {
                    if ('%%' === match) return;
                    index++;
                    if ('%c' === match) {
                        // we only are interested in the *last* %c
                        // (the user may have provided their own)
                        lastC = index;
                    }
                });

                args.splice(lastC, 0, c);
            }

            /**
             * Invokes `console.log()` when available.
             * No-op when `console.log` is not a "function".
             *
             * @api public
             */

            function log() {
                // this hackery is required for IE8/9, where
                // the `console.log` function doesn't have 'apply'
                return 'object' === typeof console
                    && console.log
                    && Function.prototype.apply.call(console.log, console, arguments);
            }

            /**
             * Save `namespaces`.
             *
             * @param {String} namespaces
             * @api private
             */

            function save(namespaces) {
                try {
                    if (null == namespaces) {
                        exports.storage.removeItem('debug');
                    } else {
                        exports.storage.debug = namespaces;
                    }
                } catch(e) {}
            }

            /**
             * Load `namespaces`.
             *
             * @return {String} returns the previously persisted debug modes
             * @api private
             */

            function load() {
                var r;
                try {
                    r = exports.storage.debug;
                } catch(e) {}

                // If debug isn't set in LS, and we're in Electron, try to load $DEBUG
                if (!r && typeof process !== 'undefined' && 'env' in process) {
                    r = process.env.DEBUG;
                }

                return r;
            }

            /**
             * Enable namespaces listed in `localStorage.debug` initially.
             */

            exports.enable(load());

            /**
             * Localstorage attempts to return the localstorage.
             *
             * This is necessary because safari throws
             * when a user disables cookies/localstorage
             * and you attempt to access it.
             *
             * @return {LocalStorage}
             * @api private
             */

            function localstorage() {
                try {
                    return window.localStorage;
                } catch (e) {}
            }

        }).call(this,require('_process'))
    },{"./debug":49,"_process":35}],49:[function(require,module,exports){

        /**
         * This is the common logic for both the Node.js and web browser
         * implementations of `debug()`.
         *
         * Expose `debug()` as the module.
         */

        exports = module.exports = createDebug.debug = createDebug['default'] = createDebug;
        exports.coerce = coerce;
        exports.disable = disable;
        exports.enable = enable;
        exports.enabled = enabled;
        exports.humanize = require('ms');

        /**
         * Active `debug` instances.
         */
        exports.instances = [];

        /**
         * The currently active debug mode names, and names to skip.
         */

        exports.names = [];
        exports.skips = [];

        /**
         * Map of special "%n" handling functions, for the debug "format" argument.
         *
         * Valid key names are a single, lower or upper-case letter, i.e. "n" and "N".
         */

        exports.formatters = {};

        /**
         * Select a color.
         * @param {String} namespace
         * @return {Number}
         * @api private
         */

        function selectColor(namespace) {
            var hash = 0, i;

            for (i in namespace) {
                hash  = ((hash << 5) - hash) + namespace.charCodeAt(i);
                hash |= 0; // Convert to 32bit integer
            }

            return exports.colors[Math.abs(hash) % exports.colors.length];
        }

        /**
         * Create a debugger with the given `namespace`.
         *
         * @param {String} namespace
         * @return {Function}
         * @api public
         */

        function createDebug(namespace) {

            var prevTime;

            function debug() {
                // disabled?
                if (!debug.enabled) return;

                var self = debug;

                // set `diff` timestamp
                var curr = +new Date();
                var ms = curr - (prevTime || curr);
                self.diff = ms;
                self.prev = prevTime;
                self.curr = curr;
                prevTime = curr;

                // turn the `arguments` into a proper Array
                var args = new Array(arguments.length);
                for (var i = 0; i < args.length; i++) {
                    args[i] = arguments[i];
                }

                args[0] = exports.coerce(args[0]);

                if ('string' !== typeof args[0]) {
                    // anything else let's inspect with %O
                    args.unshift('%O');
                }

                // apply any `formatters` transformations
                var index = 0;
                args[0] = args[0].replace(/%([a-zA-Z%])/g, function(match, format) {
                    // if we encounter an escaped % then don't increase the array index
                    if (match === '%%') return match;
                    index++;
                    var formatter = exports.formatters[format];
                    if ('function' === typeof formatter) {
                        var val = args[index];
                        match = formatter.call(self, val);

                        // now we need to remove `args[index]` since it's inlined in the `format`
                        args.splice(index, 1);
                        index--;
                    }
                    return match;
                });

                // apply env-specific formatting (colors, etc.)
                exports.formatArgs.call(self, args);

                var logFn = debug.log || exports.log || console.log.bind(console);
                logFn.apply(self, args);
            }

            debug.namespace = namespace;
            debug.enabled = exports.enabled(namespace);
            debug.useColors = exports.useColors();
            debug.color = selectColor(namespace);
            debug.destroy = destroy;

            // env-specific initialization logic for debug instances
            if ('function' === typeof exports.init) {
                exports.init(debug);
            }

            exports.instances.push(debug);

            return debug;
        }

        function destroy () {
            var index = exports.instances.indexOf(this);
            if (index !== -1) {
                exports.instances.splice(index, 1);
                return true;
            } else {
                return false;
            }
        }

        /**
         * Enables a debug mode by namespaces. This can include modes
         * separated by a colon and wildcards.
         *
         * @param {String} namespaces
         * @api public
         */

        function enable(namespaces) {
            exports.save(namespaces);

            exports.names = [];
            exports.skips = [];

            var i;
            var split = (typeof namespaces === 'string' ? namespaces : '').split(/[\s,]+/);
            var len = split.length;

            for (i = 0; i < len; i++) {
                if (!split[i]) continue; // ignore empty strings
                namespaces = split[i].replace(/\*/g, '.*?');
                if (namespaces[0] === '-') {
                    exports.skips.push(new RegExp('^' + namespaces.substr(1) + '$'));
                } else {
                    exports.names.push(new RegExp('^' + namespaces + '$'));
                }
            }

            for (i = 0; i < exports.instances.length; i++) {
                var instance = exports.instances[i];
                instance.enabled = exports.enabled(instance.namespace);
            }
        }

        /**
         * Disable debug output.
         *
         * @api public
         */

        function disable() {
            exports.enable('');
        }

        /**
         * Returns true if the given mode name is enabled, false otherwise.
         *
         * @param {String} name
         * @return {Boolean}
         * @api public
         */

        function enabled(name) {
            if (name[name.length - 1] === '*') {
                return true;
            }
            var i, len;
            for (i = 0, len = exports.skips.length; i < len; i++) {
                if (exports.skips[i].test(name)) {
                    return false;
                }
            }
            for (i = 0, len = exports.names.length; i < len; i++) {
                if (exports.names[i].test(name)) {
                    return true;
                }
            }
            return false;
        }

        /**
         * Coerce `val`.
         *
         * @param {Mixed} val
         * @return {Mixed}
         * @api private
         */

        function coerce(val) {
            if (val instanceof Error) return val.stack || val.message;
            return val;
        }

    },{"ms":50}],50:[function(require,module,exports){
        /**
         * Helpers.
         */

        var s = 1000;
        var m = s * 60;
        var h = m * 60;
        var d = h * 24;
        var y = d * 365.25;

        /**
         * Parse or format the given `val`.
         *
         * Options:
         *
         *  - `long` verbose formatting [false]
         *
         * @param {String|Number} val
         * @param {Object} [options]
         * @throws {Error} throw an error if val is not a non-empty string or a number
         * @return {String|Number}
         * @api public
         */

        module.exports = function(val, options) {
            options = options || {};
            var type = typeof val;
            if (type === 'string' && val.length > 0) {
                return parse(val);
            } else if (type === 'number' && isNaN(val) === false) {
                return options.long ? fmtLong(val) : fmtShort(val);
            }
            throw new Error(
                'val is not a non-empty string or a valid number. val=' +
                JSON.stringify(val)
            );
        };

        /**
         * Parse the given `str` and return milliseconds.
         *
         * @param {String} str
         * @return {Number}
         * @api private
         */

        function parse(str) {
            str = String(str);
            if (str.length > 100) {
                return;
            }
            var match = /^((?:\d+)?\.?\d+) *(milliseconds?|msecs?|ms|seconds?|secs?|s|minutes?|mins?|m|hours?|hrs?|h|days?|d|years?|yrs?|y)?$/i.exec(
                str
            );
            if (!match) {
                return;
            }
            var n = parseFloat(match[1]);
            var type = (match[2] || 'ms').toLowerCase();
            switch (type) {
                case 'years':
                case 'year':
                case 'yrs':
                case 'yr':
                case 'y':
                    return n * y;
                case 'days':
                case 'day':
                case 'd':
                    return n * d;
                case 'hours':
                case 'hour':
                case 'hrs':
                case 'hr':
                case 'h':
                    return n * h;
                case 'minutes':
                case 'minute':
                case 'mins':
                case 'min':
                case 'm':
                    return n * m;
                case 'seconds':
                case 'second':
                case 'secs':
                case 'sec':
                case 's':
                    return n * s;
                case 'milliseconds':
                case 'millisecond':
                case 'msecs':
                case 'msec':
                case 'ms':
                    return n;
                default:
                    return undefined;
            }
        }

        /**
         * Short format for `ms`.
         *
         * @param {Number} ms
         * @return {String}
         * @api private
         */

        function fmtShort(ms) {
            if (ms >= d) {
                return Math.round(ms / d) + 'd';
            }
            if (ms >= h) {
                return Math.round(ms / h) + 'h';
            }
            if (ms >= m) {
                return Math.round(ms / m) + 'm';
            }
            if (ms >= s) {
                return Math.round(ms / s) + 's';
            }
            return ms + 'ms';
        }

        /**
         * Long format for `ms`.
         *
         * @param {Number} ms
         * @return {String}
         * @api private
         */

        function fmtLong(ms) {
            return plural(ms, d, 'day') ||
                plural(ms, h, 'hour') ||
                plural(ms, m, 'minute') ||
                plural(ms, s, 'second') ||
                ms + ' ms';
        }

        /**
         * Pluralization helper.
         */

        function plural(ms, n, name) {
            if (ms < n) {
                return;
            }
            if (ms < n * 1.5) {
                return Math.floor(ms / n) + ' ' + name;
            }
            return Math.ceil(ms / n) + ' ' + name + 's';
        }

    },{}],51:[function(require,module,exports){
        module.exports = toArray

        function toArray(list, index) {
            var array = []

            index = index || 0

            for (var i = index || 0; i < list.length; i++) {
                array[i - index] = list[i]
            }

            return array
        }

    },{}],52:[function(require,module,exports){
        'use strict';

        var alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_'.split('')
            , length = 64
            , map = {}
            , seed = 0
            , i = 0
            , prev;

        /**
         * Return a string representing the specified number.
         *
         * @param {Number} num The number to convert.
         * @returns {String} The string representation of the number.
         * @api public
         */
        function encode(num) {
            var encoded = '';

            do {
                encoded = alphabet[num % length] + encoded;
                num = Math.floor(num / length);
            } while (num > 0);

            return encoded;
        }

        /**
         * Return the integer value specified by the given string.
         *
         * @param {String} str The string to convert.
         * @returns {Number} The integer value represented by the string.
         * @api public
         */
        function decode(str) {
            var decoded = 0;

            for (i = 0; i < str.length; i++) {
                decoded = decoded * length + map[str.charAt(i)];
            }

            return decoded;
        }

        /**
         * Yeast: A tiny growing id generator.
         *
         * @returns {String} A unique id.
         * @api public
         */
        function yeast() {
            var now = encode(+new Date());

            if (now !== prev) return seed = 0, prev = now;
            return now +'.'+ encode(seed++);
        }

//
// Map each character to its index.
//
        for (; i < length; i++) map[alphabet[i]] = i;

//
// Expose the `yeast`, `encode` and `decode` functions.
//
        yeast.encode = encode;
        yeast.decode = decode;
        module.exports = yeast;

    },{}],53:[function(require,module,exports){
        (function () {
            'use strict';

            var CollaborativeEditing = function () {
                function CollaborativeEditing(editor, user) {
                    var _this = this;
                    this.editor = editor;
                    this.cursors = new Map();
                    this.selections = new Map();
                    this.colors = new Map();
                    this.myUser = user;
                    this.clients = new Set();
                    this.io = require('socket.io-client');
                    this.ioClient = this.io.connect(user.socketUrl);
                    this.ioClient.on('connect', function () {
                        _this.ioClient.emit('connect_client', _this.myUser);
                    });
                    this.ioClient.on('update_clients', function (array) {
                        var map = new Map(array);
                        map.forEach(function (value, key) {
                            if (user.name !== value.name && !_this.clients.has(key)) {
                                _this.setUser(value);
                                _this.clients.add(key);
                            }
                        });
                    });
                    this.ioClient.on('delete_client', function (disconnected) {
                        _this.removeUser(disconnected.user);
                        _this.clients.delete(disconnected.key);
                    });
                    this.ioClient.on('update_cursor', function (obj) {
                        var parsedObj = JSON.parse(obj);
                        if (parsedObj.user.name !== _this.myUser.name) {
                            _this.deleteUserInteractions(parsedObj.user);
                            var appendRange = new Range();
                            var node = _this.editor.getDoc().querySelector('.mce-content-body').children[parsedObj.nodeIndex];
                            var textNode = _this.findTextNode(node, parsedObj.content);
                            var offset = 0;
                            if (parsedObj.range.endOffset > textNode.textContent.length) {
                                offset++;
                            }
                            appendRange.setStart(textNode, parsedObj.range.startOffset - offset);
                            appendRange.setEnd(textNode, parsedObj.range.endOffset - offset);
                            _this.moveCursor(appendRange, parsedObj.user, node);
                        }
                    });
                    this.ioClient.on('update_selection', function (obj) {
                        var parsedObj = JSON.parse(obj);
                        if (parsedObj.user.name !== _this.myUser.name) {
                            _this.deleteUserInteractions(parsedObj.user);
                            var appendRange = new Range();
                            var startNode = _this.editor.getDoc().querySelector('.mce-content-body').children[parsedObj.startNodeIndex];
                            var endNode = _this.editor.getDoc().querySelector('.mce-content-body').children[parsedObj.endNodeIndex];
                            var startTextContent = _this.findTextNode(startNode, parsedObj.startContent);
                            var endTextContent = _this.findTextNode(endNode, parsedObj.endContent);
                            if (!endTextContent) {
                                endTextContent = startTextContent;
                            }
                            var startOffset = 0;
                            if (parsedObj.range.startOffset > startTextContent.textContent.length) {
                                startOffset++;
                            }
                            var endOffset = 0;
                            if (parsedObj.range.endOffset > endTextContent.textContent.length) {
                                endOffset++;
                            }
                            appendRange.setStart(startTextContent, parsedObj.range.startOffset - startOffset);
                            appendRange.setEnd(endTextContent, parsedObj.range.endOffset - endOffset);
                            _this.selections.set(_this.hash(user.name), {
                                range: appendRange,
                                user: parsedObj.user
                            });
                            _this.moveSelection(Array.from(appendRange.getClientRects()), parsedObj.user);
                        }
                    });
                    this.ioClient.on('update_content', function (message) {
                        if (message.username !== _this.myUser.name) {
                            _this.editor.setContent(message.content);
                        }
                    });
                }
                CollaborativeEditing.prototype.onResize = function () {
                    var _this = this;
                    this.cursors.forEach(function (_a, key) {
                        var range = _a.range, node = _a.node;
                        var element = _this.editor.getDoc().querySelector('#cursor-' + key);
                        var bounding = range.getBoundingClientRect();
                        if (bounding.top === 0) {
                            element.style.top = node.offsetTop + 'px';
                            element.style.left = '1em';
                        } else {
                            element.style.top = _this.editor.getDoc().children[0].scrollTop + bounding.top + 'px';
                            element.style.left = bounding.left + 'px';
                        }
                    });
                    this.selections.forEach(function (_a, key) {
                        var range = _a.range, user = _a.user;
                        _this.deleteUserInteractions(user);
                        _this.moveSelection(Array.from(range.getClientRects()), user);
                    });
                };
                CollaborativeEditing.prototype.updateContent = function (event) {
                    this.ioClient.emit('set_content', {
                        username: this.myUser.name,
                        content: this.editor.getContent()
                    });
                    this.onResize();
                };
                CollaborativeEditing.prototype.setUser = function (user) {
                    var exists = true;
                    var _loop_1 = function () {
                        var randomColor = this_1.getRandomColor();
                        var isColorPresent = false;
                        this_1.colors.forEach(function (color, key) {
                            if (randomColor === color) {
                                isColorPresent = true;
                            }
                        });
                        if (!isColorPresent) {
                            this_1.colors.set(user.name, randomColor);
                            return 'break';
                        }
                    };
                    var this_1 = this;
                    while (exists) {
                        var state_1 = _loop_1();
                        if (state_1 === 'break')
                            break;
                    }
                    var container = document.createElement('div');
                    container.id = 'container-' + this.hash(user.name);
                    container.style.display = 'inline-flex';
                    container.style.position = 'relative';
                    container.style.cursor = 'pointer';
                    container.style.width = '35px';
                    container.style.alignItems = 'center';
                    container.addEventListener('mouseover', function (event) {
                        event.target.children[1].style.visibility = 'visible';
                    });
                    container.addEventListener('mouseout', function (event) {
                        event.target.children[1].style.visibility = 'hidden';
                    });
                    if (user.photoUrl && user.photoUrl.length > 0 && user.photoUrl !== 'undefined') {
                        var avatar = document.createElement('img');
                        avatar.id = 'avatar-' + user.name;
                        avatar.src = user.photoUrl;
                        avatar.style.height = '38px';
                        avatar.style.width = '35px';
                        avatar.style.borderRadius = '35px';
                        avatar.style.pointerEvents = 'none';
                        container.appendChild(avatar);
                    } else {
                        var avatar = document.createElement('div');
                        avatar.id = 'avatar-' + user.name;
                        avatar.style.height = '30px';
                        avatar.style.width = '30px';
                        avatar.style.borderRadius = '30px';
                        avatar.style.backgroundColor = this.colors.get(user.name);
                        avatar.style.pointerEvents = 'none';
                        avatar.style.textAlign = 'center';
                        avatar.style.color = 'white';
                        avatar.style.opacity = '70%';
                        avatar.style.display = 'flex';
                        avatar.style.alignItems = 'center';
                        avatar.style.justifyContent = 'center';
                        avatar.innerText = user.name.charAt(0);
                        container.appendChild(avatar);
                    }
                    var text = document.createElement('span');
                    text.id = 'text-' + user.name;
                    text.textContent = user.name;
                    text.style.visibility = 'hidden';
                    text.style.position = 'absolute';
                    text.style.pointerEvents = 'none';
                    text.style.verticalAlign = 'center';
                    text.style.backgroundColor = this.colors.get(user.name);
                    text.style.opacity = '85%';
                    text.style.color = 'white';
                    text.style.fontSize = '9px';
                    text.style.paddingTop = '9px';
                    text.style.paddingBottom = '9px';
                    text.style.width = user.name.length * 7 + 'px';
                    text.style.textAlign = 'center';
                    text.style.left = '35px';
                    text.style.borderRadius = '5px';
                    text.style.zIndex = '10';
                    container.appendChild(text);
                    var userContainer = document.querySelector(this.editor.getParam('selector')).parentElement.querySelector('#user-container');
                    userContainer.appendChild(container);
                };
                CollaborativeEditing.prototype.onListen = function (event, user) {
                    var selection = this.editor.getDoc().getSelection();

                    if (selection.rangeCount >= 1) {
                        var range = selection.getRangeAt(0);
                    }else{
                        return;
                    }
                    Object.defineProperties(range, {
                        startOffset: {
                            value: range.startOffset,
                            enumerable: true
                        },
                        endOffset: {
                            value: range.endOffset,
                            enumerable: true
                        }
                    });
                    var startNode = range.startContainer;
                    while (startNode.parentElement.id !== 'tinymce') {
                        startNode = startNode.parentElement;
                    }
                    var sibling = startNode.previousSibling;
                    var startIndex = 0;
                    while (sibling !== null) {
                        startIndex++;
                        sibling = sibling.previousSibling;
                    }
                    var endNode = range.endContainer;
                    while (endNode.parentElement.id !== 'tinymce') {
                        endNode = endNode.parentElement;
                    }
                    var endIndex = 0;
                    sibling = endNode.previousSibling;
                    while (sibling !== null) {
                        endIndex++;
                        sibling = sibling.previousSibling;
                    }
                    this.deleteUserInteractions(user);
                    if (range.startOffset === range.endOffset) {
                        this.ioClient.emit('set_cursor', JSON.stringify({
                            range: range,
                            user: user,
                            nodeIndex: startIndex,
                            content: range.startContainer.textContent
                        }));
                    } else {
                        this.ioClient.emit('set_selection', JSON.stringify({
                            range: range,
                            startNodeIndex: startIndex,
                            endNodeIndex: endIndex,
                            user: user,
                            startContent: range.startContainer.textContent,
                            endContent: range.endContainer.textContent
                        }));
                    }
                };
                CollaborativeEditing.prototype.findTextNode = function (node, content) {
                    var textNode;
                    if (node.textContent.trim().indexOf(content.trim()) > -1 && node.childNodes.length === 0) {
                        return node;
                    }
                    for (var _i = 0, _a = Array.from(node.childNodes); _i < _a.length; _i++) {
                        var currentNode = _a[_i];
                        if (currentNode.textContent.trim().indexOf(content.trim()) > -1 && currentNode.childNodes.length === 0) {
                            return currentNode;
                        }
                        textNode = this.findTextNode(currentNode, content);
                        if (textNode) {
                            return textNode;
                        }
                    }
                    return textNode;
                };
                CollaborativeEditing.prototype.removeUser = function (user) {
                    this.deleteUserInteractions(user);
                    var userContainer = document.querySelector(this.editor.getParam('selector')).parentElement.querySelector('#user-container');
                    userContainer.querySelector('#container-' + this.hash(user.name)).remove();
                };
                CollaborativeEditing.prototype.deleteUserInteractions = function (user) {
                    var cursor = this.editor.getDoc().querySelector('#cursor-' + this.hash(user.name));
                    if (cursor) {
                        cursor.remove();
                    }
                    var selections = this.editor.getDoc().querySelectorAll('#selection-' + this.hash(user.name));
                    if (selections && selections.length > 0) {
                        selections.forEach(function (selection) {
                            selection.remove();
                        });
                    }
                };
                CollaborativeEditing.prototype.moveCursor = function (range, user, node) {
                    var userId = this.hash(user.name);
                    var bounding = range.getBoundingClientRect();
                    var cursorId = 'cursor-' + userId;
                    var cursor = document.createElement('div');
                    cursor.id = cursorId;
                    cursor.style.position = 'absolute';
                    cursor.style.zIndex = '-1';
                    cursor.style.position = 'block';
                    var cursorBar = document.createElement('div');
                    cursorBar.style.backgroundColor = this.colors.get(user.name);
                    cursorBar.style.opacity = '60%';
                    cursorBar.style.width = '2.5px';
                    cursorBar.style.height = bounding.height ? bounding.height + 'px' : '1em';
                    cursorBar.style.position = 'relative';
                    var cursorText = document.createElement('span');
                    cursorText.style.backgroundColor = this.colors.get(user.name);
                    cursorText.style.color = 'white';
                    cursorText.style.fontSize = '10px';
                    cursorText.style.height = '12px';
                    cursorText.textContent = user.name;
                    cursorText.style.position = 'absolute';
                    cursorText.style.top = '-12px';
                    cursorBar.appendChild(cursorText);
                    cursor.appendChild(cursorBar);
                    if (bounding.top === 0) {
                        cursor.style.top = node.offsetTop + 'px';
                        cursor.style.left = '1em';
                    } else {
                        cursor.style.top = this.editor.getDoc().children[0].scrollTop + bounding.top + 'px';
                        cursor.style.left = bounding.left + 'px';
                    }
                    var body = this.editor.getDoc().body;
                    body.parentElement.insertBefore(cursor, body);
                    this.cursors.set(userId, {
                        range: range,
                        node: node
                    });
                    this.selections.delete(userId);
                };
                CollaborativeEditing.prototype.moveSelection = function (ranges, user) {
                    var body = this.editor.getDoc().body;
                    var selectionId = 'selection-' + this.hash(user.name);
                    for (var i = 0; i < ranges.length; i++) {
                        var selection = document.createElement('span');
                        selection.id = selectionId;
                        selection.style.backgroundColor = this.colors.get(user.name);
                        selection.style.opacity = '50%';
                        selection.style.position = 'absolute';
                        selection.style.width = ranges[i].width + 'px';
                        selection.style.height = ranges[i].height + 'px';
                        selection.style.top = ranges[i].y + this.editor.getBody().parentElement.scrollTop + 'px';
                        selection.style.left = ranges[i].x + 'px';
                        selection.style.zIndex = '-1';
                        if (i === 0) {
                            var selectionText = document.createElement('span');
                            selectionText.style.backgroundColor = this.colors.get(user.name);
                            selectionText.style.color = 'white';
                            selectionText.style.fontSize = '10px';
                            selectionText.style.height = '14px';
                            selectionText.textContent = user.name;
                            selectionText.style.position = 'absolute';
                            selectionText.style.top = '-14px';
                            selection.appendChild(selectionText);
                        }
                        body.parentElement.insertBefore(selection, body);
                    }
                    this.cursors.delete(this.hash(user.name));
                };
                CollaborativeEditing.prototype.hash = function (username) {
                    var hash = 0;
                    if (username.length === 0) {
                        return hash;
                    }
                    for (var i = 0; i < username.length; i++) {
                        var char = username.charCodeAt(i);
                        hash = (hash << 5) - hash + char;
                        hash = hash & hash;
                    }
                    return hash;
                };
                CollaborativeEditing.prototype.getRandomColor = function () {
                    var letters = '0123456789ABCDEF';
                    var color = '#';
                    for (var i = 0; i < 6; i++) {
                        color += letters[Math.floor(Math.random() * 16)];
                    }
                    return color;
                };
                return CollaborativeEditing;
            }();

            var collaborativeMap = new Map();
            tinymce.create('tinymce.plugins.Budwriter', {
                Budwriter: function (editor, url) {
                    editor.on('Load', function () {
                        var textEditor = document.querySelector("textarea.complexEditor");
                        var userContainer = document.createElement('div');
                        userContainer.id = 'user-container';
                        userContainer.style.display = 'flex';
                        userContainer.style.alignItems = 'center';
                        userContainer.style.marginBottom = '10px';
                        textEditor.insertAdjacentElement('beforebegin', userContainer);

                        var user = JSON.parse(JSON.stringify(editor.getParam('budwriter')));
                        var edit = new CollaborativeEditing(editor, user);
                        collaborativeMap.set("textarea.complexEditor", edit);
                        edit.setUser(user);
                        window.onresize = function (event) {
                            edit.onResize();
                        };
                    });
                    editor.on('click', function (event) {
                        var user = JSON.parse(JSON.stringify(editor.getParam('budwriter')));
                        collaborativeMap.get(editor.getParam('selector')).onListen(event, user);
                    });
                    editor.on('NodeChange', function (event) {
                        var colab = collaborativeMap.get(editor.getParam('selector'));
                        var user = JSON.parse(JSON.stringify(editor.getParam('budwriter')));
                        if (colab) {
                            colab.updateContent(event);
                            colab.onListen(event, user);
                        }
                    });
                    editor.on('input', function (event) {
                        var colab = collaborativeMap.get(editor.getParam('selector'));
                        var user = JSON.parse(JSON.stringify(editor.getParam('budwriter')));
                        if (colab) {
                            colab.updateContent(event);
                            colab.onListen(event, user);
                        }
                    });
                }
            });
            function Plugin () {
                tinymce.PluginManager.add('budwriter', tinymce.plugins.Budwriter);
            }

            Plugin();

        }());

    },{"socket.io-client":36}]},{},[53]);

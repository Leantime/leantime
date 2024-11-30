"use strict";
(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else {
		var a = factory();
		for(var i in a) (typeof exports === 'object' ? exports : root)[i] = a[i];
	}
})(self, () => {
return (self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["/js/components/selects.module"],{

/***/ "./node_modules/@orchidjs/sifter/dist/esm/sifter.js":
/*!**********************************************************!*\
  !*** ./node_modules/@orchidjs/sifter/dist/esm/sifter.js ***!
  \**********************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Sifter: () => (/* binding */ Sifter),
/* harmony export */   cmp: () => (/* reexport safe */ _utils_js__WEBPACK_IMPORTED_MODULE_0__.cmp),
/* harmony export */   getAttr: () => (/* reexport safe */ _utils_js__WEBPACK_IMPORTED_MODULE_0__.getAttr),
/* harmony export */   getAttrNesting: () => (/* reexport safe */ _utils_js__WEBPACK_IMPORTED_MODULE_0__.getAttrNesting),
/* harmony export */   getPattern: () => (/* reexport safe */ _orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_1__.getPattern),
/* harmony export */   iterate: () => (/* reexport safe */ _utils_js__WEBPACK_IMPORTED_MODULE_0__.iterate),
/* harmony export */   propToArray: () => (/* reexport safe */ _utils_js__WEBPACK_IMPORTED_MODULE_0__.propToArray),
/* harmony export */   scoreValue: () => (/* reexport safe */ _utils_js__WEBPACK_IMPORTED_MODULE_0__.scoreValue)
/* harmony export */ });
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./utils.js */ "./node_modules/@orchidjs/sifter/dist/esm/utils.js");
/* harmony import */ var _orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @orchidjs/unicode-variants */ "./node_modules/@orchidjs/unicode-variants/dist/esm/index.js");
/* harmony import */ var _types_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./types.js */ "./node_modules/@orchidjs/sifter/dist/esm/types.js");
/**
 * sifter.js
 * Copyright (c) 2013–2020 Brian Reavis & contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 * @author Brian Reavis <brian@thirdroute.com>
 */


class Sifter {
    items; // []|{};
    settings;
    /**
     * Textually searches arrays and hashes of objects
     * by property (or multiple properties). Designed
     * specifically for autocomplete.
     *
     */
    constructor(items, settings) {
        this.items = items;
        this.settings = settings || { diacritics: true };
    }
    ;
    /**
     * Splits a search string into an array of individual
     * regexps to be used to match results.
     *
     */
    tokenize(query, respect_word_boundaries, weights) {
        if (!query || !query.length)
            return [];
        const tokens = [];
        const words = query.split(/\s+/);
        var field_regex;
        if (weights) {
            field_regex = new RegExp('^(' + Object.keys(weights).map(_orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_1__.escape_regex).join('|') + ')\:(.*)$');
        }
        words.forEach((word) => {
            let field_match;
            let field = null;
            let regex = null;
            // look for "field:query" tokens
            if (field_regex && (field_match = word.match(field_regex))) {
                field = field_match[1];
                word = field_match[2];
            }
            if (word.length > 0) {
                if (this.settings.diacritics) {
                    regex = (0,_orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_1__.getPattern)(word) || null;
                }
                else {
                    regex = (0,_orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_1__.escape_regex)(word);
                }
                if (regex && respect_word_boundaries)
                    regex = "\\b" + regex;
            }
            tokens.push({
                string: word,
                regex: regex ? new RegExp(regex, 'iu') : null,
                field: field,
            });
        });
        return tokens;
    }
    ;
    /**
     * Returns a function to be used to score individual results.
     *
     * Good matches will have a higher score than poor matches.
     * If an item is not a match, 0 will be returned by the function.
     *
     * @returns {T.ScoreFn}
     */
    getScoreFunction(query, options) {
        var search = this.prepareSearch(query, options);
        return this._getScoreFunction(search);
    }
    /**
     * @returns {T.ScoreFn}
     *
     */
    _getScoreFunction(search) {
        const tokens = search.tokens, token_count = tokens.length;
        if (!token_count) {
            return function () { return 0; };
        }
        const fields = search.options.fields, weights = search.weights, field_count = fields.length, getAttrFn = search.getAttrFn;
        if (!field_count) {
            return function () { return 1; };
        }
        /**
         * Calculates the score of an object
         * against the search query.
         *
         */
        const scoreObject = (function () {
            if (field_count === 1) {
                return function (token, data) {
                    const field = fields[0].field;
                    return (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.scoreValue)(getAttrFn(data, field), token, weights[field] || 1);
                };
            }
            return function (token, data) {
                var sum = 0;
                // is the token specific to a field?
                if (token.field) {
                    const value = getAttrFn(data, token.field);
                    if (!token.regex && value) {
                        sum += (1 / field_count);
                    }
                    else {
                        sum += (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.scoreValue)(value, token, 1);
                    }
                }
                else {
                    (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.iterate)(weights, (weight, field) => {
                        sum += (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.scoreValue)(getAttrFn(data, field), token, weight);
                    });
                }
                return sum / field_count;
            };
        })();
        if (token_count === 1) {
            return function (data) {
                return scoreObject(tokens[0], data);
            };
        }
        if (search.options.conjunction === 'and') {
            return function (data) {
                var score, sum = 0;
                for (let token of tokens) {
                    score = scoreObject(token, data);
                    if (score <= 0)
                        return 0;
                    sum += score;
                }
                return sum / token_count;
            };
        }
        else {
            return function (data) {
                var sum = 0;
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.iterate)(tokens, (token) => {
                    sum += scoreObject(token, data);
                });
                return sum / token_count;
            };
        }
    }
    ;
    /**
     * Returns a function that can be used to compare two
     * results, for sorting purposes. If no sorting should
     * be performed, `null` will be returned.
     *
     * @return function(a,b)
     */
    getSortFunction(query, options) {
        var search = this.prepareSearch(query, options);
        return this._getSortFunction(search);
    }
    _getSortFunction(search) {
        var implicit_score, sort_flds = [];
        const self = this, options = search.options, sort = (!search.query && options.sort_empty) ? options.sort_empty : options.sort;
        if (typeof sort == 'function') {
            return sort.bind(this);
        }
        /**
         * Fetches the specified sort field value
         * from a search result item.
         *
         */
        const get_field = function (name, result) {
            if (name === '$score')
                return result.score;
            return search.getAttrFn(self.items[result.id], name);
        };
        // parse options
        if (sort) {
            for (let s of sort) {
                if (search.query || s.field !== '$score') {
                    sort_flds.push(s);
                }
            }
        }
        // the "$score" field is implied to be the primary
        // sort field, unless it's manually specified
        if (search.query) {
            implicit_score = true;
            for (let fld of sort_flds) {
                if (fld.field === '$score') {
                    implicit_score = false;
                    break;
                }
            }
            if (implicit_score) {
                sort_flds.unshift({ field: '$score', direction: 'desc' });
            }
            // without a search.query, all items will have the same score
        }
        else {
            sort_flds = sort_flds.filter((fld) => fld.field !== '$score');
        }
        // build function
        const sort_flds_count = sort_flds.length;
        if (!sort_flds_count) {
            return null;
        }
        return function (a, b) {
            var result, field;
            for (let sort_fld of sort_flds) {
                field = sort_fld.field;
                let multiplier = sort_fld.direction === 'desc' ? -1 : 1;
                result = multiplier * (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.cmp)(get_field(field, a), get_field(field, b));
                if (result)
                    return result;
            }
            return 0;
        };
    }
    ;
    /**
     * Parses a search query and returns an object
     * with tokens and fields ready to be populated
     * with results.
     *
     */
    prepareSearch(query, optsUser) {
        const weights = {};
        var options = Object.assign({}, optsUser);
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.propToArray)(options, 'sort');
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.propToArray)(options, 'sort_empty');
        // convert fields to new format
        if (options.fields) {
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.propToArray)(options, 'fields');
            const fields = [];
            options.fields.forEach((field) => {
                if (typeof field == 'string') {
                    field = { field: field, weight: 1 };
                }
                fields.push(field);
                weights[field.field] = ('weight' in field) ? field.weight : 1;
            });
            options.fields = fields;
        }
        return {
            options: options,
            query: query.toLowerCase().trim(),
            tokens: this.tokenize(query, options.respect_word_boundaries, weights),
            total: 0,
            items: [],
            weights: weights,
            getAttrFn: (options.nesting) ? _utils_js__WEBPACK_IMPORTED_MODULE_0__.getAttrNesting : _utils_js__WEBPACK_IMPORTED_MODULE_0__.getAttr,
        };
    }
    ;
    /**
     * Searches through all items and returns a sorted array of matches.
     *
     */
    search(query, options) {
        var self = this, score, search;
        search = this.prepareSearch(query, options);
        options = search.options;
        query = search.query;
        // generate result scoring function
        const fn_score = options.score || self._getScoreFunction(search);
        // perform search and sort
        if (query.length) {
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.iterate)(self.items, (item, id) => {
                score = fn_score(item);
                if (options.filter === false || score > 0) {
                    search.items.push({ 'score': score, 'id': id });
                }
            });
        }
        else {
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.iterate)(self.items, (_, id) => {
                search.items.push({ 'score': 1, 'id': id });
            });
        }
        const fn_sort = self._getSortFunction(search);
        if (fn_sort)
            search.items.sort(fn_sort);
        // apply limits
        search.total = search.items.length;
        if (typeof options.limit === 'number') {
            search.items = search.items.slice(0, options.limit);
        }
        return search;
    }
    ;
}


//# sourceMappingURL=sifter.js.map

/***/ }),

/***/ "./node_modules/@orchidjs/sifter/dist/esm/types.js":
/*!*********************************************************!*\
  !*** ./node_modules/@orchidjs/sifter/dist/esm/types.js ***!
  \*********************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);

//# sourceMappingURL=types.js.map

/***/ }),

/***/ "./node_modules/@orchidjs/sifter/dist/esm/utils.js":
/*!*********************************************************!*\
  !*** ./node_modules/@orchidjs/sifter/dist/esm/utils.js ***!
  \*********************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   cmp: () => (/* binding */ cmp),
/* harmony export */   getAttr: () => (/* binding */ getAttr),
/* harmony export */   getAttrNesting: () => (/* binding */ getAttrNesting),
/* harmony export */   iterate: () => (/* binding */ iterate),
/* harmony export */   propToArray: () => (/* binding */ propToArray),
/* harmony export */   scoreValue: () => (/* binding */ scoreValue)
/* harmony export */ });
/* harmony import */ var _orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @orchidjs/unicode-variants */ "./node_modules/@orchidjs/unicode-variants/dist/esm/index.js");

/**
 * A property getter resolving dot-notation
 * @param  {Object}  obj     The root object to fetch property on
 * @param  {String}  name    The optionally dotted property name to fetch
 * @return {Object}          The resolved property value
 */
const getAttr = (obj, name) => {
    if (!obj)
        return;
    return obj[name];
};
/**
 * A property getter resolving dot-notation
 * @param  {Object}  obj     The root object to fetch property on
 * @param  {String}  name    The optionally dotted property name to fetch
 * @return {Object}          The resolved property value
 */
const getAttrNesting = (obj, name) => {
    if (!obj)
        return;
    var part, names = name.split(".");
    while ((part = names.shift()) && (obj = obj[part]))
        ;
    return obj;
};
/**
 * Calculates how close of a match the
 * given value is against a search token.
 *
 */
const scoreValue = (value, token, weight) => {
    var score, pos;
    if (!value)
        return 0;
    value = value + '';
    if (token.regex == null)
        return 0;
    pos = value.search(token.regex);
    if (pos === -1)
        return 0;
    score = token.string.length / value.length;
    if (pos === 0)
        score += 0.5;
    return score * weight;
};
/**
 * Cast object property to an array if it exists and has a value
 *
 */
const propToArray = (obj, key) => {
    var value = obj[key];
    if (typeof value == 'function')
        return value;
    if (value && !Array.isArray(value)) {
        obj[key] = [value];
    }
};
/**
 * Iterates over arrays and hashes.
 *
 * ```
 * iterate(this.items, function(item, id) {
 *    // invoked for each item
 * });
 * ```
 *
 */
const iterate = (object, callback) => {
    if (Array.isArray(object)) {
        object.forEach(callback);
    }
    else {
        for (var key in object) {
            if (object.hasOwnProperty(key)) {
                callback(object[key], key);
            }
        }
    }
};
const cmp = (a, b) => {
    if (typeof a === 'number' && typeof b === 'number') {
        return a > b ? 1 : (a < b ? -1 : 0);
    }
    a = (0,_orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_0__.asciifold)(a + '').toLowerCase();
    b = (0,_orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_0__.asciifold)(b + '').toLowerCase();
    if (a > b)
        return 1;
    if (b > a)
        return -1;
    return 0;
};
//# sourceMappingURL=utils.js.map

/***/ }),

/***/ "./node_modules/@orchidjs/unicode-variants/dist/esm/index.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@orchidjs/unicode-variants/dist/esm/index.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   _asciifold: () => (/* binding */ _asciifold),
/* harmony export */   asciifold: () => (/* binding */ asciifold),
/* harmony export */   code_points: () => (/* binding */ code_points),
/* harmony export */   escape_regex: () => (/* reexport safe */ _regex_js__WEBPACK_IMPORTED_MODULE_0__.escape_regex),
/* harmony export */   generateMap: () => (/* binding */ generateMap),
/* harmony export */   generateSets: () => (/* binding */ generateSets),
/* harmony export */   generator: () => (/* binding */ generator),
/* harmony export */   getPattern: () => (/* binding */ getPattern),
/* harmony export */   initialize: () => (/* binding */ initialize),
/* harmony export */   mapSequence: () => (/* binding */ mapSequence),
/* harmony export */   normalize: () => (/* binding */ normalize),
/* harmony export */   substringsToPattern: () => (/* binding */ substringsToPattern),
/* harmony export */   unicode_map: () => (/* binding */ unicode_map)
/* harmony export */ });
/* harmony import */ var _regex_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./regex.js */ "./node_modules/@orchidjs/unicode-variants/dist/esm/regex.js");
/* harmony import */ var _strings_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./strings.js */ "./node_modules/@orchidjs/unicode-variants/dist/esm/strings.js");


const code_points = [[0, 65535]];
const accent_pat = '[\u0300-\u036F\u{b7}\u{2be}\u{2bc}]';
let unicode_map;
let multi_char_reg;
const max_char_length = 3;
const latin_convert = {};
const latin_condensed = {
    '/': '⁄∕',
    '0': '߀',
    "a": "ⱥɐɑ",
    "aa": "ꜳ",
    "ae": "æǽǣ",
    "ao": "ꜵ",
    "au": "ꜷ",
    "av": "ꜹꜻ",
    "ay": "ꜽ",
    "b": "ƀɓƃ",
    "c": "ꜿƈȼↄ",
    "d": "đɗɖᴅƌꮷԁɦ",
    "e": "ɛǝᴇɇ",
    "f": "ꝼƒ",
    "g": "ǥɠꞡᵹꝿɢ",
    "h": "ħⱨⱶɥ",
    "i": "ɨı",
    "j": "ɉȷ",
    "k": "ƙⱪꝁꝃꝅꞣ",
    "l": "łƚɫⱡꝉꝇꞁɭ",
    "m": "ɱɯϻ",
    "n": "ꞥƞɲꞑᴎлԉ",
    "o": "øǿɔɵꝋꝍᴑ",
    "oe": "œ",
    "oi": "ƣ",
    "oo": "ꝏ",
    "ou": "ȣ",
    "p": "ƥᵽꝑꝓꝕρ",
    "q": "ꝗꝙɋ",
    "r": "ɍɽꝛꞧꞃ",
    "s": "ßȿꞩꞅʂ",
    "t": "ŧƭʈⱦꞇ",
    "th": "þ",
    "tz": "ꜩ",
    "u": "ʉ",
    "v": "ʋꝟʌ",
    "vy": "ꝡ",
    "w": "ⱳ",
    "y": "ƴɏỿ",
    "z": "ƶȥɀⱬꝣ",
    "hv": "ƕ"
};
for (let latin in latin_condensed) {
    let unicode = latin_condensed[latin] || '';
    for (let i = 0; i < unicode.length; i++) {
        let char = unicode.substring(i, i + 1);
        latin_convert[char] = latin;
    }
}
const convert_pat = new RegExp(Object.keys(latin_convert).join('|') + '|' + accent_pat, 'gu');
/**
 * Initialize the unicode_map from the give code point ranges
 */
const initialize = (_code_points) => {
    if (unicode_map !== undefined)
        return;
    unicode_map = generateMap(_code_points || code_points);
};
/**
 * Helper method for normalize a string
 * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/normalize
 */
const normalize = (str, form = 'NFKD') => str.normalize(form);
/**
 * Remove accents without reordering string
 * calling str.normalize('NFKD') on \u{594}\u{595}\u{596} becomes \u{596}\u{594}\u{595}
 * via https://github.com/krisk/Fuse/issues/133#issuecomment-318692703
 */
const asciifold = (str) => {
    return Array.from(str).reduce(
    /**
     * @param {string} result
     * @param {string} char
     */
    (result, char) => {
        return result + _asciifold(char);
    }, '');
};
const _asciifold = (str) => {
    str = normalize(str)
        .toLowerCase()
        .replace(convert_pat, (/** @type {string} */ char) => {
        return latin_convert[char] || '';
    });
    //return str;
    return normalize(str, 'NFC');
};
/**
 * Generate a list of unicode variants from the list of code points
 */
function* generator(code_points) {
    for (const [code_point_min, code_point_max] of code_points) {
        for (let i = code_point_min; i <= code_point_max; i++) {
            let composed = String.fromCharCode(i);
            let folded = asciifold(composed);
            if (folded == composed.toLowerCase()) {
                continue;
            }
            // skip when folded is a string longer than 3 characters long
            // bc the resulting regex patterns will be long
            // eg:
            // folded صلى الله عليه وسلم length 18 code point 65018
            // folded جل جلاله length 8 code point 65019
            if (folded.length > max_char_length) {
                continue;
            }
            if (folded.length == 0) {
                continue;
            }
            yield { folded: folded, composed: composed, code_point: i };
        }
    }
}
/**
 * Generate a unicode map from the list of code points
 */
const generateSets = (code_points) => {
    const unicode_sets = {};
    const addMatching = (folded, to_add) => {
        /** @type {Set<string>} */
        const folded_set = unicode_sets[folded] || new Set();
        const patt = new RegExp('^' + (0,_regex_js__WEBPACK_IMPORTED_MODULE_0__.setToPattern)(folded_set) + '$', 'iu');
        if (to_add.match(patt)) {
            return;
        }
        folded_set.add((0,_regex_js__WEBPACK_IMPORTED_MODULE_0__.escape_regex)(to_add));
        unicode_sets[folded] = folded_set;
    };
    for (let value of generator(code_points)) {
        addMatching(value.folded, value.folded);
        addMatching(value.folded, value.composed);
    }
    return unicode_sets;
};
/**
 * Generate a unicode map from the list of code points
 * ae => (?:(?:ae|Æ|Ǽ|Ǣ)|(?:A|Ⓐ|Ａ...)(?:E|ɛ|Ⓔ...))
 */
const generateMap = (code_points) => {
    const unicode_sets = generateSets(code_points);
    const unicode_map = {};
    let multi_char = [];
    for (let folded in unicode_sets) {
        let set = unicode_sets[folded];
        if (set) {
            unicode_map[folded] = (0,_regex_js__WEBPACK_IMPORTED_MODULE_0__.setToPattern)(set);
        }
        if (folded.length > 1) {
            multi_char.push((0,_regex_js__WEBPACK_IMPORTED_MODULE_0__.escape_regex)(folded));
        }
    }
    multi_char.sort((a, b) => b.length - a.length);
    const multi_char_patt = (0,_regex_js__WEBPACK_IMPORTED_MODULE_0__.arrayToPattern)(multi_char);
    multi_char_reg = new RegExp('^' + multi_char_patt, 'u');
    return unicode_map;
};
/**
 * Map each element of an array from its folded value to all possible unicode matches
 */
const mapSequence = (strings, min_replacement = 1) => {
    let chars_replaced = 0;
    strings = strings.map((str) => {
        if (unicode_map[str]) {
            chars_replaced += str.length;
        }
        return unicode_map[str] || str;
    });
    if (chars_replaced >= min_replacement) {
        return (0,_regex_js__WEBPACK_IMPORTED_MODULE_0__.sequencePattern)(strings);
    }
    return '';
};
/**
 * Convert a short string and split it into all possible patterns
 * Keep a pattern only if min_replacement is met
 *
 * 'abc'
 * 		=> [['abc'],['ab','c'],['a','bc'],['a','b','c']]
 *		=> ['abc-pattern','ab-c-pattern'...]
 */
const substringsToPattern = (str, min_replacement = 1) => {
    min_replacement = Math.max(min_replacement, str.length - 1);
    return (0,_regex_js__WEBPACK_IMPORTED_MODULE_0__.arrayToPattern)((0,_strings_js__WEBPACK_IMPORTED_MODULE_1__.allSubstrings)(str).map((sub_pat) => {
        return mapSequence(sub_pat, min_replacement);
    }));
};
/**
 * Convert an array of sequences into a pattern
 * [{start:0,end:3,length:3,substr:'iii'}...] => (?:iii...)
 */
const sequencesToPattern = (sequences, all = true) => {
    let min_replacement = sequences.length > 1 ? 1 : 0;
    return (0,_regex_js__WEBPACK_IMPORTED_MODULE_0__.arrayToPattern)(sequences.map((sequence) => {
        let seq = [];
        const len = all ? sequence.length() : sequence.length() - 1;
        for (let j = 0; j < len; j++) {
            seq.push(substringsToPattern(sequence.substrs[j] || '', min_replacement));
        }
        return (0,_regex_js__WEBPACK_IMPORTED_MODULE_0__.sequencePattern)(seq);
    }));
};
/**
 * Return true if the sequence is already in the sequences
 */
const inSequences = (needle_seq, sequences) => {
    for (const seq of sequences) {
        if (seq.start != needle_seq.start || seq.end != needle_seq.end) {
            continue;
        }
        if (seq.substrs.join('') !== needle_seq.substrs.join('')) {
            continue;
        }
        let needle_parts = needle_seq.parts;
        const filter = (part) => {
            for (const needle_part of needle_parts) {
                if (needle_part.start === part.start && needle_part.substr === part.substr) {
                    return false;
                }
                if (part.length == 1 || needle_part.length == 1) {
                    continue;
                }
                // check for overlapping parts
                // a = ['::=','==']
                // b = ['::','===']
                // a = ['r','sm']
                // b = ['rs','m']
                if (part.start < needle_part.start && part.end > needle_part.start) {
                    return true;
                }
                if (needle_part.start < part.start && needle_part.end > part.start) {
                    return true;
                }
            }
            return false;
        };
        let filtered = seq.parts.filter(filter);
        if (filtered.length > 0) {
            continue;
        }
        return true;
    }
    return false;
};
class Sequence {
    parts;
    substrs;
    start;
    end;
    constructor() {
        this.parts = [];
        this.substrs = [];
        this.start = 0;
        this.end = 0;
    }
    add(part) {
        if (part) {
            this.parts.push(part);
            this.substrs.push(part.substr);
            this.start = Math.min(part.start, this.start);
            this.end = Math.max(part.end, this.end);
        }
    }
    last() {
        return this.parts[this.parts.length - 1];
    }
    length() {
        return this.parts.length;
    }
    clone(position, last_piece) {
        let clone = new Sequence();
        let parts = JSON.parse(JSON.stringify(this.parts));
        let last_part = parts.pop();
        for (const part of parts) {
            clone.add(part);
        }
        let last_substr = last_piece.substr.substring(0, position - last_part.start);
        let clone_last_len = last_substr.length;
        clone.add({ start: last_part.start, end: last_part.start + clone_last_len, length: clone_last_len, substr: last_substr });
        return clone;
    }
}
/**
 * Expand a regular expression pattern to include unicode variants
 * 	eg /a/ becomes /aⓐａẚàáâầấẫẩãāăằắẵẳȧǡäǟảåǻǎȁȃạậặḁąⱥɐɑAⒶＡÀÁÂẦẤẪẨÃĀĂẰẮẴẲȦǠÄǞẢÅǺǍȀȂẠẬẶḀĄȺⱯ/
 *
 * Issue:
 *  ﺊﺋ [ 'ﺊ = \\u{fe8a}', 'ﺋ = \\u{fe8b}' ]
 *	becomes:	ئئ [ 'ي = \\u{64a}', 'ٔ = \\u{654}', 'ي = \\u{64a}', 'ٔ = \\u{654}' ]
 *
 *	İĲ = IIJ = ⅡJ
 *
 * 	1/2/4
 */
const getPattern = (str) => {
    initialize();
    str = asciifold(str);
    let pattern = '';
    let sequences = [new Sequence()];
    for (let i = 0; i < str.length; i++) {
        let substr = str.substring(i);
        let match = substr.match(multi_char_reg);
        const char = str.substring(i, i + 1);
        const match_str = match ? match[0] : null;
        // loop through sequences
        // add either the char or multi_match
        let overlapping = [];
        let added_types = new Set();
        for (const sequence of sequences) {
            const last_piece = sequence.last();
            if (!last_piece || last_piece.length == 1 || last_piece.end <= i) {
                // if we have a multi match
                if (match_str) {
                    const len = match_str.length;
                    sequence.add({ start: i, end: i + len, length: len, substr: match_str });
                    added_types.add('1');
                }
                else {
                    sequence.add({ start: i, end: i + 1, length: 1, substr: char });
                    added_types.add('2');
                }
            }
            else if (match_str) {
                let clone = sequence.clone(i, last_piece);
                const len = match_str.length;
                clone.add({ start: i, end: i + len, length: len, substr: match_str });
                overlapping.push(clone);
            }
            else {
                // don't add char
                // adding would create invalid patterns: 234 => [2,34,4]
                added_types.add('3');
            }
        }
        // if we have overlapping
        if (overlapping.length > 0) {
            // ['ii','iii'] before ['i','i','iii']
            overlapping = overlapping.sort((a, b) => {
                return a.length() - b.length();
            });
            for (let clone of overlapping) {
                // don't add if we already have an equivalent sequence
                if (inSequences(clone, sequences)) {
                    continue;
                }
                sequences.push(clone);
            }
            continue;
        }
        // if we haven't done anything unique
        // clean up the patterns
        // helps keep patterns smaller
        // if str = 'r₨㎧aarss', pattern will be 446 instead of 655
        if (i > 0 && added_types.size == 1 && !added_types.has('3')) {
            pattern += sequencesToPattern(sequences, false);
            let new_seq = new Sequence();
            const old_seq = sequences[0];
            if (old_seq) {
                new_seq.add(old_seq.last());
            }
            sequences = [new_seq];
        }
    }
    pattern += sequencesToPattern(sequences, true);
    return pattern;
};

//# sourceMappingURL=index.js.map

/***/ }),

/***/ "./node_modules/@orchidjs/unicode-variants/dist/esm/regex.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@orchidjs/unicode-variants/dist/esm/regex.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   arrayToPattern: () => (/* binding */ arrayToPattern),
/* harmony export */   escape_regex: () => (/* binding */ escape_regex),
/* harmony export */   hasDuplicates: () => (/* binding */ hasDuplicates),
/* harmony export */   maxValueLength: () => (/* binding */ maxValueLength),
/* harmony export */   sequencePattern: () => (/* binding */ sequencePattern),
/* harmony export */   setToPattern: () => (/* binding */ setToPattern),
/* harmony export */   unicodeLength: () => (/* binding */ unicodeLength)
/* harmony export */ });
/**
 * Convert array of strings to a regular expression
 *	ex ['ab','a'] => (?:ab|a)
 * 	ex ['a','b'] => [ab]
 */
const arrayToPattern = (chars) => {
    chars = chars.filter(Boolean);
    if (chars.length < 2) {
        return chars[0] || '';
    }
    return (maxValueLength(chars) == 1) ? '[' + chars.join('') + ']' : '(?:' + chars.join('|') + ')';
};
const sequencePattern = (array) => {
    if (!hasDuplicates(array)) {
        return array.join('');
    }
    let pattern = '';
    let prev_char_count = 0;
    const prev_pattern = () => {
        if (prev_char_count > 1) {
            pattern += '{' + prev_char_count + '}';
        }
    };
    array.forEach((char, i) => {
        if (char === array[i - 1]) {
            prev_char_count++;
            return;
        }
        prev_pattern();
        pattern += char;
        prev_char_count = 1;
    });
    prev_pattern();
    return pattern;
};
/**
 * Convert array of strings to a regular expression
 *	ex ['ab','a'] => (?:ab|a)
 * 	ex ['a','b'] => [ab]
 */
const setToPattern = (chars) => {
    let array = Array.from(chars);
    return arrayToPattern(array);
};
/**
 * https://stackoverflow.com/questions/7376598/in-javascript-how-do-i-check-if-an-array-has-duplicate-values
 */
const hasDuplicates = (array) => {
    return (new Set(array)).size !== array.length;
};
/**
 * https://stackoverflow.com/questions/63006601/why-does-u-throw-an-invalid-escape-error
 */
const escape_regex = (str) => {
    return (str + '').replace(/([\$\(\)\*\+\.\?\[\]\^\{\|\}\\])/gu, '\\$1');
};
/**
 * Return the max length of array values
 */
const maxValueLength = (array) => {
    return array.reduce((longest, value) => Math.max(longest, unicodeLength(value)), 0);
};
const unicodeLength = (str) => {
    return Array.from(str).length;
};
//# sourceMappingURL=regex.js.map

/***/ }),

/***/ "./node_modules/@orchidjs/unicode-variants/dist/esm/strings.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@orchidjs/unicode-variants/dist/esm/strings.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   allSubstrings: () => (/* binding */ allSubstrings)
/* harmony export */ });
/**
 * Get all possible combinations of substrings that add up to the given string
 * https://stackoverflow.com/questions/30169587/find-all-the-combination-of-substrings-that-add-up-to-the-given-string
 */
const allSubstrings = (input) => {
    if (input.length === 1)
        return [[input]];
    let result = [];
    const start = input.substring(1);
    const suba = allSubstrings(start);
    suba.forEach(function (subresult) {
        let tmp = subresult.slice(0);
        tmp[0] = input.charAt(0) + tmp[0];
        result.push(tmp);
        tmp = subresult.slice(0);
        tmp.unshift(input.charAt(0));
        result.push(tmp);
    });
    return result;
};
//# sourceMappingURL=strings.js.map

/***/ }),

/***/ "./public/assets/js/app/componentManager/BaseComponentManager.mjs":
/*!************************************************************************!*\
  !*** ./public/assets/js/app/componentManager/BaseComponentManager.mjs ***!
  \************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   BaseComponentManager: () => (/* binding */ BaseComponentManager)
/* harmony export */ });
/* harmony import */ var htmx_org__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! htmx.org */ "./node_modules/htmx.org/dist/htmx.esm.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }

var BaseComponentManager = /*#__PURE__*/function () {
  function BaseComponentManager() {
    _classCallCheck(this, BaseComponentManager);
    this.instances = new Map();
    this.setupHtmxListeners();
  }
  return _createClass(BaseComponentManager, [{
    key: "setupHtmxListeners",
    value: function setupHtmxListeners() {
      var _this = this;
      htmx_org__WEBPACK_IMPORTED_MODULE_0__["default"].on('htmx:beforeCleanupElement', function (evt) {
        _this.cleanupElements(evt.detail.element);
      });
    }
  }, {
    key: "cleanupElements",
    value: function cleanupElements(parentElement) {
      var _this2 = this;
      if (!parentElement) {
        console.warn('Attempted to cleanup undefined parent element');
        return;
      }
      var elements = this.findElements(parentElement);
      elements === null || elements === void 0 || elements.forEach(function (element) {
        _this2.destroyInstance(element);
      });
    }
  }, {
    key: "initializeComponent",
    value: function initializeComponent(element) {
      var config = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      if (this.instances.has(element)) {
        console.warn("".concat(this.constructor.name, ": Component already initialized for element:"), element);
        return this.instances.get(element);
      }
      try {
        var instance = this.createInstance(element, config);
        if (instance) {
          this.instances.set(element, instance);
          this.dispatchEvent('component:initialized', {
            element: element,
            instance: instance
          });
        }
        return instance;
      } catch (error) {
        console.error("".concat(this.constructor.name, ": Failed to initialize component:"), error);
        return null;
      }
    }
  }, {
    key: "destroyInstance",
    value: function destroyInstance(element) {
      if (!element) return;
      var instance = this.instances.get(element);
      if (instance) {
        this.dispatchEvent('component:beforeDestroy', {
          element: element,
          instance: instance
        });
        this.cleanup(instance);
        this.instances["delete"](element);
        this.dispatchEvent('component:destroyed', {
          element: element
        });
      }
    }
  }, {
    key: "getInstance",
    value: function getInstance(element) {
      return this.instances.get(element);
    }

    // Methods to be implemented by child classes
  }, {
    key: "findElements",
    value: function findElements(parentElement) {
      throw new Error('findElements must be implemented by child class');
    }
  }, {
    key: "createInstance",
    value: function createInstance(element, config) {
      throw new Error('createInstance must be implemented by child class');
    }
  }, {
    key: "cleanup",
    value: function cleanup(instance) {
      throw new Error('cleanup must be implemented by child class');
    }

    // Event handling
  }, {
    key: "dispatchEvent",
    value: function dispatchEvent(eventName, detail) {
      var event = new CustomEvent(eventName, {
        detail: detail,
        bubbles: true,
        cancelable: true
      });
      document.dispatchEvent(event);
    }
  }]);
}();

/***/ }),

/***/ "./public/assets/js/app/componentManager/SelectManager.mjs":
/*!*****************************************************************!*\
  !*** ./public/assets/js/app/componentManager/SelectManager.mjs ***!
  \*****************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   selectManager: () => (/* binding */ selectManager)
/* harmony export */ });
/* harmony import */ var _BaseComponentManager_mjs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./BaseComponentManager.mjs */ "./public/assets/js/app/componentManager/BaseComponentManager.mjs");
/* harmony import */ var tom_select_dist_esm_tom_select_complete_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! tom-select/dist/esm/tom-select.complete.js */ "./node_modules/tom-select/dist/esm/tom-select.complete.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _callSuper(t, o, e) { return o = _getPrototypeOf(o), _possibleConstructorReturn(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], _getPrototypeOf(t).constructor) : o.apply(t, e)); }
function _possibleConstructorReturn(t, e) { if (e && ("object" == _typeof(e) || "function" == typeof e)) return e; if (void 0 !== e) throw new TypeError("Derived constructors may only return object or undefined"); return _assertThisInitialized(t); }
function _assertThisInitialized(e) { if (void 0 === e) throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); return e; }
function _isNativeReflectConstruct() { try { var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); } catch (t) {} return (_isNativeReflectConstruct = function _isNativeReflectConstruct() { return !!t; })(); }
function _getPrototypeOf(t) { return _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function (t) { return t.__proto__ || Object.getPrototypeOf(t); }, _getPrototypeOf(t); }
function _inherits(t, e) { if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function"); t.prototype = Object.create(e && e.prototype, { constructor: { value: t, writable: !0, configurable: !0 } }), Object.defineProperty(t, "prototype", { writable: !1 }), e && _setPrototypeOf(t, e); }
function _setPrototypeOf(t, e) { return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) { return t.__proto__ = e, t; }, _setPrototypeOf(t, e); }


var SelectManager = /*#__PURE__*/function (_BaseComponentManager) {
  function SelectManager() {
    _classCallCheck(this, SelectManager);
    return _callSuper(this, SelectManager, arguments);
  }
  _inherits(SelectManager, _BaseComponentManager);
  return _createClass(SelectManager, [{
    key: "findElements",
    value: function findElements(parentElement) {
      try {
        return (parentElement === null || parentElement === void 0 ? void 0 : parentElement.querySelectorAll('select[data-component="select"]')) || [];
      } catch (error) {
        console.error('Error finding select elements:', error);
        return [];
      }
    }
  }, {
    key: "createInstance",
    value: function createInstance(element) {
      var config = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var defaultConfig = {
        create: false,
        sortField: {
          field: "text",
          direction: "asc"
        }
      };
      var mergedConfig = _objectSpread(_objectSpread({}, defaultConfig), config);
      return new tom_select_dist_esm_tom_select_complete_js__WEBPACK_IMPORTED_MODULE_1__["default"](element, mergedConfig);
    }
  }, {
    key: "cleanup",
    value: function cleanup(instance) {
      try {
        if (instance && typeof instance.destroy === 'function') {
          instance.destroy();
        }
      } catch (error) {
        console.error('Error cleaning up select instance:', error);
      }
    }
  }]);
}(_BaseComponentManager_mjs__WEBPACK_IMPORTED_MODULE_0__.BaseComponentManager);
var selectManager = new SelectManager();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (selectManager);

/***/ }),

/***/ "./public/assets/js/app/components/selects.module.mjs":
/*!************************************************************!*\
  !*** ./public/assets/js/app/components/selects.module.mjs ***!
  \************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   initComponent: () => (/* binding */ initComponent),
/* harmony export */   initSelect: () => (/* binding */ initSelect),
/* harmony export */   initTags: () => (/* binding */ initTags),
/* harmony export */   selects: () => (/* binding */ selects)
/* harmony export */ });
/* harmony import */ var tom_select_dist_esm_tom_select_complete_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! tom-select/dist/esm/tom-select.complete.js */ "./node_modules/tom-select/dist/esm/tom-select.complete.js");
/* harmony import */ var _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../core/instance-info.module.mjs */ "./public/assets/js/app/core/instance-info.module.mjs");
/* harmony import */ var _componentManager_SelectManager_mjs__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../componentManager/SelectManager.mjs */ "./public/assets/js/app/componentManager/SelectManager.mjs");
/* provided dependency */ var jQuery = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");



function getOptions(selectElement) {
  var items = [];
  if (jQuery(selectElement).children()) {
    jQuery(selectElement).children().each(function (option) {
      var optionClone = jQuery(this).clone();
      items.push({
        value: optionClone.val(),
        label: decode(optionClone.html()),
        selected: optionClone.attr("selected"),
        disabled: optionClone.attr("disabled")
      });
      jQuery(this).remove();
    });
  }
  return items;
}
var initComponent = function initComponent() {};
var initSelect = function initSelect(element, enableSearch, additionalClasses) {
  return _componentManager_SelectManager_mjs__WEBPACK_IMPORTED_MODULE_2__.selectManager.initializeSelect(element, {
    create: false,
    sortField: {
      field: "text",
      direction: "asc"
    }
  });
};
var initTags = function initTags(element, enableSearch, autoCompleteTags, additionalClasses) {
  var maxItemCount = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 4;
  var outerClasses = ["select"];
  if (additionalClasses !== "") {
    var selectClasses = additionalClasses.trim().split(" ");
    outerClasses = selectClasses;
  }
  var select = new Choices(element, {
    editItems: true,
    addItems: true,
    allowHTML: true,
    addChoices: true,
    searchEnabled: enableSearch,
    duplicateItemsAllowed: false,
    addItemText: function addItemText(value) {
      return "Press Enter to add \"".concat(value, "\"");
    },
    placeholderValue: "🏷️ Add a tag",
    noChoicesText: "No choices option",
    uniqueItemText: "Only unique values can be added",
    searchPlaceholderValue: "Search for tags",
    removeItemButton: true,
    maxItemCount: maxItemCount,
    maxItemText: function maxItemText(maxItemCount) {
      return "Only ".concat(maxItemCount, " values can be added");
    },
    classNames: {}
  });
  if (autoCompleteTags) {
    select.setChoices(function (callback) {
      return fetch(_core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_1__.appUrl + "/api/tags").then(function (res) {
        console.log(res);
        return res.json();
      }).then(function (data) {
        console.log(data);
        return data.map(function (release) {
          return {
            label: release,
            value: release
          };
        });
      });
    });
  }
  select.passedElement.element.addEventListener("addItem", function (event) {
    // do something creative here...
  }, false);
  select.passedElement.element.addEventListener("addChoice", function (event) {
    // do something creative here...
  }, false);
};
var selects = {
  initSelect: initSelect,
  initTags: initTags
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (selects);

/***/ }),

/***/ "./public/assets/js/app/core/instance-info.module.mjs":
/*!************************************************************!*\
  !*** ./public/assets/js/app/core/instance-info.module.mjs ***!
  \************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   appUrl: () => (/* binding */ appUrl),
/* harmony export */   colorScheme: () => (/* binding */ colorScheme),
/* harmony export */   companyColor: () => (/* binding */ companyColor),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   theme: () => (/* binding */ theme),
/* harmony export */   version: () => (/* binding */ version)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");

var companyColor = jquery__WEBPACK_IMPORTED_MODULE_0__('meta[name=theme-color]').attr('content');
var colorScheme = jquery__WEBPACK_IMPORTED_MODULE_0__('meta[name=color-scheme]').attr('content');
var theme = jquery__WEBPACK_IMPORTED_MODULE_0__('meta[name=theme]').attr('content');
var appUrl = jquery__WEBPACK_IMPORTED_MODULE_0__('meta[name=identifier-URL]').attr('content');
var version = jquery__WEBPACK_IMPORTED_MODULE_0__('meta[name=leantime-version]').attr('content');
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  companyColor: companyColor,
  colorScheme: colorScheme,
  theme: theme,
  appUrl: appUrl,
  version: version
});

/***/ }),

/***/ "./node_modules/tom-select/dist/esm/constants.js":
/*!*******************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/constants.js ***!
  \*******************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   IS_MAC: () => (/* binding */ IS_MAC),
/* harmony export */   KEY_A: () => (/* binding */ KEY_A),
/* harmony export */   KEY_BACKSPACE: () => (/* binding */ KEY_BACKSPACE),
/* harmony export */   KEY_DELETE: () => (/* binding */ KEY_DELETE),
/* harmony export */   KEY_DOWN: () => (/* binding */ KEY_DOWN),
/* harmony export */   KEY_ESC: () => (/* binding */ KEY_ESC),
/* harmony export */   KEY_LEFT: () => (/* binding */ KEY_LEFT),
/* harmony export */   KEY_RETURN: () => (/* binding */ KEY_RETURN),
/* harmony export */   KEY_RIGHT: () => (/* binding */ KEY_RIGHT),
/* harmony export */   KEY_SHORTCUT: () => (/* binding */ KEY_SHORTCUT),
/* harmony export */   KEY_TAB: () => (/* binding */ KEY_TAB),
/* harmony export */   KEY_UP: () => (/* binding */ KEY_UP)
/* harmony export */ });
const KEY_A = 65;
const KEY_RETURN = 13;
const KEY_ESC = 27;
const KEY_LEFT = 37;
const KEY_UP = 38;
const KEY_RIGHT = 39;
const KEY_DOWN = 40;
const KEY_BACKSPACE = 8;
const KEY_DELETE = 46;
const KEY_TAB = 9;
const IS_MAC = typeof navigator === 'undefined' ? false : /Mac/.test(navigator.userAgent);
const KEY_SHORTCUT = IS_MAC ? 'metaKey' : 'ctrlKey'; // ctrl key or apple key for ma
//# sourceMappingURL=constants.js.map

/***/ }),

/***/ "./node_modules/tom-select/dist/esm/contrib/highlight.js":
/*!***************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/contrib/highlight.js ***!
  \***************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   highlight: () => (/* binding */ highlight),
/* harmony export */   removeHighlight: () => (/* binding */ removeHighlight)
/* harmony export */ });
/* harmony import */ var _vanilla_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../vanilla.js */ "./node_modules/tom-select/dist/esm/vanilla.js");
/**
 * highlight v3 | MIT license | Johann Burkard <jb@eaio.com>
 * Highlights arbitrary terms in a node.
 *
 * - Modified by Marshal <beatgates@gmail.com> 2011-6-24 (added regex)
 * - Modified by Brian Reavis <brian@thirdroute.com> 2012-8-27 (cleanup)
 */

const highlight = (element, regex) => {
    if (regex === null)
        return;
    // convet string to regex
    if (typeof regex === 'string') {
        if (!regex.length)
            return;
        regex = new RegExp(regex, 'i');
    }
    // Wrap matching part of text node with highlighting <span>, e.g.
    // Soccer  ->  <span class="highlight">Soc</span>cer  for regex = /soc/i
    const highlightText = (node) => {
        var match = node.data.match(regex);
        if (match && node.data.length > 0) {
            var spannode = document.createElement('span');
            spannode.className = 'highlight';
            var middlebit = node.splitText(match.index);
            middlebit.splitText(match[0].length);
            var middleclone = middlebit.cloneNode(true);
            spannode.appendChild(middleclone);
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_0__.replaceNode)(middlebit, spannode);
            return 1;
        }
        return 0;
    };
    // Recurse element node, looking for child text nodes to highlight, unless element
    // is childless, <script>, <style>, or already highlighted: <span class="hightlight">
    const highlightChildren = (node) => {
        if (node.nodeType === 1 && node.childNodes && !/(script|style)/i.test(node.tagName) && (node.className !== 'highlight' || node.tagName !== 'SPAN')) {
            Array.from(node.childNodes).forEach(element => {
                highlightRecursive(element);
            });
        }
    };
    const highlightRecursive = (node) => {
        if (node.nodeType === 3) {
            return highlightText(node);
        }
        highlightChildren(node);
        return 0;
    };
    highlightRecursive(element);
};
/**
 * removeHighlight fn copied from highlight v5 and
 * edited to remove with(), pass js strict mode, and use without jquery
 */
const removeHighlight = (el) => {
    var elements = el.querySelectorAll("span.highlight");
    Array.prototype.forEach.call(elements, function (el) {
        var parent = el.parentNode;
        parent.replaceChild(el.firstChild, el);
        parent.normalize();
    });
};
//# sourceMappingURL=highlight.js.map

/***/ }),

/***/ "./node_modules/tom-select/dist/esm/contrib/microevent.js":
/*!****************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/contrib/microevent.js ***!
  \****************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ MicroEvent)
/* harmony export */ });
/**
 * MicroEvent - to make any js object an event emitter
 *
 * - pure javascript - server compatible, browser compatible
 * - dont rely on the browser doms
 * - super simple - you get it immediatly, no mistery, no magic involved
 *
 * @author Jerome Etienne (https://github.com/jeromeetienne)
 */
/**
 * Execute callback for each event in space separated list of event names
 *
 */
function forEvents(events, callback) {
    events.split(/\s+/).forEach((event) => {
        callback(event);
    });
}
class MicroEvent {
    constructor() {
        this._events = {};
    }
    on(events, fct) {
        forEvents(events, (event) => {
            const event_array = this._events[event] || [];
            event_array.push(fct);
            this._events[event] = event_array;
        });
    }
    off(events, fct) {
        var n = arguments.length;
        if (n === 0) {
            this._events = {};
            return;
        }
        forEvents(events, (event) => {
            if (n === 1) {
                delete this._events[event];
                return;
            }
            const event_array = this._events[event];
            if (event_array === undefined)
                return;
            event_array.splice(event_array.indexOf(fct), 1);
            this._events[event] = event_array;
        });
    }
    trigger(events, ...args) {
        var self = this;
        forEvents(events, (event) => {
            const event_array = self._events[event];
            if (event_array === undefined)
                return;
            event_array.forEach(fct => {
                fct.apply(self, args);
            });
        });
    }
}
;
//# sourceMappingURL=microevent.js.map

/***/ }),

/***/ "./node_modules/tom-select/dist/esm/contrib/microplugin.js":
/*!*****************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/contrib/microplugin.js ***!
  \*****************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ MicroPlugin)
/* harmony export */ });
/**
 * microplugin.js
 * Copyright (c) 2013 Brian Reavis & contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 * @author Brian Reavis <brian@thirdroute.com>
 */
function MicroPlugin(Interface) {
    Interface.plugins = {};
    return class extends Interface {
        constructor() {
            super(...arguments);
            this.plugins = {
                names: [],
                settings: {},
                requested: {},
                loaded: {}
            };
        }
        /**
         * Registers a plugin.
         *
         * @param {function} fn
         */
        static define(name, fn) {
            Interface.plugins[name] = {
                'name': name,
                'fn': fn
            };
        }
        /**
         * Initializes the listed plugins (with options).
         * Acceptable formats:
         *
         * List (without options):
         *   ['a', 'b', 'c']
         *
         * List (with options):
         *   [{'name': 'a', options: {}}, {'name': 'b', options: {}}]
         *
         * Hash (with options):
         *   {'a': { ... }, 'b': { ... }, 'c': { ... }}
         *
         * @param {array|object} plugins
         */
        initializePlugins(plugins) {
            var key, name;
            const self = this;
            const queue = [];
            if (Array.isArray(plugins)) {
                plugins.forEach((plugin) => {
                    if (typeof plugin === 'string') {
                        queue.push(plugin);
                    }
                    else {
                        self.plugins.settings[plugin.name] = plugin.options;
                        queue.push(plugin.name);
                    }
                });
            }
            else if (plugins) {
                for (key in plugins) {
                    if (plugins.hasOwnProperty(key)) {
                        self.plugins.settings[key] = plugins[key];
                        queue.push(key);
                    }
                }
            }
            while (name = queue.shift()) {
                self.require(name);
            }
        }
        loadPlugin(name) {
            var self = this;
            var plugins = self.plugins;
            var plugin = Interface.plugins[name];
            if (!Interface.plugins.hasOwnProperty(name)) {
                throw new Error('Unable to find "' + name + '" plugin');
            }
            plugins.requested[name] = true;
            plugins.loaded[name] = plugin.fn.apply(self, [self.plugins.settings[name] || {}]);
            plugins.names.push(name);
        }
        /**
         * Initializes a plugin.
         *
         */
        require(name) {
            var self = this;
            var plugins = self.plugins;
            if (!self.plugins.loaded.hasOwnProperty(name)) {
                if (plugins.requested[name]) {
                    throw new Error('Plugin has circular dependency ("' + name + '")');
                }
                self.loadPlugin(name);
            }
            return plugins.loaded[name];
        }
    };
}
//# sourceMappingURL=microplugin.js.map

/***/ }),

/***/ "./node_modules/tom-select/dist/esm/defaults.js":
/*!******************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/defaults.js ***!
  \******************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
    options: [],
    optgroups: [],
    plugins: [],
    delimiter: ',',
    splitOn: null, // regexp or string for splitting up values from a paste command
    persist: true,
    diacritics: true,
    create: null,
    createOnBlur: false,
    createFilter: null,
    highlight: true,
    openOnFocus: true,
    shouldOpen: null,
    maxOptions: 50,
    maxItems: null,
    hideSelected: null,
    duplicates: false,
    addPrecedence: false,
    selectOnTab: false,
    preload: null,
    allowEmptyOption: false,
    //closeAfterSelect: false,
    refreshThrottle: 300,
    loadThrottle: 300,
    loadingClass: 'loading',
    dataAttr: null, //'data-data',
    optgroupField: 'optgroup',
    valueField: 'value',
    labelField: 'text',
    disabledField: 'disabled',
    optgroupLabelField: 'label',
    optgroupValueField: 'value',
    lockOptgroupOrder: false,
    sortField: '$order',
    searchField: ['text'],
    searchConjunction: 'and',
    mode: null,
    wrapperClass: 'ts-wrapper',
    controlClass: 'ts-control',
    dropdownClass: 'ts-dropdown',
    dropdownContentClass: 'ts-dropdown-content',
    itemClass: 'item',
    optionClass: 'option',
    dropdownParent: null,
    controlInput: '<input type="text" autocomplete="off" size="1" />',
    copyClassesToDropdown: false,
    placeholder: null,
    hidePlaceholder: null,
    shouldLoad: function (query) {
        return query.length > 0;
    },
    /*
    load                 : null, // function(query, callback) { ... }
    score                : null, // function(search) { ... }
    onInitialize         : null, // function() { ... }
    onChange             : null, // function(value) { ... }
    onItemAdd            : null, // function(value, $item) { ... }
    onItemRemove         : null, // function(value) { ... }
    onClear              : null, // function() { ... }
    onOptionAdd          : null, // function(value, data) { ... }
    onOptionRemove       : null, // function(value) { ... }
    onOptionClear        : null, // function() { ... }
    onOptionGroupAdd     : null, // function(id, data) { ... }
    onOptionGroupRemove  : null, // function(id) { ... }
    onOptionGroupClear   : null, // function() { ... }
    onDropdownOpen       : null, // function(dropdown) { ... }
    onDropdownClose      : null, // function(dropdown) { ... }
    onType               : null, // function(str) { ... }
    onDelete             : null, // function(values) { ... }
    */
    render: {
    /*
    item: null,
    optgroup: null,
    optgroup_header: null,
    option: null,
    option_create: null
    */
    }
});
//# sourceMappingURL=defaults.js.map

/***/ }),

/***/ "./node_modules/tom-select/dist/esm/getSettings.js":
/*!*********************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/getSettings.js ***!
  \*********************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ getSettings)
/* harmony export */ });
/* harmony import */ var _defaults_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./defaults.js */ "./node_modules/tom-select/dist/esm/defaults.js");
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./utils.js */ "./node_modules/tom-select/dist/esm/utils.js");


function getSettings(input, settings_user) {
    var settings = Object.assign({}, _defaults_js__WEBPACK_IMPORTED_MODULE_0__["default"], settings_user);
    var attr_data = settings.dataAttr;
    var field_label = settings.labelField;
    var field_value = settings.valueField;
    var field_disabled = settings.disabledField;
    var field_optgroup = settings.optgroupField;
    var field_optgroup_label = settings.optgroupLabelField;
    var field_optgroup_value = settings.optgroupValueField;
    var tag_name = input.tagName.toLowerCase();
    var placeholder = input.getAttribute('placeholder') || input.getAttribute('data-placeholder');
    if (!placeholder && !settings.allowEmptyOption) {
        let option = input.querySelector('option[value=""]');
        if (option) {
            placeholder = option.textContent;
        }
    }
    var settings_element = {
        placeholder: placeholder,
        options: [],
        optgroups: [],
        items: [],
        maxItems: null,
    };
    /**
     * Initialize from a <select> element.
     *
     */
    var init_select = () => {
        var tagName;
        var options = settings_element.options;
        var optionsMap = {};
        var group_count = 1;
        let $order = 0;
        var readData = (el) => {
            var data = Object.assign({}, el.dataset); // get plain object from DOMStringMap
            var json = attr_data && data[attr_data];
            if (typeof json === 'string' && json.length) {
                data = Object.assign(data, JSON.parse(json));
            }
            return data;
        };
        var addOption = (option, group) => {
            var value = (0,_utils_js__WEBPACK_IMPORTED_MODULE_1__.hash_key)(option.value);
            if (value == null)
                return;
            if (!value && !settings.allowEmptyOption)
                return;
            // if the option already exists, it's probably been
            // duplicated in another optgroup. in this case, push
            // the current group to the "optgroup" property on the
            // existing option so that it's rendered in both places.
            if (optionsMap.hasOwnProperty(value)) {
                if (group) {
                    var arr = optionsMap[value][field_optgroup];
                    if (!arr) {
                        optionsMap[value][field_optgroup] = group;
                    }
                    else if (!Array.isArray(arr)) {
                        optionsMap[value][field_optgroup] = [arr, group];
                    }
                    else {
                        arr.push(group);
                    }
                }
            }
            else {
                var option_data = readData(option);
                option_data[field_label] = option_data[field_label] || option.textContent;
                option_data[field_value] = option_data[field_value] || value;
                option_data[field_disabled] = option_data[field_disabled] || option.disabled;
                option_data[field_optgroup] = option_data[field_optgroup] || group;
                option_data.$option = option;
                option_data.$order = option_data.$order || ++$order;
                optionsMap[value] = option_data;
                options.push(option_data);
            }
            if (option.selected) {
                settings_element.items.push(value);
            }
        };
        var addGroup = (optgroup) => {
            var id, optgroup_data;
            optgroup_data = readData(optgroup);
            optgroup_data[field_optgroup_label] = optgroup_data[field_optgroup_label] || optgroup.getAttribute('label') || '';
            optgroup_data[field_optgroup_value] = optgroup_data[field_optgroup_value] || group_count++;
            optgroup_data[field_disabled] = optgroup_data[field_disabled] || optgroup.disabled;
            optgroup_data.$order = optgroup_data.$order || ++$order;
            settings_element.optgroups.push(optgroup_data);
            id = optgroup_data[field_optgroup_value];
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_1__.iterate)(optgroup.children, (option) => {
                addOption(option, id);
            });
        };
        settings_element.maxItems = input.hasAttribute('multiple') ? null : 1;
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_1__.iterate)(input.children, (child) => {
            tagName = child.tagName.toLowerCase();
            if (tagName === 'optgroup') {
                addGroup(child);
            }
            else if (tagName === 'option') {
                addOption(child);
            }
        });
    };
    /**
     * Initialize from a <input type="text"> element.
     *
     */
    var init_textbox = () => {
        const data_raw = input.getAttribute(attr_data);
        if (!data_raw) {
            var value = input.value.trim() || '';
            if (!settings.allowEmptyOption && !value.length)
                return;
            const values = value.split(settings.delimiter);
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_1__.iterate)(values, (value) => {
                const option = {};
                option[field_label] = value;
                option[field_value] = value;
                settings_element.options.push(option);
            });
            settings_element.items = values;
        }
        else {
            settings_element.options = JSON.parse(data_raw);
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_1__.iterate)(settings_element.options, (opt) => {
                settings_element.items.push(opt[field_value]);
            });
        }
    };
    if (tag_name === 'select') {
        init_select();
    }
    else {
        init_textbox();
    }
    return Object.assign({}, _defaults_js__WEBPACK_IMPORTED_MODULE_0__["default"], settings_element, settings_user);
}
;
//# sourceMappingURL=getSettings.js.map

/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/caret_position/plugin.js":
/*!***************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/caret_position/plugin.js ***!
  \***************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Iterates over arrays and hashes.
 *
 * ```
 * iterate(this.items, function(item, id) {
 *    // invoked for each item
 * });
 * ```
 *
 */
const iterate = (object, callback) => {
  if (Array.isArray(object)) {
    object.forEach(callback);
  } else {
    for (var key in object) {
      if (object.hasOwnProperty(key)) {
        callback(object[key], key);
      }
    }
  }
};

/**
 * Remove css classes
 *
 */
const removeClasses = (elmts, ...classes) => {
  var norm_classes = classesArray(classes);
  elmts = castAsArray(elmts);
  elmts.map(el => {
    norm_classes.map(cls => {
      el.classList.remove(cls);
    });
  });
};

/**
 * Return arguments
 *
 */
const classesArray = args => {
  var classes = [];
  iterate(args, _classes => {
    if (typeof _classes === 'string') {
      _classes = _classes.trim().split(/[\t\n\f\r\s]/);
    }
    if (Array.isArray(_classes)) {
      classes = classes.concat(_classes);
    }
  });
  return classes.filter(Boolean);
};

/**
 * Create an array from arg if it's not already an array
 *
 */
const castAsArray = arg => {
  if (!Array.isArray(arg)) {
    arg = [arg];
  }
  return arg;
};

/**
 * Get the index of an element amongst sibling nodes of the same type
 *
 */
const nodeIndex = (el, amongst) => {
  if (!el) return -1;
  amongst = amongst || el.nodeName;
  var i = 0;
  while (el = el.previousElementSibling) {
    if (el.matches(amongst)) {
      i++;
    }
  }
  return i;
};

/**
 * Plugin: "dropdown_input" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  var self = this;

  /**
   * Moves the caret to the specified index.
   *
   * The input must be moved by leaving it in place and moving the
   * siblings, due to the fact that focus cannot be restored once lost
   * on mobile webkit devices
   *
   */
  self.hook('instead', 'setCaret', new_pos => {
    if (self.settings.mode === 'single' || !self.control.contains(self.control_input)) {
      new_pos = self.items.length;
    } else {
      new_pos = Math.max(0, Math.min(self.items.length, new_pos));
      if (new_pos != self.caretPos && !self.isPending) {
        self.controlChildren().forEach((child, j) => {
          if (j < new_pos) {
            self.control_input.insertAdjacentElement('beforebegin', child);
          } else {
            self.control.appendChild(child);
          }
        });
      }
    }
    self.caretPos = new_pos;
  });
  self.hook('instead', 'moveCaret', direction => {
    if (!self.isFocused) return;

    // move caret before or after selected items
    const last_active = self.getLastActive(direction);
    if (last_active) {
      const idx = nodeIndex(last_active);
      self.setCaret(direction > 0 ? idx + 1 : idx);
      self.setActiveItem();
      removeClasses(last_active, 'last-active');

      // move caret left or right of current position
    } else {
      self.setCaret(self.caretPos + direction);
    }
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/change_listener/plugin.js":
/*!****************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/change_listener/plugin.js ***!
  \****************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Add event helper
 *
 */
const addEvent = (target, type, callback, options) => {
  target.addEventListener(type, callback, options);
};

/**
 * Plugin: "change_listener" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  addEvent(this.input, 'change', () => {
    this.sync();
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/checkbox_options/plugin.js":
/*!*****************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/checkbox_options/plugin.js ***!
  \*****************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */
const hash_key = value => {
  if (typeof value === 'undefined' || value === null) return null;
  return get_hash(value);
};
const get_hash = value => {
  if (typeof value === 'boolean') return value ? '1' : '0';
  return value + '';
};

/**
 * Prevent default
 *
 */
const preventDefault = (evt, stop = false) => {
  if (evt) {
    evt.preventDefault();
    if (stop) {
      evt.stopPropagation();
    }
  }
};

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = query => {
  if (query.jquery) {
    return query[0];
  }
  if (query instanceof HTMLElement) {
    return query;
  }
  if (isHtmlString(query)) {
    var tpl = document.createElement('template');
    tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
    return tpl.content.firstChild;
  }
  return document.querySelector(query);
};
const isHtmlString = arg => {
  if (typeof arg === 'string' && arg.indexOf('<') > -1) {
    return true;
  }
  return false;
};

/**
 * Plugin: "checkbox_options" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin (userOptions) {
  var self = this;
  var orig_onOptionSelect = self.onOptionSelect;
  self.settings.hideSelected = false;
  const cbOptions = Object.assign({
    // so that the user may add different ones as well
    className: "tomselect-checkbox",
    // the following default to the historic plugin's values
    checkedClassNames: undefined,
    uncheckedClassNames: undefined
  }, userOptions);
  var UpdateChecked = function UpdateChecked(checkbox, toCheck) {
    if (toCheck) {
      checkbox.checked = true;
      if (cbOptions.uncheckedClassNames) {
        checkbox.classList.remove(...cbOptions.uncheckedClassNames);
      }
      if (cbOptions.checkedClassNames) {
        checkbox.classList.add(...cbOptions.checkedClassNames);
      }
    } else {
      checkbox.checked = false;
      if (cbOptions.checkedClassNames) {
        checkbox.classList.remove(...cbOptions.checkedClassNames);
      }
      if (cbOptions.uncheckedClassNames) {
        checkbox.classList.add(...cbOptions.uncheckedClassNames);
      }
    }
  };

  // update the checkbox for an option
  var UpdateCheckbox = function UpdateCheckbox(option) {
    setTimeout(() => {
      var checkbox = option.querySelector('input.' + cbOptions.className);
      if (checkbox instanceof HTMLInputElement) {
        UpdateChecked(checkbox, option.classList.contains('selected'));
      }
    }, 1);
  };

  // add checkbox to option template
  self.hook('after', 'setupTemplates', () => {
    var orig_render_option = self.settings.render.option;
    self.settings.render.option = (data, escape_html) => {
      var rendered = getDom(orig_render_option.call(self, data, escape_html));
      var checkbox = document.createElement('input');
      if (cbOptions.className) {
        checkbox.classList.add(cbOptions.className);
      }
      checkbox.addEventListener('click', function (evt) {
        preventDefault(evt);
      });
      checkbox.type = 'checkbox';
      const hashed = hash_key(data[self.settings.valueField]);
      UpdateChecked(checkbox, !!(hashed && self.items.indexOf(hashed) > -1));
      rendered.prepend(checkbox);
      return rendered;
    };
  });

  // uncheck when item removed
  self.on('item_remove', value => {
    var option = self.getOption(value);
    if (option) {
      // if dropdown hasn't been opened yet, the option won't exist
      option.classList.remove('selected'); // selected class won't be removed yet
      UpdateCheckbox(option);
    }
  });

  // check when item added
  self.on('item_add', value => {
    var option = self.getOption(value);
    if (option) {
      // if dropdown hasn't been opened yet, the option won't exist
      UpdateCheckbox(option);
    }
  });

  // remove items when selected option is clicked
  self.hook('instead', 'onOptionSelect', (evt, option) => {
    if (option.classList.contains('selected')) {
      option.classList.remove('selected');
      self.removeItem(option.dataset.value);
      self.refreshOptions();
      preventDefault(evt, true);
      return;
    }
    orig_onOptionSelect.call(self, evt, option);
    UpdateCheckbox(option);
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/clear_button/plugin.js":
/*!*************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/clear_button/plugin.js ***!
  \*************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = query => {
  if (query.jquery) {
    return query[0];
  }
  if (query instanceof HTMLElement) {
    return query;
  }
  if (isHtmlString(query)) {
    var tpl = document.createElement('template');
    tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
    return tpl.content.firstChild;
  }
  return document.querySelector(query);
};
const isHtmlString = arg => {
  if (typeof arg === 'string' && arg.indexOf('<') > -1) {
    return true;
  }
  return false;
};

/**
 * Plugin: "dropdown_header" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin (userOptions) {
  const self = this;
  const options = Object.assign({
    className: 'clear-button',
    title: 'Clear All',
    html: data => {
      return `<div class="${data.className}" title="${data.title}">&#10799;</div>`;
    }
  }, userOptions);
  self.on('initialize', () => {
    var button = getDom(options.html(options));
    button.addEventListener('click', evt => {
      if (self.isLocked) return;
      self.clear();
      if (self.settings.mode === 'single' && self.settings.allowEmptyOption) {
        self.addItem('');
      }
      evt.preventDefault();
      evt.stopPropagation();
    });
    self.control.appendChild(button);
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/drag_drop/plugin.js":
/*!**********************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/drag_drop/plugin.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Prevent default
 *
 */
const preventDefault = (evt, stop = false) => {
  if (evt) {
    evt.preventDefault();
    if (stop) {
      evt.stopPropagation();
    }
  }
};

/**
 * Add event helper
 *
 */
const addEvent = (target, type, callback, options) => {
  target.addEventListener(type, callback, options);
};

/**
 * Iterates over arrays and hashes.
 *
 * ```
 * iterate(this.items, function(item, id) {
 *    // invoked for each item
 * });
 * ```
 *
 */
const iterate = (object, callback) => {
  if (Array.isArray(object)) {
    object.forEach(callback);
  } else {
    for (var key in object) {
      if (object.hasOwnProperty(key)) {
        callback(object[key], key);
      }
    }
  }
};

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = query => {
  if (query.jquery) {
    return query[0];
  }
  if (query instanceof HTMLElement) {
    return query;
  }
  if (isHtmlString(query)) {
    var tpl = document.createElement('template');
    tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
    return tpl.content.firstChild;
  }
  return document.querySelector(query);
};
const isHtmlString = arg => {
  if (typeof arg === 'string' && arg.indexOf('<') > -1) {
    return true;
  }
  return false;
};

/**
 * Set attributes of an element
 *
 */
const setAttr = (el, attrs) => {
  iterate(attrs, (val, attr) => {
    if (val == null) {
      el.removeAttribute(attr);
    } else {
      el.setAttribute(attr, '' + val);
    }
  });
};

/**
 * Plugin: "drag_drop" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

const insertAfter = (referenceNode, newNode) => {
  var _referenceNode$parent;
  (_referenceNode$parent = referenceNode.parentNode) == null || _referenceNode$parent.insertBefore(newNode, referenceNode.nextSibling);
};
const insertBefore = (referenceNode, newNode) => {
  var _referenceNode$parent2;
  (_referenceNode$parent2 = referenceNode.parentNode) == null || _referenceNode$parent2.insertBefore(newNode, referenceNode);
};
const isBefore = (referenceNode, newNode) => {
  do {
    var _newNode;
    newNode = (_newNode = newNode) == null ? void 0 : _newNode.previousElementSibling;
    if (referenceNode == newNode) {
      return true;
    }
  } while (newNode && newNode.previousElementSibling);
  return false;
};
function plugin () {
  var self = this;
  if (self.settings.mode !== 'multi') return;
  var orig_lock = self.lock;
  var orig_unlock = self.unlock;
  let sortable = true;
  let drag_item;

  /**
   * Add draggable attribute to item
   */
  self.hook('after', 'setupTemplates', () => {
    var orig_render_item = self.settings.render.item;
    self.settings.render.item = (data, escape) => {
      const item = getDom(orig_render_item.call(self, data, escape));
      setAttr(item, {
        'draggable': 'true'
      });

      // prevent doc_mousedown (see tom-select.ts)
      const mousedown = evt => {
        if (!sortable) preventDefault(evt);
        evt.stopPropagation();
      };
      const dragStart = evt => {
        drag_item = item;
        setTimeout(() => {
          item.classList.add('ts-dragging');
        }, 0);
      };
      const dragOver = evt => {
        evt.preventDefault();
        item.classList.add('ts-drag-over');
        moveitem(item, drag_item);
      };
      const dragLeave = () => {
        item.classList.remove('ts-drag-over');
      };
      const moveitem = (targetitem, dragitem) => {
        if (dragitem === undefined) return;
        if (isBefore(dragitem, item)) {
          insertAfter(targetitem, dragitem);
        } else {
          insertBefore(targetitem, dragitem);
        }
      };
      const dragend = () => {
        var _drag_item;
        document.querySelectorAll('.ts-drag-over').forEach(el => el.classList.remove('ts-drag-over'));
        (_drag_item = drag_item) == null || _drag_item.classList.remove('ts-dragging');
        drag_item = undefined;
        var values = [];
        self.control.querySelectorAll(`[data-value]`).forEach(el => {
          if (el.dataset.value) {
            let value = el.dataset.value;
            if (value) {
              values.push(value);
            }
          }
        });
        self.setValue(values);
      };
      addEvent(item, 'mousedown', mousedown);
      addEvent(item, 'dragstart', dragStart);
      addEvent(item, 'dragenter', dragOver);
      addEvent(item, 'dragover', dragOver);
      addEvent(item, 'dragleave', dragLeave);
      addEvent(item, 'dragend', dragend);
      return item;
    };
  });
  self.hook('instead', 'lock', () => {
    sortable = false;
    return orig_lock.call(self);
  });
  self.hook('instead', 'unlock', () => {
    sortable = true;
    return orig_unlock.call(self);
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/dropdown_header/plugin.js":
/*!****************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/dropdown_header/plugin.js ***!
  \****************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Prevent default
 *
 */
const preventDefault = (evt, stop = false) => {
  if (evt) {
    evt.preventDefault();
    if (stop) {
      evt.stopPropagation();
    }
  }
};

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = query => {
  if (query.jquery) {
    return query[0];
  }
  if (query instanceof HTMLElement) {
    return query;
  }
  if (isHtmlString(query)) {
    var tpl = document.createElement('template');
    tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
    return tpl.content.firstChild;
  }
  return document.querySelector(query);
};
const isHtmlString = arg => {
  if (typeof arg === 'string' && arg.indexOf('<') > -1) {
    return true;
  }
  return false;
};

/**
 * Plugin: "dropdown_header" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin (userOptions) {
  const self = this;
  const options = Object.assign({
    title: 'Untitled',
    headerClass: 'dropdown-header',
    titleRowClass: 'dropdown-header-title',
    labelClass: 'dropdown-header-label',
    closeClass: 'dropdown-header-close',
    html: data => {
      return '<div class="' + data.headerClass + '">' + '<div class="' + data.titleRowClass + '">' + '<span class="' + data.labelClass + '">' + data.title + '</span>' + '<a class="' + data.closeClass + '">&times;</a>' + '</div>' + '</div>';
    }
  }, userOptions);
  self.on('initialize', () => {
    var header = getDom(options.html(options));
    var close_link = header.querySelector('.' + options.closeClass);
    if (close_link) {
      close_link.addEventListener('click', evt => {
        preventDefault(evt, true);
        self.close();
      });
    }
    self.dropdown.insertBefore(header, self.dropdown.firstChild);
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/dropdown_input/plugin.js":
/*!***************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/dropdown_input/plugin.js ***!
  \***************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

const KEY_ESC = 27;
const KEY_TAB = 9;
 // ctrl key or apple key for ma

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Prevent default
 *
 */
const preventDefault = (evt, stop = false) => {
  if (evt) {
    evt.preventDefault();
    if (stop) {
      evt.stopPropagation();
    }
  }
};

/**
 * Add event helper
 *
 */
const addEvent = (target, type, callback, options) => {
  target.addEventListener(type, callback, options);
};

/**
 * Iterates over arrays and hashes.
 *
 * ```
 * iterate(this.items, function(item, id) {
 *    // invoked for each item
 * });
 * ```
 *
 */
const iterate = (object, callback) => {
  if (Array.isArray(object)) {
    object.forEach(callback);
  } else {
    for (var key in object) {
      if (object.hasOwnProperty(key)) {
        callback(object[key], key);
      }
    }
  }
};

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = query => {
  if (query.jquery) {
    return query[0];
  }
  if (query instanceof HTMLElement) {
    return query;
  }
  if (isHtmlString(query)) {
    var tpl = document.createElement('template');
    tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
    return tpl.content.firstChild;
  }
  return document.querySelector(query);
};
const isHtmlString = arg => {
  if (typeof arg === 'string' && arg.indexOf('<') > -1) {
    return true;
  }
  return false;
};

/**
 * Add css classes
 *
 */
const addClasses = (elmts, ...classes) => {
  var norm_classes = classesArray(classes);
  elmts = castAsArray(elmts);
  elmts.map(el => {
    norm_classes.map(cls => {
      el.classList.add(cls);
    });
  });
};

/**
 * Return arguments
 *
 */
const classesArray = args => {
  var classes = [];
  iterate(args, _classes => {
    if (typeof _classes === 'string') {
      _classes = _classes.trim().split(/[\t\n\f\r\s]/);
    }
    if (Array.isArray(_classes)) {
      classes = classes.concat(_classes);
    }
  });
  return classes.filter(Boolean);
};

/**
 * Create an array from arg if it's not already an array
 *
 */
const castAsArray = arg => {
  if (!Array.isArray(arg)) {
    arg = [arg];
  }
  return arg;
};

/**
 * Plugin: "dropdown_input" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  const self = this;
  self.settings.shouldOpen = true; // make sure the input is shown even if there are no options to display in the dropdown

  self.hook('before', 'setup', () => {
    self.focus_node = self.control;
    addClasses(self.control_input, 'dropdown-input');
    const div = getDom('<div class="dropdown-input-wrap">');
    div.append(self.control_input);
    self.dropdown.insertBefore(div, self.dropdown.firstChild);

    // set a placeholder in the select control
    const placeholder = getDom('<input class="items-placeholder" tabindex="-1" />');
    placeholder.placeholder = self.settings.placeholder || '';
    self.control.append(placeholder);
  });
  self.on('initialize', () => {
    // set tabIndex on control to -1, otherwise [shift+tab] will put focus right back on control_input
    self.control_input.addEventListener('keydown', evt => {
      //addEvent(self.control_input,'keydown' as const,(evt:KeyboardEvent) =>{
      switch (evt.keyCode) {
        case KEY_ESC:
          if (self.isOpen) {
            preventDefault(evt, true);
            self.close();
          }
          self.clearActiveItems();
          return;
        case KEY_TAB:
          self.focus_node.tabIndex = -1;
          break;
      }
      return self.onKeyDown.call(self, evt);
    });
    self.on('blur', () => {
      self.focus_node.tabIndex = self.isDisabled ? -1 : self.tabIndex;
    });

    // give the control_input focus when the dropdown is open
    self.on('dropdown_open', () => {
      self.control_input.focus();
    });

    // prevent onBlur from closing when focus is on the control_input
    const orig_onBlur = self.onBlur;
    self.hook('instead', 'onBlur', evt => {
      if (evt && evt.relatedTarget == self.control_input) return;
      return orig_onBlur.call(self);
    });
    addEvent(self.control_input, 'blur', () => self.onBlur());

    // return focus to control to allow further keyboard input
    self.hook('before', 'close', () => {
      if (!self.isOpen) return;
      self.focus_node.focus({
        preventScroll: true
      });
    });
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/input_autogrow/plugin.js":
/*!***************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/input_autogrow/plugin.js ***!
  \***************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Add event helper
 *
 */
const addEvent = (target, type, callback, options) => {
  target.addEventListener(type, callback, options);
};

/**
 * Plugin: "input_autogrow" (Tom Select)
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  var self = this;
  self.on('initialize', () => {
    var test_input = document.createElement('span');
    var control = self.control_input;
    test_input.style.cssText = 'position:absolute; top:-99999px; left:-99999px; width:auto; padding:0; white-space:pre; ';
    self.wrapper.appendChild(test_input);
    var transfer_styles = ['letterSpacing', 'fontSize', 'fontFamily', 'fontWeight', 'textTransform'];
    for (const style_name of transfer_styles) {
      // @ts-ignore TS7015 https://stackoverflow.com/a/50506154/697576
      test_input.style[style_name] = control.style[style_name];
    }

    /**
     * Set the control width
     *
     */
    var resize = () => {
      test_input.textContent = control.value;
      control.style.width = test_input.clientWidth + 'px';
    };
    resize();
    self.on('update item_add item_remove', resize);
    addEvent(control, 'input', resize);
    addEvent(control, 'keyup', resize);
    addEvent(control, 'blur', resize);
    addEvent(control, 'update', resize);
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/no_active_items/plugin.js":
/*!****************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/no_active_items/plugin.js ***!
  \****************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Plugin: "no_active_items" (Tom Select)
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  this.hook('instead', 'setActiveItem', () => {});
  this.hook('instead', 'selectAll', () => {});
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/no_backspace_delete/plugin.js":
/*!********************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/no_backspace_delete/plugin.js ***!
  \********************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Plugin: "input_autogrow" (Tom Select)
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  var self = this;
  var orig_deleteSelection = self.deleteSelection;
  this.hook('instead', 'deleteSelection', evt => {
    if (self.activeItems.length) {
      return orig_deleteSelection.call(self, evt);
    }
    return false;
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/optgroup_columns/plugin.js":
/*!*****************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/optgroup_columns/plugin.js ***!
  \*****************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

const KEY_LEFT = 37;
const KEY_RIGHT = 39;
 // ctrl key or apple key for ma

/**
 * Get the closest node to the evt.target matching the selector
 * Stops at wrapper
 *
 */
const parentMatch = (target, selector, wrapper) => {
  while (target && target.matches) {
    if (target.matches(selector)) {
      return target;
    }
    target = target.parentNode;
  }
};

/**
 * Get the index of an element amongst sibling nodes of the same type
 *
 */
const nodeIndex = (el, amongst) => {
  if (!el) return -1;
  amongst = amongst || el.nodeName;
  var i = 0;
  while (el = el.previousElementSibling) {
    if (el.matches(amongst)) {
      i++;
    }
  }
  return i;
};

/**
 * Plugin: "optgroup_columns" (Tom Select.js)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  var self = this;
  var orig_keydown = self.onKeyDown;
  self.hook('instead', 'onKeyDown', evt => {
    var index, option, options, optgroup;
    if (!self.isOpen || !(evt.keyCode === KEY_LEFT || evt.keyCode === KEY_RIGHT)) {
      return orig_keydown.call(self, evt);
    }
    self.ignoreHover = true;
    optgroup = parentMatch(self.activeOption, '[data-group]');
    index = nodeIndex(self.activeOption, '[data-selectable]');
    if (!optgroup) {
      return;
    }
    if (evt.keyCode === KEY_LEFT) {
      optgroup = optgroup.previousSibling;
    } else {
      optgroup = optgroup.nextSibling;
    }
    if (!optgroup) {
      return;
    }
    options = optgroup.querySelectorAll('[data-selectable]');
    option = options[Math.min(options.length - 1, index)];
    if (option) {
      self.setActiveOption(option);
    }
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/remove_button/plugin.js":
/*!**************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/remove_button/plugin.js ***!
  \**************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Escapes a string for use within HTML.
 *
 */
const escape_html = str => {
  return (str + '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
};

/**
 * Prevent default
 *
 */
const preventDefault = (evt, stop = false) => {
  if (evt) {
    evt.preventDefault();
    if (stop) {
      evt.stopPropagation();
    }
  }
};

/**
 * Add event helper
 *
 */
const addEvent = (target, type, callback, options) => {
  target.addEventListener(type, callback, options);
};

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = query => {
  if (query.jquery) {
    return query[0];
  }
  if (query instanceof HTMLElement) {
    return query;
  }
  if (isHtmlString(query)) {
    var tpl = document.createElement('template');
    tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
    return tpl.content.firstChild;
  }
  return document.querySelector(query);
};
const isHtmlString = arg => {
  if (typeof arg === 'string' && arg.indexOf('<') > -1) {
    return true;
  }
  return false;
};

/**
 * Plugin: "remove_button" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin (userOptions) {
  const options = Object.assign({
    label: '&times;',
    title: 'Remove',
    className: 'remove',
    append: true
  }, userOptions);

  //options.className = 'remove-single';
  var self = this;

  // override the render method to add remove button to each item
  if (!options.append) {
    return;
  }
  var html = '<a href="javascript:void(0)" class="' + options.className + '" tabindex="-1" title="' + escape_html(options.title) + '">' + options.label + '</a>';
  self.hook('after', 'setupTemplates', () => {
    var orig_render_item = self.settings.render.item;
    self.settings.render.item = (data, escape) => {
      var item = getDom(orig_render_item.call(self, data, escape));
      var close_button = getDom(html);
      item.appendChild(close_button);
      addEvent(close_button, 'mousedown', evt => {
        preventDefault(evt, true);
      });
      addEvent(close_button, 'click', evt => {
        if (self.isLocked) return;

        // propagating will trigger the dropdown to show for single mode
        preventDefault(evt, true);
        if (self.isLocked) return;
        if (!self.shouldDelete([item], evt)) return;
        self.removeItem(item);
        self.refreshOptions(false);
        self.inputState();
      });
      return item;
    };
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/restore_on_backspace/plugin.js":
/*!*********************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/restore_on_backspace/plugin.js ***!
  \*********************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Plugin: "restore_on_backspace" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin (userOptions) {
  const self = this;
  const options = Object.assign({
    text: option => {
      return option[self.settings.labelField];
    }
  }, userOptions);
  self.on('item_remove', function (value) {
    if (!self.isFocused) {
      return;
    }
    if (self.control_input.value.trim() === '') {
      var option = self.options[value];
      if (option) {
        self.setTextboxValue(options.text.call(self, option));
      }
    }
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/virtual_scroll/plugin.js":
/*!***************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/virtual_scroll/plugin.js ***!
  \***************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Iterates over arrays and hashes.
 *
 * ```
 * iterate(this.items, function(item, id) {
 *    // invoked for each item
 * });
 * ```
 *
 */
const iterate = (object, callback) => {
  if (Array.isArray(object)) {
    object.forEach(callback);
  } else {
    for (var key in object) {
      if (object.hasOwnProperty(key)) {
        callback(object[key], key);
      }
    }
  }
};

/**
 * Add css classes
 *
 */
const addClasses = (elmts, ...classes) => {
  var norm_classes = classesArray(classes);
  elmts = castAsArray(elmts);
  elmts.map(el => {
    norm_classes.map(cls => {
      el.classList.add(cls);
    });
  });
};

/**
 * Return arguments
 *
 */
const classesArray = args => {
  var classes = [];
  iterate(args, _classes => {
    if (typeof _classes === 'string') {
      _classes = _classes.trim().split(/[\t\n\f\r\s]/);
    }
    if (Array.isArray(_classes)) {
      classes = classes.concat(_classes);
    }
  });
  return classes.filter(Boolean);
};

/**
 * Create an array from arg if it's not already an array
 *
 */
const castAsArray = arg => {
  if (!Array.isArray(arg)) {
    arg = [arg];
  }
  return arg;
};

/**
 * Plugin: "restore_on_backspace" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  const self = this;
  const orig_canLoad = self.canLoad;
  const orig_clearActiveOption = self.clearActiveOption;
  const orig_loadCallback = self.loadCallback;
  var pagination = {};
  var dropdown_content;
  var loading_more = false;
  var load_more_opt;
  var default_values = [];
  if (!self.settings.shouldLoadMore) {
    // return true if additional results should be loaded
    self.settings.shouldLoadMore = () => {
      const scroll_percent = dropdown_content.clientHeight / (dropdown_content.scrollHeight - dropdown_content.scrollTop);
      if (scroll_percent > 0.9) {
        return true;
      }
      if (self.activeOption) {
        var selectable = self.selectable();
        var index = Array.from(selectable).indexOf(self.activeOption);
        if (index >= selectable.length - 2) {
          return true;
        }
      }
      return false;
    };
  }
  if (!self.settings.firstUrl) {
    throw 'virtual_scroll plugin requires a firstUrl() method';
  }

  // in order for virtual scrolling to work,
  // options need to be ordered the same way they're returned from the remote data source
  self.settings.sortField = [{
    field: '$order'
  }, {
    field: '$score'
  }];

  // can we load more results for given query?
  const canLoadMore = query => {
    if (typeof self.settings.maxOptions === 'number' && dropdown_content.children.length >= self.settings.maxOptions) {
      return false;
    }
    if (query in pagination && pagination[query]) {
      return true;
    }
    return false;
  };
  const clearFilter = (option, value) => {
    if (self.items.indexOf(value) >= 0 || default_values.indexOf(value) >= 0) {
      return true;
    }
    return false;
  };

  // set the next url that will be
  self.setNextUrl = (value, next_url) => {
    pagination[value] = next_url;
  };

  // getUrl() to be used in settings.load()
  self.getUrl = query => {
    if (query in pagination) {
      const next_url = pagination[query];
      pagination[query] = false;
      return next_url;
    }

    // if the user goes back to a previous query
    // we need to load the first page again
    self.clearPagination();
    return self.settings.firstUrl.call(self, query);
  };

  // clear pagination
  self.clearPagination = () => {
    pagination = {};
  };

  // don't clear the active option (and cause unwanted dropdown scroll)
  // while loading more results
  self.hook('instead', 'clearActiveOption', () => {
    if (loading_more) {
      return;
    }
    return orig_clearActiveOption.call(self);
  });

  // override the canLoad method
  self.hook('instead', 'canLoad', query => {
    // first time the query has been seen
    if (!(query in pagination)) {
      return orig_canLoad.call(self, query);
    }
    return canLoadMore(query);
  });

  // wrap the load
  self.hook('instead', 'loadCallback', (options, optgroups) => {
    if (!loading_more) {
      self.clearOptions(clearFilter);
    } else if (load_more_opt) {
      const first_option = options[0];
      if (first_option !== undefined) {
        load_more_opt.dataset.value = first_option[self.settings.valueField];
      }
    }
    orig_loadCallback.call(self, options, optgroups);
    loading_more = false;
  });

  // add templates to dropdown
  //	loading_more if we have another url in the queue
  //	no_more_results if we don't have another url in the queue
  self.hook('after', 'refreshOptions', () => {
    const query = self.lastValue;
    var option;
    if (canLoadMore(query)) {
      option = self.render('loading_more', {
        query: query
      });
      if (option) {
        option.setAttribute('data-selectable', ''); // so that navigating dropdown with [down] keypresses can navigate to this node
        load_more_opt = option;
      }
    } else if (query in pagination && !dropdown_content.querySelector('.no-results')) {
      option = self.render('no_more_results', {
        query: query
      });
    }
    if (option) {
      addClasses(option, self.settings.optionClass);
      dropdown_content.append(option);
    }
  });

  // add scroll listener and default templates
  self.on('initialize', () => {
    default_values = Object.keys(self.options);
    dropdown_content = self.dropdown_content;

    // default templates
    self.settings.render = Object.assign({}, {
      loading_more: () => {
        return `<div class="loading-more-results">Loading more results ... </div>`;
      },
      no_more_results: () => {
        return `<div class="no-more-results">No more results</div>`;
      }
    }, self.settings.render);

    // watch dropdown content scroll position
    dropdown_content.addEventListener('scroll', () => {
      if (!self.settings.shouldLoadMore.call(self)) {
        return;
      }

      // !important: this will get checked again in load() but we still need to check here otherwise loading_more will be set to true
      if (!canLoadMore(self.lastValue)) {
        return;
      }

      // don't call load() too much
      if (loading_more) return;
      loading_more = true;
      self.load.call(self, self.lastValue);
    });
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/tom-select.complete.js":
/*!*****************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/tom-select.complete.js ***!
  \*****************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _tom_select_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./tom-select.js */ "./node_modules/tom-select/dist/esm/tom-select.js");
/* harmony import */ var _plugins_change_listener_plugin_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./plugins/change_listener/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/change_listener/plugin.js");
/* harmony import */ var _plugins_checkbox_options_plugin_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./plugins/checkbox_options/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/checkbox_options/plugin.js");
/* harmony import */ var _plugins_clear_button_plugin_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./plugins/clear_button/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/clear_button/plugin.js");
/* harmony import */ var _plugins_drag_drop_plugin_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./plugins/drag_drop/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/drag_drop/plugin.js");
/* harmony import */ var _plugins_dropdown_header_plugin_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./plugins/dropdown_header/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/dropdown_header/plugin.js");
/* harmony import */ var _plugins_caret_position_plugin_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./plugins/caret_position/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/caret_position/plugin.js");
/* harmony import */ var _plugins_dropdown_input_plugin_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./plugins/dropdown_input/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/dropdown_input/plugin.js");
/* harmony import */ var _plugins_input_autogrow_plugin_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./plugins/input_autogrow/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/input_autogrow/plugin.js");
/* harmony import */ var _plugins_no_backspace_delete_plugin_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./plugins/no_backspace_delete/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/no_backspace_delete/plugin.js");
/* harmony import */ var _plugins_no_active_items_plugin_js__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./plugins/no_active_items/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/no_active_items/plugin.js");
/* harmony import */ var _plugins_optgroup_columns_plugin_js__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./plugins/optgroup_columns/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/optgroup_columns/plugin.js");
/* harmony import */ var _plugins_remove_button_plugin_js__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./plugins/remove_button/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/remove_button/plugin.js");
/* harmony import */ var _plugins_restore_on_backspace_plugin_js__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! ./plugins/restore_on_backspace/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/restore_on_backspace/plugin.js");
/* harmony import */ var _plugins_virtual_scroll_plugin_js__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! ./plugins/virtual_scroll/plugin.js */ "./node_modules/tom-select/dist/esm/plugins/virtual_scroll/plugin.js");















_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('change_listener', _plugins_change_listener_plugin_js__WEBPACK_IMPORTED_MODULE_1__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('checkbox_options', _plugins_checkbox_options_plugin_js__WEBPACK_IMPORTED_MODULE_2__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('clear_button', _plugins_clear_button_plugin_js__WEBPACK_IMPORTED_MODULE_3__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('drag_drop', _plugins_drag_drop_plugin_js__WEBPACK_IMPORTED_MODULE_4__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('dropdown_header', _plugins_dropdown_header_plugin_js__WEBPACK_IMPORTED_MODULE_5__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('caret_position', _plugins_caret_position_plugin_js__WEBPACK_IMPORTED_MODULE_6__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('dropdown_input', _plugins_dropdown_input_plugin_js__WEBPACK_IMPORTED_MODULE_7__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('input_autogrow', _plugins_input_autogrow_plugin_js__WEBPACK_IMPORTED_MODULE_8__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('no_backspace_delete', _plugins_no_backspace_delete_plugin_js__WEBPACK_IMPORTED_MODULE_9__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('no_active_items', _plugins_no_active_items_plugin_js__WEBPACK_IMPORTED_MODULE_10__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('optgroup_columns', _plugins_optgroup_columns_plugin_js__WEBPACK_IMPORTED_MODULE_11__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('remove_button', _plugins_remove_button_plugin_js__WEBPACK_IMPORTED_MODULE_12__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('restore_on_backspace', _plugins_restore_on_backspace_plugin_js__WEBPACK_IMPORTED_MODULE_13__["default"]);
_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"].define('virtual_scroll', _plugins_virtual_scroll_plugin_js__WEBPACK_IMPORTED_MODULE_14__["default"]);
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_tom_select_js__WEBPACK_IMPORTED_MODULE_0__["default"]);
//# sourceMappingURL=tom-select.complete.js.map

/***/ }),

/***/ "./node_modules/tom-select/dist/esm/tom-select.js":
/*!********************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/tom-select.js ***!
  \********************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ TomSelect)
/* harmony export */ });
/* harmony import */ var _contrib_microevent_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./contrib/microevent.js */ "./node_modules/tom-select/dist/esm/contrib/microevent.js");
/* harmony import */ var _contrib_microplugin_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./contrib/microplugin.js */ "./node_modules/tom-select/dist/esm/contrib/microplugin.js");
/* harmony import */ var _orchidjs_sifter__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @orchidjs/sifter */ "./node_modules/@orchidjs/sifter/dist/esm/sifter.js");
/* harmony import */ var _orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @orchidjs/unicode-variants */ "./node_modules/@orchidjs/unicode-variants/dist/esm/index.js");
/* harmony import */ var _contrib_highlight_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./contrib/highlight.js */ "./node_modules/tom-select/dist/esm/contrib/highlight.js");
/* harmony import */ var _constants_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./constants.js */ "./node_modules/tom-select/dist/esm/constants.js");
/* harmony import */ var _getSettings_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./getSettings.js */ "./node_modules/tom-select/dist/esm/getSettings.js");
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./utils.js */ "./node_modules/tom-select/dist/esm/utils.js");
/* harmony import */ var _vanilla_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./vanilla.js */ "./node_modules/tom-select/dist/esm/vanilla.js");









var instance_i = 0;
class TomSelect extends (0,_contrib_microplugin_js__WEBPACK_IMPORTED_MODULE_1__["default"])(_contrib_microevent_js__WEBPACK_IMPORTED_MODULE_0__["default"]) {
    constructor(input_arg, user_settings) {
        super();
        this.order = 0;
        this.isOpen = false;
        this.isDisabled = false;
        this.isReadOnly = false;
        this.isInvalid = false; // @deprecated 1.8
        this.isValid = true;
        this.isLocked = false;
        this.isFocused = false;
        this.isInputHidden = false;
        this.isSetup = false;
        this.ignoreFocus = false;
        this.ignoreHover = false;
        this.hasOptions = false;
        this.lastValue = '';
        this.caretPos = 0;
        this.loading = 0;
        this.loadedSearches = {};
        this.activeOption = null;
        this.activeItems = [];
        this.optgroups = {};
        this.options = {};
        this.userOptions = {};
        this.items = [];
        this.refreshTimeout = null;
        instance_i++;
        var dir;
        var input = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)(input_arg);
        if (input.tomselect) {
            throw new Error('Tom Select already initialized on this element');
        }
        input.tomselect = this;
        // detect rtl environment
        var computedStyle = window.getComputedStyle && window.getComputedStyle(input, null);
        dir = computedStyle.getPropertyValue('direction');
        // setup default state
        const settings = (0,_getSettings_js__WEBPACK_IMPORTED_MODULE_6__["default"])(input, user_settings);
        this.settings = settings;
        this.input = input;
        this.tabIndex = input.tabIndex || 0;
        this.is_select_tag = input.tagName.toLowerCase() === 'select';
        this.rtl = /rtl/i.test(dir);
        this.inputId = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.getId)(input, 'tomselect-' + instance_i);
        this.isRequired = input.required;
        // search system
        this.sifter = new _orchidjs_sifter__WEBPACK_IMPORTED_MODULE_2__.Sifter(this.options, { diacritics: settings.diacritics });
        // option-dependent defaults
        settings.mode = settings.mode || (settings.maxItems === 1 ? 'single' : 'multi');
        if (typeof settings.hideSelected !== 'boolean') {
            settings.hideSelected = settings.mode === 'multi';
        }
        if (typeof settings.hidePlaceholder !== 'boolean') {
            settings.hidePlaceholder = settings.mode !== 'multi';
        }
        // set up createFilter callback
        var filter = settings.createFilter;
        if (typeof filter !== 'function') {
            if (typeof filter === 'string') {
                filter = new RegExp(filter);
            }
            if (filter instanceof RegExp) {
                settings.createFilter = (input) => filter.test(input);
            }
            else {
                settings.createFilter = (value) => {
                    return this.settings.duplicates || !this.options[value];
                };
            }
        }
        this.initializePlugins(settings.plugins);
        this.setupCallbacks();
        this.setupTemplates();
        // Create all elements
        const wrapper = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)('<div>');
        const control = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)('<div>');
        const dropdown = this._render('dropdown');
        const dropdown_content = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)(`<div role="listbox" tabindex="-1">`);
        const classes = this.input.getAttribute('class') || '';
        const inputMode = settings.mode;
        var control_input;
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(wrapper, settings.wrapperClass, classes, inputMode);
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(control, settings.controlClass);
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.append)(wrapper, control);
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(dropdown, settings.dropdownClass, inputMode);
        if (settings.copyClassesToDropdown) {
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(dropdown, classes);
        }
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(dropdown_content, settings.dropdownContentClass);
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.append)(dropdown, dropdown_content);
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)(settings.dropdownParent || wrapper).appendChild(dropdown);
        // default controlInput
        if ((0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.isHtmlString)(settings.controlInput)) {
            control_input = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)(settings.controlInput);
            // set attributes
            var attrs = ['autocorrect', 'autocapitalize', 'autocomplete', 'spellcheck'];
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(attrs, (attr) => {
                if (input.getAttribute(attr)) {
                    (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(control_input, { [attr]: input.getAttribute(attr) });
                }
            });
            control_input.tabIndex = -1;
            control.appendChild(control_input);
            this.focus_node = control_input;
            // dom element
        }
        else if (settings.controlInput) {
            control_input = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)(settings.controlInput);
            this.focus_node = control_input;
        }
        else {
            control_input = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)('<input/>');
            this.focus_node = control;
        }
        this.wrapper = wrapper;
        this.dropdown = dropdown;
        this.dropdown_content = dropdown_content;
        this.control = control;
        this.control_input = control_input;
        this.setup();
    }
    /**
     * set up event bindings.
     *
     */
    setup() {
        const self = this;
        const settings = self.settings;
        const control_input = self.control_input;
        const dropdown = self.dropdown;
        const dropdown_content = self.dropdown_content;
        const wrapper = self.wrapper;
        const control = self.control;
        const input = self.input;
        const focus_node = self.focus_node;
        const passive_event = { passive: true };
        const listboxId = self.inputId + '-ts-dropdown';
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(dropdown_content, {
            id: listboxId
        });
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(focus_node, {
            role: 'combobox',
            'aria-haspopup': 'listbox',
            'aria-expanded': 'false',
            'aria-controls': listboxId
        });
        const control_id = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.getId)(focus_node, self.inputId + '-ts-control');
        const query = "label[for='" + (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.escapeQuery)(self.inputId) + "']";
        const label = document.querySelector(query);
        const label_click = self.focus.bind(self);
        if (label) {
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(label, 'click', label_click);
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(label, { for: control_id });
            const label_id = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.getId)(label, self.inputId + '-ts-label');
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(focus_node, { 'aria-labelledby': label_id });
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(dropdown_content, { 'aria-labelledby': label_id });
        }
        wrapper.style.width = input.style.width;
        if (self.plugins.names.length) {
            const classes_plugins = 'plugin-' + self.plugins.names.join(' plugin-');
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)([wrapper, dropdown], classes_plugins);
        }
        if ((settings.maxItems === null || settings.maxItems > 1) && self.is_select_tag) {
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(input, { multiple: 'multiple' });
        }
        if (settings.placeholder) {
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(control_input, { placeholder: settings.placeholder });
        }
        // if splitOn was not passed in, construct it from the delimiter to allow pasting universally
        if (!settings.splitOn && settings.delimiter) {
            settings.splitOn = new RegExp('\\s*' + (0,_orchidjs_unicode_variants__WEBPACK_IMPORTED_MODULE_3__.escape_regex)(settings.delimiter) + '+\\s*');
        }
        // debounce user defined load() if loadThrottle > 0
        // after initializePlugins() so plugins can create/modify user defined loaders
        if (settings.load && settings.loadThrottle) {
            settings.load = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.loadDebounce)(settings.load, settings.loadThrottle);
        }
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(dropdown, 'mousemove', () => {
            self.ignoreHover = false;
        });
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(dropdown, 'mouseenter', (e) => {
            var target_match = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.parentMatch)(e.target, '[data-selectable]', dropdown);
            if (target_match)
                self.onOptionHover(e, target_match);
        }, { capture: true });
        // clicking on an option should select it
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(dropdown, 'click', (evt) => {
            const option = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.parentMatch)(evt.target, '[data-selectable]');
            if (option) {
                self.onOptionSelect(evt, option);
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(evt, true);
            }
        });
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(control, 'click', (evt) => {
            var target_match = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.parentMatch)(evt.target, '[data-ts-item]', control);
            if (target_match && self.onItemSelect(evt, target_match)) {
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(evt, true);
                return;
            }
            // retain focus (see control_input mousedown)
            if (control_input.value != '') {
                return;
            }
            self.onClick();
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(evt, true);
        });
        // keydown on focus_node for arrow_down/arrow_up
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(focus_node, 'keydown', (e) => self.onKeyDown(e));
        // keypress and input/keyup
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(control_input, 'keypress', (e) => self.onKeyPress(e));
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(control_input, 'input', (e) => self.onInput(e));
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(focus_node, 'blur', (e) => self.onBlur(e));
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(focus_node, 'focus', (e) => self.onFocus(e));
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(control_input, 'paste', (e) => self.onPaste(e));
        const doc_mousedown = (evt) => {
            // blur if target is outside of this instance
            // dropdown is not always inside wrapper
            const target = evt.composedPath()[0];
            if (!wrapper.contains(target) && !dropdown.contains(target)) {
                if (self.isFocused) {
                    self.blur();
                }
                self.inputState();
                return;
            }
            // retain focus by preventing native handling. if the
            // event target is the input it should not be modified.
            // otherwise, text selection within the input won't work.
            // Fixes bug #212 which is no covered by tests
            if (target == control_input && self.isOpen) {
                evt.stopPropagation();
                // clicking anywhere in the control should not blur the control_input (which would close the dropdown)
            }
            else {
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(evt, true);
            }
        };
        const win_scroll = () => {
            if (self.isOpen) {
                self.positionDropdown();
            }
        };
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(document, 'mousedown', doc_mousedown);
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(window, 'scroll', win_scroll, passive_event);
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(window, 'resize', win_scroll, passive_event);
        this._destroy = () => {
            document.removeEventListener('mousedown', doc_mousedown);
            window.removeEventListener('scroll', win_scroll);
            window.removeEventListener('resize', win_scroll);
            if (label)
                label.removeEventListener('click', label_click);
        };
        // store original html and tab index so that they can be
        // restored when the destroy() method is called.
        this.revertSettings = {
            innerHTML: input.innerHTML,
            tabIndex: input.tabIndex
        };
        input.tabIndex = -1;
        input.insertAdjacentElement('afterend', self.wrapper);
        self.sync(false);
        settings.items = [];
        delete settings.optgroups;
        delete settings.options;
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addEvent)(input, 'invalid', () => {
            if (self.isValid) {
                self.isValid = false;
                self.isInvalid = true;
                self.refreshState();
            }
        });
        self.updateOriginalInput();
        self.refreshItems();
        self.close(false);
        self.inputState();
        self.isSetup = true;
        if (input.disabled) {
            self.disable();
        }
        else if (input.readOnly) {
            self.setReadOnly(true);
        }
        else {
            self.enable(); //sets tabIndex
        }
        self.on('change', this.onChange);
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(input, 'tomselected', 'ts-hidden-accessible');
        self.trigger('initialize');
        // preload options
        if (settings.preload === true) {
            self.preload();
        }
    }
    /**
     * Register options and optgroups
     *
     */
    setupOptions(options = [], optgroups = []) {
        // build options table
        this.addOptions(options);
        // build optgroup table
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(optgroups, (optgroup) => {
            this.registerOptionGroup(optgroup);
        });
    }
    /**
     * Sets up default rendering functions.
     */
    setupTemplates() {
        var self = this;
        var field_label = self.settings.labelField;
        var field_optgroup = self.settings.optgroupLabelField;
        var templates = {
            'optgroup': (data) => {
                let optgroup = document.createElement('div');
                optgroup.className = 'optgroup';
                optgroup.appendChild(data.options);
                return optgroup;
            },
            'optgroup_header': (data, escape) => {
                return '<div class="optgroup-header">' + escape(data[field_optgroup]) + '</div>';
            },
            'option': (data, escape) => {
                return '<div>' + escape(data[field_label]) + '</div>';
            },
            'item': (data, escape) => {
                return '<div>' + escape(data[field_label]) + '</div>';
            },
            'option_create': (data, escape) => {
                return '<div class="create">Add <strong>' + escape(data.input) + '</strong>&hellip;</div>';
            },
            'no_results': () => {
                return '<div class="no-results">No results found</div>';
            },
            'loading': () => {
                return '<div class="spinner"></div>';
            },
            'not_loading': () => { },
            'dropdown': () => {
                return '<div></div>';
            }
        };
        self.settings.render = Object.assign({}, templates, self.settings.render);
    }
    /**
     * Maps fired events to callbacks provided
     * in the settings used when creating the control.
     */
    setupCallbacks() {
        var key, fn;
        var callbacks = {
            'initialize': 'onInitialize',
            'change': 'onChange',
            'item_add': 'onItemAdd',
            'item_remove': 'onItemRemove',
            'item_select': 'onItemSelect',
            'clear': 'onClear',
            'option_add': 'onOptionAdd',
            'option_remove': 'onOptionRemove',
            'option_clear': 'onOptionClear',
            'optgroup_add': 'onOptionGroupAdd',
            'optgroup_remove': 'onOptionGroupRemove',
            'optgroup_clear': 'onOptionGroupClear',
            'dropdown_open': 'onDropdownOpen',
            'dropdown_close': 'onDropdownClose',
            'type': 'onType',
            'load': 'onLoad',
            'focus': 'onFocus',
            'blur': 'onBlur'
        };
        for (key in callbacks) {
            fn = this.settings[callbacks[key]];
            if (fn)
                this.on(key, fn);
        }
    }
    /**
     * Sync the Tom Select instance with the original input or select
     *
     */
    sync(get_settings = true) {
        const self = this;
        const settings = get_settings ? (0,_getSettings_js__WEBPACK_IMPORTED_MODULE_6__["default"])(self.input, { delimiter: self.settings.delimiter }) : self.settings;
        self.setupOptions(settings.options, settings.optgroups);
        self.setValue(settings.items || [], true); // silent prevents recursion
        self.lastQuery = null; // so updated options will be displayed in dropdown
    }
    /**
     * Triggered when the main control element
     * has a click event.
     *
     */
    onClick() {
        var self = this;
        if (self.activeItems.length > 0) {
            self.clearActiveItems();
            self.focus();
            return;
        }
        if (self.isFocused && self.isOpen) {
            self.blur();
        }
        else {
            self.focus();
        }
    }
    /**
     * @deprecated v1.7
     *
     */
    onMouseDown() { }
    /**
     * Triggered when the value of the control has been changed.
     * This should propagate the event to the original DOM
     * input / select element.
     */
    onChange() {
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.triggerEvent)(this.input, 'input');
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.triggerEvent)(this.input, 'change');
    }
    /**
     * Triggered on <input> paste.
     *
     */
    onPaste(e) {
        var self = this;
        if (self.isInputHidden || self.isLocked) {
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
            return;
        }
        // If a regex or string is included, this will split the pasted
        // input and create Items for each separate value
        if (!self.settings.splitOn) {
            return;
        }
        // Wait for pasted text to be recognized in value
        setTimeout(() => {
            var pastedText = self.inputValue();
            if (!pastedText.match(self.settings.splitOn)) {
                return;
            }
            var splitInput = pastedText.trim().split(self.settings.splitOn);
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(splitInput, (piece) => {
                const hash = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(piece);
                if (hash) {
                    if (this.options[piece]) {
                        self.addItem(piece);
                    }
                    else {
                        self.createItem(piece);
                    }
                }
            });
        }, 0);
    }
    /**
     * Triggered on <input> keypress.
     *
     */
    onKeyPress(e) {
        var self = this;
        if (self.isLocked) {
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
            return;
        }
        var character = String.fromCharCode(e.keyCode || e.which);
        if (self.settings.create && self.settings.mode === 'multi' && character === self.settings.delimiter) {
            self.createItem();
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
            return;
        }
    }
    /**
     * Triggered on <input> keydown.
     *
     */
    onKeyDown(e) {
        var self = this;
        self.ignoreHover = true;
        if (self.isLocked) {
            if (e.keyCode !== _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_TAB) {
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
            }
            return;
        }
        switch (e.keyCode) {
            // ctrl+A: select all
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_A:
                if ((0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.isKeyDown)(_constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_SHORTCUT, e)) {
                    if (self.control_input.value == '') {
                        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
                        self.selectAll();
                        return;
                    }
                }
                break;
            // esc: close dropdown
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_ESC:
                if (self.isOpen) {
                    (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e, true);
                    self.close();
                }
                self.clearActiveItems();
                return;
            // down: open dropdown or move selection down
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_DOWN:
                if (!self.isOpen && self.hasOptions) {
                    self.open();
                }
                else if (self.activeOption) {
                    let next = self.getAdjacent(self.activeOption, 1);
                    if (next)
                        self.setActiveOption(next);
                }
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
                return;
            // up: move selection up
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_UP:
                if (self.activeOption) {
                    let prev = self.getAdjacent(self.activeOption, -1);
                    if (prev)
                        self.setActiveOption(prev);
                }
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
                return;
            // return: select active option
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_RETURN:
                if (self.canSelect(self.activeOption)) {
                    self.onOptionSelect(e, self.activeOption);
                    (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
                    // if the option_create=null, the dropdown might be closed
                }
                else if (self.settings.create && self.createItem()) {
                    (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
                    // don't submit form when searching for a value
                }
                else if (document.activeElement == self.control_input && self.isOpen) {
                    (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
                }
                return;
            // left: modifiy item selection to the left
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_LEFT:
                self.advanceSelection(-1, e);
                return;
            // right: modifiy item selection to the right
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_RIGHT:
                self.advanceSelection(1, e);
                return;
            // tab: select active option and/or create item
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_TAB:
                if (self.settings.selectOnTab) {
                    if (self.canSelect(self.activeOption)) {
                        self.onOptionSelect(e, self.activeOption);
                        // prevent default [tab] behaviour of jump to the next field
                        // if select isFull, then the dropdown won't be open and [tab] will work normally
                        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
                    }
                    if (self.settings.create && self.createItem()) {
                        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
                    }
                }
                return;
            // delete|backspace: delete items
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_BACKSPACE:
            case _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_DELETE:
                self.deleteSelection(e);
                return;
        }
        // don't enter text in the control_input when active items are selected
        if (self.isInputHidden && !(0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.isKeyDown)(_constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_SHORTCUT, e)) {
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
        }
    }
    /**
     * Triggered on <input> keyup.
     *
     */
    onInput(e) {
        if (this.isLocked) {
            return;
        }
        const value = this.inputValue();
        if (this.lastValue === value)
            return;
        this.lastValue = value;
        if (value == '') {
            this._onInput();
            return;
        }
        if (this.refreshTimeout) {
            window.clearTimeout(this.refreshTimeout);
        }
        this.refreshTimeout = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.timeout)(() => {
            this.refreshTimeout = null;
            this._onInput();
        }, this.settings.refreshThrottle);
    }
    _onInput() {
        const value = this.lastValue;
        if (this.settings.shouldLoad.call(this, value)) {
            this.load(value);
        }
        this.refreshOptions();
        this.trigger('type', value);
    }
    /**
     * Triggered when the user rolls over
     * an option in the autocomplete dropdown menu.
     *
     */
    onOptionHover(evt, option) {
        if (this.ignoreHover)
            return;
        this.setActiveOption(option, false);
    }
    /**
     * Triggered on <input> focus.
     *
     */
    onFocus(e) {
        var self = this;
        var wasFocused = self.isFocused;
        if (self.isDisabled || self.isReadOnly) {
            self.blur();
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
            return;
        }
        if (self.ignoreFocus)
            return;
        self.isFocused = true;
        if (self.settings.preload === 'focus')
            self.preload();
        if (!wasFocused)
            self.trigger('focus');
        if (!self.activeItems.length) {
            self.inputState();
            self.refreshOptions(!!self.settings.openOnFocus);
        }
        self.refreshState();
    }
    /**
     * Triggered on <input> blur.
     *
     */
    onBlur(e) {
        if (document.hasFocus() === false)
            return;
        var self = this;
        if (!self.isFocused)
            return;
        self.isFocused = false;
        self.ignoreFocus = false;
        var deactivate = () => {
            self.close();
            self.setActiveItem();
            self.setCaret(self.items.length);
            self.trigger('blur');
        };
        if (self.settings.create && self.settings.createOnBlur) {
            self.createItem(null, deactivate);
        }
        else {
            deactivate();
        }
    }
    /**
     * Triggered when the user clicks on an option
     * in the autocomplete dropdown menu.
     *
     */
    onOptionSelect(evt, option) {
        var value, self = this;
        // should not be possible to trigger a option under a disabled optgroup
        if (option.parentElement && option.parentElement.matches('[data-disabled]')) {
            return;
        }
        if (option.classList.contains('create')) {
            self.createItem(null, () => {
                if (self.settings.closeAfterSelect) {
                    self.close();
                }
            });
        }
        else {
            value = option.dataset.value;
            if (typeof value !== 'undefined') {
                self.lastQuery = null;
                self.addItem(value);
                if (self.settings.closeAfterSelect) {
                    self.close();
                }
                if (!self.settings.hideSelected && evt.type && /click/.test(evt.type)) {
                    self.setActiveOption(option);
                }
            }
        }
    }
    /**
     * Return true if the given option can be selected
     *
     */
    canSelect(option) {
        if (this.isOpen && option && this.dropdown_content.contains(option)) {
            return true;
        }
        return false;
    }
    /**
     * Triggered when the user clicks on an item
     * that has been selected.
     *
     */
    onItemSelect(evt, item) {
        var self = this;
        if (!self.isLocked && self.settings.mode === 'multi') {
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(evt);
            self.setActiveItem(item, evt);
            return true;
        }
        return false;
    }
    /**
     * Determines whether or not to invoke
     * the user-provided option provider / loader
     *
     * Note, there is a subtle difference between
     * this.canLoad() and this.settings.shouldLoad();
     *
     *	- settings.shouldLoad() is a user-input validator.
     *	When false is returned, the not_loading template
     *	will be added to the dropdown
     *
     *	- canLoad() is lower level validator that checks
     * 	the Tom Select instance. There is no inherent user
     *	feedback when canLoad returns false
     *
     */
    canLoad(value) {
        if (!this.settings.load)
            return false;
        if (this.loadedSearches.hasOwnProperty(value))
            return false;
        return true;
    }
    /**
     * Invokes the user-provided option provider / loader.
     *
     */
    load(value) {
        const self = this;
        if (!self.canLoad(value))
            return;
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(self.wrapper, self.settings.loadingClass);
        self.loading++;
        const callback = self.loadCallback.bind(self);
        self.settings.load.call(self, value, callback);
    }
    /**
     * Invoked by the user-provided option provider
     *
     */
    loadCallback(options, optgroups) {
        const self = this;
        self.loading = Math.max(self.loading - 1, 0);
        self.lastQuery = null;
        self.clearActiveOption(); // when new results load, focus should be on first option
        self.setupOptions(options, optgroups);
        self.refreshOptions(self.isFocused && !self.isInputHidden);
        if (!self.loading) {
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.removeClasses)(self.wrapper, self.settings.loadingClass);
        }
        self.trigger('load', options, optgroups);
    }
    preload() {
        var classList = this.wrapper.classList;
        if (classList.contains('preloaded'))
            return;
        classList.add('preloaded');
        this.load('');
    }
    /**
     * Sets the input field of the control to the specified value.
     *
     */
    setTextboxValue(value = '') {
        var input = this.control_input;
        var changed = input.value !== value;
        if (changed) {
            input.value = value;
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.triggerEvent)(input, 'update');
            this.lastValue = value;
        }
    }
    /**
     * Returns the value of the control. If multiple items
     * can be selected (e.g. <select multiple>), this returns
     * an array. If only one item can be selected, this
     * returns a string.
     *
     */
    getValue() {
        if (this.is_select_tag && this.input.hasAttribute('multiple')) {
            return this.items;
        }
        return this.items.join(this.settings.delimiter);
    }
    /**
     * Resets the selected items to the given value.
     *
     */
    setValue(value, silent) {
        var events = silent ? [] : ['change'];
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.debounce_events)(this, events, () => {
            this.clear(silent);
            this.addItems(value, silent);
        });
    }
    /**
     * Resets the number of max items to the given value
     *
     */
    setMaxItems(value) {
        if (value === 0)
            value = null; //reset to unlimited items.
        this.settings.maxItems = value;
        this.refreshState();
    }
    /**
     * Sets the selected item.
     *
     */
    setActiveItem(item, e) {
        var self = this;
        var eventName;
        var i, begin, end, swap;
        var last;
        if (self.settings.mode === 'single')
            return;
        // clear the active selection
        if (!item) {
            self.clearActiveItems();
            if (self.isFocused) {
                self.inputState();
            }
            return;
        }
        // modify selection
        eventName = e && e.type.toLowerCase();
        if (eventName === 'click' && (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.isKeyDown)('shiftKey', e) && self.activeItems.length) {
            last = self.getLastActive();
            begin = Array.prototype.indexOf.call(self.control.children, last);
            end = Array.prototype.indexOf.call(self.control.children, item);
            if (begin > end) {
                swap = begin;
                begin = end;
                end = swap;
            }
            for (i = begin; i <= end; i++) {
                item = self.control.children[i];
                if (self.activeItems.indexOf(item) === -1) {
                    self.setActiveItemClass(item);
                }
            }
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e);
        }
        else if ((eventName === 'click' && (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.isKeyDown)(_constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_SHORTCUT, e)) || (eventName === 'keydown' && (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.isKeyDown)('shiftKey', e))) {
            if (item.classList.contains('active')) {
                self.removeActiveItem(item);
            }
            else {
                self.setActiveItemClass(item);
            }
        }
        else {
            self.clearActiveItems();
            self.setActiveItemClass(item);
        }
        // ensure control has focus
        self.inputState();
        if (!self.isFocused) {
            self.focus();
        }
    }
    /**
     * Set the active and last-active classes
     *
     */
    setActiveItemClass(item) {
        const self = this;
        const last_active = self.control.querySelector('.last-active');
        if (last_active)
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.removeClasses)(last_active, 'last-active');
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(item, 'active last-active');
        self.trigger('item_select', item);
        if (self.activeItems.indexOf(item) == -1) {
            self.activeItems.push(item);
        }
    }
    /**
     * Remove active item
     *
     */
    removeActiveItem(item) {
        var idx = this.activeItems.indexOf(item);
        this.activeItems.splice(idx, 1);
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.removeClasses)(item, 'active');
    }
    /**
     * Clears all the active items
     *
     */
    clearActiveItems() {
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.removeClasses)(this.activeItems, 'active');
        this.activeItems = [];
    }
    /**
     * Sets the selected item in the dropdown menu
     * of available options.
     *
     */
    setActiveOption(option, scroll = true) {
        if (option === this.activeOption) {
            return;
        }
        this.clearActiveOption();
        if (!option)
            return;
        this.activeOption = option;
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(this.focus_node, { 'aria-activedescendant': option.getAttribute('id') });
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(option, { 'aria-selected': 'true' });
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(option, 'active');
        if (scroll)
            this.scrollToOption(option);
    }
    /**
     * Sets the dropdown_content scrollTop to display the option
     *
     */
    scrollToOption(option, behavior) {
        if (!option)
            return;
        const content = this.dropdown_content;
        const height_menu = content.clientHeight;
        const scrollTop = content.scrollTop || 0;
        const height_item = option.offsetHeight;
        const y = option.getBoundingClientRect().top - content.getBoundingClientRect().top + scrollTop;
        if (y + height_item > height_menu + scrollTop) {
            this.scroll(y - height_menu + height_item, behavior);
        }
        else if (y < scrollTop) {
            this.scroll(y, behavior);
        }
    }
    /**
     * Scroll the dropdown to the given position
     *
     */
    scroll(scrollTop, behavior) {
        const content = this.dropdown_content;
        if (behavior) {
            content.style.scrollBehavior = behavior;
        }
        content.scrollTop = scrollTop;
        content.style.scrollBehavior = '';
    }
    /**
     * Clears the active option
     *
     */
    clearActiveOption() {
        if (this.activeOption) {
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.removeClasses)(this.activeOption, 'active');
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(this.activeOption, { 'aria-selected': null });
        }
        this.activeOption = null;
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(this.focus_node, { 'aria-activedescendant': null });
    }
    /**
     * Selects all items (CTRL + A).
     */
    selectAll() {
        const self = this;
        if (self.settings.mode === 'single')
            return;
        const activeItems = self.controlChildren();
        if (!activeItems.length)
            return;
        self.inputState();
        self.close();
        self.activeItems = activeItems;
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(activeItems, (item) => {
            self.setActiveItemClass(item);
        });
    }
    /**
     * Determines if the control_input should be in a hidden or visible state
     *
     */
    inputState() {
        var self = this;
        if (!self.control.contains(self.control_input))
            return;
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(self.control_input, { placeholder: self.settings.placeholder });
        if (self.activeItems.length > 0 || (!self.isFocused && self.settings.hidePlaceholder && self.items.length > 0)) {
            self.setTextboxValue();
            self.isInputHidden = true;
        }
        else {
            if (self.settings.hidePlaceholder && self.items.length > 0) {
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(self.control_input, { placeholder: '' });
            }
            self.isInputHidden = false;
        }
        self.wrapper.classList.toggle('input-hidden', self.isInputHidden);
    }
    /**
     * Get the input value
     */
    inputValue() {
        return this.control_input.value.trim();
    }
    /**
     * Gives the control focus.
     */
    focus() {
        var self = this;
        if (self.isDisabled || self.isReadOnly)
            return;
        self.ignoreFocus = true;
        if (self.control_input.offsetWidth) {
            self.control_input.focus();
        }
        else {
            self.focus_node.focus();
        }
        setTimeout(() => {
            self.ignoreFocus = false;
            self.onFocus();
        }, 0);
    }
    /**
     * Forces the control out of focus.
     *
     */
    blur() {
        this.focus_node.blur();
        this.onBlur();
    }
    /**
     * Returns a function that scores an object
     * to show how good of a match it is to the
     * provided query.
     *
     * @return {function}
     */
    getScoreFunction(query) {
        return this.sifter.getScoreFunction(query, this.getSearchOptions());
    }
    /**
     * Returns search options for sifter (the system
     * for scoring and sorting results).
     *
     * @see https://github.com/orchidjs/sifter.js
     * @return {object}
     */
    getSearchOptions() {
        var settings = this.settings;
        var sort = settings.sortField;
        if (typeof settings.sortField === 'string') {
            sort = [{ field: settings.sortField }];
        }
        return {
            fields: settings.searchField,
            conjunction: settings.searchConjunction,
            sort: sort,
            nesting: settings.nesting
        };
    }
    /**
     * Searches through available options and returns
     * a sorted array of matches.
     *
     */
    search(query) {
        var result, calculateScore;
        var self = this;
        var options = this.getSearchOptions();
        // validate user-provided result scoring function
        if (self.settings.score) {
            calculateScore = self.settings.score.call(self, query);
            if (typeof calculateScore !== 'function') {
                throw new Error('Tom Select "score" setting must be a function that returns a function');
            }
        }
        // perform search
        if (query !== self.lastQuery) {
            self.lastQuery = query;
            result = self.sifter.search(query, Object.assign(options, { score: calculateScore }));
            self.currentResults = result;
        }
        else {
            result = Object.assign({}, self.currentResults);
        }
        // filter out selected items
        if (self.settings.hideSelected) {
            result.items = result.items.filter((item) => {
                let hashed = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(item.id);
                return !(hashed && self.items.indexOf(hashed) !== -1);
            });
        }
        return result;
    }
    /**
     * Refreshes the list of available options shown
     * in the autocomplete dropdown menu.
     *
     */
    refreshOptions(triggerDropdown = true) {
        var i, j, k, n, optgroup, optgroups, html, has_create_option, active_group;
        var create;
        const groups = {};
        const groups_order = [];
        var self = this;
        var query = self.inputValue();
        const same_query = query === self.lastQuery || (query == '' && self.lastQuery == null);
        var results = self.search(query);
        var active_option = null;
        var show_dropdown = self.settings.shouldOpen || false;
        var dropdown_content = self.dropdown_content;
        if (same_query) {
            active_option = self.activeOption;
            if (active_option) {
                active_group = active_option.closest('[data-group]');
            }
        }
        // build markup
        n = results.items.length;
        if (typeof self.settings.maxOptions === 'number') {
            n = Math.min(n, self.settings.maxOptions);
        }
        if (n > 0) {
            show_dropdown = true;
        }
        // get fragment for group and the position of the group in group_order
        const getGroupFragment = (optgroup, order) => {
            let group_order_i = groups[optgroup];
            if (group_order_i !== undefined) {
                let order_group = groups_order[group_order_i];
                if (order_group !== undefined) {
                    return [group_order_i, order_group.fragment];
                }
            }
            let group_fragment = document.createDocumentFragment();
            group_order_i = groups_order.length;
            groups_order.push({ fragment: group_fragment, order, optgroup });
            return [group_order_i, group_fragment];
        };
        // render and group available options individually
        for (i = 0; i < n; i++) {
            // get option dom element
            let item = results.items[i];
            if (!item)
                continue;
            let opt_value = item.id;
            let option = self.options[opt_value];
            if (option === undefined)
                continue;
            let opt_hash = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.get_hash)(opt_value);
            let option_el = self.getOption(opt_hash, true);
            // toggle 'selected' class
            if (!self.settings.hideSelected) {
                option_el.classList.toggle('selected', self.items.includes(opt_hash));
            }
            optgroup = option[self.settings.optgroupField] || '';
            optgroups = Array.isArray(optgroup) ? optgroup : [optgroup];
            for (j = 0, k = optgroups && optgroups.length; j < k; j++) {
                optgroup = optgroups[j];
                let order = option.$order;
                let self_optgroup = self.optgroups[optgroup];
                if (self_optgroup === undefined) {
                    optgroup = '';
                }
                else {
                    order = self_optgroup.$order;
                }
                const [group_order_i, group_fragment] = getGroupFragment(optgroup, order);
                // nodes can only have one parent, so if the option is in mutple groups, we need a clone
                if (j > 0) {
                    option_el = option_el.cloneNode(true);
                    (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(option_el, { id: option.$id + '-clone-' + j, 'aria-selected': null });
                    option_el.classList.add('ts-cloned');
                    (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.removeClasses)(option_el, 'active');
                    // make sure we keep the activeOption in the same group
                    if (self.activeOption && self.activeOption.dataset.value == opt_value) {
                        if (active_group && active_group.dataset.group === optgroup.toString()) {
                            active_option = option_el;
                        }
                    }
                }
                group_fragment.appendChild(option_el);
                if (optgroup != '') {
                    groups[optgroup] = group_order_i;
                }
            }
        }
        // sort optgroups
        if (self.settings.lockOptgroupOrder) {
            groups_order.sort((a, b) => {
                return a.order - b.order;
            });
        }
        // render optgroup headers & join groups
        html = document.createDocumentFragment();
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(groups_order, (group_order) => {
            let group_fragment = group_order.fragment;
            let optgroup = group_order.optgroup;
            if (!group_fragment || !group_fragment.children.length)
                return;
            let group_heading = self.optgroups[optgroup];
            if (group_heading !== undefined) {
                let group_options = document.createDocumentFragment();
                let header = self.render('optgroup_header', group_heading);
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.append)(group_options, header);
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.append)(group_options, group_fragment);
                let group_html = self.render('optgroup', { group: group_heading, options: group_options });
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.append)(html, group_html);
            }
            else {
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.append)(html, group_fragment);
            }
        });
        dropdown_content.innerHTML = '';
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.append)(dropdown_content, html);
        // highlight matching terms inline
        if (self.settings.highlight) {
            (0,_contrib_highlight_js__WEBPACK_IMPORTED_MODULE_4__.removeHighlight)(dropdown_content);
            if (results.query.length && results.tokens.length) {
                (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(results.tokens, (tok) => {
                    (0,_contrib_highlight_js__WEBPACK_IMPORTED_MODULE_4__.highlight)(dropdown_content, tok.regex);
                });
            }
        }
        // helper method for adding templates to dropdown
        var add_template = (template) => {
            let content = self.render(template, { input: query });
            if (content) {
                show_dropdown = true;
                dropdown_content.insertBefore(content, dropdown_content.firstChild);
            }
            return content;
        };
        // add loading message
        if (self.loading) {
            add_template('loading');
            // invalid query
        }
        else if (!self.settings.shouldLoad.call(self, query)) {
            add_template('not_loading');
            // add no_results message
        }
        else if (results.items.length === 0) {
            add_template('no_results');
        }
        // add create option
        has_create_option = self.canCreate(query);
        if (has_create_option) {
            create = add_template('option_create');
        }
        // activate
        self.hasOptions = results.items.length > 0 || has_create_option;
        if (show_dropdown) {
            if (results.items.length > 0) {
                if (!active_option && self.settings.mode === 'single' && self.items[0] != undefined) {
                    active_option = self.getOption(self.items[0]);
                }
                if (!dropdown_content.contains(active_option)) {
                    let active_index = 0;
                    if (create && !self.settings.addPrecedence) {
                        active_index = 1;
                    }
                    active_option = self.selectable()[active_index];
                }
            }
            else if (create) {
                active_option = create;
            }
            if (triggerDropdown && !self.isOpen) {
                self.open();
                self.scrollToOption(active_option, 'auto');
            }
            self.setActiveOption(active_option);
        }
        else {
            self.clearActiveOption();
            if (triggerDropdown && self.isOpen) {
                self.close(false); // if create_option=null, we want the dropdown to close but not reset the textbox value
            }
        }
    }
    /**
     * Return list of selectable options
     *
     */
    selectable() {
        return this.dropdown_content.querySelectorAll('[data-selectable]');
    }
    /**
     * Adds an available option. If it already exists,
     * nothing will happen. Note: this does not refresh
     * the options list dropdown (use `refreshOptions`
     * for that).
     *
     * Usage:
     *
     *   this.addOption(data)
     *
     */
    addOption(data, user_created = false) {
        const self = this;
        // @deprecated 1.7.7
        // use addOptions( array, user_created ) for adding multiple options
        if (Array.isArray(data)) {
            self.addOptions(data, user_created);
            return false;
        }
        const key = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(data[self.settings.valueField]);
        if (key === null || self.options.hasOwnProperty(key)) {
            return false;
        }
        data.$order = data.$order || ++self.order;
        data.$id = self.inputId + '-opt-' + data.$order;
        self.options[key] = data;
        self.lastQuery = null;
        if (user_created) {
            self.userOptions[key] = user_created;
            self.trigger('option_add', key, data);
        }
        return key;
    }
    /**
     * Add multiple options
     *
     */
    addOptions(data, user_created = false) {
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(data, (dat) => {
            this.addOption(dat, user_created);
        });
    }
    /**
     * @deprecated 1.7.7
     */
    registerOption(data) {
        return this.addOption(data);
    }
    /**
     * Registers an option group to the pool of option groups.
     *
     * @return {boolean|string}
     */
    registerOptionGroup(data) {
        var key = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(data[this.settings.optgroupValueField]);
        if (key === null)
            return false;
        data.$order = data.$order || ++this.order;
        this.optgroups[key] = data;
        return key;
    }
    /**
     * Registers a new optgroup for options
     * to be bucketed into.
     *
     */
    addOptionGroup(id, data) {
        var hashed_id;
        data[this.settings.optgroupValueField] = id;
        if (hashed_id = this.registerOptionGroup(data)) {
            this.trigger('optgroup_add', hashed_id, data);
        }
    }
    /**
     * Removes an existing option group.
     *
     */
    removeOptionGroup(id) {
        if (this.optgroups.hasOwnProperty(id)) {
            delete this.optgroups[id];
            this.clearCache();
            this.trigger('optgroup_remove', id);
        }
    }
    /**
     * Clears all existing option groups.
     */
    clearOptionGroups() {
        this.optgroups = {};
        this.clearCache();
        this.trigger('optgroup_clear');
    }
    /**
     * Updates an option available for selection. If
     * it is visible in the selected items or options
     * dropdown, it will be re-rendered automatically.
     *
     */
    updateOption(value, data) {
        const self = this;
        var item_new;
        var index_item;
        const value_old = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(value);
        const value_new = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(data[self.settings.valueField]);
        // sanity checks
        if (value_old === null)
            return;
        const data_old = self.options[value_old];
        if (data_old == undefined)
            return;
        if (typeof value_new !== 'string')
            throw new Error('Value must be set in option data');
        const option = self.getOption(value_old);
        const item = self.getItem(value_old);
        data.$order = data.$order || data_old.$order;
        delete self.options[value_old];
        // invalidate render cache
        // don't remove existing node yet, we'll remove it after replacing it
        self.uncacheValue(value_new);
        self.options[value_new] = data;
        // update the option if it's in the dropdown
        if (option) {
            if (self.dropdown_content.contains(option)) {
                const option_new = self._render('option', data);
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.replaceNode)(option, option_new);
                if (self.activeOption === option) {
                    self.setActiveOption(option_new);
                }
            }
            option.remove();
        }
        // update the item if we have one
        if (item) {
            index_item = self.items.indexOf(value_old);
            if (index_item !== -1) {
                self.items.splice(index_item, 1, value_new);
            }
            item_new = self._render('item', data);
            if (item.classList.contains('active'))
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(item_new, 'active');
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.replaceNode)(item, item_new);
        }
        // invalidate last query because we might have updated the sortField
        self.lastQuery = null;
    }
    /**
     * Removes a single option.
     *
     */
    removeOption(value, silent) {
        const self = this;
        value = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.get_hash)(value);
        self.uncacheValue(value);
        delete self.userOptions[value];
        delete self.options[value];
        self.lastQuery = null;
        self.trigger('option_remove', value);
        self.removeItem(value, silent);
    }
    /**
     * Clears all options.
     */
    clearOptions(filter) {
        const boundFilter = (filter || this.clearFilter).bind(this);
        this.loadedSearches = {};
        this.userOptions = {};
        this.clearCache();
        const selected = {};
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(this.options, (option, key) => {
            if (boundFilter(option, key)) {
                selected[key] = option;
            }
        });
        this.options = this.sifter.items = selected;
        this.lastQuery = null;
        this.trigger('option_clear');
    }
    /**
     * Used by clearOptions() to decide whether or not an option should be removed
     * Return true to keep an option, false to remove
     *
     */
    clearFilter(option, value) {
        if (this.items.indexOf(value) >= 0) {
            return true;
        }
        return false;
    }
    /**
     * Returns the dom element of the option
     * matching the given value.
     *
     */
    getOption(value, create = false) {
        const hashed = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(value);
        if (hashed === null)
            return null;
        const option = this.options[hashed];
        if (option != undefined) {
            if (option.$div) {
                return option.$div;
            }
            if (create) {
                return this._render('option', option);
            }
        }
        return null;
    }
    /**
     * Returns the dom element of the next or previous dom element of the same type
     * Note: adjacent options may not be adjacent DOM elements (optgroups)
     *
     */
    getAdjacent(option, direction, type = 'option') {
        var self = this, all;
        if (!option) {
            return null;
        }
        if (type == 'item') {
            all = self.controlChildren();
        }
        else {
            all = self.dropdown_content.querySelectorAll('[data-selectable]');
        }
        for (let i = 0; i < all.length; i++) {
            if (all[i] != option) {
                continue;
            }
            if (direction > 0) {
                return all[i + 1];
            }
            return all[i - 1];
        }
        return null;
    }
    /**
     * Returns the dom element of the item
     * matching the given value.
     *
     */
    getItem(item) {
        if (typeof item == 'object') {
            return item;
        }
        var value = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(item);
        return value !== null
            ? this.control.querySelector(`[data-value="${(0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addSlashes)(value)}"]`)
            : null;
    }
    /**
     * "Selects" multiple items at once. Adds them to the list
     * at the current caret position.
     *
     */
    addItems(values, silent) {
        var self = this;
        var items = Array.isArray(values) ? values : [values];
        items = items.filter(x => self.items.indexOf(x) === -1);
        const last_item = items[items.length - 1];
        items.forEach(item => {
            self.isPending = (item !== last_item);
            self.addItem(item, silent);
        });
    }
    /**
     * "Selects" an item. Adds it to the list
     * at the current caret position.
     *
     */
    addItem(value, silent) {
        var events = silent ? [] : ['change', 'dropdown_close'];
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.debounce_events)(this, events, () => {
            var item, wasFull;
            const self = this;
            const inputMode = self.settings.mode;
            const hashed = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(value);
            if (hashed && self.items.indexOf(hashed) !== -1) {
                if (inputMode === 'single') {
                    self.close();
                }
                if (inputMode === 'single' || !self.settings.duplicates) {
                    return;
                }
            }
            if (hashed === null || !self.options.hasOwnProperty(hashed))
                return;
            if (inputMode === 'single')
                self.clear(silent);
            if (inputMode === 'multi' && self.isFull())
                return;
            item = self._render('item', self.options[hashed]);
            if (self.control.contains(item)) { // duplicates
                item = item.cloneNode(true);
            }
            wasFull = self.isFull();
            self.items.splice(self.caretPos, 0, hashed);
            self.insertAtCaret(item);
            if (self.isSetup) {
                // update menu / remove the option (if this is not one item being added as part of series)
                if (!self.isPending && self.settings.hideSelected) {
                    let option = self.getOption(hashed);
                    let next = self.getAdjacent(option, 1);
                    if (next) {
                        self.setActiveOption(next);
                    }
                }
                // refreshOptions after setActiveOption(),
                // otherwise setActiveOption() will be called by refreshOptions() with the wrong value
                if (!self.isPending && !self.settings.closeAfterSelect) {
                    self.refreshOptions(self.isFocused && inputMode !== 'single');
                }
                // hide the menu if the maximum number of items have been selected or no options are left
                if (self.settings.closeAfterSelect != false && self.isFull()) {
                    self.close();
                }
                else if (!self.isPending) {
                    self.positionDropdown();
                }
                self.trigger('item_add', hashed, item);
                if (!self.isPending) {
                    self.updateOriginalInput({ silent: silent });
                }
            }
            if (!self.isPending || (!wasFull && self.isFull())) {
                self.inputState();
                self.refreshState();
            }
        });
    }
    /**
     * Removes the selected item matching
     * the provided value.
     *
     */
    removeItem(item = null, silent) {
        const self = this;
        item = self.getItem(item);
        if (!item)
            return;
        var i, idx;
        const value = item.dataset.value;
        i = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.nodeIndex)(item);
        item.remove();
        if (item.classList.contains('active')) {
            idx = self.activeItems.indexOf(item);
            self.activeItems.splice(idx, 1);
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.removeClasses)(item, 'active');
        }
        self.items.splice(i, 1);
        self.lastQuery = null;
        if (!self.settings.persist && self.userOptions.hasOwnProperty(value)) {
            self.removeOption(value, silent);
        }
        if (i < self.caretPos) {
            self.setCaret(self.caretPos - 1);
        }
        self.updateOriginalInput({ silent: silent });
        self.refreshState();
        self.positionDropdown();
        self.trigger('item_remove', value, item);
    }
    /**
     * Invokes the `create` method provided in the
     * TomSelect options that should provide the data
     * for the new item, given the user input.
     *
     * Once this completes, it will be added
     * to the item list.
     *
     */
    createItem(input = null, callback = () => { }) {
        // triggerDropdown parameter @deprecated 2.1.1
        if (arguments.length === 3) {
            callback = arguments[2];
        }
        if (typeof callback != 'function') {
            callback = () => { };
        }
        var self = this;
        var caret = self.caretPos;
        var output;
        input = input || self.inputValue();
        if (!self.canCreate(input)) {
            callback();
            return false;
        }
        self.lock();
        var created = false;
        var create = (data) => {
            self.unlock();
            if (!data || typeof data !== 'object')
                return callback();
            var value = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.hash_key)(data[self.settings.valueField]);
            if (typeof value !== 'string') {
                return callback();
            }
            self.setTextboxValue();
            self.addOption(data, true);
            self.setCaret(caret);
            self.addItem(value);
            callback(data);
            created = true;
        };
        if (typeof self.settings.create === 'function') {
            output = self.settings.create.call(this, input, create);
        }
        else {
            output = {
                [self.settings.labelField]: input,
                [self.settings.valueField]: input,
            };
        }
        if (!created) {
            create(output);
        }
        return true;
    }
    /**
     * Re-renders the selected item lists.
     */
    refreshItems() {
        var self = this;
        self.lastQuery = null;
        if (self.isSetup) {
            self.addItems(self.items);
        }
        self.updateOriginalInput();
        self.refreshState();
    }
    /**
     * Updates all state-dependent attributes
     * and CSS classes.
     */
    refreshState() {
        const self = this;
        self.refreshValidityState();
        const isFull = self.isFull();
        const isLocked = self.isLocked;
        self.wrapper.classList.toggle('rtl', self.rtl);
        const wrap_classList = self.wrapper.classList;
        wrap_classList.toggle('focus', self.isFocused);
        wrap_classList.toggle('disabled', self.isDisabled);
        wrap_classList.toggle('readonly', self.isReadOnly);
        wrap_classList.toggle('required', self.isRequired);
        wrap_classList.toggle('invalid', !self.isValid);
        wrap_classList.toggle('locked', isLocked);
        wrap_classList.toggle('full', isFull);
        wrap_classList.toggle('input-active', self.isFocused && !self.isInputHidden);
        wrap_classList.toggle('dropdown-active', self.isOpen);
        wrap_classList.toggle('has-options', (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.isEmptyObject)(self.options));
        wrap_classList.toggle('has-items', self.items.length > 0);
    }
    /**
     * Update the `required` attribute of both input and control input.
     *
     * The `required` property needs to be activated on the control input
     * for the error to be displayed at the right place. `required` also
     * needs to be temporarily deactivated on the input since the input is
     * hidden and can't show errors.
     */
    refreshValidityState() {
        var self = this;
        if (!self.input.validity) {
            return;
        }
        self.isValid = self.input.validity.valid;
        self.isInvalid = !self.isValid;
    }
    /**
     * Determines whether or not more items can be added
     * to the control without exceeding the user-defined maximum.
     *
     * @returns {boolean}
     */
    isFull() {
        return this.settings.maxItems !== null && this.items.length >= this.settings.maxItems;
    }
    /**
     * Refreshes the original <select> or <input>
     * element to reflect the current state.
     *
     */
    updateOriginalInput(opts = {}) {
        const self = this;
        var option, label;
        const empty_option = self.input.querySelector('option[value=""]');
        if (self.is_select_tag) {
            const selected = [];
            const has_selected = self.input.querySelectorAll('option:checked').length;
            function AddSelected(option_el, value, label) {
                if (!option_el) {
                    option_el = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)('<option value="' + (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.escape_html)(value) + '">' + (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.escape_html)(label) + '</option>');
                }
                // don't move empty option from top of list
                // fixes bug in firefox https://bugzilla.mozilla.org/show_bug.cgi?id=1725293
                if (option_el != empty_option) {
                    self.input.append(option_el);
                }
                selected.push(option_el);
                // marking empty option as selected can break validation
                // fixes https://github.com/orchidjs/tom-select/issues/303
                if (option_el != empty_option || has_selected > 0) {
                    option_el.selected = true;
                }
                return option_el;
            }
            // unselect all selected options
            self.input.querySelectorAll('option:checked').forEach((option_el) => {
                option_el.selected = false;
            });
            // nothing selected?
            if (self.items.length == 0 && self.settings.mode == 'single') {
                AddSelected(empty_option, "", "");
                // order selected <option> tags for values in self.items
            }
            else {
                self.items.forEach((value) => {
                    option = self.options[value];
                    label = option[self.settings.labelField] || '';
                    if (selected.includes(option.$option)) {
                        const reuse_opt = self.input.querySelector(`option[value="${(0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.addSlashes)(value)}"]:not(:checked)`);
                        AddSelected(reuse_opt, value, label);
                    }
                    else {
                        option.$option = AddSelected(option.$option, value, label);
                    }
                });
            }
        }
        else {
            self.input.value = self.getValue();
        }
        if (self.isSetup) {
            if (!opts.silent) {
                self.trigger('change', self.getValue());
            }
        }
    }
    /**
     * Shows the autocomplete dropdown containing
     * the available options.
     */
    open() {
        var self = this;
        if (self.isLocked || self.isOpen || (self.settings.mode === 'multi' && self.isFull()))
            return;
        self.isOpen = true;
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(self.focus_node, { 'aria-expanded': 'true' });
        self.refreshState();
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.applyCSS)(self.dropdown, { visibility: 'hidden', display: 'block' });
        self.positionDropdown();
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.applyCSS)(self.dropdown, { visibility: 'visible', display: 'block' });
        self.focus();
        self.trigger('dropdown_open', self.dropdown);
    }
    /**
     * Closes the autocomplete dropdown menu.
     */
    close(setTextboxValue = true) {
        var self = this;
        var trigger = self.isOpen;
        if (setTextboxValue) {
            // before blur() to prevent form onchange event
            self.setTextboxValue();
            if (self.settings.mode === 'single' && self.items.length) {
                self.inputState();
            }
        }
        self.isOpen = false;
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(self.focus_node, { 'aria-expanded': 'false' });
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.applyCSS)(self.dropdown, { display: 'none' });
        if (self.settings.hideSelected) {
            self.clearActiveOption();
        }
        self.refreshState();
        if (trigger)
            self.trigger('dropdown_close', self.dropdown);
    }
    /**
     * Calculates and applies the appropriate
     * position of the dropdown if dropdownParent = 'body'.
     * Otherwise, position is determined by css
     */
    positionDropdown() {
        if (this.settings.dropdownParent !== 'body') {
            return;
        }
        var context = this.control;
        var rect = context.getBoundingClientRect();
        var top = context.offsetHeight + rect.top + window.scrollY;
        var left = rect.left + window.scrollX;
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.applyCSS)(this.dropdown, {
            width: rect.width + 'px',
            top: top + 'px',
            left: left + 'px'
        });
    }
    /**
     * Resets / clears all selected items
     * from the control.
     *
     */
    clear(silent) {
        var self = this;
        if (!self.items.length)
            return;
        var items = self.controlChildren();
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(items, (item) => {
            self.removeItem(item, true);
        });
        self.inputState();
        if (!silent)
            self.updateOriginalInput();
        self.trigger('clear');
    }
    /**
     * A helper method for inserting an element
     * at the current caret position.
     *
     */
    insertAtCaret(el) {
        const self = this;
        const caret = self.caretPos;
        const target = self.control;
        target.insertBefore(el, target.children[caret] || null);
        self.setCaret(caret + 1);
    }
    /**
     * Removes the current selected item(s).
     *
     */
    deleteSelection(e) {
        var direction, selection, caret, tail;
        var self = this;
        direction = (e && e.keyCode === _constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_BACKSPACE) ? -1 : 1;
        selection = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.getSelection)(self.control_input);
        // determine items that will be removed
        const rm_items = [];
        if (self.activeItems.length) {
            tail = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getTail)(self.activeItems, direction);
            caret = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.nodeIndex)(tail);
            if (direction > 0) {
                caret++;
            }
            (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(self.activeItems, (item) => rm_items.push(item));
        }
        else if ((self.isFocused || self.settings.mode === 'single') && self.items.length) {
            const items = self.controlChildren();
            let rm_item;
            if (direction < 0 && selection.start === 0 && selection.length === 0) {
                rm_item = items[self.caretPos - 1];
            }
            else if (direction > 0 && selection.start === self.inputValue().length) {
                rm_item = items[self.caretPos];
            }
            if (rm_item !== undefined) {
                rm_items.push(rm_item);
            }
        }
        if (!self.shouldDelete(rm_items, e)) {
            return false;
        }
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.preventDefault)(e, true);
        // perform removal
        if (typeof caret !== 'undefined') {
            self.setCaret(caret);
        }
        while (rm_items.length) {
            self.removeItem(rm_items.pop());
        }
        self.inputState();
        self.positionDropdown();
        self.refreshOptions(false);
        return true;
    }
    /**
     * Return true if the items should be deleted
     */
    shouldDelete(items, evt) {
        const values = items.map(item => item.dataset.value);
        // allow the callback to abort
        if (!values.length || (typeof this.settings.onDelete === 'function' && this.settings.onDelete(values, evt) === false)) {
            return false;
        }
        return true;
    }
    /**
     * Selects the previous / next item (depending on the `direction` argument).
     *
     * > 0 - right
     * < 0 - left
     *
     */
    advanceSelection(direction, e) {
        var last_active, adjacent, self = this;
        if (self.rtl)
            direction *= -1;
        if (self.inputValue().length)
            return;
        // add or remove to active items
        if ((0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.isKeyDown)(_constants_js__WEBPACK_IMPORTED_MODULE_5__.KEY_SHORTCUT, e) || (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.isKeyDown)('shiftKey', e)) {
            last_active = self.getLastActive(direction);
            if (last_active) {
                if (!last_active.classList.contains('active')) {
                    adjacent = last_active;
                }
                else {
                    adjacent = self.getAdjacent(last_active, direction, 'item');
                }
                // if no active item, get items adjacent to the control input
            }
            else if (direction > 0) {
                adjacent = self.control_input.nextElementSibling;
            }
            else {
                adjacent = self.control_input.previousElementSibling;
            }
            if (adjacent) {
                if (adjacent.classList.contains('active')) {
                    self.removeActiveItem(last_active);
                }
                self.setActiveItemClass(adjacent); // mark as last_active !! after removeActiveItem() on last_active
            }
            // move caret to the left or right
        }
        else {
            self.moveCaret(direction);
        }
    }
    moveCaret(direction) { }
    /**
     * Get the last active item
     *
     */
    getLastActive(direction) {
        let last_active = this.control.querySelector('.last-active');
        if (last_active) {
            return last_active;
        }
        var result = this.control.querySelectorAll('.active');
        if (result) {
            return (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getTail)(result, direction);
        }
    }
    /**
     * Moves the caret to the specified index.
     *
     * The input must be moved by leaving it in place and moving the
     * siblings, due to the fact that focus cannot be restored once lost
     * on mobile webkit devices
     *
     */
    setCaret(new_pos) {
        this.caretPos = this.items.length;
    }
    /**
     * Return list of item dom elements
     *
     */
    controlChildren() {
        return Array.from(this.control.querySelectorAll('[data-ts-item]'));
    }
    /**
     * Disables user input on the control. Used while
     * items are being asynchronously created.
     */
    lock() {
        this.setLocked(true);
    }
    /**
     * Re-enables user input on the control.
     */
    unlock() {
        this.setLocked(false);
    }
    /**
     * Disable or enable user input on the control
     */
    setLocked(lock = this.isReadOnly || this.isDisabled) {
        this.isLocked = lock;
        this.refreshState();
    }
    /**
     * Disables user input on the control completely.
     * While disabled, it cannot receive focus.
     */
    disable() {
        this.setDisabled(true);
        this.close();
    }
    /**
     * Enables the control so that it can respond
     * to focus and user input.
     */
    enable() {
        this.setDisabled(false);
    }
    setDisabled(disabled) {
        this.focus_node.tabIndex = disabled ? -1 : this.tabIndex;
        this.isDisabled = disabled;
        this.input.disabled = disabled;
        this.control_input.disabled = disabled;
        this.setLocked();
    }
    setReadOnly(isReadOnly) {
        this.isReadOnly = isReadOnly;
        this.input.readOnly = isReadOnly;
        this.control_input.readOnly = isReadOnly;
        this.setLocked();
    }
    /**
     * Completely destroys the control and
     * unbinds all event listeners so that it can
     * be garbage collected.
     */
    destroy() {
        var self = this;
        var revertSettings = self.revertSettings;
        self.trigger('destroy');
        self.off();
        self.wrapper.remove();
        self.dropdown.remove();
        self.input.innerHTML = revertSettings.innerHTML;
        self.input.tabIndex = revertSettings.tabIndex;
        (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.removeClasses)(self.input, 'tomselected', 'ts-hidden-accessible');
        self._destroy();
        delete self.input.tomselect;
    }
    /**
     * A helper method for rendering "item" and
     * "option" templates, given the data.
     *
     */
    render(templateName, data) {
        var id, html;
        const self = this;
        if (typeof this.settings.render[templateName] !== 'function') {
            return null;
        }
        // render markup
        html = self.settings.render[templateName].call(this, data, _utils_js__WEBPACK_IMPORTED_MODULE_7__.escape_html);
        if (!html) {
            return null;
        }
        html = (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.getDom)(html);
        // add mandatory attributes
        if (templateName === 'option' || templateName === 'option_create') {
            if (data[self.settings.disabledField]) {
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(html, { 'aria-disabled': 'true' });
            }
            else {
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(html, { 'data-selectable': '' });
            }
        }
        else if (templateName === 'optgroup') {
            id = data.group[self.settings.optgroupValueField];
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(html, { 'data-group': id });
            if (data.group[self.settings.disabledField]) {
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(html, { 'data-disabled': '' });
            }
        }
        if (templateName === 'option' || templateName === 'item') {
            const value = (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.get_hash)(data[self.settings.valueField]);
            (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(html, { 'data-value': value });
            // make sure we have some classes if a template is overwritten
            if (templateName === 'item') {
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(html, self.settings.itemClass);
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(html, { 'data-ts-item': '' });
            }
            else {
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.addClasses)(html, self.settings.optionClass);
                (0,_vanilla_js__WEBPACK_IMPORTED_MODULE_8__.setAttr)(html, {
                    role: 'option',
                    id: data.$id
                });
                // update cache
                data.$div = html;
                self.options[value] = data;
            }
        }
        return html;
    }
    /**
     * Type guarded rendering
     *
     */
    _render(templateName, data) {
        const html = this.render(templateName, data);
        if (html == null) {
            throw 'HTMLElement expected';
        }
        return html;
    }
    /**
     * Clears the render cache for a template. If
     * no template is given, clears all render
     * caches.
     *
     */
    clearCache() {
        (0,_utils_js__WEBPACK_IMPORTED_MODULE_7__.iterate)(this.options, (option) => {
            if (option.$div) {
                option.$div.remove();
                delete option.$div;
            }
        });
    }
    /**
     * Removes a value from item and option caches
     *
     */
    uncacheValue(value) {
        const option_el = this.getOption(value);
        if (option_el)
            option_el.remove();
    }
    /**
     * Determines whether or not to display the
     * create item prompt, given a user input.
     *
     */
    canCreate(input) {
        return this.settings.create && (input.length > 0) && this.settings.createFilter.call(this, input);
    }
    /**
     * Wraps this.`method` so that `new_fn` can be invoked 'before', 'after', or 'instead' of the original method
     *
     * this.hook('instead','onKeyDown',function( arg1, arg2 ...){
     *
     * });
     */
    hook(when, method, new_fn) {
        var self = this;
        var orig_method = self[method];
        self[method] = function () {
            var result, result_new;
            if (when === 'after') {
                result = orig_method.apply(self, arguments);
            }
            result_new = new_fn.apply(self, arguments);
            if (when === 'instead') {
                return result_new;
            }
            if (when === 'before') {
                result = orig_method.apply(self, arguments);
            }
            return result;
        };
    }
}
;
//# sourceMappingURL=tom-select.js.map

/***/ }),

/***/ "./node_modules/tom-select/dist/esm/utils.js":
/*!***************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/utils.js ***!
  \***************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addEvent: () => (/* binding */ addEvent),
/* harmony export */   addSlashes: () => (/* binding */ addSlashes),
/* harmony export */   append: () => (/* binding */ append),
/* harmony export */   debounce_events: () => (/* binding */ debounce_events),
/* harmony export */   escape_html: () => (/* binding */ escape_html),
/* harmony export */   getId: () => (/* binding */ getId),
/* harmony export */   getSelection: () => (/* binding */ getSelection),
/* harmony export */   get_hash: () => (/* binding */ get_hash),
/* harmony export */   hash_key: () => (/* binding */ hash_key),
/* harmony export */   isKeyDown: () => (/* binding */ isKeyDown),
/* harmony export */   iterate: () => (/* binding */ iterate),
/* harmony export */   loadDebounce: () => (/* binding */ loadDebounce),
/* harmony export */   preventDefault: () => (/* binding */ preventDefault),
/* harmony export */   timeout: () => (/* binding */ timeout)
/* harmony export */ });
/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */
const hash_key = (value) => {
    if (typeof value === 'undefined' || value === null)
        return null;
    return get_hash(value);
};
const get_hash = (value) => {
    if (typeof value === 'boolean')
        return value ? '1' : '0';
    return value + '';
};
/**
 * Escapes a string for use within HTML.
 *
 */
const escape_html = (str) => {
    return (str + '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
};
/**
 * use setTimeout if timeout > 0
 */
const timeout = (fn, timeout) => {
    if (timeout > 0) {
        return window.setTimeout(fn, timeout);
    }
    fn.call(null);
    return null;
};
/**
 * Debounce the user provided load function
 *
 */
const loadDebounce = (fn, delay) => {
    var timeout;
    return function (value, callback) {
        var self = this;
        if (timeout) {
            self.loading = Math.max(self.loading - 1, 0);
            clearTimeout(timeout);
        }
        timeout = setTimeout(function () {
            timeout = null;
            self.loadedSearches[value] = true;
            fn.call(self, value, callback);
        }, delay);
    };
};
/**
 * Debounce all fired events types listed in `types`
 * while executing the provided `fn`.
 *
 */
const debounce_events = (self, types, fn) => {
    var type;
    var trigger = self.trigger;
    var event_args = {};
    // override trigger method
    self.trigger = function () {
        var type = arguments[0];
        if (types.indexOf(type) !== -1) {
            event_args[type] = arguments;
        }
        else {
            return trigger.apply(self, arguments);
        }
    };
    // invoke provided function
    fn.apply(self, []);
    self.trigger = trigger;
    // trigger queued events
    for (type of types) {
        if (type in event_args) {
            trigger.apply(self, event_args[type]);
        }
    }
};
/**
 * Determines the current selection within a text input control.
 * Returns an object containing:
 *   - start
 *   - length
 *
 * Note: "selectionStart, selectionEnd ... apply only to inputs of types text, search, URL, tel and password"
 * 	- https://developer.mozilla.org/en-US/docs/Web/API/HTMLInputElement/setSelectionRange
 */
const getSelection = (input) => {
    return {
        start: input.selectionStart || 0,
        length: (input.selectionEnd || 0) - (input.selectionStart || 0),
    };
};
/**
 * Prevent default
 *
 */
const preventDefault = (evt, stop = false) => {
    if (evt) {
        evt.preventDefault();
        if (stop) {
            evt.stopPropagation();
        }
    }
};
/**
 * Add event helper
 *
 */
const addEvent = (target, type, callback, options) => {
    target.addEventListener(type, callback, options);
};
/**
 * Return true if the requested key is down
 * Will return false if more than one control character is pressed ( when [ctrl+shift+a] != [ctrl+a] )
 * The current evt may not always set ( eg calling advanceSelection() )
 *
 */
const isKeyDown = (key_name, evt) => {
    if (!evt) {
        return false;
    }
    if (!evt[key_name]) {
        return false;
    }
    var count = (evt.altKey ? 1 : 0) + (evt.ctrlKey ? 1 : 0) + (evt.shiftKey ? 1 : 0) + (evt.metaKey ? 1 : 0);
    if (count === 1) {
        return true;
    }
    return false;
};
/**
 * Get the id of an element
 * If the id attribute is not set, set the attribute with the given id
 *
 */
const getId = (el, id) => {
    const existing_id = el.getAttribute('id');
    if (existing_id) {
        return existing_id;
    }
    el.setAttribute('id', id);
    return id;
};
/**
 * Returns a string with backslashes added before characters that need to be escaped.
 */
const addSlashes = (str) => {
    return str.replace(/[\\"']/g, '\\$&');
};
/**
 *
 */
const append = (parent, node) => {
    if (node)
        parent.append(node);
};
/**
 * Iterates over arrays and hashes.
 *
 * ```
 * iterate(this.items, function(item, id) {
 *    // invoked for each item
 * });
 * ```
 *
 */
const iterate = (object, callback) => {
    if (Array.isArray(object)) {
        object.forEach(callback);
    }
    else {
        for (var key in object) {
            if (object.hasOwnProperty(key)) {
                callback(object[key], key);
            }
        }
    }
};
//# sourceMappingURL=utils.js.map

/***/ }),

/***/ "./node_modules/tom-select/dist/esm/vanilla.js":
/*!*****************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/vanilla.js ***!
  \*****************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addClasses: () => (/* binding */ addClasses),
/* harmony export */   applyCSS: () => (/* binding */ applyCSS),
/* harmony export */   castAsArray: () => (/* binding */ castAsArray),
/* harmony export */   classesArray: () => (/* binding */ classesArray),
/* harmony export */   escapeQuery: () => (/* binding */ escapeQuery),
/* harmony export */   getDom: () => (/* binding */ getDom),
/* harmony export */   getTail: () => (/* binding */ getTail),
/* harmony export */   isEmptyObject: () => (/* binding */ isEmptyObject),
/* harmony export */   isHtmlString: () => (/* binding */ isHtmlString),
/* harmony export */   nodeIndex: () => (/* binding */ nodeIndex),
/* harmony export */   parentMatch: () => (/* binding */ parentMatch),
/* harmony export */   removeClasses: () => (/* binding */ removeClasses),
/* harmony export */   replaceNode: () => (/* binding */ replaceNode),
/* harmony export */   setAttr: () => (/* binding */ setAttr),
/* harmony export */   triggerEvent: () => (/* binding */ triggerEvent)
/* harmony export */ });
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./utils.js */ "./node_modules/tom-select/dist/esm/utils.js");

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = (query) => {
    if (query.jquery) {
        return query[0];
    }
    if (query instanceof HTMLElement) {
        return query;
    }
    if (isHtmlString(query)) {
        var tpl = document.createElement('template');
        tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
        return tpl.content.firstChild;
    }
    return document.querySelector(query);
};
const isHtmlString = (arg) => {
    if (typeof arg === 'string' && arg.indexOf('<') > -1) {
        return true;
    }
    return false;
};
const escapeQuery = (query) => {
    return query.replace(/['"\\]/g, '\\$&');
};
/**
 * Dispatch an event
 *
 */
const triggerEvent = (dom_el, event_name) => {
    var event = document.createEvent('HTMLEvents');
    event.initEvent(event_name, true, false);
    dom_el.dispatchEvent(event);
};
/**
 * Apply CSS rules to a dom element
 *
 */
const applyCSS = (dom_el, css) => {
    Object.assign(dom_el.style, css);
};
/**
 * Add css classes
 *
 */
const addClasses = (elmts, ...classes) => {
    var norm_classes = classesArray(classes);
    elmts = castAsArray(elmts);
    elmts.map(el => {
        norm_classes.map(cls => {
            el.classList.add(cls);
        });
    });
};
/**
 * Remove css classes
 *
 */
const removeClasses = (elmts, ...classes) => {
    var norm_classes = classesArray(classes);
    elmts = castAsArray(elmts);
    elmts.map(el => {
        norm_classes.map(cls => {
            el.classList.remove(cls);
        });
    });
};
/**
 * Return arguments
 *
 */
const classesArray = (args) => {
    var classes = [];
    (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.iterate)(args, (_classes) => {
        if (typeof _classes === 'string') {
            _classes = _classes.trim().split(/[\t\n\f\r\s]/);
        }
        if (Array.isArray(_classes)) {
            classes = classes.concat(_classes);
        }
    });
    return classes.filter(Boolean);
};
/**
 * Create an array from arg if it's not already an array
 *
 */
const castAsArray = (arg) => {
    if (!Array.isArray(arg)) {
        arg = [arg];
    }
    return arg;
};
/**
 * Get the closest node to the evt.target matching the selector
 * Stops at wrapper
 *
 */
const parentMatch = (target, selector, wrapper) => {
    if (wrapper && !wrapper.contains(target)) {
        return;
    }
    while (target && target.matches) {
        if (target.matches(selector)) {
            return target;
        }
        target = target.parentNode;
    }
};
/**
 * Get the first or last item from an array
 *
 * > 0 - right (last)
 * <= 0 - left (first)
 *
 */
const getTail = (list, direction = 0) => {
    if (direction > 0) {
        return list[list.length - 1];
    }
    return list[0];
};
/**
 * Return true if an object is empty
 *
 */
const isEmptyObject = (obj) => {
    return (Object.keys(obj).length === 0);
};
/**
 * Get the index of an element amongst sibling nodes of the same type
 *
 */
const nodeIndex = (el, amongst) => {
    if (!el)
        return -1;
    amongst = amongst || el.nodeName;
    var i = 0;
    while (el = el.previousElementSibling) {
        if (el.matches(amongst)) {
            i++;
        }
    }
    return i;
};
/**
 * Set attributes of an element
 *
 */
const setAttr = (el, attrs) => {
    (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.iterate)(attrs, (val, attr) => {
        if (val == null) {
            el.removeAttribute(attr);
        }
        else {
            el.setAttribute(attr, '' + val);
        }
    });
};
/**
 * Replace a node
 */
const replaceNode = (existing, replacement) => {
    if (existing.parentNode)
        existing.parentNode.replaceChild(replacement, existing);
};
//# sourceMappingURL=vanilla.js.map

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["js/vendor"], () => (__webpack_exec__("./public/assets/js/app/components/selects.module.mjs")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});
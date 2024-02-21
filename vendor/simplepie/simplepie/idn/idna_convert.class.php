<?php
// {{{ license

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */
//
// +----------------------------------------------------------------------+
// | This library is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU Lesser General Public License as       |
// | published by the Free Software Foundation; either version 2.1 of the |
// | License, or (at your option) any later version.                      |
// |                                                                      |
// | This library is distributed in the hope that it will be useful, but  |
// | WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU    |
// | Lesser General Public License for more details.                      |
// |                                                                      |
// | You should have received a copy of the GNU Lesser General Public     |
// | License along with this library; if not, write to the Free Software  |
// | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 |
// | USA.                                                                 |
// +----------------------------------------------------------------------+
//

// }}}

/**
 * Encode/decode Internationalized Domain Names.
 *
 * The class allows to convert internationalized domain names
 * (see RFC 3490 for details) as they can be used with various registries worldwide
 * to be translated between their original (localized) form and their encoded form
 * as it will be used in the DNS (Domain Name System).
 *
 * The class provides two public methods, encode() and decode(), which do exactly
 * what you would expect them to do. You are allowed to use complete domain names,
 * simple strings and complete email addresses as well. That means, that you might
 * use any of the following notations:
 *
 * - www.nörgler.com
 * - xn--nrgler-wxa
 * - xn--brse-5qa.xn--knrz-1ra.info
 *
 * Unicode input might be given as either UTF-8 string, UCS-4 string or UCS-4
 * array. Unicode output is available in the same formats.
 * You can select your preferred format via {@link set_paramter()}.
 *
 * ACE input and output is always expected to be ASCII.
 *
 * @author  Matthias Sommerfeld <mso@phlylabs.de>
 * @copyright 2004-2007 phlyLabs Berlin, http://phlylabs.de
 * @version 0.5.1
 *
 */
class idna_convert
{
    /**
     * Holds all relevant mapping tables, loaded from a seperate file on construct
     * See RFC3454 for details
     *
     * @var array
     * @access private
     */
    var $NP = array();

    // Internal settings, do not mess with them
    var $_punycode_prefix = 'xn--';
    var $_invalid_ucs =     0x80000000;
    var $_max_ucs =         0x10FFFF;
    var $_base =            36;
    var $_tmin =            1;
    var $_tmax =            26;
    var $_skew =            38;
    var $_damp =            700;
    var $_initial_bias =    72;
    var $_initial_n =       0x80;
    var $_sbase =           0xAC00;
    var $_lbase =           0x1100;
    var $_vbase =           0x1161;
    var $_tbase =           0x11A7;
    var $_lcount =          19;
    var $_vcount =          21;
    var $_tcount =          28;
    var $_ncount =          588;   // _vcount * _tcount
    var $_scount =          11172; // _lcount * _tcount * _vcount
    var $_error =           false;

    // See {@link set_paramter()} for details of how to change the following
    // settings from within your script / application
    var $_api_encoding   =  'utf8'; // Default input charset is UTF-8
    var $_allow_overlong =  false;  // Overlong UTF-8 encodings are forbidden
    var $_strict_mode    =  false;  // Behave strict or not

    // The constructor
    function __construct($options = false)
    {
        $this->slast = $this->_sbase + $this->_lcount * $this->_vcount * $this->_tcount;
        if (function_exists('file_get_contents')) {
            $this->NP = unserialize(file_get_contents(dirname(__FILE__).'/npdata.ser'));
        } else {
            $this->NP = unserialize(join('', file(dirname(__FILE__).'/npdata.ser')));
        }
        // If parameters are given, pass these to the respective method
        if (is_array($options)) {
            return $this->set_parameter($options);
        }
        return true;
    }

    /**
     * Sets a new option value. Available options and values:
     * [encoding - Use either UTF-8, UCS4 as array or UCS4 as string as input ('utf8' for UTF-8,
     *         'ucs4_string' and 'ucs4_array' respectively for UCS4); The output is always UTF-8]
     * [overlong - Unicode does not allow unnecessarily long encodings of chars,
     *             to allow this, set this parameter to true, else to false;
     *             default is false.]
     * [strict - true: strict mode, good for registration purposes - Causes errors
     *           on failures; false: loose mode, ideal for "wildlife" applications
     *           by silently ignoring errors and returning the original input instead
     *
     * @param    mixed     Parameter to set (string: single parameter; array of Parameter => Value pairs)
     * @param    string    Value to use (if parameter 1 is a string)
     * @return   boolean   true on success, false otherwise
     * @access   public
     */
    function set_parameter($option, $value = false)
    {
        if (!is_array($option)) {
            $option = array($option => $value);
        }
        foreach ($option as $k => $v) {
            switch ($k) {
            case 'encoding':
                switch ($v) {
                case 'utf8':
                case 'ucs4_string':
                case 'ucs4_array':
                    $this->_api_encoding = $v;
                    break;
                default:
                    $this->_error('Set Parameter: Unknown parameter '.$v.' for option '.$k);
                    return false;
                }
                break;
            case 'overlong':
                $this->_allow_overlong = ($v) ? true : false;
                break;
            case 'strict':
                $this->_strict_mode = ($v) ? true : false;
                break;
            default:
                $this->_error('Set Parameter: Unknown option '.$k);
                return false;
            }
        }
        return true;
    }

    /**
     * Decode a given ACE domain name
     * @param    string   Domain name (ACE string)
     * [@param    string   Desired output encoding, see {@link set_parameter}]
     * @return   string   Decoded Domain name (UTF-8 or UCS-4)
     * @access   public
     */
    function decode($input, $one_time_encoding = false)
    {
        // Optionally set
        if ($one_time_encoding) {
            switch ($one_time_encoding) {
            case 'utf8':
            case 'ucs4_string':
            case 'ucs4_array':
                break;
            default:
                $this->_error('Unknown encoding '.$one_time_encoding);
                return false;
            }
        }
        // Make sure to drop any newline characters around
        $input = trim($input);

        // Negotiate input and try to determine, whether it is a plain string,
        // an email address or something like a complete URL
        if (strpos($input, '@')) { // Maybe it is an email address
            // No no in strict mode
            if ($this->_strict_mode) {
                $this->_error('Only simple domain name parts can be handled in strict mode');
                return false;
            }
            list ($email_pref, $input) = explode('@', $input, 2);
            $arr = explode('.', $input);
            foreach ($arr as $k => $v) {
                if (preg_match('!^'.preg_quote($this->_punycode_prefix, '!').'!', $v)) {
                    $conv = $this->_decode($v);
                    if ($conv) $arr[$k] = $conv;
                }
            }
            $input = join('.', $arr);
            $arr = explode('.', $email_pref);
            foreach ($arr as $k => $v) {
                if (preg_match('!^'.preg_quote($this->_punycode_prefix, '!').'!', $v)) {
                    $conv = $this->_decode($v);
                    if ($conv) $arr[$k] = $conv;
                }
            }
            $email_pref = join('.', $arr);
            $return = $email_pref . '@' . $input;
        } elseif (preg_match('![:\./]!', $input)) { // Or a complete domain name (with or without paths / parameters)
            // No no in strict mode
            if ($this->_strict_mode) {
                $this->_error('Only simple domain name parts can be handled in strict mode');
                return false;
            }
            $parsed = parse_url($input);
            if (isset($parsed['host'])) {
                $arr = explode('.', $parsed['host']);
                foreach ($arr as $k => $v) {
                    $conv = $this->_decode($v);
                    if ($conv) $arr[$k] = $conv;
                }
                $parsed['host'] = join('.', $arr);
                $return =
                        (empty($parsed['scheme']) ? '' : $parsed['scheme'].(strtolower($parsed['scheme']) == 'mailto' ? ':' : '://'))
                        .(empty($parsed['user']) ? '' : $parsed['user'].(empty($parsed['pass']) ? '' : ':'.$parsed['pass']).'@')
                        .$parsed['host']
                        .(empty($parsed['port']) ? '' : ':'.$parsed['port'])
                        .(empty($parsed['path']) ? '' : $parsed['path'])
                        .(empty($parsed['query']) ? '' : '?'.$parsed['query'])
                        .(empty($parsed['fragment']) ? '' : '#'.$parsed['fragment']);
            } else { // parse_url seems to have failed, try without it
                $arr = explode('.', $input);
                foreach ($arr as $k => $v) {
                    $conv = $this->_decode($v);
                    $arr[$k] = ($conv) ? $conv : $v;
                }
                $return = join('.', $arr);
            }
        } else { // Otherwise we consider it being a pure domain name string
            $return = $this->_decode($input);
            if (!$return) $return = $input;
        }
        // The output is UTF-8 by default, other output formats need conversion here
        // If one time encoding is given, use this, else the objects property
        switch (($one_time_encoding) ? $one_time_encoding : $this->_api_encoding) {
        case 'utf8':
            return $return;
            break;
        case 'ucs4_string':
           return $this->_ucs4_to_ucs4_string($this->_utf8_to_ucs4($return));
           break;
        case 'ucs4_array':
            return $this->_utf8_to_ucs4($return);
            break;
        default:
            $this->_error('Unsupported output format');
            return false;
        }
    }

    /**
     * Encode a given UTF-8 domain name
     * @param    string   Domain name (UTF-8 or UCS-4)
     * [@param    string   Desired input encoding, see {@link set_parameter}]
     * @return   string   Encoded Domain name (ACE string)
     * @access   public
     */
    function encode($decoded, $one_time_encoding = false)
    {
        // Forcing conversion of input to UCS4 array
        // If one time encoding is given, use this, else the objects property
        switch ($one_time_encoding ? $one_time_encoding : $this->_api_encoding) {
        case 'utf8':
            $decoded = $this->_utf8_to_ucs4($decoded);
            break;
        case 'ucs4_string':
           $decoded = $this->_ucs4_string_to_ucs4($decoded);
        case 'ucs4_array':
           break;
        default:
            $this->_error('Unsupported input format: '.($one_time_encoding ? $one_time_encoding : $this->_api_encoding));
            return false;
        }

        // No input, no output, what else did you expect?
        if (empty($decoded)) return '';

        // Anchors for iteration
        $last_begin = 0;
        // Output string
        $output = '';
        foreach ($decoded as $k => $v) {
            // Make sure to use just the plain dot
            switch($v) {
            case 0x3002:
            case 0xFF0E:
            case 0xFF61:
                $decoded[$k] = 0x2E;
                // Right, no break here, the above are converted to dots anyway
            // Stumbling across an anchoring character
            case 0x2E:
            case 0x2F:
            case 0x3A:
            case 0x3F:
            case 0x40:
                // Neither email addresses nor URLs allowed in strict mode
                if ($this->_strict_mode) {
                   $this->_error('Neither email addresses nor URLs are allowed in strict mode.');
                   return false;
                }

                // Skip first char
                if ($k) {
                    $encoded = '';
                    $encoded = $this->_encode(array_slice($decoded, $last_begin, (($k)-$last_begin)));
                    if ($encoded) {
                        $output .= $encoded;
                    } else {
                        $output .= $this->_ucs4_to_utf8(array_slice($decoded, $last_begin, (($k)-$last_begin)));
                    }
                    $output .= chr($decoded[$k]);
                }
                $last_begin = $k + 1;
            }
        }
        // Catch the rest of the string
        if ($last_begin) {
            $inp_len = sizeof($decoded);
            $encoded = '';
            $encoded = $this->_encode(array_slice($decoded, $last_begin, (($inp_len)-$last_begin)));
            if ($encoded) {
                $output .= $encoded;
            } else {
                $output .= $this->_ucs4_to_utf8(array_slice($decoded, $last_begin, (($inp_len)-$last_begin)));
            }
            return $output;
        }

        if ($output = $this->_encode($decoded)) {
            return $output;
        }

        return $this->_ucs4_to_utf8($decoded);
    }

    /**
     * Use this method to get the last error ocurred
     * @param    void
     * @return   string   The last error, that occured
     * @access   public
     */
    function get_last_error()
    {
        return $this->_error;
    }

    /**
     * The actual decoding algorithm
     * @access   private
     */
    function _decode($encoded)
    {
        // We do need to find the Punycode prefix
        if (!preg_match('!^'.preg_quote($this->_punycode_prefix, '!').'!', $encoded)) {
            $this->_error('This is not a punycode string');
            return false;
        }
        $encode_test = preg_replace('!^'.preg_quote($this->_punycode_prefix, '!').'!', '', $encoded);
        // If nothing left after removing the prefix, it is hopeless
        if (!$encode_test) {
            $this->_error('The given encoded string was empty');
            return false;
        }
        // Find last occurence of the delimiter
        $delim_pos = strrpos($encoded, '-');
        if ($delim_pos > strlen($this->_punycode_prefix)) {
            for ($k = strlen($this->_punycode_prefix); $k < $delim_pos; ++$k) {
                $decoded[] = ord($encoded[$k]);
            }
        } else {
            $decoded = array();
        }
        $deco_len = count($decoded);
        $enco_len = strlen($encoded);

        // Wandering through the strings; init
        $is_first = true;
        $bias     = $this->_initial_bias;
        $idx      = 0;
        $char     = $this->_initial_n;

        for ($enco_idx = ($delim_pos) ? ($delim_pos + 1) : 0; $enco_idx < $enco_len; ++$deco_len) {
            for ($old_idx = $idx, $w = 1, $k = $this->_base; 1 ; $k += $this->_base) {
                $digit = $this->_decode_digit($encoded[$enco_idx++]);
                $idx += $digit * $w;
                $t = ($k <= $bias) ? $this->_tmin :
                        (($k >= $bias + $this->_tmax) ? $this->_tmax : ($k - $bias));
                if ($digit < $t) break;
                $w = (int) ($w * ($this->_base - $t));
            }
            $bias = $this->_adapt($idx - $old_idx, $deco_len + 1, $is_first);
            $is_first = false;
            $char += (int) ($idx / ($deco_len + 1));
            $idx %= ($deco_len + 1);
            if ($deco_len > 0) {
                // Make room for the decoded char
                for ($i = $deco_len; $i > $idx; $i--) {
                    $decoded[$i] = $decoded[($i - 1)];
                }
            }
            $decoded[$idx++] = $char;
        }
        return $this->_ucs4_to_utf8($decoded);
    }

    /**
     * The actual encoding algorithm
     * @access   private
     */
    function _encode($decoded)
    {
        // We cannot encode a domain name containing the Punycode prefix
        $extract = strlen($this->_punycode_prefix);
        $check_pref = $this->_utf8_to_ucs4($this->_punycode_prefix);
        $check_deco = array_slice($decoded, 0, $extract);

        if ($check_pref == $check_deco) {
            $this->_error('This is already a punycode string');
            return false;
        }
        // We will not try to encode strings consisting of basic code points only
        $encodable = false;
        foreach ($decoded as $k => $v) {
            if ($v > 0x7a) {
                $encodable = true;
                break;
            }
        }
        if (!$encodable) {
            $this->_error('The given string does not contain encodable chars');
            return false;
        }

        // Do NAMEPREP
        $decoded = $this->_nameprep($decoded);
        if (!$decoded || !is_array($decoded)) return false; // NAMEPREP failed

        $deco_len  = count($decoded);
        if (!$deco_len) return false; // Empty array

        $codecount = 0; // How many chars have been consumed

        $encoded = '';
        // Copy all basic code points to output
        for ($i = 0; $i < $deco_len; ++$i) {
            $test = $decoded[$i];
            // Will match [-0-9a-zA-Z]
            if ((0x2F < $test && $test < 0x40) || (0x40 < $test && $test < 0x5B)
                    || (0x60 < $test && $test <= 0x7B) || (0x2D == $test)) {
                $encoded .= chr($decoded[$i]);
                $codecount++;
            }
        }
        if ($codecount == $deco_len) return $encoded; // All codepoints were basic ones

        // Start with the prefix; copy it to output
        $encoded = $this->_punycode_prefix.$encoded;

        // If we have basic code points in output, add an hyphen to the end
        if ($codecount) $encoded .= '-';

        // Now find and encode all non-basic code points
        $is_first  = true;
        $cur_code  = $this->_initial_n;
        $bias      = $this->_initial_bias;
        $delta     = 0;
        while ($codecount < $deco_len) {
            // Find the smallest code point >= the current code point and
            // remember the last ouccrence of it in the input
            for ($i = 0, $next_code = $this->_max_ucs; $i < $deco_len; $i++) {
                if ($decoded[$i] >= $cur_code && $decoded[$i] <= $next_code) {
                    $next_code = $decoded[$i];
                }
            }

            $delta += ($next_code - $cur_code) * ($codecount + 1);
            $cur_code = $next_code;

            // Scan input again and encode all characters whose code point is $cur_code
            for ($i = 0; $i < $deco_len; $i++) {
                if ($decoded[$i] < $cur_code) {
                    $delta++;
                } elseif ($decoded[$i] == $cur_code) {
                    for ($q = $delta, $k = $this->_base; 1; $k += $this->_base) {
                        $t = ($k <= $bias) ? $this->_tmin :
                                (($k >= $bias + $this->_tmax) ? $this->_tmax : $k - $bias);
                        if ($q < $t) break;
                        $encoded .= $this->_encode_digit(intval($t + (($q - $t) % ($this->_base - $t)))); //v0.4.5 Changed from ceil() to intval()
                        $q = (int) (($q - $t) / ($this->_base - $t));
                    }
                    $encoded .= $this->_encode_digit($q);
                    $bias = $this->_adapt($delta, $codecount+1, $is_first);
                    $codecount++;
                    $delta = 0;
                    $is_first = false;
                }
            }
            $delta++;
            $cur_code++;
        }
        return $encoded;
    }

    /**
     * Adapt the bias according to the current code point and position
     * @access   private
     */
    function _adapt($delta, $npoints, $is_first)
    {
        $delta = intval($is_first ? ($delta / $this->_damp) : ($delta / 2));
        $delta += intval($delta / $npoints);
        for ($k = 0; $delta > (($this->_base - $this->_tmin) * $this->_tmax) / 2; $k += $this->_base) {
            $delta = intval($delta / ($this->_base - $this->_tmin));
        }
        return intval($k + ($this->_base - $this->_tmin + 1) * $delta / ($delta + $this->_skew));
    }

    /**
     * Encoding a certain digit
     * @access   private
     */
    function _encode_digit($d)
    {
        return chr($d + 22 + 75 * ($d < 26));
    }

    /**
     * Decode a certain digit
     * @access   private
     */
    function _decode_digit($cp)
    {
        $cp = ord($cp);
        return ($cp - 48 < 10) ? $cp - 22 : (($cp - 65 < 26) ? $cp - 65 : (($cp - 97 < 26) ? $cp - 97 : $this->_base));
    }

    /**
     * Internal error handling method
     * @access   private
     */
    function _error($error = '')
    {
        $this->_error = $error;
    }

    /**
     * Do Nameprep according to RFC3491 and RFC3454
     * @param    array    Unicode Characters
     * @return   string   Unicode Characters, Nameprep'd
     * @access   private
     */
    function _nameprep($input)
    {
        $output = array();
        $error = false;
        //
        // Mapping
        // Walking through the input array, performing the required steps on each of
        // the input chars and putting the result into the output array
        // While mapping required chars we apply the cannonical ordering
        foreach ($input as $v) {
            // Map to nothing == skip that code point
            if (in_array($v, $this->NP['map_nothing'])) continue;

            // Try to find prohibited input
            if (in_array($v, $this->NP['prohibit']) || in_array($v, $this->NP['general_prohibited'])) {
                $this->_error('NAMEPREP: Prohibited input U+'.sprintf('%08X', $v));
                return false;
            }
            foreach ($this->NP['prohibit_ranges'] as $range) {
                if ($range[0] <= $v && $v <= $range[1]) {
                    $this->_error('NAMEPREP: Prohibited input U+'.sprintf('%08X', $v));
                    return false;
                }
            }
            //
            // Hangul syllable decomposition
            if (0xAC00 <= $v && $v <= 0xD7AF) {
                foreach ($this->_hangul_decompose($v) as $out) {
                    $output[] = (int) $out;
                }
            // There's a decomposition mapping for that code point
            } elseif (isset($this->NP['replacemaps'][$v])) {
                foreach ($this->_apply_cannonical_ordering($this->NP['replacemaps'][$v]) as $out) {
                    $output[] = (int) $out;
                }
            } else {
                $output[] = (int) $v;
            }
        }
        // Before applying any Combining, try to rearrange any Hangul syllables
        $output = $this->_hangul_compose($output);
        //
        // Combine code points
        //
        $last_class   = 0;
        $last_starter = 0;
        $out_len      = count($output);
        for ($i = 0; $i < $out_len; ++$i) {
            $class = $this->_get_combining_class($output[$i]);
            if ((!$last_class || $last_class > $class) && $class) {
                // Try to match
                $seq_len = $i - $last_starter;
                $out = $this->_combine(array_slice($output, $last_starter, $seq_len));
                // On match: Replace the last starter with the composed character and remove
                // the now redundant non-starter(s)
                if ($out) {
                    $output[$last_starter] = $out;
                    if (count($out) != $seq_len) {
                        for ($j = $i+1; $j < $out_len; ++$j) {
                            $output[$j-1] = $output[$j];
                        }
                        unset($output[$out_len]);
                    }
                    // Rewind the for loop by one, since there can be more possible compositions
                    $i--;
                    $out_len--;
                    $last_class = ($i == $last_starter) ? 0 : $this->_get_combining_class($output[$i-1]);
                    continue;
                }
            }
            // The current class is 0
            if (!$class) $last_starter = $i;
            $last_class = $class;
        }
        return $output;
    }

    /**
     * Decomposes a Hangul syllable
     * (see http://www.unicode.org/unicode/reports/tr15/#Hangul
     * @param    integer  32bit UCS4 code point
     * @return   array    Either Hangul Syllable decomposed or original 32bit value as one value array
     * @access   private
     */
    function _hangul_decompose($char)
    {
        $sindex = (int) $char - $this->_sbase;
        if ($sindex < 0 || $sindex >= $this->_scount) {
            return array($char);
        }
        $result = array();
        $result[] = (int) $this->_lbase + $sindex / $this->_ncount;
        $result[] = (int) $this->_vbase + ($sindex % $this->_ncount) / $this->_tcount;
        $T = intval($this->_tbase + $sindex % $this->_tcount);
        if ($T != $this->_tbase) $result[] = $T;
        return $result;
    }
    /**
     * Ccomposes a Hangul syllable
     * (see http://www.unicode.org/unicode/reports/tr15/#Hangul
     * @param    array    Decomposed UCS4 sequence
     * @return   array    UCS4 sequence with syllables composed
     * @access   private
     */
    function _hangul_compose($input)
    {
        $inp_len = count($input);
        if (!$inp_len) return array();
        $result = array();
        $last = (int) $input[0];
        $result[] = $last; // copy first char from input to output

        for ($i = 1; $i < $inp_len; ++$i) {
            $char = (int) $input[$i];
            $sindex = $last - $this->_sbase;
            $lindex = $last - $this->_lbase;
            $vindex = $char - $this->_vbase;
            $tindex = $char - $this->_tbase;
            // Find out, whether two current characters are LV and T
            if (0 <= $sindex && $sindex < $this->_scount && ($sindex % $this->_tcount == 0)
                    && 0 <= $tindex && $tindex <= $this->_tcount) {
                // create syllable of form LVT
                $last += $tindex;
                $result[(count($result) - 1)] = $last; // reset last
                continue; // discard char
            }
            // Find out, whether two current characters form L and V
            if (0 <= $lindex && $lindex < $this->_lcount && 0 <= $vindex && $vindex < $this->_vcount) {
                // create syllable of form LV
                $last = (int) $this->_sbase + ($lindex * $this->_vcount + $vindex) * $this->_tcount;
                $result[(count($result) - 1)] = $last; // reset last
                continue; // discard char
            }
            // if neither case was true, just add the character
            $last = $char;
            $result[] = $char;
        }
        return $result;
    }

    /**
     * Returns the combining class of a certain wide char
     * @param    integer    Wide char to check (32bit integer)
     * @return   integer    Combining class if found, else 0
     * @access   private
     */
    function _get_combining_class($char)
    {
        return isset($this->NP['norm_combcls'][$char]) ? $this->NP['norm_combcls'][$char] : 0;
    }

    /**
     * Apllies the cannonical ordering of a decomposed UCS4 sequence
     * @param    array      Decomposed UCS4 sequence
     * @return   array      Ordered USC4 sequence
     * @access   private
     */
    function _apply_cannonical_ordering($input)
    {
        $swap = true;
        $size = count($input);
        while ($swap) {
            $swap = false;
            $last = $this->_get_combining_class(intval($input[0]));
            for ($i = 0; $i < $size-1; ++$i) {
                $next = $this->_get_combining_class(intval($input[$i+1]));
                if ($next != 0 && $last > $next) {
                    // Move item leftward until it fits
                    for ($j = $i + 1; $j > 0; --$j) {
                        if ($this->_get_combining_class(intval($input[$j-1])) <= $next) break;
                        $t = intval($input[$j]);
                        $input[$j] = intval($input[$j-1]);
                        $input[$j-1] = $t;
                        $swap = true;
                    }
                    // Reentering the loop looking at the old character again
                    $next = $last;
                }
                $last = $next;
            }
        }
        return $input;
    }

    /**
     * Do composition of a sequence of starter and non-starter
     * @param    array      UCS4 Decomposed sequence
     * @return   array      Ordered USC4 sequence
     * @access   private
     */
    function _combine($input)
    {
        $inp_len = count($input);
        foreach ($this->NP['replacemaps'] as $np_src => $np_target) {
            if ($np_target[0] != $input[0]) continue;
            if (count($np_target) != $inp_len) continue;
            $hit = false;
            foreach ($input as $k2 => $v2) {
                if ($v2 == $np_target[$k2]) {
                    $hit = true;
                } else {
                    $hit = false;
                    break;
                }
            }
            if ($hit) return $np_src;
        }
        return false;
    }

    /**
     * This converts an UTF-8 encoded string to its UCS-4 representation
     * By talking about UCS-4 "strings" we mean arrays of 32bit integers representing
     * each of the "chars". This is due to PHP not being able to handle strings with
     * bit depth different from 8. This apllies to the reverse method _ucs4_to_utf8(), too.
     * The following UTF-8 encodings are supported:
     * bytes bits  representation
     * 1        7  0xxxxxxx
     * 2       11  110xxxxx 10xxxxxx
     * 3       16  1110xxxx 10xxxxxx 10xxxxxx
     * 4       21  11110xxx 10xxxxxx 10xxxxxx 10xxxxxx
     * 5       26  111110xx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
     * 6       31  1111110x 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
     * Each x represents a bit that can be used to store character data.
     * The five and six byte sequences are part of Annex D of ISO/IEC 10646-1:2000
     * @access   private
     */
    function _utf8_to_ucs4($input)
    {
        $output = array();
        $out_len = 0;
        $inp_len = strlen($input);
        $mode = 'next';
        $test = 'none';
        for ($k = 0; $k < $inp_len; ++$k) {
            $v = ord($input[$k]); // Extract byte from input string

            if ($v < 128) { // We found an ASCII char - put into stirng as is
                $output[$out_len] = $v;
                ++$out_len;
                if ('add' == $mode) {
                    $this->_error('Conversion from UTF-8 to UCS-4 failed: malformed input at byte '.$k);
                    return false;
                }
                continue;
            }
            if ('next' == $mode) { // Try to find the next start byte; determine the width of the Unicode char
                $start_byte = $v;
                $mode = 'add';
                $test = 'range';
                if ($v >> 5 == 6) { // &110xxxxx 10xxxxx
                    $next_byte = 0; // Tells, how many times subsequent bitmasks must rotate 6bits to the left
                    $v = ($v - 192) << 6;
                } elseif ($v >> 4 == 14) { // &1110xxxx 10xxxxxx 10xxxxxx
                    $next_byte = 1;
                    $v = ($v - 224) << 12;
                } elseif ($v >> 3 == 30) { // &11110xxx 10xxxxxx 10xxxxxx 10xxxxxx
                    $next_byte = 2;
                    $v = ($v - 240) << 18;
                } elseif ($v >> 2 == 62) { // &111110xx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
                    $next_byte = 3;
                    $v = ($v - 248) << 24;
                } elseif ($v >> 1 == 126) { // &1111110x 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
                    $next_byte = 4;
                    $v = ($v - 252) << 30;
                } else {
                    $this->_error('This might be UTF-8, but I don\'t understand it at byte '.$k);
                    return false;
                }
                if ('add' == $mode) {
                    $output[$out_len] = (int) $v;
                    ++$out_len;
                    continue;
                }
            }
            if ('add' == $mode) {
                if (!$this->_allow_overlong && $test == 'range') {
                    $test = 'none';
                    if (($v < 0xA0 && $start_byte == 0xE0) || ($v < 0x90 && $start_byte == 0xF0) || ($v > 0x8F && $start_byte == 0xF4)) {
                        $this->_error('Bogus UTF-8 character detected (out of legal range) at byte '.$k);
                        return false;
                    }
                }
                if ($v >> 6 == 2) { // Bit mask must be 10xxxxxx
                    $v = ($v - 128) << ($next_byte * 6);
                    $output[($out_len - 1)] += $v;
                    --$next_byte;
                } else {
                    $this->_error('Conversion from UTF-8 to UCS-4 failed: malformed input at byte '.$k);
                    return false;
                }
                if ($next_byte < 0) {
                    $mode = 'next';
                }
            }
        } // for
        return $output;
    }

    /**
     * Convert UCS-4 string into UTF-8 string
     * See _utf8_to_ucs4() for details
     * @access   private
     */
    function _ucs4_to_utf8($input)
    {
        $output = '';
        $k = 0;
        foreach ($input as $v) {
            ++$k;
            // $v = ord($v);
            if ($v < 128) { // 7bit are transferred literally
                $output .= chr($v);
            } elseif ($v < (1 << 11)) { // 2 bytes
                $output .= chr(192 + ($v >> 6)) . chr(128 + ($v & 63));
            } elseif ($v < (1 << 16)) { // 3 bytes
                $output .= chr(224 + ($v >> 12)) . chr(128 + (($v >> 6) & 63)) . chr(128 + ($v & 63));
            } elseif ($v < (1 << 21)) { // 4 bytes
                $output .= chr(240 + ($v >> 18)) . chr(128 + (($v >> 12) & 63))
                         . chr(128 + (($v >> 6) & 63)) . chr(128 + ($v & 63));
            } elseif ($v < (1 << 26)) { // 5 bytes
                $output .= chr(248 + ($v >> 24)) . chr(128 + (($v >> 18) & 63))
                         . chr(128 + (($v >> 12) & 63)) . chr(128 + (($v >> 6) & 63))
                         . chr(128 + ($v & 63));
            } elseif ($v < (1 << 31)) { // 6 bytes
                $output .= chr(252 + ($v >> 30)) . chr(128 + (($v >> 24) & 63))
                         . chr(128 + (($v >> 18) & 63)) . chr(128 + (($v >> 12) & 63))
                         . chr(128 + (($v >> 6) & 63)) . chr(128 + ($v & 63));
            } else {
                $this->_error('Conversion from UCS-4 to UTF-8 failed: malformed input at byte '.$k);
                return false;
            }
        }
        return $output;
    }

    /**
      * Convert UCS-4 array into UCS-4 string
      *
      * @access   private
      */
    function _ucs4_to_ucs4_string($input)
    {
        $output = '';
        // Take array values and split output to 4 bytes per value
        // The bit mask is 255, which reads &11111111
        foreach ($input as $v) {
            $output .= chr(($v >> 24) & 255).chr(($v >> 16) & 255).chr(($v >> 8) & 255).chr($v & 255);
        }
        return $output;
    }

    /**
      * Convert UCS-4 strin into UCS-4 garray
      *
      * @access   private
      */
    function _ucs4_string_to_ucs4($input)
    {
        $output = array();
        $inp_len = strlen($input);
        // Input length must be dividable by 4
        if ($inp_len % 4) {
            $this->_error('Input UCS4 string is broken');
            return false;
        }
        // Empty input - return empty output
        if (!$inp_len) return $output;
        for ($i = 0, $out_len = -1; $i < $inp_len; ++$i) {
            // Increment output position every 4 input bytes
            if (!($i % 4)) {
                $out_len++;
                $output[$out_len] = 0;
            }
            $output[$out_len] += ord($input[$i]) << (8 * (3 - ($i % 4) ) );
        }
        return $output;
    }
}

/**
* Adapter class for aligning the API of idna_convert with that of Net_IDNA
* @author  Matthias Sommerfeld <mso@phlylabs.de>
*/
class Net_IDNA_php4 extends idna_convert
{
    /**
     * Sets a new option value. Available options and values:
     * [encoding - Use either UTF-8, UCS4 as array or UCS4 as string as input ('utf8' for UTF-8,
     *         'ucs4_string' and 'ucs4_array' respectively for UCS4); The output is always UTF-8]
     * [overlong - Unicode does not allow unnecessarily long encodings of chars,
     *             to allow this, set this parameter to true, else to false;
     *             default is false.]
     * [strict - true: strict mode, good for registration purposes - Causes errors
     *           on failures; false: loose mode, ideal for "wildlife" applications
     *           by silently ignoring errors and returning the original input instead
     *
     * @param    mixed     Parameter to set (string: single parameter; array of Parameter => Value pairs)
     * @param    string    Value to use (if parameter 1 is a string)
     * @return   boolean   true on success, false otherwise
     * @access   public
     */
    function setParams($option, $param = false)
    {
        return $this->IC->set_parameters($option, $param);
    }
}

?>

import i18n from 'i18n';

let phpFormatSeed = {
    'A': 'A', // Uppercase Ante meridiem and Post meridiem: AM or PM
    'a': 'a', // Lowercase Ante meridiem and Post meridiem: am or pm
    'B': 'B', // Swatch Internet Time. There's no JavaScript equivalent
    'c': 'c', // ISO 8601 date: 2004-02-12T15:19:21+00:00
    'D': 'D', // Textual representation of a day - Mon through Sun
    'd': 'd', // Day of the month, 2 digits with leading zeros: 01 to 31
    'e': 'e', // Timezone identifier (deprecated in Moment.js)
    'F': 'F', // Full month name, e.g. January
    'G': 'G', // 24-hour format of an hour without leading zeros: 0 to 23
    'g': 'g', // 12-hour format of an hour without leading zeros: 1 to 12
    'H': 'H', // 24-hour format of an hour with leading zeros: 00 to 23
    'h': 'h', // 12-hour format of an hour with leading zeros: 01 to 12
    'I': 'I', // Whether or not the date is in daylight saving time: 1 if Daylight Saving Time, 0 otherwise.
    'i': 'i', // Minutes with leading zeros: 00 to 59
    'j': 'j', // Day of the month without leading zeros: 1 to 31
    'L': 'L', // Whether it's a leap year: 1 if it is a leap year, 0 otherwise.
    'l': 'l', // A full textual representation of the day of the week: Sunday through Saturday
    'm': 'm', // Numeric representation of a month, with leading zero: 01 through 12
    'M': 'M', // A short textual representation of a month, three letters: Jan through Dec
    'N': 'N', // ISO-8601 numeric representation of the day of the week: 1 (for Monday) through 7 (for Sunday)
    'n': 'n', // Numeric representation of a month, without leading zeros: 1 through 12
    'O': 'O', // Difference to Greenwich time (GMT) in hours: Example: +0200
    'o': 'o', // ISO-8601 year number
    'P': 'P', // Difference to Greenwich time (GMT) with colon between hours and minutes: Example: +02:00
    'r': 'r', // » RFC 2822 formatted date: Example: Thu, 21 Dec 2000 16:01:07 +0200
    'S': 'S', // English ordinal suffix for the day of the month, 2 characters: st, nd, rd or th. Works well with j
    's': 's', // Seconds, with leading zeros: 00 through 59
    'T': 'T', // Timezone abbreviation: Examples: EST, MDT, PDT ...
    't': 't', // Number of days in the given month: 28 through 31
    'U': 'U', // The seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
    'u': 'u', // Microseconds: Example: 654321
    'v': 'v', // Milliseconds (added in PHP 7.0.0). Example: 654
    'W': 'W', // ISO-8601 week number of year, weeks starting on Monday: Example: 42 (the 42nd week in the year)
    'w': 'w', // Numeric representation of the day of the week: 0 (for Sunday) through 6 (for Saturday)
    'Z': 'Z', // Timezone offset in seconds. The offset for timezones west of UTC is always negative, and for those east of UTC is always positive.
    'z': 'z', // The day of the year (starting from 0): 0 through 365
    'Y': 'Y', // A full numeric representation of a year, 4 digits: Examples: 1999 or 2003
};

var target = {
    "jquery": {},
    "luxon": {},
    "flatpickr": {},
};

target.jquery = {
    'd': 'dd',   // Day of month with leading zeroes
    'D': 'D',    // Short day name
    'j': 'd',    // Day of month with no leading zeroes
    'l': 'DD',   // Full day name
    'N': '',     // jQuery DatePicker does not have an ISO-8601 numeric representation of the day of the week
    'S': '',     // jQuery DatePicker does not support ordinal suffixes for the day of the month
    'w': '',     // jQuery DatePicker does not have a numeric day of week,
    'z': 'o',    // Day of the year
    'W': '',     // jQuery DatePicker does not have ISO-8601 week number of year
    'F': 'MM',   // Full month name
    'm': 'mm',   // Month of the year, leading zero
    'M': 'M',    // Short month name
    'n': 'm',    // Month of the year without leading zero
    't': '',     // jQuery DatePicker does not have number of days in the given month
    'L': '',     // jQuery DatePicker does not have leap year detection
    'o': 'yy',   // ISO-8601 year number - can be approximated with four digit year
    'Y': 'yy',   // Year, four digits
    'y': 'y',    // Year, two digits
    'a': '',     // jQuery DatePicker does not have lowercase ante meridiem and post meridiem
    'A': '',     // jQuery DatePicker does not have uppercase ante meridiem and post meridiem
    'B': '',     // jQuery DatePicker does not support Swatch Internet Time
    'g': '',     // jQuery DatePicker does not support 12-hour format without leading zero
    'G': '',     // jQuery DatePicker does not support 24-hour format without leading zero
    'h': '',     // jQuery DatePicker does not support 12-hour format with leading zero
    'H': '',     // jQuery DatePicker does not support 24-hour format with leading zero,
    'i': '',     // jQuery DatePicker does not support minutes with leading zero
    's': '',     // jQuery DatePicker does not support seconds with leading zero
    'u': '',     // jQuery DatePicker does not support microseconds
    'e': '',     // jQuery DatePicker does not support timezone identifiers
    'I': '',     // jQuery DatePicker does not support whether or not the date is in daylight saving time
    'O': '',     // jQuery DatePicker does not support difference to Greenwich time
    'P': '',     // jQuery DatePicker does not have difference to Greenwich time
    'T': '',     // jQuery DatePicker does not support timezone abbreviation
    'Z': '',     // jQuery DatePicker does not support timezone offset in seconds
    'c': '',     // jQuery DatePicker does not support ISO 8601 dates
    'r': '',     // jQuery DatePicker does not support RFC 2822 dates
    'U': '@'     // Unix timestamp - seconds since January 1 1970 00:00:00 GMT
};

target.flatpickr = {
    'd': 'd',   // Day of the month, two digits with leading zeros
    'D': 'D',   // A textual representation of a day, abbreviated (Mon through Sun)
    'j': 'j',   // Day of the month without leading zeros
    'l': 'l',   // A full textual representation of the day of the week (Sunday through Saturday)
    'N': '',    // ISO-8601 numeric representation of the day of the week (1 for Monday through 7 for Sunday)
    'S': 'J',   // English ordinal suffix for the day of the month, 2 characters.
    'w': 'w',   // Numeric representation of the day of the week (0 for Sunday through 6 for Saturday). Converted to ISO (1 for Monday through 7 for Sunday)
    'z': '',    // Day of the year (0 through 365)
    'W': '',    // ISO-8601 week number of year, weeks starting on Monday
    'F': 'F',   // A full textual representation of a month (January through December)
    'm': 'm',   // Numeric representation of a month, with leading zeros (01 through 12)
    'M': 'M',   // A short textual representation of a month (Jan through Dec)
    'n': 'n',   // Numeric representation of a month, without leading zeros (1 through 12)
    't': '',    // Number of days in the given month. No equivalent in Luxon.
    'L': '',    // Whether it's a leap year (1 if it is a leap year, 0 otherwise). No equivalent in Luxon.
    'o': '',    // ISO-8601 year number
    'Y': 'Y',   // A full numeric representation of a year, 4 digits
    'y': 'y',   // A two digit representation of a year
    'a': 'K',   // Lowercase Ante meridiem and Post meridiem (am or pm)
    'A': 'K',   // Uppercase Ante meridiem and Post meridiem (AM or PM)
    'B': '',    // Swatch Internet time (000 through 999). No equivalent in Luxon.
    'g': '',    // 12-hour format of an hour without leading zeros (1 through 12)
    'G': 'G',   // 24-hour format of an hour without leading zeros (0 through 23)
    'h': 'h',   // 12-hour format of an hour with leading zeros (01 through 12)
    'H': 'H',   // 24-hour format of an hour with leading zeros (00 through 23)
    'i': 'i',   // Minutes with leading zeros (00 to 59)
    's': 'S',   // Seconds with leading zeros (00 through 59)
    'u': '',    // Microseconds (up to 999), mapped to milliseconds
    'v': '',    // Milliseconds (added in PHP 7.0.0). No equivalent in Luxon, so we map it to the same as 'u'
    'e': '',    // Timezone identifier (e.g., UTC, GMT, Atlantic/Azores)
    'I': '',    // Whether or not the date is in daylight saving time. No equivalent in Luxon.
    'O': '',    // Difference to Greenwich time (GMT) without colon between hours and minutes (+0200)
    'P': '',    // Difference to Greenwich time (GMT) with colon between hours and minutes (+02:00)
    'T': '',    // Timezone (e.g., EST, MDT). Mapped to the closest thing in Luxon, display in the user's locale.
    'Z': '',    // Timezone offset in seconds. The offset for time zones west of UTC is always negative, and for those east of UTC is always positive. No equivalent in Luxon.
    'c': '',    // ISO 8601 formatted date.
    'r': '',    // RFC2822 formatted date.
    'U': 'U',   // Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
};

target.luxon = {
    'd': 'dd',   // Day of the month, two digits with leading zeros
    'D': 'ccc',  // A textual representation of a day, abbreviated (Mon through Sun)
    'j': 'd',    // Day of the month without leading zeros
    'l': 'cccc', // A full textual representation of the day of the week (Sunday through Saturday)
    'N': 'c',    // ISO-8601 numeric representation of the day of the week (1 for Monday through 7 for Sunday)
    'S': '',     // English ordinal suffix for the day of the month, 2 characters. No equivalent in Luxon.
    'w': 'c',    // Numeric representation of the day of the week (0 for Sunday through 6 for Saturday). Converted to ISO (1 for Monday through 7 for Sunday)
    'z': 'o',    // Day of the year (0 through 365)
    'W': 'WW',   // ISO-8601 week number of year, weeks starting on Monday
    'F': 'MMMM', // A full textual representation of a month (January through December)
    'm': 'LL',   // Numeric representation of a month, with leading zeros (01 through 12)
    'M': 'LLL',  // A short textual representation of a month (Jan through Dec)
    'n': 'L',    // Numeric representation of a month, without leading zeros (1 through 12)
    't': '',     // Number of days in the given month. No equivalent in Luxon.
    'L': '',     // Whether it's a leap year (1 if it is a leap year, 0 otherwise). No equivalent in Luxon.
    'o': 'kk',   // ISO-8601 year number
    'Y': 'yyyy', // A full numeric representation of a year, 4 digits
    'y': 'yy',   // A two digit representation of a year
    'a': 'a',    // Lowercase Ante meridiem and Post meridiem (am or pm)
    'A': 'a',    // Uppercase Ante meridiem and Post meridiem (AM or PM)
    'B': '',     // Swatch Internet time (000 through 999). No equivalent in Luxon.
    'g': 'h',    // 12-hour format of an hour without leading zeros (1 through 12)
    'G': 'H',    // 24-hour format of an hour without leading zeros (0 through 23)
    'h': 'hh',   // 12-hour format of an hour with leading zeros (01 through 12)
    'H': 'HH',   // 24-hour format of an hour with leading zeros (00 through 23)
    'i': 'mm',   // Minutes with leading zeros (00 to 59)
    's': 'ss',   // Seconds with leading zeros (00 through 59)
    'u': 'SSS',  // Microseconds (up to 999), mapped to milliseconds
    'v': 'SSS',  // Milliseconds (added in PHP 7.0.0). No equivalent in Luxon, so we map it to the same as 'u'
    'e': 'z',    // Timezone identifier (e.g., UTC, GMT, Atlantic/Azores)
    'I': '',     // Whether or not the date is in daylight saving time. No equivalent in Luxon.
    'O': 'ZZ',   // Difference to Greenwich time (GMT) without colon between hours and minutes (+0200)
    'P': 'ZZ',   // Difference to Greenwich time (GMT) with colon between hours and minutes (+02:00)
    'T': 'ZZZ',  // Timezone (e.g., EST, MDT). Mapped to the closest thing in Luxon, display in the user's locale.
    'Z': '',     // Timezone offset in seconds. The offset for time zones west of UTC is always negative, and for those east of UTC is always positive. No equivalent in Luxon.
    'c': '',     // ISO 8601 formatted date. No equivalent in Luxon.
    'r': '',     // RFC2822 formatted date. No equivalent in Luxon.
    'U': 'X',    // Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
};

export const mapFormat = function (inputPhpFormat, targetFormat) {
    let mapAgainstFormat = target[targetFormat];

    let mappedFormat = "";
    inputPhpFormat.split('').forEach(function (character) {
        if (mapAgainstFormat[character] !== undefined) {
            mappedFormat = mappedFormat + "" + mapAgainstFormat[character]
        } else {
            mappedFormat = mappedFormat + "" + character;
        }
    });

    return mappedFormat;
}

export const getFormatFromSettings = function (formattingKey, targetFormat) {
    let format = i18n.__("language."+formattingKey);

    return mapFormat(format, targetFormat);
}

export default {
    mapFormat: mapFormat,
    getFormatFromSettings: getFormatFromSettings
};

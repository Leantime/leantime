package br.com.stimuli.string{

        /**
        *   Creates a string with variable substitutions. Very similiar to printf, specially python's printf
        *   @param raw The string to be substituted.
        *   @param rest  The objects to be substitued, can be positional or by properties inside the object (in wich case only one object can be passed)
        *   @return The formated and substitued string. 
        *   @example
        *   <pre>
        *   import br.com.stimuli.string.printf;
        *   // objects are substitued in the other they appear
        *   
        *   printf("This is an %s lybrary for creating %s", "Actioscript 3.0", "strings");
        *   // outputs: "This is an Actioscript 3.0 lybrary for creating strings";
        *   // you can also format numbers:
        *   
        *   printf("You can also display numbers like PI: %f, and format them to a fixed precision, such as PI with 3 decimal places %.3f", Math.PI, Math.PI);
        *   // outputs: " You can also display numbers like PI: 3.141592653589793, and format them to a fixed precision, such as PI with 3 decimal places 3.142"
        *   // Instead of positional (the order of arguments to print f, you can also use propertie of an object):
        *   var userInfo : Object = {
        	"name": "Arthur Debert",
        	"email": "arthur@stimuli.com.br",
        	"website":"http://www.stimuli.com.br/",
        	"ocupation": "developer"
        }
        *   
        *   printf("My name is %(name)s and I am a %(ocupation)s. You can read more on my personal %(website)s, or reach me through my %(email)s", userInfo);
        *   // outputs: "My name is Arthur Debert and I am a developer. You can read more on my personal http://www.stimuli.com.br/, or reach me through my arthur@stimuli.com.br"
        *   // you can also use date parts:
        *   var date : Date = new Date();
        *   printf("Today is %d/%m/%Y", date, date, date)
        *   
        *   </pre>
        *   @see br.com.stimuli.string 
        */
         public function printf(raw : String, ...rest) : String{
         	/**
			* Pretty ugly!
			*   basicaly
			*   % -> the start of a substitution hole
			*   (some_var_name) -> [optional] used in named substitutions
			*   .xx -> [optional] the precision with witch numbers will be formated  
			*   x -> the formatter (string, hexa, float, date part)
			*/
			var SUBS_RE : RegExp = /%(\((?P<var_name>[\w_\d]+)\))?(\.(?P<precision>[0-9]))?(?P<formater>[sxofaAbBcdHIjmMpSUwWxXyYZ])/ig;

            var matches : Array = [];
            var result : Object = SUBS_RE.exec(raw);
            var match : Match;
            var runs : int = 0;
            var numMatches : int = 0;
            var numberVariables : int = rest.length;
            // quick check if we find string subs amongst the text to match (something like %(foo)s
            var isPositionalSubts : Boolean = !Boolean(raw.match(/%\(\s*[\w\d_]+\s*\)/));
            trace(raw, isPositionalSubts);
            var replacementValue : *;
            var formater : String;
            var varName : String;
            var precision : String;
            // matched through the string, creating Match objects for easier later reuse
            while (Boolean(result)){
                match = new Match();
                match.startIndex = result.index;
                match.length = String(result[0]).length;
                match.endIndex = match.startIndex + match.length;
                match.content = String(result[0]);
                trace(match.content);
                // try to get substitution properties
                formater = result.formater;
                varName = result.var_name;
                precision = result.precision;
                
                if (isPositionalSubts){
                    // by position, grab next subs:
                    try{
                        replacementValue = rest[matches.length];        
                    }catch(e : Error){
                        throw new Error(BAD_VARIABLE_NUMBER)
                    }
                    
                }else{
                    // be hash / properties 
                    replacementValue = rest[0][varName];
                    if (replacementValue == undefined){
                        // check for bad variable names
                        var errorMsg : String = "Var name:'" + varName + "' not found on " + rest[0];
                        throw new Error(errorMsg);
                    }
                    
                    
                }
                // format the string accodingly to the formatter
                if (formater == STRING_FORMATTER){
                    match.replacement = replacementValue.toString();
                }else if (formater == FLOAT_FORMATER){
                    // floats, check if we need to truncate precision
                    if (precision){
                        match.replacement = truncateNumber(Number(replacementValue), int(precision)).toString()
                    }else{
                        match.replacement = replacementValue.toString();
                    }
                }else if (formater == OCTAL_FORMATER){
                    match.replacement = int(replacementValue).toString(8);
                }else if (formater == HEXA_FORMATER){
                    match.replacement = "0x" + int(replacementValue).toString(16);
                }else if(DATES_FORMATERS.indexOf(formater) > -1){
                    switch (formater){
                        case DATE_DAY_FORMATTER:
                            match.replacement = replacementValue.date;
                            break
                        case DATE_FULLYEAR_FORMATTER:
                            match.replacement = replacementValue.fullYear;
                            break
                        case DATE_YEAR_FORMATTER:
                            match.replacement = replacementValue.fullYear.toString().substr(2,2);
                            break
                        case DATE_MONTH_FORMATTER:
                            match.replacement = replacementValue.month + 1;
                            break
                        case DATE_HOUR24_FORMATTER:
                            match.replacement = replacementValue.hours;
                            break
                        case DATE_HOUR_FORMATTER:
                            var hours24 : Number = replacementValue.hours;
                            match.replacement =  (hours24 -12).toString();
                            break
                        case DATE_HOUR_AMPM_FORMATTER:
                            match.replacement =  (replacementValue.hours  >= 12 ? "p.m" : "a.m");
                            break
                        case DATE_TOLOCALE_FORMATTER:
                            match.replacement = replacementValue.toLocaleString();
                            break
                        case DATE_MINUTES_FORMATTER:
                            match.replacement = replacementValue.minutes;
                            break
                        case DATE_SECONDS_FORMATTER:
                            match.replacement = replacementValue.seconds;
                            break    
                    }
                }else{
                    trace("no good replacment " );
                }
                matches.push(match);
                // just a small check in case we get stuck: kludge!
                runs ++;
                if (runs > 10000){
                    trace("something is wrong, breaking out")
                    break
                }
                numMatches ++;
                // iterates next match
                result = SUBS_RE.exec(raw);
            }
            // in case there's nothing to substitute, just return the initial string
            if(matches.length == 0){
                trace("no matches, returning" );
                return raw;
            }
            // now actually do the substitution, keeping a buffer to be joined at 
            //the end for better performance
            var buffer : Array = [];
            var lastMatch : Match;  
            // beggininf os string, if it doesn't start with a substitition
            var previous : String = raw.substr(0, matches[0].startIndex);
            var subs : String;
            for each(match in matches){
                // finds out the previous string part and the next substitition
                if (lastMatch){
                    previous = raw.substring(lastMatch.endIndex  ,  match.startIndex);
                }
                buffer.push(previous);
                buffer.push(match.replacement);
                lastMatch = match;
                
            }
            // buffer the tail of the string: text after the last substitution
            buffer.push(raw.substr(match.endIndex, raw.length - match.endIndex));
            return buffer.join("");
        }
    }


// internal usage
/** @private */
const BAD_VARIABLE_NUMBER : String = "The number of variables to be replaced and template holes don't match";
/** Converts to a string*/
const STRING_FORMATTER : String = "s";
/** Outputs as a Number, can use the precision specifier: %.2sf will output a float with 2 decimal digits.*/
const FLOAT_FORMATER : String = "f";
/** Converts to an OCTAL number */
const OCTAL_FORMATER : String = "o";
/** Converts to a Hexa number (includes 0x) */
const HEXA_FORMATER : String = "x";
/** @private */
const DATES_FORMATERS : String = "aAbBcdHIjmMpSUwWxXyYZ";
/** Day of month, from 0 to 30 on <code>Date</code> objects.*/
const DATE_DAY_FORMATTER : String = "d";
/** Full year, e.g. 2007 on <code>Date</code> objects.*/
const DATE_FULLYEAR_FORMATTER : String = "Y";
/** Year, e.g. 07 on <code>Date</code> objects.*/
const DATE_YEAR_FORMATTER : String = "y";
/** Month from 1 to 12 on <code>Date</code> objects.*/
const DATE_MONTH_FORMATTER : String = "m";
/** Hours (0-23) on <code>Date</code> objects.*/
const DATE_HOUR24_FORMATTER : String = "H";
/** Hours 0-12 on <code>Date</code> objects.*/
const DATE_HOUR_FORMATTER : String = "I";
/** a.m or p.m on <code>Date</code> objects.*/
const DATE_HOUR_AMPM_FORMATTER : String = "p";
/** Minutes on <code>Date</code> objects.*/
const DATE_MINUTES_FORMATTER : String = "M";
/** Seconds on <code>Date</code> objects.*/
const DATE_SECONDS_FORMATTER : String = "S";
/** A string rep of a <code>Date</code> object on the current locale.*/
const DATE_TOLOCALE_FORMATTER : String = "c";

var version : String = "$Id: printf.as 5 2008-08-01 12:18:25Z debert $"

  

/** @private
 * Internal class that normalizes matching information.
 */
class Match{
    public var startIndex : int;
    public var endIndex : int;
    public var length : int;
    public var content : String;
    public var replacement : String;
    public var before : String;
    public function toString() : String{
        return "Match [" + startIndex + " - " + endIndex + "] (" + length + ") " + content + ", replacement:" +replacement + ";"
    }
}
/** @private */
function truncateNumber(raw : Number, decimals :int =2) : Number {
    var power : int = Math.pow(10, decimals);
   return Math.round(raw * ( power )) / power;
}

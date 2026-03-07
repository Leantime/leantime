import{g as Zt}from"./chunk-WVR4S24B-CnuN37H-.js";import{s as te}from"./chunk-NRVI72HA-4P9RcDN7.js";import{_ as d,l as _,c as w,r as ee,u as se,a as ie,b as re,g as ae,s as ne,p as oe,q as le,T as ce,k as U,y as he}from"./compiled-global-component-CjKGaiAj.js";var kt=function(){var t=d(function(Y,a,c,r){for(c=c||{},r=Y.length;r--;c[Y[r]]=a);return c},"o"),e=[1,2],o=[1,3],s=[1,4],h=[2,4],u=[1,9],S=[1,11],g=[1,16],n=[1,17],T=[1,18],b=[1,19],C=[1,33],A=[1,20],k=[1,21],f=[1,22],D=[1,23],R=[1,24],L=[1,26],$=[1,27],I=[1,28],P=[1,29],et=[1,30],st=[1,31],it=[1,32],rt=[1,35],at=[1,36],nt=[1,37],ot=[1,38],j=[1,34],p=[1,4,5,16,17,19,21,22,24,25,26,27,28,29,33,35,37,38,41,45,48,51,52,53,54,57],lt=[1,4,5,14,15,16,17,19,21,22,24,25,26,27,28,29,33,35,37,38,39,40,41,45,48,51,52,53,54,57],At=[4,5,16,17,19,21,22,24,25,26,27,28,29,33,35,37,38,41,45,48,51,52,53,54,57],yt={trace:d(function(){},"trace"),yy:{},symbols_:{error:2,start:3,SPACE:4,NL:5,SD:6,document:7,line:8,statement:9,classDefStatement:10,styleStatement:11,cssClassStatement:12,idStatement:13,DESCR:14,"-->":15,HIDE_EMPTY:16,scale:17,WIDTH:18,COMPOSIT_STATE:19,STRUCT_START:20,STRUCT_STOP:21,STATE_DESCR:22,AS:23,ID:24,FORK:25,JOIN:26,CHOICE:27,CONCURRENT:28,note:29,notePosition:30,NOTE_TEXT:31,direction:32,acc_title:33,acc_title_value:34,acc_descr:35,acc_descr_value:36,acc_descr_multiline_value:37,CLICK:38,STRING:39,HREF:40,classDef:41,CLASSDEF_ID:42,CLASSDEF_STYLEOPTS:43,DEFAULT:44,style:45,STYLE_IDS:46,STYLEDEF_STYLEOPTS:47,class:48,CLASSENTITY_IDS:49,STYLECLASS:50,direction_tb:51,direction_bt:52,direction_rl:53,direction_lr:54,eol:55,";":56,EDGE_STATE:57,STYLE_SEPARATOR:58,left_of:59,right_of:60,$accept:0,$end:1},terminals_:{2:"error",4:"SPACE",5:"NL",6:"SD",14:"DESCR",15:"-->",16:"HIDE_EMPTY",17:"scale",18:"WIDTH",19:"COMPOSIT_STATE",20:"STRUCT_START",21:"STRUCT_STOP",22:"STATE_DESCR",23:"AS",24:"ID",25:"FORK",26:"JOIN",27:"CHOICE",28:"CONCURRENT",29:"note",31:"NOTE_TEXT",33:"acc_title",34:"acc_title_value",35:"acc_descr",36:"acc_descr_value",37:"acc_descr_multiline_value",38:"CLICK",39:"STRING",40:"HREF",41:"classDef",42:"CLASSDEF_ID",43:"CLASSDEF_STYLEOPTS",44:"DEFAULT",45:"style",46:"STYLE_IDS",47:"STYLEDEF_STYLEOPTS",48:"class",49:"CLASSENTITY_IDS",50:"STYLECLASS",51:"direction_tb",52:"direction_bt",53:"direction_rl",54:"direction_lr",56:";",57:"EDGE_STATE",58:"STYLE_SEPARATOR",59:"left_of",60:"right_of"},productions_:[0,[3,2],[3,2],[3,2],[7,0],[7,2],[8,2],[8,1],[8,1],[9,1],[9,1],[9,1],[9,1],[9,2],[9,3],[9,4],[9,1],[9,2],[9,1],[9,4],[9,3],[9,6],[9,1],[9,1],[9,1],[9,1],[9,4],[9,4],[9,1],[9,2],[9,2],[9,1],[9,5],[9,5],[10,3],[10,3],[11,3],[12,3],[32,1],[32,1],[32,1],[32,1],[55,1],[55,1],[13,1],[13,1],[13,3],[13,3],[30,1],[30,1]],performAction:d(function(a,c,r,y,E,i,X){var l=i.length-1;switch(E){case 3:return y.setRootDoc(i[l]),i[l];case 4:this.$=[];break;case 5:i[l]!="nl"&&(i[l-1].push(i[l]),this.$=i[l-1]);break;case 6:case 7:this.$=i[l];break;case 8:this.$="nl";break;case 12:this.$=i[l];break;case 13:const J=i[l-1];J.description=y.trimColon(i[l]),this.$=J;break;case 14:this.$={stmt:"relation",state1:i[l-2],state2:i[l]};break;case 15:const gt=y.trimColon(i[l]);this.$={stmt:"relation",state1:i[l-3],state2:i[l-1],description:gt};break;case 19:this.$={stmt:"state",id:i[l-3],type:"default",description:"",doc:i[l-1]};break;case 20:var B=i[l],H=i[l-2].trim();if(i[l].match(":")){var ht=i[l].split(":");B=ht[0],H=[H,ht[1]]}this.$={stmt:"state",id:B,type:"default",description:H};break;case 21:this.$={stmt:"state",id:i[l-3],type:"default",description:i[l-5],doc:i[l-1]};break;case 22:this.$={stmt:"state",id:i[l],type:"fork"};break;case 23:this.$={stmt:"state",id:i[l],type:"join"};break;case 24:this.$={stmt:"state",id:i[l],type:"choice"};break;case 25:this.$={stmt:"state",id:y.getDividerId(),type:"divider"};break;case 26:this.$={stmt:"state",id:i[l-1].trim(),note:{position:i[l-2].trim(),text:i[l].trim()}};break;case 29:this.$=i[l].trim(),y.setAccTitle(this.$);break;case 30:case 31:this.$=i[l].trim(),y.setAccDescription(this.$);break;case 32:this.$={stmt:"click",id:i[l-3],url:i[l-2],tooltip:i[l-1]};break;case 33:this.$={stmt:"click",id:i[l-3],url:i[l-1],tooltip:""};break;case 34:case 35:this.$={stmt:"classDef",id:i[l-1].trim(),classes:i[l].trim()};break;case 36:this.$={stmt:"style",id:i[l-1].trim(),styleClass:i[l].trim()};break;case 37:this.$={stmt:"applyClass",id:i[l-1].trim(),styleClass:i[l].trim()};break;case 38:y.setDirection("TB"),this.$={stmt:"dir",value:"TB"};break;case 39:y.setDirection("BT"),this.$={stmt:"dir",value:"BT"};break;case 40:y.setDirection("RL"),this.$={stmt:"dir",value:"RL"};break;case 41:y.setDirection("LR"),this.$={stmt:"dir",value:"LR"};break;case 44:case 45:this.$={stmt:"state",id:i[l].trim(),type:"default",description:""};break;case 46:this.$={stmt:"state",id:i[l-2].trim(),classes:[i[l].trim()],type:"default",description:""};break;case 47:this.$={stmt:"state",id:i[l-2].trim(),classes:[i[l].trim()],type:"default",description:""};break}},"anonymous"),table:[{3:1,4:e,5:o,6:s},{1:[3]},{3:5,4:e,5:o,6:s},{3:6,4:e,5:o,6:s},t([1,4,5,16,17,19,22,24,25,26,27,28,29,33,35,37,38,41,45,48,51,52,53,54,57],h,{7:7}),{1:[2,1]},{1:[2,2]},{1:[2,3],4:u,5:S,8:8,9:10,10:12,11:13,12:14,13:15,16:g,17:n,19:T,22:b,24:C,25:A,26:k,27:f,28:D,29:R,32:25,33:L,35:$,37:I,38:P,41:et,45:st,48:it,51:rt,52:at,53:nt,54:ot,57:j},t(p,[2,5]),{9:39,10:12,11:13,12:14,13:15,16:g,17:n,19:T,22:b,24:C,25:A,26:k,27:f,28:D,29:R,32:25,33:L,35:$,37:I,38:P,41:et,45:st,48:it,51:rt,52:at,53:nt,54:ot,57:j},t(p,[2,7]),t(p,[2,8]),t(p,[2,9]),t(p,[2,10]),t(p,[2,11]),t(p,[2,12],{14:[1,40],15:[1,41]}),t(p,[2,16]),{18:[1,42]},t(p,[2,18],{20:[1,43]}),{23:[1,44]},t(p,[2,22]),t(p,[2,23]),t(p,[2,24]),t(p,[2,25]),{30:45,31:[1,46],59:[1,47],60:[1,48]},t(p,[2,28]),{34:[1,49]},{36:[1,50]},t(p,[2,31]),{13:51,24:C,57:j},{42:[1,52],44:[1,53]},{46:[1,54]},{49:[1,55]},t(lt,[2,44],{58:[1,56]}),t(lt,[2,45],{58:[1,57]}),t(p,[2,38]),t(p,[2,39]),t(p,[2,40]),t(p,[2,41]),t(p,[2,6]),t(p,[2,13]),{13:58,24:C,57:j},t(p,[2,17]),t(At,h,{7:59}),{24:[1,60]},{24:[1,61]},{23:[1,62]},{24:[2,48]},{24:[2,49]},t(p,[2,29]),t(p,[2,30]),{39:[1,63],40:[1,64]},{43:[1,65]},{43:[1,66]},{47:[1,67]},{50:[1,68]},{24:[1,69]},{24:[1,70]},t(p,[2,14],{14:[1,71]}),{4:u,5:S,8:8,9:10,10:12,11:13,12:14,13:15,16:g,17:n,19:T,21:[1,72],22:b,24:C,25:A,26:k,27:f,28:D,29:R,32:25,33:L,35:$,37:I,38:P,41:et,45:st,48:it,51:rt,52:at,53:nt,54:ot,57:j},t(p,[2,20],{20:[1,73]}),{31:[1,74]},{24:[1,75]},{39:[1,76]},{39:[1,77]},t(p,[2,34]),t(p,[2,35]),t(p,[2,36]),t(p,[2,37]),t(lt,[2,46]),t(lt,[2,47]),t(p,[2,15]),t(p,[2,19]),t(At,h,{7:78}),t(p,[2,26]),t(p,[2,27]),{5:[1,79]},{5:[1,80]},{4:u,5:S,8:8,9:10,10:12,11:13,12:14,13:15,16:g,17:n,19:T,21:[1,81],22:b,24:C,25:A,26:k,27:f,28:D,29:R,32:25,33:L,35:$,37:I,38:P,41:et,45:st,48:it,51:rt,52:at,53:nt,54:ot,57:j},t(p,[2,32]),t(p,[2,33]),t(p,[2,21])],defaultActions:{5:[2,1],6:[2,2],47:[2,48],48:[2,49]},parseError:d(function(a,c){if(c.recoverable)this.trace(a);else{var r=new Error(a);throw r.hash=c,r}},"parseError"),parse:d(function(a){var c=this,r=[0],y=[],E=[null],i=[],X=this.table,l="",B=0,H=0,ht=2,J=1,gt=i.slice.call(arguments,1),m=Object.create(this.lexer),V={yy:{}};for(var Tt in this.yy)Object.prototype.hasOwnProperty.call(this.yy,Tt)&&(V.yy[Tt]=this.yy[Tt]);m.setInput(a,V.yy),V.yy.lexer=m,V.yy.parser=this,typeof m.yylloc>"u"&&(m.yylloc={});var Et=m.yylloc;i.push(Et);var qt=m.options&&m.options.ranges;typeof V.yy.parseError=="function"?this.parseError=V.yy.parseError:this.parseError=Object.getPrototypeOf(this).parseError;function Qt(O){r.length=r.length-2*O,E.length=E.length-O,i.length=i.length-O}d(Qt,"popStack");function xt(){var O;return O=y.pop()||m.lex()||J,typeof O!="number"&&(O instanceof Array&&(y=O,O=y.pop()),O=c.symbols_[O]||O),O}d(xt,"lex");for(var x,M,N,_t,W={},ut,F,Lt,dt;;){if(M=r[r.length-1],this.defaultActions[M]?N=this.defaultActions[M]:((x===null||typeof x>"u")&&(x=xt()),N=X[M]&&X[M][x]),typeof N>"u"||!N.length||!N[0]){var mt="";dt=[];for(ut in X[M])this.terminals_[ut]&&ut>ht&&dt.push("'"+this.terminals_[ut]+"'");m.showPosition?mt="Parse error on line "+(B+1)+`:
`+m.showPosition()+`
Expecting `+dt.join(", ")+", got '"+(this.terminals_[x]||x)+"'":mt="Parse error on line "+(B+1)+": Unexpected "+(x==J?"end of input":"'"+(this.terminals_[x]||x)+"'"),this.parseError(mt,{text:m.match,token:this.terminals_[x]||x,line:m.yylineno,loc:Et,expected:dt})}if(N[0]instanceof Array&&N.length>1)throw new Error("Parse Error: multiple actions possible at state: "+M+", token: "+x);switch(N[0]){case 1:r.push(x),E.push(m.yytext),i.push(m.yylloc),r.push(N[1]),x=null,H=m.yyleng,l=m.yytext,B=m.yylineno,Et=m.yylloc;break;case 2:if(F=this.productions_[N[1]][1],W.$=E[E.length-F],W._$={first_line:i[i.length-(F||1)].first_line,last_line:i[i.length-1].last_line,first_column:i[i.length-(F||1)].first_column,last_column:i[i.length-1].last_column},qt&&(W._$.range=[i[i.length-(F||1)].range[0],i[i.length-1].range[1]]),_t=this.performAction.apply(W,[l,H,B,V.yy,N[1],E,i].concat(gt)),typeof _t<"u")return _t;F&&(r=r.slice(0,-1*F*2),E=E.slice(0,-1*F),i=i.slice(0,-1*F)),r.push(this.productions_[N[1]][0]),E.push(W.$),i.push(W._$),Lt=X[r[r.length-2]][r[r.length-1]],r.push(Lt);break;case 3:return!0}}return!0},"parse")},Jt=function(){var Y={EOF:1,parseError:d(function(c,r){if(this.yy.parser)this.yy.parser.parseError(c,r);else throw new Error(c)},"parseError"),setInput:d(function(a,c){return this.yy=c||this.yy||{},this._input=a,this._more=this._backtrack=this.done=!1,this.yylineno=this.yyleng=0,this.yytext=this.matched=this.match="",this.conditionStack=["INITIAL"],this.yylloc={first_line:1,first_column:0,last_line:1,last_column:0},this.options.ranges&&(this.yylloc.range=[0,0]),this.offset=0,this},"setInput"),input:d(function(){var a=this._input[0];this.yytext+=a,this.yyleng++,this.offset++,this.match+=a,this.matched+=a;var c=a.match(/(?:\r\n?|\n).*/g);return c?(this.yylineno++,this.yylloc.last_line++):this.yylloc.last_column++,this.options.ranges&&this.yylloc.range[1]++,this._input=this._input.slice(1),a},"input"),unput:d(function(a){var c=a.length,r=a.split(/(?:\r\n?|\n)/g);this._input=a+this._input,this.yytext=this.yytext.substr(0,this.yytext.length-c),this.offset-=c;var y=this.match.split(/(?:\r\n?|\n)/g);this.match=this.match.substr(0,this.match.length-1),this.matched=this.matched.substr(0,this.matched.length-1),r.length-1&&(this.yylineno-=r.length-1);var E=this.yylloc.range;return this.yylloc={first_line:this.yylloc.first_line,last_line:this.yylineno+1,first_column:this.yylloc.first_column,last_column:r?(r.length===y.length?this.yylloc.first_column:0)+y[y.length-r.length].length-r[0].length:this.yylloc.first_column-c},this.options.ranges&&(this.yylloc.range=[E[0],E[0]+this.yyleng-c]),this.yyleng=this.yytext.length,this},"unput"),more:d(function(){return this._more=!0,this},"more"),reject:d(function(){if(this.options.backtrack_lexer)this._backtrack=!0;else return this.parseError("Lexical error on line "+(this.yylineno+1)+`. You can only invoke reject() in the lexer when the lexer is of the backtracking persuasion (options.backtrack_lexer = true).
`+this.showPosition(),{text:"",token:null,line:this.yylineno});return this},"reject"),less:d(function(a){this.unput(this.match.slice(a))},"less"),pastInput:d(function(){var a=this.matched.substr(0,this.matched.length-this.match.length);return(a.length>20?"...":"")+a.substr(-20).replace(/\n/g,"")},"pastInput"),upcomingInput:d(function(){var a=this.match;return a.length<20&&(a+=this._input.substr(0,20-a.length)),(a.substr(0,20)+(a.length>20?"...":"")).replace(/\n/g,"")},"upcomingInput"),showPosition:d(function(){var a=this.pastInput(),c=new Array(a.length+1).join("-");return a+this.upcomingInput()+`
`+c+"^"},"showPosition"),test_match:d(function(a,c){var r,y,E;if(this.options.backtrack_lexer&&(E={yylineno:this.yylineno,yylloc:{first_line:this.yylloc.first_line,last_line:this.last_line,first_column:this.yylloc.first_column,last_column:this.yylloc.last_column},yytext:this.yytext,match:this.match,matches:this.matches,matched:this.matched,yyleng:this.yyleng,offset:this.offset,_more:this._more,_input:this._input,yy:this.yy,conditionStack:this.conditionStack.slice(0),done:this.done},this.options.ranges&&(E.yylloc.range=this.yylloc.range.slice(0))),y=a[0].match(/(?:\r\n?|\n).*/g),y&&(this.yylineno+=y.length),this.yylloc={first_line:this.yylloc.last_line,last_line:this.yylineno+1,first_column:this.yylloc.last_column,last_column:y?y[y.length-1].length-y[y.length-1].match(/\r?\n?/)[0].length:this.yylloc.last_column+a[0].length},this.yytext+=a[0],this.match+=a[0],this.matches=a,this.yyleng=this.yytext.length,this.options.ranges&&(this.yylloc.range=[this.offset,this.offset+=this.yyleng]),this._more=!1,this._backtrack=!1,this._input=this._input.slice(a[0].length),this.matched+=a[0],r=this.performAction.call(this,this.yy,this,c,this.conditionStack[this.conditionStack.length-1]),this.done&&this._input&&(this.done=!1),r)return r;if(this._backtrack){for(var i in E)this[i]=E[i];return!1}return!1},"test_match"),next:d(function(){if(this.done)return this.EOF;this._input||(this.done=!0);var a,c,r,y;this._more||(this.yytext="",this.match="");for(var E=this._currentRules(),i=0;i<E.length;i++)if(r=this._input.match(this.rules[E[i]]),r&&(!c||r[0].length>c[0].length)){if(c=r,y=i,this.options.backtrack_lexer){if(a=this.test_match(r,E[i]),a!==!1)return a;if(this._backtrack){c=!1;continue}else return!1}else if(!this.options.flex)break}return c?(a=this.test_match(c,E[y]),a!==!1?a:!1):this._input===""?this.EOF:this.parseError("Lexical error on line "+(this.yylineno+1)+`. Unrecognized text.
`+this.showPosition(),{text:"",token:null,line:this.yylineno})},"next"),lex:d(function(){var c=this.next();return c||this.lex()},"lex"),begin:d(function(c){this.conditionStack.push(c)},"begin"),popState:d(function(){var c=this.conditionStack.length-1;return c>0?this.conditionStack.pop():this.conditionStack[0]},"popState"),_currentRules:d(function(){return this.conditionStack.length&&this.conditionStack[this.conditionStack.length-1]?this.conditions[this.conditionStack[this.conditionStack.length-1]].rules:this.conditions.INITIAL.rules},"_currentRules"),topState:d(function(c){return c=this.conditionStack.length-1-Math.abs(c||0),c>=0?this.conditionStack[c]:"INITIAL"},"topState"),pushState:d(function(c){this.begin(c)},"pushState"),stateStackSize:d(function(){return this.conditionStack.length},"stateStackSize"),options:{"case-insensitive":!0},performAction:d(function(c,r,y,E){switch(y){case 0:return 38;case 1:return 40;case 2:return 39;case 3:return 44;case 4:return 51;case 5:return 52;case 6:return 53;case 7:return 54;case 8:break;case 9:break;case 10:return 5;case 11:break;case 12:break;case 13:break;case 14:break;case 15:return this.pushState("SCALE"),17;case 16:return 18;case 17:this.popState();break;case 18:return this.begin("acc_title"),33;case 19:return this.popState(),"acc_title_value";case 20:return this.begin("acc_descr"),35;case 21:return this.popState(),"acc_descr_value";case 22:this.begin("acc_descr_multiline");break;case 23:this.popState();break;case 24:return"acc_descr_multiline_value";case 25:return this.pushState("CLASSDEF"),41;case 26:return this.popState(),this.pushState("CLASSDEFID"),"DEFAULT_CLASSDEF_ID";case 27:return this.popState(),this.pushState("CLASSDEFID"),42;case 28:return this.popState(),43;case 29:return this.pushState("CLASS"),48;case 30:return this.popState(),this.pushState("CLASS_STYLE"),49;case 31:return this.popState(),50;case 32:return this.pushState("STYLE"),45;case 33:return this.popState(),this.pushState("STYLEDEF_STYLES"),46;case 34:return this.popState(),47;case 35:return this.pushState("SCALE"),17;case 36:return 18;case 37:this.popState();break;case 38:this.pushState("STATE");break;case 39:return this.popState(),r.yytext=r.yytext.slice(0,-8).trim(),25;case 40:return this.popState(),r.yytext=r.yytext.slice(0,-8).trim(),26;case 41:return this.popState(),r.yytext=r.yytext.slice(0,-10).trim(),27;case 42:return this.popState(),r.yytext=r.yytext.slice(0,-8).trim(),25;case 43:return this.popState(),r.yytext=r.yytext.slice(0,-8).trim(),26;case 44:return this.popState(),r.yytext=r.yytext.slice(0,-10).trim(),27;case 45:return 51;case 46:return 52;case 47:return 53;case 48:return 54;case 49:this.pushState("STATE_STRING");break;case 50:return this.pushState("STATE_ID"),"AS";case 51:return this.popState(),"ID";case 52:this.popState();break;case 53:return"STATE_DESCR";case 54:return 19;case 55:this.popState();break;case 56:return this.popState(),this.pushState("struct"),20;case 57:break;case 58:return this.popState(),21;case 59:break;case 60:return this.begin("NOTE"),29;case 61:return this.popState(),this.pushState("NOTE_ID"),59;case 62:return this.popState(),this.pushState("NOTE_ID"),60;case 63:this.popState(),this.pushState("FLOATING_NOTE");break;case 64:return this.popState(),this.pushState("FLOATING_NOTE_ID"),"AS";case 65:break;case 66:return"NOTE_TEXT";case 67:return this.popState(),"ID";case 68:return this.popState(),this.pushState("NOTE_TEXT"),24;case 69:return this.popState(),r.yytext=r.yytext.substr(2).trim(),31;case 70:return this.popState(),r.yytext=r.yytext.slice(0,-8).trim(),31;case 71:return 6;case 72:return 6;case 73:return 16;case 74:return 57;case 75:return 24;case 76:return r.yytext=r.yytext.trim(),14;case 77:return 15;case 78:return 28;case 79:return 58;case 80:return 5;case 81:return"INVALID"}},"anonymous"),rules:[/^(?:click\b)/i,/^(?:href\b)/i,/^(?:"[^"]*")/i,/^(?:default\b)/i,/^(?:.*direction\s+TB[^\n]*)/i,/^(?:.*direction\s+BT[^\n]*)/i,/^(?:.*direction\s+RL[^\n]*)/i,/^(?:.*direction\s+LR[^\n]*)/i,/^(?:%%(?!\{)[^\n]*)/i,/^(?:[^\}]%%[^\n]*)/i,/^(?:[\n]+)/i,/^(?:[\s]+)/i,/^(?:((?!\n)\s)+)/i,/^(?:#[^\n]*)/i,/^(?:%[^\n]*)/i,/^(?:scale\s+)/i,/^(?:\d+)/i,/^(?:\s+width\b)/i,/^(?:accTitle\s*:\s*)/i,/^(?:(?!\n||)*[^\n]*)/i,/^(?:accDescr\s*:\s*)/i,/^(?:(?!\n||)*[^\n]*)/i,/^(?:accDescr\s*\{\s*)/i,/^(?:[\}])/i,/^(?:[^\}]*)/i,/^(?:classDef\s+)/i,/^(?:DEFAULT\s+)/i,/^(?:\w+\s+)/i,/^(?:[^\n]*)/i,/^(?:class\s+)/i,/^(?:(\w+)+((,\s*\w+)*))/i,/^(?:[^\n]*)/i,/^(?:style\s+)/i,/^(?:[\w,]+\s+)/i,/^(?:[^\n]*)/i,/^(?:scale\s+)/i,/^(?:\d+)/i,/^(?:\s+width\b)/i,/^(?:state\s+)/i,/^(?:.*<<fork>>)/i,/^(?:.*<<join>>)/i,/^(?:.*<<choice>>)/i,/^(?:.*\[\[fork\]\])/i,/^(?:.*\[\[join\]\])/i,/^(?:.*\[\[choice\]\])/i,/^(?:.*direction\s+TB[^\n]*)/i,/^(?:.*direction\s+BT[^\n]*)/i,/^(?:.*direction\s+RL[^\n]*)/i,/^(?:.*direction\s+LR[^\n]*)/i,/^(?:["])/i,/^(?:\s*as\s+)/i,/^(?:[^\n\{]*)/i,/^(?:["])/i,/^(?:[^"]*)/i,/^(?:[^\n\s\{]+)/i,/^(?:\n)/i,/^(?:\{)/i,/^(?:%%(?!\{)[^\n]*)/i,/^(?:\})/i,/^(?:[\n])/i,/^(?:note\s+)/i,/^(?:left of\b)/i,/^(?:right of\b)/i,/^(?:")/i,/^(?:\s*as\s*)/i,/^(?:["])/i,/^(?:[^"]*)/i,/^(?:[^\n]*)/i,/^(?:\s*[^:\n\s\-]+)/i,/^(?:\s*:[^:\n;]+)/i,/^(?:[\s\S]*?end note\b)/i,/^(?:stateDiagram\s+)/i,/^(?:stateDiagram-v2\s+)/i,/^(?:hide empty description\b)/i,/^(?:\[\*\])/i,/^(?:[^:\n\s\-\{]+)/i,/^(?:\s*:[^:\n;]+)/i,/^(?:-->)/i,/^(?:--)/i,/^(?::::)/i,/^(?:$)/i,/^(?:.)/i],conditions:{LINE:{rules:[12,13],inclusive:!1},struct:{rules:[12,13,25,29,32,38,45,46,47,48,57,58,59,60,74,75,76,77,78],inclusive:!1},FLOATING_NOTE_ID:{rules:[67],inclusive:!1},FLOATING_NOTE:{rules:[64,65,66],inclusive:!1},NOTE_TEXT:{rules:[69,70],inclusive:!1},NOTE_ID:{rules:[68],inclusive:!1},NOTE:{rules:[61,62,63],inclusive:!1},STYLEDEF_STYLEOPTS:{rules:[],inclusive:!1},STYLEDEF_STYLES:{rules:[34],inclusive:!1},STYLE_IDS:{rules:[],inclusive:!1},STYLE:{rules:[33],inclusive:!1},CLASS_STYLE:{rules:[31],inclusive:!1},CLASS:{rules:[30],inclusive:!1},CLASSDEFID:{rules:[28],inclusive:!1},CLASSDEF:{rules:[26,27],inclusive:!1},acc_descr_multiline:{rules:[23,24],inclusive:!1},acc_descr:{rules:[21],inclusive:!1},acc_title:{rules:[19],inclusive:!1},SCALE:{rules:[16,17,36,37],inclusive:!1},ALIAS:{rules:[],inclusive:!1},STATE_ID:{rules:[51],inclusive:!1},STATE_STRING:{rules:[52,53],inclusive:!1},FORK_STATE:{rules:[],inclusive:!1},STATE:{rules:[12,13,39,40,41,42,43,44,49,50,54,55,56],inclusive:!1},ID:{rules:[12,13],inclusive:!1},INITIAL:{rules:[0,1,2,3,4,5,6,7,8,9,10,11,13,14,15,18,20,22,25,29,32,35,38,56,60,71,72,73,74,75,76,77,79,80,81],inclusive:!0}}};return Y}();yt.lexer=Jt;function ct(){this.yy={}}return d(ct,"Parser"),ct.prototype=yt,yt.Parser=ct,new ct}();kt.parser=kt;var Ge=kt,ue="TB",Ft="TB",It="dir",K="state",z="root",vt="relation",de="classDef",fe="style",pe="applyClass",Z="default",Yt="divider",Gt="fill:none",Bt="fill: #333",Vt="c",Mt="text",Ut="normal",bt="rect",Dt="rectWithTitle",Se="stateStart",ye="stateEnd",Ot="divider",Rt="roundedWithTitle",ge="note",Te="noteGroup",tt="statediagram",Ee="state",_e=`${tt}-${Ee}`,jt="transition",me="note",be="note-edge",De=`${jt} ${be}`,ke=`${tt}-${me}`,ve="cluster",Ce=`${tt}-${ve}`,Ae="cluster-alt",xe=`${tt}-${Ae}`,Ht="parent",Wt="note",Le="state",Ct="----",Ie=`${Ct}${Wt}`,Nt=`${Ct}${Ht}`,zt=d((t,e=Ft)=>{if(!t.doc)return e;let o=e;for(const s of t.doc)s.stmt==="dir"&&(o=s.value);return o},"getDir"),Oe=d(function(t,e){return e.db.getClasses()},"getClasses"),Re=d(async function(t,e,o,s){_.info("REF0:"),_.info("Drawing state diagram (v2)",e);const{securityLevel:h,state:u,layout:S}=w();s.db.extract(s.db.getRootDocV2());const g=s.db.getData(),n=Zt(e,h);g.type=s.type,g.layoutAlgorithm=S,g.nodeSpacing=u?.nodeSpacing||50,g.rankSpacing=u?.rankSpacing||50,g.markers=["barb"],g.diagramId=e,await ee(g,n);const T=8;try{(typeof s.db.getLinks=="function"?s.db.getLinks():new Map).forEach((C,A)=>{const k=typeof A=="string"?A:typeof A?.id=="string"?A.id:"";if(!k){_.warn("⚠️ Invalid or missing stateId from key:",JSON.stringify(A));return}const f=n.node()?.querySelectorAll("g");let D;if(f?.forEach(I=>{I.textContent?.trim()===k&&(D=I)}),!D){_.warn("⚠️ Could not find node matching text:",k);return}const R=D.parentNode;if(!R){_.warn("⚠️ Node has no parent, cannot wrap:",k);return}const L=document.createElementNS("http://www.w3.org/2000/svg","a"),$=C.url.replace(/^"+|"+$/g,"");if(L.setAttributeNS("http://www.w3.org/1999/xlink","xlink:href",$),L.setAttribute("target","_blank"),C.tooltip){const I=C.tooltip.replace(/^"+|"+$/g,"");L.setAttribute("title",I)}R.replaceChild(L,D),L.appendChild(D),_.info("🔗 Wrapped node in <a> tag for:",k,C.url)})}catch(b){_.error("❌ Error injecting clickable links:",b)}se.insertTitle(n,"statediagramTitleText",u?.titleTopMargin??25,s.db.getDiagramTitle()),te(n,T,tt,u?.useMaxWidth??!0)},"draw"),Be={getClasses:Oe,draw:Re,getDir:zt},pt=new Map,G=0;function St(t="",e=0,o="",s=Ct){const h=o!==null&&o.length>0?`${s}${o}`:"";return`${Le}-${t}${h}-${e}`}d(St,"stateDomId");var Ne=d((t,e,o,s,h,u,S,g)=>{_.trace("items",e),e.forEach(n=>{switch(n.stmt){case K:Q(t,n,o,s,h,u,S,g);break;case Z:Q(t,n,o,s,h,u,S,g);break;case vt:{Q(t,n.state1,o,s,h,u,S,g),Q(t,n.state2,o,s,h,u,S,g);const T={id:"edge"+G,start:n.state1.id,end:n.state2.id,arrowhead:"normal",arrowTypeEnd:"arrow_barb",style:Gt,labelStyle:"",label:U.sanitizeText(n.description??"",w()),arrowheadStyle:Bt,labelpos:Vt,labelType:Mt,thickness:Ut,classes:jt,look:S};h.push(T),G++}break}})},"setupDoc"),wt=d((t,e=Ft)=>{let o=e;if(t.doc)for(const s of t.doc)s.stmt==="dir"&&(o=s.value);return o},"getDir");function q(t,e,o){if(!e.id||e.id==="</join></fork>"||e.id==="</choice>")return;e.cssClasses&&(Array.isArray(e.cssCompiledStyles)||(e.cssCompiledStyles=[]),e.cssClasses.split(" ").forEach(h=>{const u=o.get(h);u&&(e.cssCompiledStyles=[...e.cssCompiledStyles??[],...u.styles])}));const s=t.find(h=>h.id===e.id);s?Object.assign(s,e):t.push(e)}d(q,"insertOrUpdateNode");function Kt(t){return t?.classes?.join(" ")??""}d(Kt,"getClassesFromDbInfo");function Xt(t){return t?.styles??[]}d(Xt,"getStylesFromDbInfo");var Q=d((t,e,o,s,h,u,S,g)=>{const n=e.id,T=o.get(n),b=Kt(T),C=Xt(T),A=w();if(_.info("dataFetcher parsedItem",e,T,C),n!=="root"){let k=bt;e.start===!0?k=Se:e.start===!1&&(k=ye),e.type!==Z&&(k=e.type),pt.get(n)||pt.set(n,{id:n,shape:k,description:U.sanitizeText(n,A),cssClasses:`${b} ${_e}`,cssStyles:C});const f=pt.get(n);e.description&&(Array.isArray(f.description)?(f.shape=Dt,f.description.push(e.description)):f.description?.length&&f.description.length>0?(f.shape=Dt,f.description===n?f.description=[e.description]:f.description=[f.description,e.description]):(f.shape=bt,f.description=e.description),f.description=U.sanitizeTextOrArray(f.description,A)),f.description?.length===1&&f.shape===Dt&&(f.type==="group"?f.shape=Rt:f.shape=bt),!f.type&&e.doc&&(_.info("Setting cluster for XCX",n,wt(e)),f.type="group",f.isGroup=!0,f.dir=wt(e),f.shape=e.type===Yt?Ot:Rt,f.cssClasses=`${f.cssClasses} ${Ce} ${u?xe:""}`);const D={labelStyle:"",shape:f.shape,label:f.description,cssClasses:f.cssClasses,cssCompiledStyles:[],cssStyles:f.cssStyles,id:n,dir:f.dir,domId:St(n,G),type:f.type,isGroup:f.type==="group",padding:8,rx:10,ry:10,look:S};if(D.shape===Ot&&(D.label=""),t&&t.id!=="root"&&(_.trace("Setting node ",n," to be child of its parent ",t.id),D.parentId=t.id),D.centerLabel=!0,e.note){const R={labelStyle:"",shape:ge,label:e.note.text,cssClasses:ke,cssStyles:[],cssCompiledStyles:[],id:n+Ie+"-"+G,domId:St(n,G,Wt),type:f.type,isGroup:f.type==="group",padding:A.flowchart?.padding,look:S,position:e.note.position},L=n+Nt,$={labelStyle:"",shape:Te,label:e.note.text,cssClasses:f.cssClasses,cssStyles:[],id:n+Nt,domId:St(n,G,Ht),type:"group",isGroup:!0,padding:16,look:S,position:e.note.position};G++,$.id=L,R.parentId=L,q(s,$,g),q(s,R,g),q(s,D,g);let I=n,P=R.id;e.note.position==="left of"&&(I=R.id,P=n),h.push({id:I+"-"+P,start:I,end:P,arrowhead:"none",arrowTypeEnd:"",style:Gt,labelStyle:"",classes:De,arrowheadStyle:Bt,labelpos:Vt,labelType:Mt,thickness:Ut,look:S})}else q(s,D,g)}e.doc&&(_.trace("Adding nodes children "),Ne(e,e.doc,o,s,h,!u,S,g))},"dataFetcher"),we=d(()=>{pt.clear(),G=0},"reset"),v={START_NODE:"[*]",START_TYPE:"start",END_NODE:"[*]",END_TYPE:"end",COLOR_KEYWORD:"color",FILL_KEYWORD:"fill",BG_FILL:"bgFill",STYLECLASS_SEP:","},$t=d(()=>new Map,"newClassesList"),Pt=d(()=>({relations:[],states:new Map,documents:{}}),"newDoc"),ft=d(t=>JSON.parse(JSON.stringify(t)),"clone"),Ve=class{constructor(t){this.version=t,this.nodes=[],this.edges=[],this.rootDoc=[],this.classes=$t(),this.documents={root:Pt()},this.currentDocument=this.documents.root,this.startEndCount=0,this.dividerCnt=0,this.links=new Map,this.getAccTitle=ie,this.setAccTitle=re,this.getAccDescription=ae,this.setAccDescription=ne,this.setDiagramTitle=oe,this.getDiagramTitle=le,this.clear(),this.setRootDoc=this.setRootDoc.bind(this),this.getDividerId=this.getDividerId.bind(this),this.setDirection=this.setDirection.bind(this),this.trimColon=this.trimColon.bind(this)}static{d(this,"StateDB")}static{this.relationType={AGGREGATION:0,EXTENSION:1,COMPOSITION:2,DEPENDENCY:3}}extract(t){this.clear(!0);for(const s of Array.isArray(t)?t:t.doc)switch(s.stmt){case K:this.addState(s.id.trim(),s.type,s.doc,s.description,s.note);break;case vt:this.addRelation(s.state1,s.state2,s.description);break;case de:this.addStyleClass(s.id.trim(),s.classes);break;case fe:this.handleStyleDef(s);break;case pe:this.setCssClass(s.id.trim(),s.styleClass);break;case"click":this.addLink(s.id,s.url,s.tooltip);break}const e=this.getStates(),o=w();we(),Q(void 0,this.getRootDocV2(),e,this.nodes,this.edges,!0,o.look,this.classes);for(const s of this.nodes)if(Array.isArray(s.label)){if(s.description=s.label.slice(1),s.isGroup&&s.description.length>0)throw new Error(`Group nodes can only have label. Remove the additional description for node [${s.id}]`);s.label=s.label[0]}}handleStyleDef(t){const e=t.id.trim().split(","),o=t.styleClass.split(",");for(const s of e){let h=this.getState(s);if(!h){const u=s.trim();this.addState(u),h=this.getState(u)}h&&(h.styles=o.map(u=>u.replace(/;/g,"")?.trim()))}}setRootDoc(t){_.info("Setting root doc",t),this.rootDoc=t,this.version===1?this.extract(t):this.extract(this.getRootDocV2())}docTranslator(t,e,o){if(e.stmt===vt){this.docTranslator(t,e.state1,!0),this.docTranslator(t,e.state2,!1);return}if(e.stmt===K&&(e.id===v.START_NODE?(e.id=t.id+(o?"_start":"_end"),e.start=o):e.id=e.id.trim()),e.stmt!==z&&e.stmt!==K||!e.doc)return;const s=[];let h=[];for(const u of e.doc)if(u.type===Yt){const S=ft(u);S.doc=ft(h),s.push(S),h=[]}else h.push(u);if(s.length>0&&h.length>0){const u={stmt:K,id:ce(),type:"divider",doc:ft(h)};s.push(ft(u)),e.doc=s}e.doc.forEach(u=>this.docTranslator(e,u,!0))}getRootDocV2(){return this.docTranslator({id:z,stmt:z},{id:z,stmt:z,doc:this.rootDoc},!0),{id:z,doc:this.rootDoc}}addState(t,e=Z,o=void 0,s=void 0,h=void 0,u=void 0,S=void 0,g=void 0){const n=t?.trim();if(!this.currentDocument.states.has(n))_.info("Adding state ",n,s),this.currentDocument.states.set(n,{stmt:K,id:n,descriptions:[],type:e,doc:o,note:h,classes:[],styles:[],textStyles:[]});else{const T=this.currentDocument.states.get(n);if(!T)throw new Error(`State not found: ${n}`);T.doc||(T.doc=o),T.type||(T.type=e)}if(s&&(_.info("Setting state description",n,s),(Array.isArray(s)?s:[s]).forEach(b=>this.addDescription(n,b.trim()))),h){const T=this.currentDocument.states.get(n);if(!T)throw new Error(`State not found: ${n}`);T.note=h,T.note.text=U.sanitizeText(T.note.text,w())}u&&(_.info("Setting state classes",n,u),(Array.isArray(u)?u:[u]).forEach(b=>this.setCssClass(n,b.trim()))),S&&(_.info("Setting state styles",n,S),(Array.isArray(S)?S:[S]).forEach(b=>this.setStyle(n,b.trim()))),g&&(_.info("Setting state styles",n,S),(Array.isArray(g)?g:[g]).forEach(b=>this.setTextStyle(n,b.trim())))}clear(t){this.nodes=[],this.edges=[],this.documents={root:Pt()},this.currentDocument=this.documents.root,this.startEndCount=0,this.classes=$t(),t||(this.links=new Map,he())}getState(t){return this.currentDocument.states.get(t)}getStates(){return this.currentDocument.states}logDocuments(){_.info("Documents = ",this.documents)}getRelations(){return this.currentDocument.relations}addLink(t,e,o){this.links.set(t,{url:e,tooltip:o}),_.warn("Adding link",t,e,o)}getLinks(){return this.links}startIdIfNeeded(t=""){return t===v.START_NODE?(this.startEndCount++,`${v.START_TYPE}${this.startEndCount}`):t}startTypeIfNeeded(t="",e=Z){return t===v.START_NODE?v.START_TYPE:e}endIdIfNeeded(t=""){return t===v.END_NODE?(this.startEndCount++,`${v.END_TYPE}${this.startEndCount}`):t}endTypeIfNeeded(t="",e=Z){return t===v.END_NODE?v.END_TYPE:e}addRelationObjs(t,e,o=""){const s=this.startIdIfNeeded(t.id.trim()),h=this.startTypeIfNeeded(t.id.trim(),t.type),u=this.startIdIfNeeded(e.id.trim()),S=this.startTypeIfNeeded(e.id.trim(),e.type);this.addState(s,h,t.doc,t.description,t.note,t.classes,t.styles,t.textStyles),this.addState(u,S,e.doc,e.description,e.note,e.classes,e.styles,e.textStyles),this.currentDocument.relations.push({id1:s,id2:u,relationTitle:U.sanitizeText(o,w())})}addRelation(t,e,o){if(typeof t=="object"&&typeof e=="object")this.addRelationObjs(t,e,o);else if(typeof t=="string"&&typeof e=="string"){const s=this.startIdIfNeeded(t.trim()),h=this.startTypeIfNeeded(t),u=this.endIdIfNeeded(e.trim()),S=this.endTypeIfNeeded(e);this.addState(s,h),this.addState(u,S),this.currentDocument.relations.push({id1:s,id2:u,relationTitle:o?U.sanitizeText(o,w()):void 0})}}addDescription(t,e){const o=this.currentDocument.states.get(t),s=e.startsWith(":")?e.replace(":","").trim():e;o?.descriptions?.push(U.sanitizeText(s,w()))}cleanupLabel(t){return t.startsWith(":")?t.slice(2).trim():t.trim()}getDividerId(){return this.dividerCnt++,`divider-id-${this.dividerCnt}`}addStyleClass(t,e=""){this.classes.has(t)||this.classes.set(t,{id:t,styles:[],textStyles:[]});const o=this.classes.get(t);e&&o&&e.split(v.STYLECLASS_SEP).forEach(s=>{const h=s.replace(/([^;]*);/,"$1").trim();if(RegExp(v.COLOR_KEYWORD).exec(s)){const S=h.replace(v.FILL_KEYWORD,v.BG_FILL).replace(v.COLOR_KEYWORD,v.FILL_KEYWORD);o.textStyles.push(S)}o.styles.push(h)})}getClasses(){return this.classes}setCssClass(t,e){t.split(",").forEach(o=>{let s=this.getState(o);if(!s){const h=o.trim();this.addState(h),s=this.getState(h)}s?.classes?.push(e)})}setStyle(t,e){this.getState(t)?.styles?.push(e)}setTextStyle(t,e){this.getState(t)?.textStyles?.push(e)}getDirectionStatement(){return this.rootDoc.find(t=>t.stmt===It)}getDirection(){return this.getDirectionStatement()?.value??ue}setDirection(t){const e=this.getDirectionStatement();e?e.value=t:this.rootDoc.unshift({stmt:It,value:t})}trimColon(t){return t.startsWith(":")?t.slice(1).trim():t.trim()}getData(){const t=w();return{nodes:this.nodes,edges:this.edges,other:{},config:t,direction:zt(this.getRootDocV2())}}getConfig(){return w().state}},$e=d(t=>`
defs #statediagram-barbEnd {
    fill: ${t.transitionColor};
    stroke: ${t.transitionColor};
  }
g.stateGroup text {
  fill: ${t.nodeBorder};
  stroke: none;
  font-size: 10px;
}
g.stateGroup text {
  fill: ${t.textColor};
  stroke: none;
  font-size: 10px;

}
g.stateGroup .state-title {
  font-weight: bolder;
  fill: ${t.stateLabelColor};
}

g.stateGroup rect {
  fill: ${t.mainBkg};
  stroke: ${t.nodeBorder};
}

g.stateGroup line {
  stroke: ${t.lineColor};
  stroke-width: 1;
}

.transition {
  stroke: ${t.transitionColor};
  stroke-width: 1;
  fill: none;
}

.stateGroup .composit {
  fill: ${t.background};
  border-bottom: 1px
}

.stateGroup .alt-composit {
  fill: #e0e0e0;
  border-bottom: 1px
}

.state-note {
  stroke: ${t.noteBorderColor};
  fill: ${t.noteBkgColor};

  text {
    fill: ${t.noteTextColor};
    stroke: none;
    font-size: 10px;
  }
}

.stateLabel .box {
  stroke: none;
  stroke-width: 0;
  fill: ${t.mainBkg};
  opacity: 0.5;
}

.edgeLabel .label rect {
  fill: ${t.labelBackgroundColor};
  opacity: 0.5;
}
.edgeLabel {
  background-color: ${t.edgeLabelBackground};
  p {
    background-color: ${t.edgeLabelBackground};
  }
  rect {
    opacity: 0.5;
    background-color: ${t.edgeLabelBackground};
    fill: ${t.edgeLabelBackground};
  }
  text-align: center;
}
.edgeLabel .label text {
  fill: ${t.transitionLabelColor||t.tertiaryTextColor};
}
.label div .edgeLabel {
  color: ${t.transitionLabelColor||t.tertiaryTextColor};
}

.stateLabel text {
  fill: ${t.stateLabelColor};
  font-size: 10px;
  font-weight: bold;
}

.node circle.state-start {
  fill: ${t.specialStateColor};
  stroke: ${t.specialStateColor};
}

.node .fork-join {
  fill: ${t.specialStateColor};
  stroke: ${t.specialStateColor};
}

.node circle.state-end {
  fill: ${t.innerEndBackground};
  stroke: ${t.background};
  stroke-width: 1.5
}
.end-state-inner {
  fill: ${t.compositeBackground||t.background};
  // stroke: ${t.background};
  stroke-width: 1.5
}

.node rect {
  fill: ${t.stateBkg||t.mainBkg};
  stroke: ${t.stateBorder||t.nodeBorder};
  stroke-width: 1px;
}
.node polygon {
  fill: ${t.mainBkg};
  stroke: ${t.stateBorder||t.nodeBorder};;
  stroke-width: 1px;
}
#statediagram-barbEnd {
  fill: ${t.lineColor};
}

.statediagram-cluster rect {
  fill: ${t.compositeTitleBackground};
  stroke: ${t.stateBorder||t.nodeBorder};
  stroke-width: 1px;
}

.cluster-label, .nodeLabel {
  color: ${t.stateLabelColor};
  // line-height: 1;
}

.statediagram-cluster rect.outer {
  rx: 5px;
  ry: 5px;
}
.statediagram-state .divider {
  stroke: ${t.stateBorder||t.nodeBorder};
}

.statediagram-state .title-state {
  rx: 5px;
  ry: 5px;
}
.statediagram-cluster.statediagram-cluster .inner {
  fill: ${t.compositeBackground||t.background};
}
.statediagram-cluster.statediagram-cluster-alt .inner {
  fill: ${t.altBackground?t.altBackground:"#efefef"};
}

.statediagram-cluster .inner {
  rx:0;
  ry:0;
}

.statediagram-state rect.basic {
  rx: 5px;
  ry: 5px;
}
.statediagram-state rect.divider {
  stroke-dasharray: 10,10;
  fill: ${t.altBackground?t.altBackground:"#efefef"};
}

.note-edge {
  stroke-dasharray: 5;
}

.statediagram-note rect {
  fill: ${t.noteBkgColor};
  stroke: ${t.noteBorderColor};
  stroke-width: 1px;
  rx: 0;
  ry: 0;
}
.statediagram-note rect {
  fill: ${t.noteBkgColor};
  stroke: ${t.noteBorderColor};
  stroke-width: 1px;
  rx: 0;
  ry: 0;
}

.statediagram-note text {
  fill: ${t.noteTextColor};
}

.statediagram-note .nodeLabel {
  color: ${t.noteTextColor};
}
.statediagram .edgeLabel {
  color: red; // ${t.noteTextColor};
}

#dependencyStart, #dependencyEnd {
  fill: ${t.lineColor};
  stroke: ${t.lineColor};
  stroke-width: 1;
}

.statediagramTitleText {
  text-anchor: middle;
  font-size: 18px;
  fill: ${t.textColor};
}
`,"getStyles"),Me=$e;export{Ve as S,Ge as a,Be as b,Me as s};

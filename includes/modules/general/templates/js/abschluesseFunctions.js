	function isNumber (o) {
			  return ! isNaN (o-0);
			}


	
	function bearbeitungsGebuehrConverter() {
		 	//alert('bearbeitungsGebuehrConverter');
			f=document.forms['myForm'];
			    if (f!=null) {
			    		myDecimalField=(eval("f.bearbeitungsGebuehr"));				
						myDecimalField.value=(currencyFormatToDecimal(eval("f.bearbeitungsGebuehrCurrency.value")));
					

			    }	
			          
		}

	function calcPraemieSonderVersInklusiveConverter(){
		bearbeitungsGebuehrConverter()
		calcPraemieSonderVersicherung(1);
	}
	//ausl Gaeste
	function showHideFahrtenInputs(num) {
		/*Anzeigen/Ausblenden der einzelnen Positionen 
		  incl löschen der Werte, wenn etwas ausgeblendet wurde*/
		
		//<a href="javascript:showHideFahrtenInputs(<?php echo $j;?>);">+/-</a> 
		
		if ($('#input'+num+'').css("display") == 'none' ) {
			$('#input'+num+'').css('display', 'table-row');	
		}else{
			$('#input'+num+'').css('display', 'none');
		}
		  
	}	

	function showFahrtenInputs() {
		/*Anzeigen/Ausblenden der einzelnen Positionen 
		  incl löschen der Werte, wenn etwas ausgeblendet wurde*/
		f=document.forms['myForm'];
		var num = f.anzfahrten.value;
		//alert(num);
		//alert('showFahrtenInputs');
		//<?php echo $maxPositionen;?>
		for(i=1; i<=100; i++){
		
			if (i<=num){
				$('#input'+i+'').css('display', 'table-row');
			}else{
				$('#input'+i+'').css('display', 'none');
				//clearing text when we close positions
				/*
				fieldname="#positionen_column1_row" + i;
				$(fieldname).val('');
				fieldname="#positionen_column2_row" + i;
				$(fieldname).val('');
				fieldname="#positionen_column3_row" + i;
				$(fieldname).val('');
				fieldname="#positionen_column3_row"  + i + "_euroformat";
				$(fieldname).val('');
				*/
					 
			}
		}
		
		  
	}
	
	function showInputs(num) {
		/*Anzeigen/Ausblenden der einzelnen Positionen 
		  incl löschen der Werte, wenn etwas ausgeblendet wurde*/
		 
		var inputnumber = num;
		//<?php echo $maxPositionen;?>
		for(i=1; i<=100; i++){
		
			if (i<=num){
				$('#input'+i+'').css('display', 'block');
			}else{
				$('#input'+i+'').css('display', 'none');
				//clearing text when we close positions
				fieldname="#positionen_column1_row" + i;
				$(fieldname).val('');
				fieldname="#positionen_column2_row" + i;
				$(fieldname).val('');
				fieldname="#positionen_column3_row" + i;
				$(fieldname).val('');
				fieldname="#positionen_column3_row"  + i + "_euroformat";
				$(fieldname).val('');
					 
			}
		}
		
		  
	}
		



	
/**
 * function decimalToCurrencyFormat
 * 
 * @param amount decimal
 * @return myVal 
 */

function decimalToCurrencyFormat(amount) {

	pattern = /^\s*(\-)?(\d+)(?:\.(\d{0,2})?)?\s*$/;
	if( pattern.exec(amount.toString()) ) {
		vz = RegExp.$1
		tmp = RegExp.$2;
		vk = '';
		nk = RegExp.$3.concat('00').substr(0,2);
		while( tmp.length ) {
			len = Math.min(tmp.length, 3);
			if( vk.length ) {
				vk = tmp.substring(tmp.length-len).concat('.').concat(vk);
			} else {
				vk = tmp.substring(tmp.length-len);
			}
			tmp = tmp.substring(0, tmp.length-len);
		}
		
		return vz.concat(vk).concat(',').concat(nk);
	}
	return '0,00';
}


/**
 * function currencyFormatToDecimal
 * 
 * @param amount String
 * @return myVal decimal
 */
function currencyFormatToDecimal(amount) {
	pattern = /^\s*(\-)?(\d+(?:\.\d{3})*)(?:,(\d{0,2})?)?\s*$/;
	if( pattern.exec(amount.toString()) ) {
		vz = RegExp.$1
		vk = RegExp.$2;
		nk = RegExp.$3.concat('00').substr(0,2);
		return vz.concat(vk).replace(/\./g, '').concat('.').concat(nk);
	}
	return '0.00';
}








function kulanzZahlungenRegulierungsCalc() {
	 
	f=document.forms[0];
	    if (f!=null) {
	    	
	    	f.schaden_kulanzzahlungen_gesamtbetrag.value=(currencyFormatToDecimal(f.schaden_kulanzzahlungen_gesamtbetrag_euroformat.value));
	    }	
	       
}
	
function heilkostenRegulierungsCalc() {
 
	f=document.forms[0];
	    if (f!=null) {
	    	f.schaden_heilkosten_gesamtbetrag.value=(currencyFormatToDecimal(f.schaden_heilkosten_gesamtbetrag_euroformat.value));
	    	f.schaden_heilkosten_erstattung.value=(currencyFormatToDecimal(f.schaden_heilkosten_erstattung_euroformat.value));
	        f.schaden_heilkosten_offen.value=Math.round(((Math.round(f.schaden_heilkosten_gesamtbetrag.value*100)/100-Math.round(f.schaden_heilkosten_erstattung.value*100)/100))*100)/100;
		    f.schaden_heilkosten_offen_euroformat.value=(decimalToCurrencyFormat(f.schaden_heilkosten_offen.value));		     
	    }
}


function schadenRegulierungsCalc() {
	
    f=document.forms[0];
   
    if (f!=null) {

		//format 
        
       
        f.schadenregulierung_schadenforderung.value=(currencyFormatToDecimal(f.schadenregulierung_schadenforderung_euroformat.value));
        f.schadenregulierung_selbstbehalt.value=(currencyFormatToDecimal(f.schadenregulierung_selbstbehalt_euroformat.value));
        f.schadenregulierung_regulierungsbetrag.value=Math.round(((Math.round(f.schadenregulierung_schadenforderung.value*100)/100-Math.round(f.schadenregulierung_selbstbehalt.value*100)/100))*100)/100;

        f.schadenregulierung_regulierungsbetrag_euroformat.value=(decimalToCurrencyFormat(f.schadenregulierung_regulierungsbetrag.value));

		//set decimal format for the hidden fields (Zahlungen)
		for(i=1; i<=15; i++){

			//alert (eval("f.zahlung_col1_row"+i+".value"));
			//alert (eval("f.zahlung_col1_row"+i+"_euroformat.value"));
			myDecimalField=(eval("f.zahlung_col1_row"+i));
			myDecimalField.value=(currencyFormatToDecimal(eval("f.zahlung_col1_row"+i+"_euroformat.value")));
		}

		
		//collect Zahlungen
		myGesamtZahlungen=0;
		
		for(i=1; i<=15; i++){

			//myDecimalField.value=(currencyFormatToDecimal(eval("f.zahlung_col1_row"+i+"_euroformat.value")));
			myDecimalField=(eval("f.zahlung_col1_row"+i));
			if ((myDecimalField.value)!=''){
				myGesamtZahlungen=(myGesamtZahlungen*1)+(Math.round(myDecimalField.value*100)/100);
			}
			
		}
		
        f.schadenregulierung_gesamtzahlung.value=myGesamtZahlungen;
        f.schadenregulierung_gesamtzahlung_euroformat.value=(decimalToCurrencyFormat(myGesamtZahlungen));
        f.schadenregulierung_verbindlichkeiten.value=Math.round(((Math.round(f.schadenregulierung_regulierungsbetrag.value*100)/100-Math.round(f.schadenregulierung_gesamtzahlung.value*100)/100))*100)/100;
        f.schadenregulierung_verbindlichkeiten_euroformat.value=(decimalToCurrencyFormat(f.schadenregulierung_verbindlichkeiten.value));
        
    }
}

//Berechne die Anzahl der Tage anhand der 2 Inputdaten
function calcDays(day1, day2){
	
	//alert('test:' + day1 + ' ' + day2);
		var dayFromPlane = day1;
		var dayToPlane = day2;

		var days = 0;
		
		if(dayFromPlane != "" && dayToPlane != ""){
		
			var arrDate = dayFromPlane.split(".");
			var arrDate2 = dayToPlane.split(".");
			
			// Neues Datum berechnen
			var dateFrom = new Date( parseInt(arrDate[2],10), (parseInt(arrDate[1],10)-1), (parseInt(arrDate[0],10)),0,0,0,0);
			var dateTo = new Date( parseInt(arrDate2[2],10), (parseInt(arrDate2[1],10)-1), (parseInt(arrDate2[0],10)),0,0,0,0);
			
			
			var month1=(parseInt(arrDate[1],10));
			var month2=(parseInt(arrDate2[1],10));
			var monate=((month2*1)-(month1*1)+1);
			
			
			//var dateTo = new Date( parseInt(1), (parseInt(1), parseInt(arrDate2[0]),0,0,0,0);
			
		//	alert	(dateFrom + ' ' + (parseInt(arrDate[2])) + ' '+  (parseInt(arrDate[1])-1) + ' '+ (parseInt(arrDate[0],10)));
		//alert	(dateTo + ' ' +  (parseInt(arrDate2[2])) + ' '+  (parseInt(arrDate2[1])-1) + ' '+ (parseInt(arrDate2[0],10)));
			var day = 1000*60*60*24;		
			
		//	alert (dateFrom.getTime() + ' ' + dateTo.getTime());
			days =  Math.ceil( ((dateTo.getTime()-dateFrom.getTime()) / (day)) + 1);
			
		}


		if(days < 0) {

			alert('Fehler bei den Daten, bitte ÃœberprÃ¼fen Sie ihre Eingabe');

			$('#tage').val('');
			
		}else{

			$('#tage').val(days);
			
		}
	
		//checkFormDate(day1);
		//checkFormDate(day2);
		

	}


//Berechne die Anzahl der Tage anhand der 2 Inputdaten
function calcDaysWithParam(day1, day2, myNumber){
	//alert('calcDaysWithParam');
	//alert(myNumber);
	//alert ('test');
	//alert('test:' + day1 + ' ' + day2);
		var dayFromPlane = day1;
		var dayToPlane = day2;
		//alert(day1);
		//alert(day2);
		var days = 0;
		
		if(dayFromPlane != "" && dayToPlane != ""){
		
			var arrDate = dayFromPlane.split(".");
			var arrDate2 = dayToPlane.split(".");
			
			// Neues Datum berechnen
			var dateFrom = new Date( parseInt(arrDate[2],10), (parseInt(arrDate[1],10)-1), (parseInt(arrDate[0],10)),0,0,0,0);
			var dateTo = new Date( parseInt(arrDate2[2],10), (parseInt(arrDate2[1],10)-1), (parseInt(arrDate2[0],10)),0,0,0,0);
			
			
			var month1=(parseInt(arrDate[1],10));
			var month2=(parseInt(arrDate2[1],10));
			var monate=((month2*1)-(month1*1)+1);
			
			
			//var dateTo = new Date( parseInt(1), (parseInt(1), parseInt(arrDate2[0]),0,0,0,0);
			
		//	alert	(dateFrom + ' ' + (parseInt(arrDate[2])) + ' '+  (parseInt(arrDate[1])-1) + ' '+ (parseInt(arrDate[0],10)));
		//alert	(dateTo + ' ' +  (parseInt(arrDate2[2])) + ' '+  (parseInt(arrDate2[1])-1) + ' '+ (parseInt(arrDate2[0],10)));
			var day = 1000*60*60*24;		
			
		//	alert (dateFrom.getTime() + ' ' + dateTo.getTime());
			days =  Math.ceil( ((dateTo.getTime()-dateFrom.getTime()) / (day)) + 1);
			
		}

		
		if(days < 0) {

			alert('Fehler bei den Daten, bitte ÃœberprÃ¼fen Sie ihre Eingabe');

			$('#tage').val('');
			
		}else{
			myVal='#tage'  + myNumber;
			//alert(myVal);
			$(myVal).val(days);
			
		}
	
		//checkFormDate(day1);
		//checkFormDate(day2);
		

	}


//Berechne die Anzahl der Tage anhand der 2 Inputdaten
function calcDaysWithParamPlusOneYear(day1, day2, myNumber){
	//alert('calcDaysWithParam');
	//alert(myNumber);
	//alert ('test');
	//alert('test:' + day1 + ' ' + day2);
		var dayFromPlane = day1;
		var dayToPlane = day1;
		//alert(day1);
		//alert(day2);
		var days = 0;
		
		if(dayFromPlane != "" && dayToPlane != ""){
		
			var arrDate = dayFromPlane.split(".");
			var arrDate2 = dayToPlane.split(".");
			
			// Neues Datum berechnen
			var dateFrom = new Date( parseInt(arrDate[2],10), (parseInt(arrDate[1],10)-1), (parseInt(arrDate[0],10)),0,0,0,0);
			var dateTo = new Date( (parseInt(arrDate2[2],10)), (parseInt(arrDate2[1],10)+11), (parseInt(arrDate2[0],10)),0,0,0,0);
			
			
			var month1=(parseInt(arrDate[1],10));
			var month2=(parseInt(arrDate2[1],10));
			var monate=((month2*1)-(month1*1)+1);
			
			
		
			// var	heute = dateFrom; //new Date(2011,1,2), //Zählung der Monate beginnt bei 0=Januar
			var dateFromPlusOneYear = new Date(dateFrom.getTime() + 365 * 24 * 60 * 60 * 1000),
		       
		       // Ab hier soll es dir nur zeigen, dass es funktioniert
		       
		       	d = dateFromPlusOneYear.getDate(),
		       	m = dateFromPlusOneYear.getMonth() + 1,
		       	y = dateFromPlusOneYear.getFullYear();
		       	
		       displayDateFromPlusOneYear =	((d<10) ? "0" : "") + d + "." +
		       		((m<10) ? "0" : "") + m + "." +
		       		((y<10) ? "0" : "") + y;
		       				
		        
		       
			
			
			
			var day = 1000*60*60*24;		
			days =  Math.ceil( ((dateTo.getTime()-dateFrom.getTime()) / (day)) + 1);
			
		}

		
		if(days < 0) {

			alert('Fehler bei den Daten, bitte ÃœberprÃ¼fen Sie ihre Eingabe');

			$('#tage').val('');
			
		}else{
			
			
			myVal='#bis'  + myNumber;
			$(myVal).val(displayDateFromPlusOneYear);
			
		}
	
		//checkFormDate(day1);
		//checkFormDate(day2);
		

	}



function transferPersonenDateiName(fileName,myNumber){
//alert(escape(document.forms[0].personendatei1_file.value));
//alert (fileName);
myVal='#personendatei'  + myNumber;
$(myVal).val(fileName);

}


//Berechne Gesamtpraemie Ausl Gaeste mit Tarifsprung nach 90 Tagen
function calcPriceAuslGaesteWithParam(myNumber) {
	

	
	var anzahlTage=($('#tage'+myNumber).val());
	var praemieProEinheit=$('#praemieProEinheit').val();
	praemieProEinheit=(Math.round(praemieProEinheit*100)/100); 
	var praemieProEinheit2=$('#praemieProEinheit2').val();
	praemieProEinheit2=(Math.round(praemieProEinheit2*100)/100); 
	
	

	var anzpersonen = $('select#anzpersonen'+myNumber+' option:selected').val();
	
	/*bis 90 Tage Multiplikation mit praemieProEinheit
	 * ab 90 Tage  Multiplikation mit praemieProEinheit und praemieProEinheit2
	 */
	if (anzahlTage<=90){
		var sum = Math.round(100* anzahlTage * praemieProEinheit * anzpersonen)/100;
	}else if (anzahlTage>90){
		
		var anzahlTage2=(anzahlTage-90);
		anzahlTage=90;
		
		var sum = Math.round(100* anzahlTage * praemieProEinheit * anzpersonen)/100;
		var sum2 = Math.round(100* anzahlTage2 * praemieProEinheit2 * anzpersonen)/100;
		
		sum=sum+sum2;
		sum=Math.round(100* sum)/100;
	}
	
	
	myPraemie='#teilpraemie'  + myNumber;
	mySum=(sum);
	$(myPraemie).val(mySum);
	myPraemie='#teilpraemie'  + myNumber + "_formatted";
	mySum=decimalToCurrencyFormat(sum);
	$(myPraemie).val(mySum);
	calcGesamtPraemie();
	calcGesamtAnzahlPersonen();
	calcGesamtAnzahlTage();
	
	
	
}

	function calcGesamtPraemie() {
	calcGesamtPraemie();
	calcGesamtAnzahlPersonen();
	calcGesamtAnzahlTage();
	}


//Berechne Gesamtpraemie Ferien/Gepaeck/Reiseruecktritt
function calcPriceWithParam(myNumber) {
	
	var anzahlTage=($('#tage'+myNumber).val());
	var praemieProEinheit=$('#praemieProEinheit').val();
	var anzpersonen = $('select#anzpersonen'+myNumber+' option:selected').val();
	var sum = Math.round(100* anzahlTage * praemieProEinheit * anzpersonen)/100;
	praemieProEinheit=(Math.round(praemieProEinheit*100)/100);
	
	myPraemie='#teilpraemie'  + myNumber;
	mySum=(sum);
	$(myPraemie).val(mySum);		
	
	myPraemie='#teilpraemie'  + myNumber + "_formatted";
	mySum=decimalToCurrencyFormat(sum);
	
	$(myPraemie).val(mySum);	
	
	calcGesamtPraemie();
	calcGesamtAnzahlPersonen();
	calcGesamtAnzahlTage();
	
	
	
}



//Berechne GesamtprÃ¤mie
function calcPriceWithParamGepaeck2(myNumber) {
	
	//var f=document.forms[0].pr.value;
	 
	//alert($('#tage1').val());
	
	//alert(x);
	//alert(f.praemieProEinheit.value);
	//alert($('#tage'+myNumber).val());
	
	var anzahlTage=($('#tage'+myNumber).val());
	//alert (anzahlTage);
	//alert($('#praemieProEinheit').val());
	//var praemieProEinheit=$('#praemieProEinheit').val();
	var praemieProEinheit=111;
	praemieProEinheit=(Math.round(praemieProEinheit*100)/100); 
	//alert(praemieProEinheit);
	

	var anzpersonen = $('select#anzpersonen'+myNumber+' option:selected').val();
	
	//alert(anzpersonen);
	var sum = Math.round(100* anzahlTage * praemieProEinheit * anzpersonen)/100;
	
	//mySum=decimalToCurrencyFormat(sum);
	
	myPraemie='#teilpraemie'  + myNumber;
	mySum=(sum);
	$(myPraemie).val(mySum);		
	
	myPraemie='#teilpraemie'  + myNumber + "_formatted";
	mySum=decimalToCurrencyFormat(sum);	
	$(myPraemie).val(mySum);	
	
	calcGesamtPraemie();
	calcGesamtAnzahlPersonen();
	calcGesamtAnzahlTage();
	
	
	
}

function calcGesamtAnzahlTage() {
	var anzfahrten=$('#anzfahrten').val();	
	var tageTotal=0;
	
	for(k=1; k<=anzfahrten; k++){
		anzTage = ($('#tage'+k).val());
		anzTage=(Math.round(anzTage*100)/100);
		tageTotal=tageTotal+anzTage;
		tageTotal=(Math.round(tageTotal*100)/100);
		
	}	
	
	$('#tage').val(tageTotal);
	
}



function calcGesamtAnzahlPersonen() {
	var anzfahrten=$('#anzfahrten').val();	
	var personenTotal=0;
	
	for(k=1; k<=anzfahrten; k++){
		anzPersonen = $('select#anzpersonen'+k+' option:selected').val();
		anzPersonen=(Math.round(anzPersonen*100)/100);
		personenTotal=(Math.round(personenTotal*100)/100)+(Math.round(anzPersonen*100)/100);
		personenTotal=(Math.round(personenTotal*100)/100);
		
	}	
	
	$('#anzpersonen').val(personenTotal);
	
}


function calcGesamtPraemie() {
	var anzfahrten=$('#anzfahrten').val();
	var praemie=0;
	for(k=1; k<=anzfahrten; k++){
		teilPraemie=($('#teilpraemie'+k).val());
		
		teilPraemie= (Math.round(teilPraemie*100)/100);
		praemie= (Math.round(praemie*100)/100)+teilPraemie;
		praemie=(Math.round(praemie*100)/100);
		
		
	}	
	
	var bearbeitungsGebuehr=($('#bearbeitungsGebuehr').val());
	praemie=((1*praemie)+(1*bearbeitungsGebuehr));
	praemie=Math.round(100*(praemie))/100;
	
	$('#praemie').val(praemie);
	praemie=decimalToCurrencyFormat(praemie);
	$('#praemie_formatted').val(praemie);
	
	
}

//Berechne die Anzahl der Monate anhand der 2 Inputdaten
function calcMonth(day1, day2){
	
	//alert('test:' + day1 + ' ' + day2);
		var dayFromPlane = day1;
		var dayToPlane = day2;

		var days = 0;
		
		if(dayFromPlane != "" && dayToPlane != ""){
		
			var arrDate = dayFromPlane.split(".");
			var arrDate2 = dayToPlane.split(".");
			
			// Neues Datum berechnen
			var dateFrom = new Date( parseInt(arrDate[2],10), (parseInt(arrDate[1],10)-1), (parseInt(arrDate[0],10)),0,0,0,0);
			var dateTo = new Date( parseInt(arrDate2[2],10), (parseInt(arrDate2[1],10)-1), (parseInt(arrDate2[0],10)),0,0,0,0);
			
			

			var year1=(parseInt(arrDate[2],10));
			var year2=(parseInt(arrDate2[2],10));
			var zusatzMonate=0;
			zusatzMonate=(((year2*1)-(year1*1))*12);
			
			if  ((year2*1)!=(year1*1)){
				alert('Zeitraum bitte nur innerhalb eines Kalenderjahres angeben. Vertraege werden automatisch verlaengert. Bitte korrigieren Sie die Eingabe.')
			}
			
			
			var month1=(parseInt(arrDate[1],10));
			var month2=(parseInt(arrDate2[1],10));
			var monate=((month2*1)-(month1*1)+1);
			monate=(monate*1)+(zusatzMonate*1);
			
			//var dateTo = new Date( parseInt(1), (parseInt(1), parseInt(arrDate2[0]),0,0,0,0);
			
		//	alert	(dateFrom + ' ' + (parseInt(arrDate[2])) + ' '+  (parseInt(arrDate[1])-1) + ' '+ (parseInt(arrDate[0],10)));
		//alert	(dateTo + ' ' +  (parseInt(arrDate2[2])) + ' '+  (parseInt(arrDate2[1])-1) + ' '+ (parseInt(arrDate2[0],10)));
			var day = 1000*60*60*24;		
			
		//	alert (dateFrom.getTime() + ' ' + dateTo.getTime());
			days =  Math.ceil( ((dateTo.getTime()-dateFrom.getTime()) / (day)) + 1);
			
		}


		if(days < 0) {

			alert('Fehler bei den Daten, bitte ÃœberprÃ¼fen Sie ihre Eingabe');

			$('#tage').val('');
			
		}else{

			
			$('#monate').val(monate);

		}
	
		
		

	}



//Berechne GesamtprÃ¤mie 
function calcPriceDienstReiseVersicherungKM() {
	
	f=document.forms['myForm'];
	
	var praemieProEinheit=(Math.round(f.praemieProEinheit.value*100)/100);
	var basisPraemie=(Math.round(f.basisPraemie.value*100)/100);
	var anzahlKM=(Math.round(f.anzEinheiten.value*100)/100);
	var anzahlMonate=(Math.round(f.monate.value*100)/100);
	//Frei Kilometer
	var versSummeFreibetrag=(Math.round(f.versSummeFreibetrag.value*100)/100);
	//alert (versSummeFreibetrag);
	//alert (basisPraemie);
	//BasisPraemie mit Monatsbezug (Gewichtung)	
	var sum =  (Math.round(100* (basisPraemie) )/100) * (Math.round(100* anzahlMonate )/100);
	sum = (Math.round(100* (sum/12) )/100);
	
    //versSummeFreibetrag mit Zeitbezug auf Monate
	versSummeFreibetrag = (Math.round(100* (versSummeFreibetrag) )/100) * (Math.round(100* anzahlMonate )/100);
	versSummeFreibetrag = (Math.round(100* (versSummeFreibetrag/12) )/100);
	//alert (versSummeFreibetrag);
	//alert (anzahlKM);
	
	if (anzahlKM>versSummeFreibetrag){
		sum = (sum + Math.round(100* (anzahlKM-versSummeFreibetrag) * praemieProEinheit )/100 ) ;
	}
	
	sum =  Math.round(100* sum )/100;
	
	var bearbeitungsGebuehr=($('#bearbeitungsGebuehr').val());
	sum=((1*sum)+(1*bearbeitungsGebuehr));
	sum=Math.round(100*(sum))/100;
	
	
	mySum=decimalToCurrencyFormat(sum);
	
	$('#praemie').val(mySum);		
	 
	
}


//Berechne GesamtprÃ¤mie

function calcPricePfarrVersicherung() {
	//Anzahl Mitglieder Pfarrgemeinde und gewichtet nach Zeiteinheiten (Monate)
	f=document.forms['myForm'];
	
	var praemieProEinheit=(Math.round(f.praemieProEinheit.value*100)/100);
	var anzahlMitglieder=(Math.round(f.anzEinheiten.value*100)/100);	
	var anzahlMonate=(Math.round(f.monate.value*100)/100);
	
	var sum = (Math.round(100* (anzahlMitglieder) * praemieProEinheit )/100 ) ;
	
	sum =  Math.round(100* sum )/100;
	
	sum = (Math.round(100* (sum) )/100) * (Math.round(100* anzahlMonate )/100);
	
	sum = (Math.round(100* (sum/12) )/100); 
	
	var bearbeitungsGebuehr=($('#bearbeitungsGebuehr').val());
	sum=((1*sum)+(1*bearbeitungsGebuehr));
	sum=Math.round(100*(sum))/100;

	
	mySum=decimalToCurrencyFormat(sum);
	
	$('#praemie').val(mySum);		
	 
	
}


function calcPriceOffeneTuereVersicherung() {
	//Gewichtet nach Zeiteinheiten (Monate)
	
	f=document.forms['myForm'];
	
	var praemieProEinheit=(Math.round(f.praemieProEinheit.value*100)/100);
	
	var anzahlMonate=(Math.round(f.monate.value*100)/100);
	
	var sum = (Math.round(100* praemieProEinheit )/100 ) ;
	
	sum =  Math.round(100* sum )/100;
	
	sum = (Math.round(100* (sum) )/100) * (Math.round(100* anzahlMonate )/100);
	
	sum = (Math.round(100* (sum/12) )/100); 
	
	var bearbeitungsGebuehr=($('#bearbeitungsGebuehr').val());
	sum=((1*sum)+(1*bearbeitungsGebuehr));
	sum=Math.round(100*(sum))/100;

	mySum=decimalToCurrencyFormat(sum);
	
	$('#praemie').val(mySum);		
	 
	
}



function calcPriceJWHVersicherung() {
	//Anzahl Betten und gewichtet nach Zeiteinheiten (Monate)
	f=document.forms['myForm'];
	
	var praemieProEinheit=(Math.round(f.praemieProEinheit.value*100)/100);
	var anzahlBetten=(Math.round(f.anzEinheiten.value*100)/100);	
	var anzahlMonate=(Math.round(f.monate.value*100)/100);
	var basisPraemie=(Math.round(f.basisPraemie.value*100)/100);
	
	var sum = (Math.round(100* (anzahlBetten) * praemieProEinheit )/100 ) ;
	
	sum =  Math.round(100* sum )/100;
	
	sum = (Math.round(100* (sum) )/100) * (Math.round(100* anzahlMonate )/100);
	
	sum = (Math.round(100* (sum/12) )/100);
	
	//Basispraemie von 65 EUR Minimum mit Monatsbezug
	
	basisPraemie = (Math.round(100* (basisPraemie) )/100) * (Math.round(100* anzahlMonate )/100);
	basisPraemie = (Math.round(100* (basisPraemie/12) )/100);
	
	if (sum<basisPraemie){
		sum = (basisPraemie) ;
	}
	
	var bearbeitungsGebuehr=($('#bearbeitungsGebuehr').val());
	sum=((1*sum)+(1*bearbeitungsGebuehr));
	sum=Math.round(100*(sum))/100;

	mySum=decimalToCurrencyFormat(sum);
	
	$('#praemie').val(mySum);		
	 
	
}

function calcPriceSammelVersicherung() {
	//Anzahl Teilnehmer und gewichtet nach Zeiteinheiten (Monate)
	f=document.forms['myForm'];
	
	var praemieProEinheit=(Math.round(f.praemieProEinheit.value*100)/100);
	var anzahlMitglieder=(Math.round(f.anzEinheiten.value*100)/100);	
	var anzahlMonate=(Math.round(f.monate.value*100)/100);
	
	var sum = (Math.round(100* (anzahlMitglieder) * praemieProEinheit )/100 ) ;
	
	sum =  Math.round(100* sum )/100;
	
	sum = (Math.round(100* (sum) )/100) * (Math.round(100* anzahlMonate )/100);
	sum = (Math.round(100* (sum/12) )/100); 
	
	var bearbeitungsGebuehr=($('#bearbeitungsGebuehr').val());
	sum=((1*sum)+(1*bearbeitungsGebuehr));
	sum=Math.round(100*(sum))/100;

	
	mySum=decimalToCurrencyFormat(sum);
	
	$('#praemie').val(mySum);		
	 
	
}


//Berechne GesamtprÃ¤mie 
function calcPriceDienstReiseVersicherungName() {
	
	f=document.forms['myForm'];
	
	var praemieProEinheit=(Math.round(f.praemieProEinheit.value*100)/100);
	
	var anzahlMonate=(Math.round(f.monate.value*100)/100);
		
	var sum = (Math.round(100* (praemieProEinheit) )/100) * (Math.round(100* anzahlMonate )/100);
	sum=(sum/12);
	
	sum =  Math.round(100* sum)/100;
	
	var bearbeitungsGebuehr=($('#bearbeitungsGebuehr').val());
	sum=((1*sum)+(1*bearbeitungsGebuehr));
	sum=Math.round(100*(sum))/100;

		
	mySum=decimalToCurrencyFormat(sum);
	
	$('#praemie').val(mySum);		
	 
	
}



//Berechne Gesamtpraemie PilgerVersicherung
function calcPricePilger(tage, personen, praemieProEinheit,praemieProPerson,versSummeProzent,versSumme) {
	//alert('Pilger');
	//alert ($('#anzfahrzeuge').val());
	//alert (praemieProPerson + ' ' + versSummeProzent + ' ' + versSumme);
	//alert (praemieProEinheit + ' ' + tage + ' ' + personen);
	
	
	var tarif = Math.round(100* tage * personen * praemieProEinheit)/100;
	//Gepäck
	var tarif_gp = Math.round(100* personen * praemieProPerson)/100;
	//Reiserücktritt
	var tarif_rr = Math.round(100* versSumme * versSummeProzent)/100;
	
	//alert (tarif + ' ' + tarif_gp + ' ' + tarif_rr);
	
	var total=tarif+tarif_gp+tarif_rr;
	total = Math.round(100* total)/100;
	
	var bearbeitungsGebuehr=($('#bearbeitungsGebuehr').val());
	total=((1*total)+(1*bearbeitungsGebuehr));
	total=Math.round(100*(total))/100;

	
	mySum=decimalToCurrencyFormat(total);
	
	$('#praemie').val(mySum);		
	 
	
}







	//Berechne GesamtprÃ¤mie
	function calcPrice(faktor1, faktor2, faktor3) {
		
		//alert (faktor1 + ' ' + faktor2 + ' ' + faktor3);
		var sum = Math.round(100* faktor1 * faktor2 * faktor3)/100;
		
		var bearbeitungsGebuehr=($('#bearbeitungsGebuehr').val());
		//alert(bearbeitungsGebuehr);
		//sum=Math.round(100*(sum+bearbeitungsGebuehr))/100;
		sum=((1*sum)+(1*bearbeitungsGebuehr));
		sum=Math.round(100*(sum))/100;
		//alert(sum);
		mySum=decimalToCurrencyFormat(sum);
		//alert('calcPrice' + sum);
		$('#praemie').val(mySum);		
		 
		
	}

	


		
	
		
	
	
	//Aktiviere Sende button wenn beide checkboxen akitivert sind
	function activateButton (){
		
		var check1 = $('#clues:checked').val();
		var check2 = $('#confirm:checked').val();

		

		if((check1 == "on") && (check2 == "on")){
			
			$('#abschicken').removeAttr("disabled");
		}else{

			$('#abschicken').attr("disabled", "disabled");
			
		}
		
	

	}
	

	function showInputsDummy(num) {
		/*Anzeigen/Ausblenden der einzelnen Positionen 
		  incl löschen der Werte, wenn etwas ausgeblendet wurde*/
		 
		var inputnumber = num;
		//<?php echo $maxPositionen;?>
		for(i=1; i<=100; i++){
		
			if (i<=num){
				$('#input'+i+'').css('display', 'block');
			}else{
				$('#input'+i+'').css('display', 'none');
				//clearing text when we close positions
				fieldname="#positionen_column1_row" + i;
				$(fieldname).val('');
				fieldname="#positionen_column2_row" + i;
				$(fieldname).val('');
				fieldname="#positionen_column3_row" + i;
				$(fieldname).val('');
				fieldname="#positionen_column3_row"  + i + "_euroformat";
				$(fieldname).val('');
					 
			}
		}
		
		  
	}
	
	function checkFormDate(day)
	{	
												
				var toCheck = day;
				//alert (day);
				var ref = new Array("0","1","2","3","4","5","6","7","8","9",".");
				var numeric = 0;
				for (i1=0; i1<toCheck.length; i1++){

					if ( i1==2 || i1==5 ) {
						if (toCheck.charAt(i1) != ".") {
							patternCheck = false;
						}						
					} else {					
						if (toCheck.charAt(i1) == ".") {
						   patternCheck = false;
						}					
					}
					
					for (i2=0; i2<ref.length; i2++){
						if (toCheck.charAt(i1)==ref[i2]) numeric++;
					}
				}
				
				if (toCheck.length > 0) {

					if (toCheck.substring(0,2) < 1 || toCheck.substring(0,2) > 31)  patternCheck = false;	//days
					if (toCheck.substring(3,5) < 1 || toCheck.substring(3,5) > 12)  patternCheck = false;	//months
					
					if (toCheck.substring(3,5) == '02' && toCheck.substring(0,2) > 29)  patternCheck = false;	//February				
					
					if ((toCheck.substring(3,5) == '04' || toCheck.substring(3,5) == '06' || toCheck.substring(3,5) == '09' || toCheck.substring(3,5) == '11') && toCheck.substring(0,2) > 30)  patternCheck = false;	//April,June,September,November				
					
					if (toCheck.length > 10) patternCheck = false;

				}			
				
				if ((numeric != toCheck.length)||(patternCheck == false)||(toCheck.length>0 && toCheck.length<10 )) {
					    alert('Error in checkFormDate');
						
	            }
		
		
	}

	
<div id="pager">
	<span class="prev button">&laquo;zurück</span>
- <input class="pagedisplay" type="text" /> - <span class="next button">weiter
&raquo;</span> <select class="pagesize" id="pagersize" name="pagersize"  onchange="setPagerCookie($('select#pagersize option:selected').val())">
	<option value="5" id="5">5</option>
	<option value="10" id="10">10</option>
	<option value="25" id="25">25</option>
	<option value="50" id="50">50</option>
	<option value="100" id="100" >100</option>
	<option value="500" id="500">500</option>
	<option value="1000" id="1000">1000</option>
</select>
</div>
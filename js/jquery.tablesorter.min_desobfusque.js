'use strict';                              
(function($) 
{
$.extend({
tablesorter : new function() 
{
function benchmark(s, d) 
{
    log(s + "," + ((new Date).getTime() - d.getTime()) + "ms");
}


// retourne un truc vers la console ou avec un message alert('');
// log qui s'ffiche soit dans la console ou soit avec un message alert('');
function log(s) 
{
    if (typeof console != "undefined" && typeof console.debug != "undefined") 
    {
        console.log(s);
    } 
    else 
    {
        alert(s);
    }
}



//
function buildParserCache(table, $headers) 
{
    if (table.config.debug) 
    {
        var parsersDebug = "";
    }
    var rows = table.tBodies[0].rows;
    if (table.tBodies[0].rows[0]) 
    {
        var list = [];
        var cells = rows[0].cells;
        var l = cells.length;
        var i = 0;
        for ( i=0 ; i < l; i++) 
        {
            var p = false;
            if ($.metadata && ($($headers[i]).metadata() && $($headers[i]).metadata().sorter)) 
            {
                p = getParserById($($headers[i]).metadata().sorter);
            } 
            else 
           {
                if (table.config.headers[i] && table.config.headers[i].sorter) 
                {
					p = getParserById(table.config.headers[i].sorter);
				}
           }
           if (!p) 
           {
                p = detectParserForColumn(table, cells[i]);
           }
           if (table.config.debug) 
           {
                parsersDebug = parsersDebug + ("column:" + i + " parser:" + p.id + "\n");
           }
           list.push(p);
        }
    }

    if (table.config.debug) 
    {
        log(parsersDebug);
    }
    return list;
}




//
function detectParserForColumn(table, node) 
{
    var l = parsers.length;
    var i = 1;
    for ( i = 0 ; i < l; i++) 
    {
         if (parsers[i].is($.trim(getElementText(table.config, node)), table, node)) 
         {
             return parsers[i];
         }
    }
    return parsers[0];
}


//
function getParserById(name) 
{
    var l = parsers.length;
    var i = 0;
    for ( i = 0 ; i < l; i++) 
    {
        if (parsers[i].id.toLowerCase() == name.toLowerCase()) 
        {
             return parsers[i]; 
        }
    }
    return false;
}




//
function buildCache(table) 
{
    if (table.config.debug) 
    {
        var cacheTime = new Date;
    }
    var totalRows = table.tBodies[0] && table.tBodies[0].rows.length || 0;
    var totalCells = table.tBodies[0].rows[0] && table.tBodies[0].rows[0].cells.length || 0;
    var parsers = table.config.parsers;
    var cache = {row : [], normalized : [] };
    var i = 0;
    for ( i = 0 ; i < totalRows; ++i) 
    {
        var c = table.tBodies[0].rows[i];
        var cols = [];
        cache.row.push($(c));
        var j = 0;
        for ( j = 0 ; j < totalCells; ++j) 
        {
			cols.push(parsers[j].format(getElementText(table.config, c.cells[j]), table, c.cells[j]));
        }
        cols.push(i);
        cache.normalized.push(cols);
        cols = null;
    }
    if (table.config.debug) 
    {
        benchmark("Building cache for " + totalRows + " rows:", cacheTime);
    }
    return cache;
}


//
function getElementText(config, node) 
{
    if (!node) 
    {
        return "";
    }
    var t = "";
    if (config.textExtraction == "simple") 
    {
        if (node.childNodes[0] && node.childNodes[0].hasChildNodes()) 
        {
            t = node.childNodes[0].innerHTML;
        } 
        else 
        {
			t = node.innerHTML;
		}
	} 
	else 
	{
        if (typeof config.textExtraction == "function") 
        {
			t = config.textExtraction(node);
        } 
        else
        {
			t = $(node).text();
        }
    }
return t;
}




//
function appendToTable(table, cache) 
{
    if (table.config.debug) 
    {
		var appendTime = new Date;
	}
	var c = cache;
	var r = c.row;
	var n = c.normalized;
	var totalRows = n.length;
	var checkCell = n[0].length - 1;
	var tableBody = $(table.tBodies[0]);
	var rows = [];
	var i = 0;
	for ( i = 0 ; i < totalRows; i++) 
	{
        rows.push(r[n[i][checkCell]]);
        if (!table.config.appender) 
        {
			var o = r[n[i][checkCell]];
			var l = o.length;
			var j = 0;
			for ( j = 0 ; j < l; j++) 
			{
				tableBody[0].appendChild(o[j]);
            }
        }
    }
    if (table.config.appender) 
    {
		table.config.appender(table, rows);
    }
    rows = null;
    if (table.config.debug) 
    {
        benchmark("Rebuilt table:", appendTime);
    }
    applyWidget(table);
    setTimeout(function() {$(table).trigger("sortEnd");}, 0);
}





//
function buildHeaders(table) 
{
	if (table.config.debug) 
	{
		var time = new Date;
    }
    var meta = $.metadata ? true : false;
    var tableHeadersRows = [];
    var i = 0;
    for ( i = 0 ; i < table.tHead.rows.length; i++) 
    {
		tableHeadersRows[i] = 0;
    }
    $tableHeaders = $("thead th", table);
    $tableHeaders.each(function(index) {   //début de la fonction 
    this.count = 0;
    this.column = index;
    this.order = formatSortingOrder(table.config.sortInitialOrder);
    if (checkHeaderMetadata(this) || checkHeaderOptions(table, index)) 
    {
		this.sortDisabled = true;
    }
    if (!this.sortDisabled) 
    {
		$(this).addClass(table.config.cssHeader);
    }
    table.config.headerList[index] = this;
    });                                              //fin de le fonction
    
    

    if (table.config.debug) 
    {
		benchmark("Built headers:", time);
        log($tableHeaders);
    }
    return $tableHeaders;
}


//
function checkCellColSpan(table, rows, row) 
{
	var arr = [];
	var r = table.tHead.rows;
	var c = r[row].cells;
	var i = 0;
	for ( i = 0 ; i < c.length; i++) 
	{
		var cell = c[i];
		if (cell.colSpan > 1) 
		{
			arr = arr.concat(checkCellColSpan(table, headerArr, row++));
		} 
		else 
		{
			if (table.tHead.length == 1 || (cell.rowSpan > 1 || !r[row + 1])) 
			{
				arr.push(cell);
			}
		}
	}
	return arr;
}




//
function checkHeaderMetadata(cell) 
{
	if ($.metadata && $(cell).metadata().sorter === false) 
	{
		return true;
	}
	return false;
}


//
function checkHeaderOptions(table, i0) 
{
	if (table.config.headers[i0] && table.config.headers[i0].sorter === false) 
	{
		return true;
	}
	return false;
}


//
function applyWidget(table) 
{
	var c = table.config.widgets;
	var l = c.length;
	var i1 = 0;
	for ( il = 0 ; i1 < l; i1++) 
	{
		getWidgetById(c[i1]).format(table);
	}
}

//
function getWidgetById(name) 
{
	var l = widgets.length;
	var i2 = 0;
	for ( i2 = 0 ; i2 < l; i2++) 
	{
		if (widgets[i2].id.toLowerCase() == name.toLowerCase()) 
		{
			return widgets[i2];
		}
	}
}


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//
function formatSortingOrder(v) 
{
	if (typeof v != "Number") 
	{
		i = v.toLowerCase() == "desc" ? 1 : 0;
    } 
    else 
    {
		i = v == (0 || 1) ? v : 0;
    }
    return i;
}


//
function isValueInArray(v, a) 
{
	var l = a.length;
    var i3 = 0;
    for (; i3 < l; i3++) 
    {
        if (a[i3][0] == v) 
        {
            return true;
        }
    }
    return false;
}



//
function setHeadersCss(table, $headers, list, css) 
{
	$headers.removeClass(css[0]).removeClass(css[1]);
    var h = [];
    $headers.each(function(offset4) 
    {                                //début de la fonction
        if (!this.sortDisabled) 
        {
            h[this.column] = $(this);
        }
    });                            //fin de la fin
    var l = list.length;
    var i4 = 0;
    for (; i4 < l; i4++) 
    {
        h[list[i4][0]].addClass(css[list[i4][1]]);
    }
}



//
function fixColumnWidth(table, $headers) 
{
    var c = table.config;
    if (c.widthFixed) 
    {
        var colgroup = $("<colgroup>");
        $("tr:first td", table.tBodies[0]).each(function() 
        {                                                                //début de la fonction
            colgroup.append($("<col>").css("width", $(this).width()));
        });                                                                //fin de la fonction
        $(table).prepend(colgroup);
    }
}






//
function updateHeaderSortCount(table0, sortList) 
{  
	var c = table0.config;
    var l = sortList.length;
    var i = 0;
    for ( i = 0 ; i < l; i++) 
    {
        var s = sortList[i];
        var o = c.headerList[s[0]];
        o.count = s[1];
        o.count++;
    }
}


//
function multisort(table1, sortList, cache) 
{
	if (table1.config.debug) 
	{
		var sortTime = new Date;
    }
    var dynamicExp = "var sortWrapper = function(a,b) {";
    var l = sortList.length;
    var i = 0;
    for ( i = 0 ; i < l; i++) 
    {
		var c = sortList[i][0];
        var order = sortList[i][1];
        var s = getCachedSortType(table1.config.parsers, c) == "text" ? order == 0 ? "sortText" : "sortTextDesc" : order == 0 ? "sortNumeric" : "sortNumericDesc";
        var e = "e" + i;
        dynamicExp = dynamicExp + ("var " + e + " = " + s + "(a[" + c + "],b[" + c + "]); ");
        dynamicExp = dynamicExp + ("if(" + e + ") { return " + e + "; } ");
        dynamicExp = dynamicExp + "else { ";
    }
    var orgOrderCol = cache.normalized[0].length - 1;
    dynamicExp = dynamicExp + ("return a[" + orgOrderCol + "]-b[" + orgOrderCol + "];");
    i = 0;
    for ( i = 0 ; i < l; i++) 
    {
		dynamicExp = dynamicExp + "}; ";
    }
    dynamicExp = dynamicExp + "return 0; ";
    dynamicExp = dynamicExp + "}; ";
    eval(dynamicExp);
    cache.normalized.sort(sortWrapper);
    if (table1.config.debug) 
    {
		benchmark("Sorting on " + sortList.toString() + " and dir " + order + " time:", sortTime);
    }
    return cache;
}



//
function sortText(a, b) 
{
	return a < b ? -1 : a > b ? 1 : 0;
}


//
function sortTextDesc(a, b) 
{
	return b < a ? -1 : b > a ? 1 : 0;
}


//
function sortNumeric(a, b) 
{
	return a - b;
}


//
function sortNumericDesc(a, b) 
{
	return b - a;
}


//
function getCachedSortType(parsers, i) 
{
    return parsers[i].type;
}




// Dans la fonction principale
var parsers = [];
var widgets = [];

//
this.defaults = 
{        //début this.defaults
    cssHeader : "header",
    cssAsc : "headerSortUp",
    cssDesc : "headerSortDown",
    sortInitialOrder : "asc",
    sortMultiSortKey : "shiftKey",
    sortForce : null,
    sortAppend : null,
    textExtraction : "simple",
    parsers : {},
    widgets : [],
    widgetZebra : 
    {
        css : ["even", "odd"]
    },
    headers : {},
    widthFixed : false,
    cancelSelection : true,
    sortList : [],
    headerList : [],
    dateFormat : "us",
    decimal : ".",
    debug : false
};        //fin this.defaults


//
this.benchmark = benchmark;
this.construct = function(settings) 
{                                //this.construct
	return this.each(function() 
	{
		if (!this.tHead || !this.tBodies) 
		{
			return;
        }
    var $this;
    var $document;
    var $headers;
    var cache;
    var config;
    var shiftDown = 0;
    var sortOrder;
    this.config = {};
    config = $.extend(this.config, $.tablesorter.defaults, settings);
    $this = $(this);
    $headers = buildHeaders(this);
    this.config.parsers = buildParserCache(this, $headers);
    cache = buildCache(this);
    var sortCSS = [config.cssDesc, config.cssAsc];
    fixColumnWidth(this);
    $headers.click(function(e) 
    {
		$this.trigger("sortStart");
        var totalRows = $this[0].tBodies[0] && $this[0].tBodies[0].rows.length || 0;
        if (!this.sortDisabled && totalRows > 0) 
        {
			var $cell = $(this);
            var i8 = this.column;
            this.order = this.count++ % 2;
            if (!e[config.sortMultiSortKey]) 
            {
				config.sortList = [];
				if (config.sortForce != null) 
				{
					var a = config.sortForce;
                    var j = 0;
                    for ( j = 0 ; j < a.length; j++) 
                    {
						if (a[j][0] != i8) 
						{
							config.sortList.push(a[j]);
                        }
                    }
                }
                config.sortList.push([i8, this.order]);
            } 
            else 
            {
				if (isValueInArray(i8, config.sortList)) 
				{
					j = 0;
					for ( j = 0; j < config.sortList.length; j++) 
					{
						var s = config.sortList[j];
						var o = config.headerList[s[0]];
						if (s[0] == i8) 
						{
							o.count = s[1];
							o.count++;
							s[1] = o.count % 2;
                        }
                    }
                } 
                else 
                {
					config.sortList.push([i8, this.order]);
			    }
            }
            setTimeout(function() 
            {                                                            //début de la fonction
                setHeadersCss($this[0], $headers, config.sortList, sortCSS);
                appendToTable($this[0], multisort($this[0], config.sortList, cache));
            }, 1);                                                       //fin de la fonction
            return false;
        }
    }).mousedown(function() 
    {
		if (config.cancelSelection) 
		{
			this.onselectstart = function() 
			{
				return false;
            };
            return false;
        }
    });
    $this.bind("update", function() 
    {
		this.config.parsers = buildParserCache(this, $headers);
        cache = buildCache(this);
    }).bind("sorton", function(e, list) 
    {
		$(this).trigger("sortStart");
        config.sortList = list;
        var sortList = config.sortList;
        updateHeaderSortCount(this, sortList);
        setHeadersCss(this, $headers, sortList, sortCSS);
        appendToTable(this, multisort(this, sortList, cache));
    }).bind("appendCache", function() 
    {
		appendToTable(this, cache);
    }).bind("applyWidgetId", function(e0, id) 
    {
		getWidgetById(id).format(this);
    }).bind("applyWidgets", function() 
    {
        applyWidget(this);
    });
    if ($.metadata && ($(this).metadata() && $(this).metadata().sortlist)) 
    {
		config.sortList = $(this).metadata().sortlist;
    }
    if (config.sortList.length > 0) 
    {
		$this.trigger("sorton", [config.sortList]);
    }
    applyWidget(this);
    });
};                            //fin du this.construct



//
this.addParser = function(parser) 
{                          //début du this.addParser
    var l = parsers.length;
    var a = true;
    var i = 0;
    for ( i = 0 ; i < l; i++) 
    {
        if (parsers[i].id.toLowerCase() == parser.id.toLowerCase()) 
        {
			a = false;
        }
    }
    if (a) 
    {
		parsers.push(parser);
    }
};                        //fin du this.addParser



//
this.addWidget = function(widget) 
{
    widgets.push(widget);
};

//
this.formatFloat = function(s) 
{
    var i = parseFloat(s);
    return isNaN(i) ? 0 : i0;
};

//
this.formatInt = function(s) 
{
    var i = parseInt(s);
    return isNaN(i) ? 0 : i;
};


//
this.isDigit = function(s, config) 
{
	var DECIMAL = "\\" + config.decimal;
    var exp = "/(^[+]?0(" + DECIMAL + "0+)?$)|(^([-+]?[1-9][0-9]*)$)|(^([-+]?((0?|[1-9][0-9]*)" + DECIMAL + "(0*[1-9][0-9]*)))$)|(^[-+]?[1-9]+[0-9]*" + DECIMAL + "0+$)/";
    return RegExp(exp).test($.trim(s));
};



//
this.clearTableBody = function(table2) 
{
	if ($.browser.msie) 
	{
		var empty = function() 
		{
			for (; this.firstChild;) 
			{
				this.removeChild(this.firstChild);
            }
        };
        empty.apply(table2.tBodies[0]);
    } 
    else 
    {
		table2.tBodies[0].innerHTML = "";
    }
};



}
//fin de la fonction tablesorter : new function() ligne 5




});
//fin du extend


//Début du fn.extend 
$.fn.extend({
    tablesorter : $.tablesorter.construct
});
//fin du fn.extend


var ts = $.tablesorter;
ts.addParser({
    id : "text",
    is : function(s0) 
    {
		return true;
    },
    format : function(s1) 
    {
		return $.trim(s1.toLowerCase());
    },
    type : "text"
});





ts.addParser({
    id : "digit",
    is : function(s, table) 
    {
		var c = table.config;
        return $.tablesorter.isDigit(s, c);
    },
    format : function(s) 
    {
		return $.tablesorter.formatFloat(s);
    },
    type : "numeric"
});




ts.addParser({
    id : "currency",
    is : function(s) 
    {
		return /^[\u00c2\u00a3$\u00e2\u201a\u00ac?.]/.test(s);
    },
    format : function(s) 
    {
        return $.tablesorter.formatFloat(s.replace(new RegExp(/[^0-9.]/g), ""));
    },
    type : "numeric"
});





ts.addParser({
    id : "ipAddress",
    is : function(s) 
    {
		return /^\d{2,3}[\.]\d{2,3}[\.]\d{2,3}[\.]\d{2,3}$/.test(s);
    },
    format : function(s) 
    {
        var a = s.split(".");
        var r = "";
        var l = a.length;
        var i = 0;
        for ( i = 0 ; i < l; i++) 
        {
            var item = a[i];
            if (item.length == 2) 
            {
                r = r + ("0" + item);
            } 
            else 
            {
    		    r = r + item;
            }
        }
        return $.tablesorter.formatFloat(r);
    },
    type : "numeric"
});




ts.addParser({
    id : "url",
    is : function(s) 
    {
		return /^(https?|ftp|file):\/\/$/.test(s);
    },
    format : function(s) 
    {
        return jQuery.trim(s.replace(new RegExp(/(https?|ftp|file):\/\//), ""));
    },
    type : "text"
});



ts.addParser({
    id : "isoDate",
    is : function(s) 
    {
        return /^\d{4}[\/-]\d{1,2}[\/-]\d{1,2}$/.test(s);
    },
    format : function(s) 
    {
        return $.tablesorter.formatFloat(s != "" ? (new Date(s.replace(new RegExp(/-/g), "/"))).getTime() : "0");
    },
    type : "numeric"
});




ts.addParser({
    id : "percent",
    is : function(s) 
    {
        return /%$/.test($.trim(s));
    },
    format : function(s) 
    {
        return $.tablesorter.formatFloat(s.replace(new RegExp(/%/g), ""));
    },
    type : "numeric"
});




ts.addParser({
    id : "usLongDate",
    is : function(s) 
    {
        return s.match(new RegExp(/^[A-Za-z]{3,10}\.? [0-9]{1,2}, ([0-9]{4}|'?[0-9]{2}) (([0-2]?[0-9]:[0-5][0-9])|([0-1]?[0-9]:[0-5][0-9]\s(AM|PM)))$/));
    },
    format : function(s) 
    {
		return $.tablesorter.formatFloat((new Date(s)).getTime());
    },
    type : "numeric"
});



ts.addParser({
    id : "shortDate",
    is : function(s) 
    {
        return /\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}/.test(s);
    },
    format : function(s, table) 
    {
		var c = table.config;
		s = s.replace(/\-/g, "/");
        if (c.dateFormat == "us") 
        {
			s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/, "$3/$1/$2");
        } 
        else 
        {
            if (c.dateFormat == "uk") 
            {
				s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/, "$3/$2/$1");
            } 
            else 
            {
				if (c.dateFormat == "dd/mm/yy" || c.dateFormat == "dd-mm-yy") 
				{
					s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2})/, "$1/$2/$3");
                }
            }
        }
        return $.tablesorter.formatFloat((new Date(s7)).getTime());
    },
    type : "numeric"
});






ts.addParser({
    id : "time",
    is : function(s) 
    {
        return /^(([0-2]?[0-9]:[0-5][0-9])|([0-1]?[0-9]:[0-5][0-9]\s(am|pm)))$/.test(s);
    },
    format : function(s) 
    {
		return $.tablesorter.formatFloat((new Date("2000/01/01 " + s)).getTime());
    },
    type : "numeric"
});




ts.addParser({
    id : "metadata",
    is : function(s0) 
    {
		return false;
    },
    format : function(s1, table, cell) 
    {
		var c = table.config;
        var p = !c.parserMetadataName ? "sortValue" : c.parserMetadataName;
        return $(cell).metadata()[p];
    },
    type : "numeric"
});





ts.addWidget({
    id : "zebra",
    format : function(table) 
    {
		if (table.config.debug) 
		{
			var time = new Date;
        }
        $("tr:visible", table.tBodies[0]).filter(":even").removeClass(table.config.widgetZebra.css[1]).addClass(table.config.widgetZebra.css[0]).end().filter(":odd").removeClass(table.config.widgetZebra.css[0]).addClass(table.config.widgetZebra.css[1]);
        if (table.config.debug) 
        {
			$.tablesorter.benchmark("Applying Zebra widget", time);
        }
    }
});


})(jQuery);

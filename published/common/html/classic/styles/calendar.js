
///////////////////////////////////////////////////////////////////////////
//
//  WebCalendar 1.1
//  Copyright (C) 2004 Andy Chentsov <chentsov@mail.ru>
//
//--- Usage ---------------------------------------------------------------
//
//  showCalendar(form_field[, hide_covered = true])
//    show webcalendar
//
///////////////////////////////////////////////////////////////////////////

// consts
ctWeekNum = 1;
ctDay = 2;

Calendar.firstDayOfWeek = 1;
Calendar.DateFormat = "d/m/y";
Calendar.wbsDate = null;


// full month names
Calendar.MonthNames = new Array
("Январь",
 "Февраль",
 "Март",
 "Апрель",
 "Май",
 "Июнь",
 "Июль",
 "Август",
 "Сентябрь",
 "Октябрь",
 "Ноябрь",
 "Декабрь");

// short day names
Calendar.ShortDayNames = new Array
("Вс",
 "Пн",
 "Вт",
 "Ср",
 "Чт",
 "Пт",
 "Сб",
 "Вс");

Calendar.Text = {};
Calendar.Text["today"] = "Сегодня";
Calendar.Text["wk"] = "нед.";
Calendar.Text["wk_tip"] = "Номер недели";
Calendar.Text["close"] = "Закрыть";
Calendar.Text["prevyear"] = "Предыдущий год";
Calendar.Text["nextyear"] = "Следующий год";
Calendar.Text["prevmonth"] = "Предыдущий месяц";
Calendar.Text["nextmonth"] = "Следующий месяц";

/** adds the number of days array to the Date object. */
Date._MD = new Array(31,28,31,30,31,30,31,31,30,31,30,31);

/** returns the number of days in the current month */
Date.prototype.getMonthDays = function(month) {
  var year = this.getFullYear();
  if (typeof month == "undefined") {
          month = this.getMonth();
  }
  if (((0 == (year%4)) && ( (0 != (year%100)) || (0 == (year%400)))) && month == 1) {
          return 29;
  } else {
          return Date._MD[month];
  }
};

/** returns the number of the week in year, as defined in ISO 8601. */
Date.prototype.getWeekNumber = function() {
  var d = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
  var DoW = d.getDay();
  d.setDate(d.getDate() - (DoW + 6) % 7 + 3); // Nearest Thu
  var ms = d.valueOf(); // GMT
  d.setMonth(0);
  d.setDate(4); // Thu in Week 1
  return Math.round((ms - d.valueOf()) / (7 * 864e5)) + 1;
};

Date.prototype.prevYear = function() {
  var d = this.getFullYear();
  var r = new Date(this);
  r.setFullYear(--d);
  return r;
};

Date.prototype.nextYear = function() {
  var d = this.getFullYear();
  var r = new Date(this);
  r.setFullYear(++d);
  return r;
};

Date.prototype.prevMonth = function() {
  var d = this.getMonth();
  var r = new Date(this);
  r.setMonth(--d);
  return r;
};

Date.prototype.nextMonth = function() {
  var d = this.getMonth();
  var r = new Date(this);
  r.setMonth(++d);
  return r;
};

/** prints the date in a string according to the given format. */
// print([format])
Date.prototype.print = function () {
  var m = this.getMonth();
  var d = this.getDate();
  var y = this.getFullYear();
  m++;
  if (m.toString().length <= 1) { m = "0" + m; }
  if (d.toString().length <= 1) { d = "0" + d; }
  var mask = "d/m/y";
  if (arguments.length > 0) {
    mask = arguments[0].toLowerCase();
  }
  var f = mask.split(/[.\/]+/);
  var res = mask;
  for (var i = 0; i < f.length; i++) {
    if (f[i].length > 1) {
      f[i] = m[i].substr(0, 1);
    }
    if (f[i] == "d") {
      res = res.replace("d", d);
    }
    if (f[i] == "m") {
      res = res.replace("m", m);
    }
    if (f[i] == "y") {
      res = res.replace("y", y);
    }
  }
  return res;
};

/** returns the first day of the week in year, as defined in ISO 8601. */
Date.prototype.getFirstWeekDay = function() {
        var d = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
        var DoW = d.getDay();
        d.setDate(d.getDate() - DoW + 1);
        return d;
};

/** returns the last day of the week in year, as defined in ISO 8601. */
Date.prototype.getLastWeekDay = function() {
        var d = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
        var DoW = d.getDay();
        d.setDate(d.getDate() + (7 - DoW));
        return d;
};

/** detect week number */
String.prototype.isWeek = function () {
  var a = this.split(/[.\/]+/);
  return (a.length == 1);
};

/** parse date string (d.m.yyyy, d/m/yyyy) */
// toDate([format_mask])
String.prototype.toDate = function () {
  var a = this.split(/[.\/]+/);
/*  if (a.length == 1) { // week number
    r = new Date();
    var d = new Date(r.getFullYear(), 0, 1).getDay();
    var v = (parseInt(a[0]) * 7) - d;
    r.setDate(v);
    r.setMonth(Math.floor(v / 31));
  } else */
  {
    var mask = "d/m/y";
    if (arguments.length > 0) {
      mask = arguments[0].toLowerCase();
    }
    var m = mask.split(/[.\/]+/);
    for (var i = 0; i < a.length; i++) {
      a[i] = new Number(a[i]);
    }
    var r = new Date();
    var didx = 0;
    var midx = 1;
    var yidx = 2;
    for (var i = 0; i < m.length; i++) {
      if (m[i].length > 1) {
        m[i] = m[i].substr(0, 1);
      }
      if (m[i] == "d") {
        didx = i;
      }
      if (m[i] == "m") {
        midx = i;
      }
      if (m[i] == "y") {
        yidx = i;
      }
    }
    r.setDate(a[didx]);
    r.setMonth(a[midx] - 1);
    r.setFullYear(a[yidx]);
  }
  return r;
};

// --------------------------------------------------

var cal = null;

function showCalendar(field) {
  var hideCovered = true;
  if (arguments.length > 1) {
    hideCovered = arguments[1];
  }
  var callingevent = null;
  if (arguments.length > 2) {
    callingevent = arguments[2];
  }
  if (cal == null) {
    cal = new Calendar(field);
  } else {
    cal.field = field;
  }
  cal.hideCovered = hideCovered;
  cal.show((cal.field != null)?((cal.field.type == "hidden")?callingevent:null):null);
}

function Calendar(field) {
  this.field = field;

  this.weekends = "0,6";
  this.weekNumbers = true;
  this.hidden = false;

  this.table = null;
  this.firstdayname = null;
  this.showsOtherMonths = true;

  this.firstDayOfWeek = Calendar.firstDayOfWeek;
  this.hideCovered = true;

  this.week = null;

  this.create();
}

// ** constants

/// "static", needed for event handlers.
Calendar._C = null;

/// detect a special case of "web browser"
Calendar.is_ie = ( /msie/i.test(navigator.userAgent) &&
                   !/opera/i.test(navigator.userAgent) );

Calendar.is_ie5 = ( Calendar.is_ie && /msie 5\.0/i.test(navigator.userAgent) );

/// detect Opera browser
Calendar.is_opera = /opera/i.test(navigator.userAgent);

/// detect Mozilla browser
Calendar.is_moz = (!/opera/i.test(navigator.userAgent)) && (/gecko/i.test(navigator.userAgent));

Calendar.createElement = function(type, parent) {
  var el = null;
  if (document.createElementNS) {
    // use the XHTML namespace; IE won't normally get here unless
    // _they_ "fix" the DOM2 implementation.
    el = document.createElementNS("http://www.w3.org/1999/xhtml", type);
  } else {
    el = document.createElement(type);
  }
  if (typeof parent != "undefined") {
    parent.appendChild(el);
  }
  return el;
};

Calendar.prototype.create = function () {
  var parent = null;
  parent = document.getElementsByTagName("body")[0];
  this.date = new Date();

  var table = Calendar.createElement("table");
  this.table = table;
  table.cellSpacing = 0;
  table.cellPadding = 0;
  table.calendar = this;

  var div = Calendar.createElement("div");
  this.element = div;
  div.className = "calendar";
  div.style.position = "absolute";
  div.appendChild(table);

  var thead = Calendar.createElement("thead", table);
  var cell = null;
  var row = null;

  var cal = this;
  var hh = function (text, cs, navtype) {

    var tip = "";
    if (arguments.length > 3) {
      tip = arguments[3];
    }

    cell = Calendar.createElement("td", row);
    cell.colSpan = cs;
    cell.className = "button";
    if (navtype != 0 && Math.abs(navtype) <= 2)
      cell.className += " nav";

    if (Calendar.is_ie) {
      cell.setAttribute("unselectable", true);
    }

    Calendar.addEvent(cell, "mouseover", Calendar.btnMouseOver);
    Calendar.addEvent(cell, "mouseout", Calendar.btnMouseOut);
    Calendar.addEvent(cell, "mousedown", Calendar.btnMouseDown);

    cell.title = tip;

    cell.calendar = cal;
    cell.navtype = navtype;
    if (text.substr(0, 1) != "&") {
      cell.appendChild(document.createTextNode(text));
    }
    else {
      cell.innerHTML = text;
    }
    return cell;
  };

  row = Calendar.createElement("tr", thead);
  var title_length = 6;

  hh("?", 1, 400);
  this.title = hh("", title_length, 300);
  this.title.className = "title";

  hh("&#x00d7;", 1, 200, Calendar.Text["close"]);

  row = Calendar.createElement("tr", thead);
  row.className = "headrow";

  this._nav_py = hh("&#x00ab;", 1, -2, Calendar.Text["prevyear"]);
  this._nav_pm = hh("&#x2039;", 1, -1, Calendar.Text["prevmonth"]);
  this._nav_now = hh(Calendar.Text["today"], this.weekNumbers ? 4 : 3, 0);
  this._nav_nm = hh("&#x203a;", 1, 1, Calendar.Text["nextmonth"]);
  this._nav_ny = hh("&#x00bb;", 1, 2, Calendar.Text["nextyear"]);

  // day names
  row = Calendar.createElement("tr", thead);
  row.className = "daynames";
  if (this.weekNumbers) {
    cell = Calendar.createElement("td", row);
    cell.className = "name wn";
    cell.title = Calendar.Text["wk_tip"];
    cell.appendChild(document.createTextNode(Calendar.Text["wk"]));
  }
  for (var i = 7; i > 0; --i) {
    cell = Calendar.createElement("td", row);
    cell.appendChild(document.createTextNode(""));
    if (!i) {
      cell.navtype = 100;
      cell.calendar = this;
    }
  }

  this.firstdayname = row.firstChild.nextSibling;
  this._displayWeekdays();

  var tbody = Calendar.createElement("tbody", table);
  this.tbody = tbody;

  for (i = 6; i > 0; --i) {
    row = Calendar.createElement("tr", tbody);
    if (this.weekNumbers) {
      cell = Calendar.createElement("td", row);
      cell.appendChild(document.createTextNode(""));
      cell.calendar = this;
      cell.celltype = ctWeekNum;
      /*Calendar.addEvent(cell, "mouseover", Calendar.dayMouseOver);
      Calendar.addEvent(cell, "mouseout", Calendar.dayMouseOut);
      Calendar.addEvent(cell, "mousedown", Calendar.dayMouseDown);*/
    }
    for (var j = 7; j > 0; --j) {
      cell = Calendar.createElement("td", row);
      cell.appendChild(document.createTextNode(""));
	      cell.calendar = this;
      cell.celltype = ctDay;
      Calendar.addEvent(cell, "mouseover", Calendar.dayMouseOver);
      Calendar.addEvent(cell, "mouseout", Calendar.dayMouseOut);
      Calendar.addEvent(cell, "mousedown", Calendar.dayMouseDown);
    }
  }

  var tfoot = Calendar.createElement("tfoot", table);

  row = Calendar.createElement("tr", tfoot);
  row.className = "footrow";

  parent.appendChild(this.element);
  window.calendar = this;
};

Calendar.getAbsolutePos = function(el) {
  var SL = 0, ST = 0;
  var is_div = /^div$/i.test(el.tagName);
  if (is_div && el.scrollLeft)
    SL = el.scrollLeft;
  if (is_div && el.scrollTop)
    ST = el.scrollTop;
  var r = { x: el.offsetLeft - SL, y: el.offsetTop - ST };
  if (el.offsetParent) {
    var tmp = this.getAbsolutePos(el.offsetParent);
    r.x += tmp.x;
    r.y += tmp.y;
  }
  return r;
};

Calendar.prototype.show = function() {
  this.week = null;
  if (this.field && this.field.disabled) return;
  if (this.field && this.field.value != "") {
    if (this.field.value.isWeek()) {
      this.week = this.field.value;
    }
    this.date = this.field.value.toDate(Calendar.DateFormat);
    if (isNaN(this.date)) {
      if ( Calendar.wbsDate != null )
	      this.date = Calendar.wbsDate.toDate(Calendar.DateFormat);
      else
	      this.date = new Date( );
    }
  } else {
      if ( Calendar.wbsDate != null )
	      this.date = Calendar.wbsDate.toDate(Calendar.DateFormat);
      else
	      this.date = new Date( );
  }
  this.build(this.date);

  this.hidden = false;
  this.element.style.display = "block";

  var fromcursor = false;
  if (arguments.length > 0) {
    fromcursor = (arguments[0] != null);
  }

  if (this.field && !fromcursor) {
    var p = Calendar.getAbsolutePos(this.field);

    p.x += this.field.offsetWidth;
    p.y += this.field.offsetHeight;

    var Canvas = document.getElementsByTagName((document.compatMode && document.compatMode == "CSS1Compat") ? "HTML" : "BODY")[0];
    if (p.x >= (Canvas.clientWidth - this.element.offsetWidth - 5)) {
      p.x = (Canvas.clientWidth - this.element.offsetWidth);
    }

    if (p.x >= (Canvas.clientWidth - this.element.offsetWidth - 5 + Canvas.scrollLeft)) {
      p.x = (Canvas.clientWidth - this.element.offsetWidth - 20) + ((!Calendar.is_ie)?Canvas.scrollLeft:0) + this.field.offsetWidth;
    }
    if (p.y >= (Canvas.clientHeight - this.element.offsetHeight - 25 + Canvas.scrollTop)) {
      p.y = (Canvas.clientHeight - this.element.offsetHeight - 40) + ((!Calendar.is_ie)?Canvas.scrollTop:0) - this.field.offsetHeight;
    }

    this.element.style.left = p.x + "px";
    this.element.style.top = p.y + "px";
  }
  if (fromcursor) {
    var Canvas = document.getElementsByTagName((document.compatMode && document.compatMode == "CSS1Compat" && !Calendar.is_opera) ? "HTML" : "BODY")[0];
    if (!Calendar.is_moz) {
      var p = { x: window.event.clientX + Canvas.scrollLeft, y: window.event.clientY + Canvas.scrollTop };
    } else {
      var p = { x: arguments[0].clientX + window.pageXOffset, y: arguments[0].clientY + window.pageYOffset };
    }

    if (p.x >= (Canvas.clientWidth - this.element.offsetWidth - 5 + Canvas.scrollLeft)) {
      p.x = (Canvas.clientWidth - this.element.offsetWidth - 20) + ((!Calendar.is_ie)?Canvas.scrollLeft:0);
    }
    if (p.y >= (Canvas.clientHeight - this.element.offsetHeight - 5 + Canvas.scrollTop)) {
      p.y = (Canvas.clientHeight - this.element.offsetHeight - 20) + ((!Calendar.is_ie)?Canvas.scrollTop:0);
    }

    this.element.style.left = p.x + "px";
    this.element.style.top = p.y + "px";
  }

  Calendar.addEvent(document, "mousedown", Calendar._checkCalendar);

  this.hideShowCovered();
};

Calendar.prototype.hide = function () {
  this.element.style.display = "none";
  this.hidden = true;
  this.hideShowCovered();
};

Calendar.prototype._displayWeekdays = function () {
  var fdow = this.firstDayOfWeek;
  var cell = this.firstdayname;
  var weekend = this.weekends;
  for (var i = 0; i < 7; ++i) {
    cell.className = "day name";
    var realday = (i + fdow) % 7;
    if (i) {
      cell.navtype = 100;
      cell.calendar = this;
      cell.fdow = realday;
    }
    if (weekend.indexOf(realday.toString()) != -1) {
      Calendar.addClass(cell, "weekend");
    }
    // week day name
    cell.firstChild.data = Calendar.ShortDayNames[(i + fdow) % 7];
    cell = cell.nextSibling;
    if (cell == null) break;
  }
};

Calendar.prototype.build = function(date) {

      if ( Calendar.wbsDate != null )
		var today = Calendar.wbsDate.toDate(Calendar.DateFormat);
      else
		var today = new Date( );

  var year = date.getFullYear();

  var weekend = this.weekends;
  this.date = new Date(date);
  var month = date.getMonth();
  var mday = date.getDate();
  var no_days = date.getMonthDays();
  // calendar voodoo for computing the first day that would actually be
  // displayed in the calendar, even if it's from the previous month.
  // WARNING: this is magic. ;-)
  date.setDate(1);
  var day1 = (date.getDay() - this.firstDayOfWeek) % 7;
  if (day1 < 0)
          day1 += 7;
  date.setDate(-day1);
  date.setDate(date.getDate() + 1);

  var row = this.tbody.firstChild;

  var ar_days = new Array();
  for (var i = 0; i < 6; ++i, row = row.nextSibling) {
          var cell = row.firstChild;
          cell.className = "day wn";
          cell.firstChild.data = date.getWeekNumber();
          cell.caldate = date.getWeekNumber();
          cell = cell.nextSibling;
          row.className = "daysrow";

          if (date.getWeekNumber() == this.week) {
            cell.className += " selected";
            row.className += " rowselected";
          }

          var hasdays = false;
          for (var j = 0; j < 7; ++j, cell = cell.nextSibling, date.setDate(date.getDate() + 1)) {
                  var iday = date.getDate();
                  var wday = date.getDay();
                  cell.className = "day";
                  var current_month = (date.getMonth() == month);
                  if (!current_month) {
                          if (this.showsOtherMonths) {
                                  cell.className += " othermonth";
                                  cell.otherMonth = true;
                          } else {
                                  cell.className = "emptycell";
                                  cell.innerHTML = "&nbsp;";
                                  cell.disabled = true;
                                  continue;
                          }
                  } else {
                          cell.otherMonth = false;
                          hasdays = true;
                  }
                  cell.disabled = false;
                  cell.firstChild.data = iday;
                  if (typeof this.getDateStatus == "function") {
                          var status = this.getDateStatus(date, year, month, iday);
                          if (status === true) {
                                  cell.className += " disabled";
                                  cell.disabled = true;
                          } else {
                                  if (/disabled/i.test(status))
                                          cell.disabled = true;
                                  cell.className += " " + status;
                          }
                  }
                  if (!cell.disabled) {
                          ar_days[ar_days.length] = cell;
                          cell.caldate = new Date(date);
                          cell.ttip = "_";
                          if (current_month && iday == mday) {
                                  cell.className += " selected";
                                  this.currentDateEl = cell;
                          }
                          if (date.getFullYear() == today.getFullYear() &&
                              date.getMonth() == today.getMonth() &&
                              iday == today.getDate()) {
                                  cell.className += " today";
                          }
                          if (weekend.indexOf(wday.toString()) != -1) {
                                  cell.className += cell.otherMonth ? " oweekend" : " weekend";
                          }
                  }
          }
          if (!(hasdays || this.showsOtherMonths))
                  row.className = "emptyrow";
  }
  this.ar_days = ar_days;
  this.title.firstChild.data = Calendar.MonthNames[month] + ", " + year;
  this.table.style.visibility = "visible";
};

Calendar.addEvent = function(el, evname, func) {
  if (el.attachEvent) { // IE
    el.attachEvent("on" + evname, func);
  } else if (el.addEventListener) { // Gecko / W3C
    el.addEventListener(evname, func, true);
  } else {
    el["on" + evname] = func;
  }
};

Calendar.addClass = function(el, className) {
        Calendar.removeClass(el, className);
        el.className += " " + className;
};

Calendar.removeClass = function(el, className) {
        if (!(el && el.className)) {
                return;
        }
        var cls = el.className.split(" ");
        var ar = new Array();
        for (var i = cls.length; i > 0;) {
                if (cls[--i] != className) {
                        ar[ar.length] = cls[i];
                }
        }
        el.className = ar.join(" ");
};

Calendar.getElement = function(ev) {
        if (Calendar.is_ie) {
                return window.event.srcElement;
        } else {
                return ev.currentTarget;
        }
};

Calendar.stopEvent = function(ev) {
        ev || (ev = window.event);
        if (Calendar.is_ie) {
                ev.cancelBubble = true;
                ev.returnValue = false;
        } else {
                //ev.preventDefault();
                //ev.stopPropagation();
        }
        return false;
};

Calendar.dayMouseOver = function(ev) {
        var el = Calendar.getElement(ev);

        Calendar.addClass(el, "hilite");
        if (el.caldate) {
                Calendar.addClass(el.parentNode, "rowhilite");
        }

        return Calendar.stopEvent(ev);
};

Calendar.dayMouseOut = function(ev) {
        with (Calendar) {
                var el = getElement(ev);
                removeClass(el, "hilite");
                if (el.caldate) {
                        removeClass(el.parentNode, "rowhilite");
                }
                return stopEvent(ev);
        }
};

Calendar.dayMouseDown = function(ev) {
        var el = Calendar.getElement(ev);
        if (el.disabled) {
                return false;
        }
        var cal = el.calendar;
        if (typeof cal.onSelect == "function") {
          cal.onSelect(el.caldate, el.celltype);
        }
        cal.hide();
        return Calendar.stopEvent(ev);
};

Calendar.prototype.onSelect = function(date, celltype) {
        if (!this.field) return;
        if (celltype == ctDay) {
          this.date = date;
          this.field.value = cal.date.print(Calendar.DateFormat);
        } else {
          this.field.value = date;
        }
        if (typeof this.field.onchange == "function")
                this.field.onchange();
};

Calendar.btnMouseOver = function(ev) {
        var el = Calendar.getElement(ev);

        if (el.navtype == 300) return;

        Calendar.addClass(el, "hilite");

        return Calendar.stopEvent(ev);
};

Calendar.btnMouseOut = function(ev) {
        var el = Calendar.getElement(ev);

        if (el.navtype == 300) return;

        Calendar.removeClass(el, "hilite");

        return Calendar.stopEvent(ev);
};

Calendar.btnMouseDown = function(ev) {
        var el = Calendar.getElement(ev);

        switch(el.navtype) {
           case 400: // help
             if (el.calendar) el.calendar.showHelp();
             break;
           case 300: // title
             break;
           case 200: // close
             if (el.calendar) el.calendar.hide();
             break;
           case -2: // prev year
             if (el.calendar) {
               var newdate = el.calendar.date.prevYear();
               el.calendar.build(newdate);
             }
             break;
           case -1: // prev month
             if (el.calendar) {
               var newdate = el.calendar.date.prevMonth();
               el.calendar.build(newdate);
             }
             break;
           case 0: // today
             var newdate = new Date();
             if (el.calendar) el.calendar.build(newdate);
             break;
           case 1: // next month
             if (el.calendar) {
               var newdate = el.calendar.date.nextMonth();
               el.calendar.build(newdate);
             }
             break;
           case 2: // next year
             if (el.calendar) {
               var newdate = el.calendar.date.nextYear();
               el.calendar.build(newdate);
             }
             break;
        }

        return Calendar.stopEvent(ev);
};

Calendar.prototype.showHelp = function() {
        alert("WebAsyst Web Calendar 1.1\n" +
              "Copyright 2004 (C) WebAsyst Ltd.\n\n" +

              "Tip:\n" +
              "- Use \xab, \xbb buttons for year\n" +
              "- Use " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " buttons for month."
        );
};

Calendar.prototype.hideShowCovered = function () {
        if (!this.hideCovered) return;

        var hideShow = function (o, tagname) {
          c = document.getElementsByTagName(tagname);
          for (i = 0; i < c.length; i++) {
            if (c[i] == null) continue;
            c[i].style.visibility = (!o.hidden)?"hidden":"visible";
          }
        };

        if (!Calendar.is_ie) return;

        hideShow(this, "select");
        hideShow(this, "iframe");
        hideShow(this, "applet");
};

Calendar.getTargetElement = function(ev) {
        if (Calendar.is_ie) {
                return window.event.srcElement;
        } else {
                return ev.target;
        }
};

Calendar._checkCalendar = function(ev) {
        if (!window.calendar) {
                return false;
        }
        var el = Calendar.is_ie ? Calendar.getElement(ev) : Calendar.getTargetElement(ev);
        for (; el != null && el != calendar.element; el = el.parentNode);
        if (el == null) {
                // hide the calendar.
                window.calendar.hide();
                return Calendar.stopEvent(ev);
        }
};

function displayDate(field) {
  if (field == null) return;
  var disp = document.getElementById(field.getAttribute("display"));
  disp.innerHTML = field.value;
}

// ==UserScript==
// @name Event Invite Button
// @author Stephen Paul Weber
// @namespace http://singpolyma.net/
// @version 0.1
// @description  Nothing to see here.
// @include http://*
// ==/UserScript==

function $x( xpath, root ) {
  var doc = root ? root.evaluate ? root : root.ownerDocument : document, next;
  var got = doc.evaluate( xpath, root||doc, null, 0, null ), result = [];
  switch (got.resultType) {
    case got.STRING_TYPE:
      return got.stringValue;
    case got.NUMBER_TYPE:
      return got.numberValue;
    case got.BOOLEAN_TYPE:
      return got.booleanValue;
    default:
      while (next = got.iterateNext())
        result.push( next );
      return result;
  }
}

function $X( xpath, root ) {
  var got = $x( xpath, root );
  return got instanceof Array ? got[0] : got;
}

function init() {

        var summary = description = dtstart = dtend = elocation = url = '';
        var link;
        var events = $x('//*[contains(concat(" ",normalize-space(@class)," ")," vevent ")]');
        for(var i in events) {
                summary = description = dtstart = dtend = elocation = url = '';
                try { summary = $X('//*[contains(concat(" ",normalize-space(@class)," ")," summary ")]/text()', events[i]).textContent; } catch(e) {}
                try { description = $X('//*[contains(concat(" ",normalize-space(@class)," ")," description ")]/text()', events[i]).textContent; } catch(e) {}
                try { url = $X('//a[contains(concat(" ",normalize-space(@class)," ")," url ")]/@href', events[i]).textContent; } catch(e) {}
                try { elocation = $X('//*[contains(concat(" ",normalize-space(@class)," ")," location ")]/text()', events[i]).textContent; } catch(e) {}
                try { dtstart = $X('//abbr[contains(concat(" ",normalize-space(@class)," ")," dtstart ")]/@title', events[i]).textContent; } catch(e) {}
                try { dtend = $X('//abbr[contains(concat(" ",normalize-space(@class)," ")," dtend ")]/@title', events[i]).textContent; } catch(e) {}
                link = document.createElement('a');
                link.style.display = 'block';
                link.style.clear = 'both';
                link.style.textAlign = 'left';
                link.style.backgroundColor = '#333';
                link.style.color = '#f00';
                link.style.marginTop = '1em';
                link.style.marginBottom = '1em';
                link.style.padding = '5px';
                link.style.paddingLeft = '10px';
                link.style.fontSize = '1.2em';
                link.style.textAlign = 'center';
                link.style.fontWeight = 'normal';
                link.href = 'http://scrape.singpolyma.net/invite/?'
                        + 'summary=' + encodeURIComponent(summary)
                        + '&description=' + encodeURIComponent(description)
                        + '&url=' + encodeURIComponent(url ? url : unsafeWindow.location.href)
                        + '&location=' + encodeURIComponent(elocation)
                        + '&dtstart=' + encodeURIComponent(dtstart)
                        + '&dtend=' + encodeURIComponent(dtend);
                link.innerHTML = '<img src="http://scrape.singpolyma.net/invite/img/calendar_link.png" alt="" /> Invite Contacts';
                events[i].parentNode.insertBefore(link, events[i]);
        }

}

unsafeWindow.addEventListener('load', init, false);

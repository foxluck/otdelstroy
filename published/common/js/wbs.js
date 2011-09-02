(function(){

WBS = {
    getDom : function(el){
        if(!el || !document){
            return null;
        }
        return el.dom ? el.dom : (typeof el == 'string' ? document.getElementById(el) : el);
    }
};
WBS.util = {};
WBS.isReady = true;

WBS.apply = function(o, c, defaults){
    if(defaults){
        // no "this" reference for friendly out of scope calls
        WBS.apply(o, defaults);
    }
    if(o && c && typeof c == 'object'){
        for(var p in c){
            o[p] = c[p];
        }
    }
    return o;
};
WBS.applyIf = function(o,c){
	if(o&&c){
		for(var p in c){
			if(typeof o[p]=="undefined"){
				o[p]=c[p]
			}
		}
	}
	return o
};

(function(){
    var idSeed = 0;
    var ua = navigator.userAgent.toLowerCase();

    var isStrict = document.compatMode == "CSS1Compat",
        isOpera = ua.indexOf("opera") > -1,
        isSafari = (/webkit|khtml/).test(ua),
        isSafari3 = isSafari && ua.indexOf('webkit/5') != -1,
        isIE = !isOpera && ua.indexOf("msie") > -1,
        isIE7 = !isOpera && ua.indexOf("msie 7") > -1,
        isGecko = !isSafari && ua.indexOf("gecko") > -1,
        isGecko3 = !isSafari && ua.indexOf("rv:1.9") > -1,
        isBorderBox = isIE && !isStrict,
        isWindows = (ua.indexOf("windows") != -1 || ua.indexOf("win32") != -1),
        isMac = (ua.indexOf("macintosh") != -1 || ua.indexOf("mac os x") != -1),
        isAir = (ua.indexOf("adobeair") != -1),
        isLinux = (ua.indexOf("linux") != -1),
        isSecure = window.location.href.toLowerCase().indexOf("https") === 0;

    WBS.apply(WBS, {
        isOpera : isOpera,
        isSafari : isSafari,
        isSafari3 : isSafari3,
        isSafari2 : isSafari && !isSafari3,
        isIE : isIE,
        isIE6 : isIE && !isIE7,
        isIE7 : isIE7,
        isGecko : isGecko,
        isGecko2 : isGecko && !isGecko3,
        isGecko3 : isGecko3,
        isBorderBox : isBorderBox,
        isLinux : isLinux,
        isWindows : isWindows,
        isMac : isMac,
        isAir : isAir});
})();

/**
 * @class WBS.util.Observable
 * Abstract base class that provides a common interface for publishing events. Subclasses are expected to
 * to have a property "events" with all the events defined.<br>
 * For example:
 * <pre><code>
 Employee = function(name){
    this.name = name;
    this.addEvents({
        "fired" : true,
        "quit" : true
    });
 }
 WBS.extend(Employee, WBS.util.Observable);
</code></pre>
 */
WBS.util.Observable = function(){
    /**
     * @cfg {Object} listeners A config object containing one or more event handlers to be added to this
     * object during initialization.  This should be a valid listeners config object as specified in the
     * {@link #addListener} example for attaching multiple handlers at once.
     */
    if(this.listeners){
        this.on(this.listeners);
        delete this.listeners;
    }
};
WbsObservable = WBS.util.Observable;

WBS.util.Observable.prototype = {
    /**
     * Fires the specified event with the passed parameters (minus the event name).
     * @param {String} eventName
     * @param {Object...} args Variable number of parameters are passed to handlers
     * @return {Boolean} returns false if any of the handlers return false otherwise it returns true
     */
    fireEvent : function(){
        if(this.eventsSuspended !== true){
            var ce = this.events[arguments[0].toLowerCase()];
            if(typeof ce == "object"){
                return ce.fire.apply(ce, Array.prototype.slice.call(arguments, 1));
            }
        }
        return true;
    },

    // private
    filterOptRe : /^(?:scope|delay|buffer|single)$/,

    /**
     * Appends an event handler to this component
     * @param {String}   eventName The type of event to listen for
     * @param {Function} handler The method the event invokes
     * @param {Object}   scope (optional) The scope in which to execute the handler
     * function. The handler function's "this" context.
     * @param {Object}   options (optional) An object containing handler configuration
     * properties. This may contain any of the following properties:<ul>
     * <li><b>scope</b> : Object<p class="sub-desc">The scope in which to execute the handler function. The handler function's "this" context.</p></li>
     * <li><b>delay</b> : Number<p class="sub-desc">The number of milliseconds to delay the invocation of the handler after the event fires.</p></li>
     * <li><b>single</b> : Boolean<p class="sub-desc">True to add a handler to handle just the next firing of the event, and then remove itself.</p></li>
     * <li><b>buffer</b> : Number<p class="sub-desc">Causes the handler to be scheduled to run in an {@link Ext.util.DelayedTask} delayed
     * by the specified number of milliseconds. If the event fires again within that time, the original
     * handler is <em>not</em> invoked, but the new handler is scheduled in its place.</p></li>
     * </ul><br>
     * <p>
     * <b>Combining Options</b><br>
     * Using the options argument, it is possible to combine different types of listeners:<br>
     * <br>
     * A normalized, delayed, one-time listener that auto stops the event and passes a custom argument (forumId)
     * <pre><code>
el.on('click', this.onClick, this, {
    single: true,
    delay: 100,
    forumId: 4
});</code></pre>
     * <p>
     * <b>Attaching multiple handlers in 1 call</b><br>
      * The method also allows for a single argument to be passed which is a config object containing properties
     * which specify multiple handlers.
     * <p>
     * <pre><code>
foo.on({
    'click' : {
        fn: this.onClick,
        scope: this,
        delay: 100
    },
    'mouseover' : {
        fn: this.onMouseOver,
        scope: this
    },
    'mouseout' : {
        fn: this.onMouseOut,
        scope: this
    }
});</code></pre>
     * <p>
     * Or a shorthand syntax:<br>
     * <pre><code>
foo.on({
    'click' : this.onClick,
    'mouseover' : this.onMouseOver,
    'mouseout' : this.onMouseOut,
     scope: this
});</code></pre>
     */
    addListener : function(eventName, fn, scope, o){
        if(typeof eventName == "object"){
            o = eventName;
            for(var e in o){
                if(this.filterOptRe.test(e)){
                    continue;
                }
                if(typeof o[e] == "function"){
                    // shared options
                    this.addListener(e, o[e], o.scope,  o);
                }else{
                    // individual options
                    this.addListener(e, o[e].fn, o[e].scope, o[e]);
                }
            }
            return;
        }
        o = (!o || typeof o == "boolean") ? {} : o;
        eventName = eventName.toLowerCase();
        var ce = this.events[eventName] || true;
        if(typeof ce == "boolean"){
            ce = new WBS.util.Event(this, eventName);
            this.events[eventName] = ce;
        }
        ce.addListener(fn, scope, o);
    },

    /**
     * Removes a listener
     * @param {String}   eventName     The type of event to listen for
     * @param {Function} handler        The handler to remove
     * @param {Object}   scope  (optional) The scope (this object) for the handler
     */
    removeListener : function(eventName, fn, scope){
        var ce = this.events[eventName.toLowerCase()];
        if(typeof ce == "object"){
            ce.removeListener(fn, scope);
        }
    },

    /**
     * Removes all listeners for this object
     */
    purgeListeners : function(){
        for(var evt in this.events){
            if(typeof this.events[evt] == "object"){
                 this.events[evt].clearListeners();
            }
        }
    },

    /**
     * Relays selected events from the specified Observable as if the events were fired by <tt><b>this</b></tt>.
     * @param {Object} o The Observable whose events this object is to relay.
     * @param {Array} events Array of event names to relay.
     */
    relayEvents : function(o, events){
        var createHandler = function(ename){
            return function(){
                return this.fireEvent.apply(this, WBS.combine(ename, Array.prototype.slice.call(arguments, 0)));
            };
        };
        for(var i = 0, len = events.length; i < len; i++){
            var ename = events[i];
            if(!this.events[ename]){ this.events[ename] = true; };
            o.on(ename, createHandler(ename), this);
        }
    },

    /**
     * Used to define events on this Observable
     * @param {Object} object The object with the events defined
     */
    addEvents : function(o){
        if(!this.events){
            this.events = {};
        }
        if(typeof o == 'string'){
            for(var i = 0, a = arguments, v; v = a[i]; i++){
                if(!this.events[a[i]]){
                    this.events[a[i]] = true;
                }
            }
        }else{
            WBS.applyIf(this.events, o);
        }
    },

    /**
     * Checks to see if this object has any listeners for a specified event
     * @param {String} eventName The name of the event to check for
     * @return {Boolean} True if the event is being listened for, else false
     */
    hasListener : function(eventName){
        var e = this.events[eventName];
        return typeof e == "object" && e.listeners.length > 0;
    },

    /**
     * Suspend the firing of all events. (see {@link #resumeEvents})
     */
    suspendEvents : function(){
        this.eventsSuspended = true;
    },

    /**
     * Resume firing events. (see {@link #suspendEvents})
     */
    resumeEvents : function(){
        this.eventsSuspended = false;
    },

    // these are considered experimental
    // allows for easier interceptor and sequences, including cancelling and overwriting the return value of the call
    // private
    getMethodEvent : function(method){
        if(!this.methodEvents){
            this.methodEvents = {};
        }
        var e = this.methodEvents[method];
        if(!e){
            e = {};
            this.methodEvents[method] = e;

            e.originalFn = this[method];
            e.methodName = method;
            e.before = [];
            e.after = [];


            var returnValue, v, cancel;
            var obj = this;

            var makeCall = function(fn, scope, args){
                if((v = fn.apply(scope || obj, args)) !== undefined){
                    if(typeof v === 'object'){
                        if(v.returnValue !== undefined){
                            returnValue = v.returnValue;
                        }else{
                            returnValue = v;
                        }
                        if(v.cancel === true){
                            cancel = true;
                        }
                    }else if(v === false){
                        cancel = true;
                    }else {
                        returnValue = v;
                    }
                }
            }

            this[method] = function(){
                returnValue = v = undefined; cancel = false;
                var args = Array.prototype.slice.call(arguments, 0);
                for(var i = 0, len = e.before.length; i < len; i++){
                    makeCall(e.before[i].fn, e.before[i].scope, args);
                    if(cancel){
                        return returnValue;
                    }
                }

                if((v = e.originalFn.apply(obj, args)) !== undefined){
                    returnValue = v;
                }

                for(var i = 0, len = e.after.length; i < len; i++){
                    makeCall(e.after[i].fn, e.after[i].scope, args);
                    if(cancel){
                        return returnValue;
                    }
                }
                return returnValue;
            };
        }
        return e;
    },

    // adds an "interceptor" called before the original method
    beforeMethod : function(method, fn, scope){
        var e = this.getMethodEvent(method);
        e.before.push({fn: fn, scope: scope});
    },

    // adds a "sequence" called after the original method
    afterMethod : function(method, fn, scope){
        var e = this.getMethodEvent(method);
        e.after.push({fn: fn, scope: scope});
    },

    removeMethodListener : function(method, fn, scope){
        var e = this.getMethodEvent(method);
        for(var i = 0, len = e.before.length; i < len; i++){
            if(e.before[i].fn == fn && e.before[i].scope == scope){
                e.before.splice(i, 1);
                return;
            }
        }
        for(var i = 0, len = e.after.length; i < len; i++){
            if(e.after[i].fn == fn && e.after[i].scope == scope){
                e.after.splice(i, 1);
                return;
            }
        }
    }
};
/**
 * Appends an event handler to this element (shorthand for addListener)
 * @param {String}   eventName     The type of event to listen for
 * @param {Function} handler        The method the event invokes
 * @param {Object}   scope (optional) The scope in which to execute the handler
 * function. The handler function's "this" context.
 * @param {Object}   options  (optional)
 * @method
 */
WBS.util.Observable.prototype.on = WBS.util.Observable.prototype.addListener;
/**
 * Removes a listener (shorthand for removeListener)
 * @param {String}   eventName     The type of event to listen for
 * @param {Function} handler        The handler to remove
 * @param {Object}   scope  (optional) The scope (this object) for the handler
 * @method
 */
WBS.util.Observable.prototype.un = WBS.util.Observable.prototype.removeListener;

/**
 * Starts capture on the specified Observable. All events will be passed
 * to the supplied function with the event name + standard signature of the event
 * <b>before</b> the event is fired. If the supplied function returns false,
 * the event will not fire.
 * @param {Observable} o The Observable to capture
 * @param {Function} fn The function to call
 * @param {Object} scope (optional) The scope (this object) for the fn
 * @static
 */
WBS.util.Observable.capture = function(o, fn, scope){
    o.fireEvent = o.fireEvent.createInterceptor(fn, scope);
};

/**
 * Removes <b>all</b> added captures from the Observable.
 * @param {Observable} o The Observable to release
 * @static
 */
WBS.util.Observable.releaseCapture = function(o){
    o.fireEvent = WBS.util.Observable.prototype.fireEvent;
};

(function(){

    var createBuffered = function(h, o, scope){
        var task = new WBS.util.DelayedTask();
        return function(){
            task.delay(o.buffer, h, scope, Array.prototype.slice.call(arguments, 0));
        };
    };

    var createSingle = function(h, e, fn, scope){
        return function(){
            e.removeListener(fn, scope);
            return h.apply(scope, arguments);
        };
    };

    var createDelayed = function(h, o, scope){
        return function(){
            var args = Array.prototype.slice.call(arguments, 0);
            setTimeout(function(){
                h.apply(scope, args);
            }, o.delay || 10);
        };
    };

    WBS.util.Event = function(obj, name){
        this.name = name;
        this.obj = obj;
        this.listeners = [];
    };

    WBS.util.Event.prototype = {
        addListener : function(fn, scope, options){
            scope = scope || this.obj;
            if(!this.isListening(fn, scope)){
                var l = this.createListener(fn, scope, options);
                if(!this.firing){
                    this.listeners.push(l);
                }else{ // if we are currently firing this event, don't disturb the listener loop
                    this.listeners = this.listeners.slice(0);
                    this.listeners.push(l);
                }
            }
        },

        createListener : function(fn, scope, o){
            o = o || {};
            scope = scope || this.obj;
            var l = {fn: fn, scope: scope, options: o};
            var h = fn;
            if(o.delay){
                h = createDelayed(h, o, scope);
            }
            if(o.single){
                h = createSingle(h, this, fn, scope);
            }
            if(o.buffer){
                h = createBuffered(h, o, scope);
            }
            l.fireFn = h;
            return l;
        },

        findListener : function(fn, scope){
            scope = scope || this.obj;
            var ls = this.listeners;
            for(var i = 0, len = ls.length; i < len; i++){
                var l = ls[i];
                if(l.fn == fn && l.scope == scope){
                    return i;
                }
            }
            return -1;
        },

        isListening : function(fn, scope){
            return this.findListener(fn, scope) != -1;
        },

        removeListener : function(fn, scope){
            var index;
            if((index = this.findListener(fn, scope)) != -1){
                if(!this.firing){
                    this.listeners.splice(index, 1);
                }else{
                    this.listeners = this.listeners.slice(0);
                    this.listeners.splice(index, 1);
                }
                return true;
            }
            return false;
        },

        clearListeners : function(){
            this.listeners = [];
        },

        fire : function(){
            var ls = this.listeners, scope, len = ls.length;
            if(len > 0){
                this.firing = true;
                var args = Array.prototype.slice.call(arguments, 0);
                for(var i = 0; i < len; i++){
                    var l = ls[i];
                    if(l.fireFn.apply(l.scope||this.obj||window, arguments) === false){
                        this.firing = false;
                        return false;
                    }
                }
                this.firing = false;
            }
            return true;
        }
    };
})();



/*
 * WBS JS Library 2.2
 * Copyright(c) 2006-2008, WBS JS, LLC.
 * licensing@WBSjs.com
 *
 * http://WBSjs.com/license
 */

/**
 * @class WBS.History
 * @extends WBS.util.Observable
 * History management component that allows you to register arbitrary tokens that signify application
 * history state on navigation actions.  You can then handle the history {@link #change} event in order
 * to reset your application UI to the appropriate state when the user navigates forward or backward through
 * the browser history stack.
 * @singleton
 */
WBS.History = (function () {
    var iframe, hiddenField;
    var ready = false;
    var currentToken;

    function getHash() {
        var href = top.location.href, i = href.indexOf("#");
        return i >= 0 ? href.substr(i + 1) : null;
    }

    function doSave() {
        hiddenField.value = currentToken;
    }

    function handleStateChange(token) {
        currentToken = token;
        WBS.History.fireEvent('change', token);
    }

    function updateIFrame (token) {
        var html = ['<html><body><div id="state">',token,'</div></body></html>'].join('');
        try {
            var doc = iframe.contentWindow.document;
            doc.open();
            doc.write(html);
            doc.close();
            return true;
        } catch (e) {
            return false;
        }
    }

    function checkIFrame() {
        if (!iframe.contentWindow || !iframe.contentWindow.document) {
            setTimeout(checkIFrame, 10);
            return;
        }

        var doc = iframe.contentWindow.document;
        var elem = doc.getElementById("state");
        var token = elem ? elem.innerText : null;

        var hash = getHash();

        setInterval(function () {

            doc = iframe.contentWindow.document;
            elem = doc.getElementById("state");

            var newtoken = elem ? elem.innerText : null;

            var newHash = getHash();

            if (newtoken !== token) {
                token = newtoken;
                handleStateChange(token);
                top.location.hash = token;
                hash = token;
                doSave();
            } else if (newHash !== hash) {
                hash = newHash;
                updateIFrame(newHash);
            }

        }, 50);

        ready = true;

        WBS.History.fireEvent('ready', WBS.History);
    }

    function startUp() {
        currentToken = hiddenField.value;

        if (WBS.isIE) {
            checkIFrame();
        } else {
            var hash = getHash();
            setInterval(function () {
                var newHash = getHash();
                if (newHash !== hash) {
                    hash = newHash;
                    handleStateChange(hash);
                    doSave();
                }
            }, 50);
            ready = true;
            WBS.History.fireEvent('ready', WBS.History);
        }
    }

    return {
        /**
         * The id of the hidden field required for storing the current history token.
         * @type String
         * @property
         */
        fieldId: 'x-history-field',
        /**
         * The id of the iframe required by IE to manage the history stack.
         * @type String
         * @property
         */
        iframeId: 'x-history-frame',
        
        events:{},

        /**
         * Initialize the global History instance.
         * @param {Boolean} onReady (optional) A callback function that will be called once the history
         * component is fully initialized.
         * @param {Object} scope (optional) The callback scope
         */
        init: function (onReady, scope) {
            if(ready) {
                WBS.callback(onReady, scope, [this]);
                return;
            }
            if(!WBS.isReady){
                WBS.onReady(function(){
                    WBS.History.init(onReady, scope);
                });
                return;
            }
            hiddenField = WBS.getDom(WBS.History.fieldId);
			if (WBS.isIE) {
                iframe = WBS.getDom(WBS.History.iframeId);
            }
            this.addEvents('ready', 'change');
            if(onReady){
                this.on('ready', onReady, scope, {single:true});
            }
            startUp();
        },

        /**
         * Add a new token to the history stack. This can be any arbitrary value, although it would
         * commonly be the concatenation of a component id and another id marking the specifc history
         * state of that component.  Example usage:
         * <pre><code>
// Handle tab changes on a TabPanel
tabPanel.on('tabchange', function(tabPanel, tab){
    WBS.History.add(tabPanel.id + ':' + tab.id);
});
</code></pre>
         * @param {String} token The value that defines a particular application-specific history state
         * @param {Boolean} preventDuplicates When true, if the passed token matches the current token
         * it will not save a new history step. Set to false if the same state can be saved more than once
         * at the same history stack location (defaults to true).
         */
        add: function (token, preventDup) {
            if(preventDup !== false){
                if(this.getToken() == token){
                    return true;
                }
            }
            if (WBS.isIE) {
                return updateIFrame(token);
            } else {
                top.location.hash = token;
                return true;
            }
        },

        /**
         * Programmatically steps back one step in browser history (equivalent to the user pressing the Back button).
         */
        back: function(){
            history.go(-1);
        },

        /**
         * Programmatically steps forward one step in browser history (equivalent to the user pressing the Forward button).
         */
        forward: function(){
            history.go(1);
        },

        /**
         * Retrieves the currently-active history token.
         * @return {String} The token
         */
        getToken: function() {
            return ready ? currentToken : getHash();
        },
		_getToken: function() {
            return getHash();
        }
    };
})();
WBS.apply(WBS.History, new WBS.util.Observable());

})();

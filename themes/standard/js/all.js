// SelectBox

/*!
 * jQuery SelectBox plugin 0.2
 *
 * Copyright 2011-2012, Dimitar Ivanov (http://www.bulgaria-web-developers.com/projects/javascript/selectbox/)
 * Licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) license.
 *
 * Date: Tue Jul 17 19:58:36 2012 +0300
 */

(function ($, undefined) {
    var PROP_NAME = 'selectbox',
        FALSE = false,
        TRUE = true;
    /**
     * Selectbox manager.
     * Use the singleton instance of this class, $.selectbox, to interact with the select box.
     * Settings for (groups of) select boxes are maintained in an instance object,
     * allowing multiple different settings on the same page
     */
    function Selectbox() {
        this._state = [];
        this._defaults = { // Global defaults for all the select box instances
            classHolder: "catHolder",
            classHolderDisabled: "catHolderDisabled",
            classSelector: "catSelector",
            classOptions: "catOptions",
            classGroup: "catGroup",
            classSub: "catSub",
            classDisabled: "catDisabled",
            classToggleOpen: "catToggleOpen",
            classToggle: "catToggle",
            classFocus: "catFocus",
            speed: 200,
            effect: "slide", // "slide" or "fade"
            onChange: null, //Define a callback function when the selectbox is changed
            onOpen: null, //Define a callback function when the selectbox is open
            onClose: null //Define a callback function when the selectbox is closed
        };
    }

    $.extend(Selectbox.prototype, {
        /**
         * Is the first field in a jQuery collection open as a selectbox
         *
         * @param {Object} target
         * @return {Boolean}
         */
        _isOpenSelectbox: function (target) {
            if (!target) {
                return FALSE;
            }
            var inst = this._getInst(target);
            return inst.isOpen;
        },
        /**
         * Is the first field in a jQuery collection disabled as a selectbox
         *
         * @param {HTMLElement} target
         * @return {Boolean}
         */
        _isDisabledSelectbox: function (target) {
            if (!target) {
                return FALSE;
            }
            var inst = this._getInst(target);
            return inst.isDisabled;
        },
        /**
         * Attach the select box to a jQuery selection.
         *
         * @param {HTMLElement} target
         * @param {Object} settings
         */
        _attachSelectbox: function (target, settings) {
            if (this._getInst(target)) {
                return FALSE;
            }
            var $target = $(target),
                self = this,
                inst = self._newInst($target),
                catHolder, catSelector, catToggle, catOptions,
                s = FALSE, optGroup = $target.find("optgroup"), opts = $target.find("option"), olen = opts.length;

            $target.attr("cat", inst.uid);

            $.extend(inst.settings, self._defaults, settings);
            self._state[inst.uid] = FALSE;
            $target.hide();

            function closeOthers() {
                var key, sel,
                    uid = this.attr("id").split("_")[1];
                for (key in self._state) {
                    if (key !== uid) {
                        if (self._state.hasOwnProperty(key)) {
                            sel = $("select[cat='" + key + "']")[0];
                            if (sel) {
                                self._closeSelectbox(sel);
                            }
                        }
                    }
                }
            }

            catHolder = $("<div>", {
                "id": "catHolder_" + inst.uid,
                "class": inst.settings.classHolder,
                "tabindex": $target.attr("tabindex")
            });

            catSelector = $("<a>", {
                "id": "catSelector_" + inst.uid,
                "href": "#",
                "class": inst.settings.classSelector,
                "click": function (e) {
                    e.preventDefault();
                    closeOthers.apply($(this), []);
                    var uid = $(this).attr("id").split("_")[1];
                    if (self._state[uid]) {
                        self._closeSelectbox(target);
                    } else {
                        self._openSelectbox(target);
                    }
                }
            });

            catToggle = $("<a>", {
                "id": "catToggle_" + inst.uid,
                "href": "#",
                "class": inst.settings.classToggle,
                "click": function (e) {
                    e.preventDefault();
                    closeOthers.apply($(this), []);
                    var uid = $(this).attr("id").split("_")[1];
                    if (self._state[uid]) {
                        self._closeSelectbox(target);
                    } else {
                        self._openSelectbox(target);
                    }
                }
            });
            catToggle.appendTo(catHolder);

            catOptions = $("<ul>", {
                "id": "catOptions_" + inst.uid,
                "class": inst.settings.classOptions,
                "css": {
                    "display": "none"
                }
            });

            $target.children().each(function(i) {
                var that = $(this), li, config = {};
                if (that.is("option")) {
                    getOptions(that);
                } else if (that.is("optgroup")) {
                    li = $("<li>");
                    $("<span>", {
                        "text": that.attr("label")
                    }).addClass(inst.settings.classGroup).appendTo(li);
                    li.appendTo(catOptions);
                    if (that.is(":disabled")) {
                        config.disabled = true;
                    }
                    config.sub = true;
                    getOptions(that.find("option"), config);
                }
            });

            function getOptions () {
                var sub = arguments[1] && arguments[1].sub ? true : false,
                    disabled = arguments[1] && arguments[1].disabled ? true : false;
                arguments[0].each(function (i) {
                    var that = $(this),
                        li = $("<li>"),
                        child;
                    if (that.is(":selected")) {
                        catSelector.text(that.text());
                        s = TRUE;
                    }
                    if (i === olen - 1) {
                        li.addClass("last");
                    }
                    if (!that.is(":disabled") && !disabled) {
                        child = $("<a>", {
                            "href": "#" + that.val(),
                            "rel": that.val()
                        }).text(that.text()).bind("click.cat", function (e) {
                                if (e && e.preventDefault) {
                                    e.preventDefault();
                                }
                                var t = catToggle,
                                    $this = $(this),
                                    uid = t.attr("id").split("_")[1];
                                self._changeSelectbox(target, $this.attr("rel"), $this.text());
                                self._closeSelectbox(target);
                            }).bind("mouseover.cat", function () {
                                var $this = $(this);
                                $this.parent().siblings().find("a").removeClass(inst.settings.classFocus);
                                $this.addClass(inst.settings.classFocus);
                            }).bind("mouseout.cat", function () {
                                $(this).removeClass(inst.settings.classFocus);
                            });
                        if (sub) {
                            child.addClass(inst.settings.classSub);
                        }
                        if (that.is(":selected")) {
                            child.addClass(inst.settings.classFocus);
                        }
                        child.appendTo(li);
                    } else {
                        child = $("<span>", {
                            "text": that.text()
                        }).addClass(inst.settings.classDisabled);
                        if (sub) {
                            child.addClass(inst.settings.classSub);
                        }
                        child.appendTo(li);
                    }
                    li.appendTo(catOptions);
                });
            }

            if (!s) {
                catSelector.text(opts.first().text());
            }

            $.data(target, PROP_NAME, inst);

            catHolder.data("uid", inst.uid).bind("keydown.cat", function (e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0,
                    $this = $(this),
                    uid = $this.data("uid"),
                    inst = $this.siblings("select[cat='"+uid+"']").data(PROP_NAME),
                    trgt = $this.siblings(["select[cat='", uid, "']"].join("")).get(0),
                    $f = $this.find("ul").find("a." + inst.settings.classFocus);
                switch (key) {
                    case 37: //Arrow Left
                    case 38: //Arrow Up
                        if ($f.length > 0) {
                            var $next;
                            $("a", $this).removeClass(inst.settings.classFocus);
                            $next = $f.parent().prevAll("li:has(a)").eq(0).find("a");
                            if ($next.length > 0) {
                                $next.addClass(inst.settings.classFocus).focus();
                                $("#catSelector_" + uid).text($next.text());
                            }
                        }
                        break;
                    case 39: //Arrow Right
                    case 40: //Arrow Down
                        var $next;
                        $("a", $this).removeClass(inst.settings.classFocus);
                        if ($f.length > 0) {
                            $next = $f.parent().nextAll("li:has(a)").eq(0).find("a");
                        } else {
                            $next = $this.find("ul").find("a").eq(0);
                        }
                        if ($next.length > 0) {
                            $next.addClass(inst.settings.classFocus).focus();
                            $("#catSelector_" + uid).text($next.text());
                        }
                        break;
                    case 13: //Enter
                        if ($f.length > 0) {
                            self._changeSelectbox(trgt, $f.attr("rel"), $f.text());
                        }
                        self._closeSelectbox(trgt);
                        break;
                    case 9: //Tab
                        if (trgt) {
                            var inst = self._getInst(trgt);
                            if (inst/* && inst.isOpen*/) {
                                if ($f.length > 0) {
                                    self._changeSelectbox(trgt, $f.attr("rel"), $f.text());
                                }
                                self._closeSelectbox(trgt);
                            }
                        }
                        var i = parseInt($this.attr("tabindex"), 10);
                        if (!e.shiftKey) {
                            i++;
                        } else {
                            i--;
                        }
                        $("*[tabindex='" + i + "']").focus();
                        break;
                    case 27: //Escape
                        self._closeSelectbox(trgt);
                        break;
                }
                e.stopPropagation();
                return false;
            }).delegate("a", "mouseover", function (e) {
                    $(this).addClass(inst.settings.classFocus);
                }).delegate("a", "mouseout", function (e) {
                    $(this).removeClass(inst.settings.classFocus);
                });

            catSelector.appendTo(catHolder);
            catOptions.appendTo(catHolder);
            catHolder.insertAfter($target);

            $("html").prop('mousedown', function(e) {
                //				e.stopPropagation();
                $("select").selectbox('close');
            });
            $([".", inst.settings.classHolder, ", .", inst.settings.classSelector].join("")).mousedown(function(e) {
                //				e.stopPropagation();
            });
        },
        /**
         * Remove the selectbox functionality completely. This will return the element back to its pre-init state.
         *
         * @param {HTMLElement} target
         */
        _detachSelectbox: function (target) {
            var inst = this._getInst(target);
            if (!inst) {
                return FALSE;
            }
            $("#catHolder_" + inst.uid).remove();
            $.data(target, PROP_NAME, null);
            $(target).show();
        },
        /**
         * Change selected attribute of the selectbox.
         *
         * @param {HTMLElement} target
         * @param {String} value
         * @param {String} text
         */
        _changeSelectbox: function (target, value, text) {
            var onChange,
                inst = this._getInst(target);
            if (inst) {
                onChange = this._get(inst, 'onChange');
                $("#catSelector_" + inst.uid).text(text);
            }
            value = value.replace(/\'/g, "\\'");
            $(target).find("option[value='" + value + "']").attr("selected", TRUE);
            if (inst && onChange) {
                onChange.apply((inst.input ? inst.input[0] : null), [value, inst]);
            } else if (inst && inst.input) {
                inst.input.trigger('change');
            }
        },
        /**
         * Enable the selectbox.
         *
         * @param {HTMLElement} target
         */
        _enableSelectbox: function (target) {
            var inst = this._getInst(target);
            if (!inst || !inst.isDisabled) {
                return FALSE;
            }
            $("#catHolder_" + inst.uid).removeClass(inst.settings.classHolderDisabled);
            inst.isDisabled = FALSE;
            $.data(target, PROP_NAME, inst);
        },
        /**
         * Disable the selectbox.
         *
         * @param {HTMLElement} target
         */
        _disableSelectbox: function (target) {
            var inst = this._getInst(target);
            if (!inst || inst.isDisabled) {
                return FALSE;
            }
            $("#catHolder_" + inst.uid).addClass(inst.settings.classHolderDisabled);
            inst.isDisabled = TRUE;
            $.data(target, PROP_NAME, inst);
        },
        /**
         * Get or set any selectbox option. If no value is specified, will act as a getter.
         *
         * @param {HTMLElement} target
         * @param {String} name
         * @param {Object} value
         */
        _optionSelectbox: function (target, name, value) {
            var inst = this._getInst(target);
            if (!inst) {
                return FALSE;
            }
            //TODO check name
            inst[name] = value;
            $.data(target, PROP_NAME, inst);
        },
        /**
         * Call up attached selectbox
         *
         * @param {HTMLElement} target
         */
        _openSelectbox: function (target) {
            var inst = this._getInst(target);
            //if (!inst || this._state[inst.uid] || inst.isDisabled) {
            if (!inst || inst.isOpen || inst.isDisabled) {
                return;
            }
            var	el = $("#catOptions_" + inst.uid),
                viewportHeight = parseInt($(window).height(), 10),
                offset = $("#catHolder_" + inst.uid).offset(),
                scrollTop = $(window).scrollTop(),
                height = el.prev().height(),
                diff = viewportHeight - (offset.top - scrollTop) - height / 2,
                onOpen = this._get(inst, 'onOpen');
            el.css({
                "top": height + "px",
                "maxHeight": "320px"
            });
            inst.settings.effect === "fade" ? el.fadeIn(inst.settings.speed) : el.slideDown(inst.settings.speed);
            $("#catToggle_" + inst.uid).addClass(inst.settings.classToggleOpen);
            this._state[inst.uid] = TRUE;
            inst.isOpen = TRUE;
            if (onOpen) {
                onOpen.apply((inst.input ? inst.input[0] : null), [inst]);
            }
            $.data(target, PROP_NAME, inst);
        },
        /**
         * Close opened selectbox
         *
         * @param {HTMLElement} target
         */
        _closeSelectbox: function (target) {
            var inst = this._getInst(target);
            //if (!inst || !this._state[inst.uid]) {
            if (!inst || !inst.isOpen) {
                return;
            }
            var onClose = this._get(inst, 'onClose');
            inst.settings.effect === "fade" ? $("#catOptions_" + inst.uid).fadeOut(inst.settings.speed) : $("#catOptions_" + inst.uid).slideUp(inst.settings.speed);
            $("#catToggle_" + inst.uid).removeClass(inst.settings.classToggleOpen);
            this._state[inst.uid] = FALSE;
            inst.isOpen = FALSE;
            if (onClose) {
                onClose.apply((inst.input ? inst.input[0] : null), [inst]);
            }
            $.data(target, PROP_NAME, inst);
        },
        /**
         * Create a new instance object
         *
         * @param {HTMLElement} target
         * @return {Object}
         */
        _newInst: function(target) {
            var id = target[0].id.replace(/([^A-Za-z0-9_-])/g, '\\\\$1');
            return {
                id: id,
                input: target,
                uid: Math.floor(Math.random() * 99999999),
                isOpen: FALSE,
                isDisabled: FALSE,
                settings: {}
            };
        },
        /**
         * Retrieve the instance data for the target control.
         *
         * @param {HTMLElement} target
         * @return {Object} - the associated instance data
         * @throws error if a jQuery problem getting data
         */
        _getInst: function(target) {
            try {
                return $.data(target, PROP_NAME);
            }
            catch (err) {
                throw 'Missing instance data for this selectbox';
            }
        },
        /**
         * Get a setting value, defaulting if necessary
         *
         * @param {Object} inst
         * @param {String} name
         * @return {Mixed}
         */
        _get: function(inst, name) {
            return inst.settings[name] !== undefined ? inst.settings[name] : this._defaults[name];
        }
    });

    /**
     * Invoke the selectbox functionality.
     *
     * @param {Object|String} options
     * @return {Object}
     */
    $.fn.selectbox = function (options) {

        var otherArgs = Array.prototype.slice.call(arguments, 1);
        if (typeof options == 'string' && options == 'isDisabled') {
            return $.selectbox['_' + options + 'Selectbox'].apply($.selectbox, [this[0]].concat(otherArgs));
        }

        if (options == 'option' && arguments.length == 2 && typeof arguments[1] == 'string') {
            return $.selectbox['_' + options + 'Selectbox'].apply($.selectbox, [this[0]].concat(otherArgs));
        }

        return this.each(function() {
            typeof options == 'string' ?
                $.selectbox['_' + options + 'Selectbox'].apply($.selectbox, [this].concat(otherArgs)) :
                $.selectbox._attachSelectbox(this, options);
        });
    };

    $.selectbox = new Selectbox(); // singleton instance
    $.selectbox.version = "0.2";
})(jQuery);

// Scroll to top starts
$(function() {
    $.fn.scrollToTop = function() {
        $(this).hide().removeAttr("href");
        if ($(window).scrollTop() != "0") {
            $(this).fadeIn("slow")
        }
        var scrollDiv = $(this);
        $(window).scroll(function() {
            if ($(window).scrollTop() == "0") {
                $(scrollDiv).fadeOut("slow")
            } else {
                $(scrollDiv).fadeIn("slow")
            }
        });
        $(this).click(function() {
            $("html, body").animate({
                scrollTop: 0
            }, "slow")
        })
    }
});
// Browse menu

$(function(){
    $('#browse-btn').click(function(){
        $('#browse-content').toggle('1000');
        $(this).toggleClass('active');							
        if ($(this).hasClass('active')) $(this).find('span').html('&#x25B2;')
        else $(this).find('span').html('&#x25BC;')
    });

    $(".scroll-top").scrollToTop();

    $("#category_id").selectbox();
});
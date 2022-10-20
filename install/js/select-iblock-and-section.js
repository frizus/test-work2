(function ($, window, document) {
    var pluginName = 'bitrixIBlockAndSection',
        defaults = {
            namespace: pluginName,
            timeout: 30 * 1000,
            method: 'get'
        }

    function Plugin(element, options) {
        this.element = element
        this.$elem = $(this.element)
        this._name = pluginName
        this.settings = $.extend({}, defaults, options)
        this._defaults = defaults

        return this.init()
    }

    Plugin.prototype = {
        options: function (option, val) {
            this.settings[option] = val
        },
        destroy: function () {
            if (this.xhr !== null) {
                this.xhr.abort()
            }

            $.removeData(this.element, pluginName)
        },
        init: function () {
            var plugin = this
            this.xhr = null
            this.$elem.find('.iblock-and-section-iblock').each(function() {
                $(this).bind('change.' + plugin.settings.namespace, $.proxy(plugin.changeIBlock, plugin, $(this)))
            })
            this.$sectionSelect = this.$elem.find('.iblock-and-section-section')

            return this
        },
        changeIBlock: function($select) {
            if ($select.val() === '') {
                this.$sectionSelect.hide()
                this.clearOptions()
                return
            }

            this.$sectionSelect.val('').attr('disabled', 'disabled').show()

            var plugin = this
            var ajaxSettings = {
                url: this.settings.url,
                data: {
                    iblockId: $select.val(),
                },
                method: this.settings.method,
                dataType: 'html',
                timeout: this.settings.timeout
            }

            this.xhr = $.ajax(ajaxSettings)
                .done(function(data, textStatus, jqXHR) {
                    plugin.ajaxDone(data, textStatus, jqXHR)
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    plugin.ajaxFail(jqXHR, textStatus, errorThrown)
                })
                .always(function(dataOrJqXHR, textStatus, jqXHRorErrorThrown) {
                    plugin.ajaxAlways(dataOrJqXHR, textStatus, jqXHRorErrorThrown)
                })
        },
        clearOptions: function() {
            this.$sectionSelect.find('option:not([value=""])').remove()
        },
        ajaxDone: function (data, textStatus, jqXHR) {
            if (!('status' in jqXHR) ||
                (
                    (parseInt(jqXHR['status']) !== 200) &&
                    (parseInt(jqXHR['status']) !== 304)
                ) ||
                (data === '')
            ) {
                this.ajaxError()
            } else {
                this.ajaxSuccess(data)
            }

        },
        ajaxFail: function (jqXHR, textStatus, errorThrown) {
            this.ajaxError()
        },
        ajaxAlways: function(dataOrJqXHR, textStatus, jqXHRorErrorThrown) {
            this.$sectionSelect.removeAttr('disabled')
        },
        ajaxSuccess: function(data) {
            this.clearOptions()
            this.$sectionSelect.append($(data).find('> option'))
            this.$sectionSelect.val('')
        },
        ajaxError: function() {
            this.clearOptions()
            this.$sectionSelect.hide()
        }
    }

    $.fn[pluginName] = function (options) {
        var args = $.makeArray(arguments),
            after = args.slice(1),
            methodCall = typeof options === 'string',
            methodResult = undefined,
            first = true

        var eachResult = this.each(function () {
            var instance = $.data(this, pluginName)

            if (instance) {
                if (instance[options]) {
                    if (first) {
                        methodResult = instance[options].apply(instance, after)
                    } else {
                        instance[options].apply(instance, after)
                    }
                } else {
                    //$.error('Method ' + options + ' does not exist on Plugin')
                }
            } else {
                var plugin = new Plugin(this, options)

                $.data(this, pluginName, plugin)
            }

            if (first) {
                first = false
            }
        })

        if (methodCall) {
            return methodResult
        }

        return eachResult
    }
    $.fn[pluginName].prototype.defaults = defaults
    $.fn[pluginName].prototype.methods = Plugin.prototype
})(jQuery, window, document)

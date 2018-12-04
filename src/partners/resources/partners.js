(function(window, document, $) {

    /**
     * Subforms are repeating groups of fields for Partner relationships
     * like `locations` and `projects`.
     *
     * Each subform is expected to have a `<script class="subform-template" type="text/html">`
     * element with html template code from which to add new subforms.
     *
     * Each subform template wrapping div is expected have a `data-id` attribute with a value
     * like `1` or `new1`. This id will replace text `__new__` within the template.
     *
     * Each subform template is responsible to initialize itself with its own script but,
     * because template code is inside a `<script type="text/html">` tag already, JS should
     * be wrapped in `__script__` `__/script__`. These will be replaced with proper `<script>`
     * tags.
     *
     * @param {element} element
     */
    var Subforms = function(element) {
        this.$el = $(element);
        this.template = this.$el.find('.subform-template').html();
        this.limit = this.$el.data('limit');
        this.$subforms = this.$el.find('.subforms');

        this.init();
    };

    Subforms.prototype = {
        init: function() {
            this.$el.on('click', '.js-subform-add', this.onAddClick.bind(this));
            this.$subforms.on('click', '.js-subform-delete', this.onDeleteClick.bind(this));
            this.$subforms.on('click', '.js-subform-up', this.onUpClick);
            this.$subforms.on('click', '.js-subform-down', this.onDownClick);
        },
        onAddClick: function(e) {
            e.preventDefault();
            var id = this.getNewIndex();
            var template = this.template
                .replace(/__new__/g, id) // update ids
                .replace(/__(\/?script)__/g, '<$1>'); // template initializes itself

            this.$subforms.append(template);
            this.checkLimit(e);
        },
        onDeleteClick: function(e) {
            e.preventDefault();
            $(e.target).first().parents('.subform').remove();
            this.checkLimit(e);
        },
        onUpClick: function(e) {
            e.preventDefault();
            var $subform = $(this).first().parents('.subform').first();
            var $subformAbove = $subform.prev('.subform');
            if ($subformAbove.length) {
                $subform.insertBefore($subformAbove).hide().fadeIn();
            }
        },
        onDownClick: function(e) {
            e.preventDefault();
            var $subform = $(this).first().parents('.subform').first();
            var $subformBelow = $subform.next('.subform');
            if ($subformBelow.length) {
                $subform.insertAfter($subformBelow).hide().fadeIn();
            }
        },
        getNewIndex: function() {
            var previousNewId = 0;
            this.$subforms.find('.subform').each(function() {
                var id = String($(this).data('id'));
                if (id.slice(0,3) === 'new') {
                    previousNewId = Math.max(previousNewId, Number(id.slice(3)))
                }
            });

            return 'new' + ++previousNewId;
        },
        /**
         * Hide
         * @param ClickEvent
         */
        checkLimit(e) {
            if (this.limit && this.$subforms.find('.subform').length >= this.limit) {
                this.$el.find('.js-subform-add').hide();
            } else {
                this.$el.find('.js-subform-add').show();
            }
        }
    };

    $('.subforms-wrap').each(function() {
        new Subforms(this);
    });

})(window, document, jQuery);

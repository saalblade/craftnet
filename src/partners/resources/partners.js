(function(window, document, $) {

    var Subforms = function(element) {
        this.$el = $(element);
        this.template = this.$el.find('.subform-template').html();
        this.$subforms = this.$el.find('.subforms');
        this.newIndex = 0;

        this.init();
    };

    Subforms.prototype = {
        init: function() {
            this.$el.on('click', '.js-subform-add', this.onAddClick.bind(this));
            this.$subforms.on('click', '.js-subform-delete', this.onDeleteClick);
            this.$subforms.on('click', '.js-subform-up', this.onUpClick);
            this.$subforms.on('click', '.js-subform-down', this.onDownClick);
        },
        onAddClick: function(e) {
            e.preventDefault();
            var id = 'new' + ++this.newIndex;
            this.$subforms.append(this.template.replace(/%new%/g, id));
            Craft.initUiElements($('#project-' + id));
        },
        onDeleteClick: function(e) {
            e.preventDefault();
            $(this).first().parents('.subform').remove();
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
        }
    };

    $('.subforms-wrap').each(function() {
        new Subforms(this);
    });

    // Craft.initUiElements([jQuery selector])

})(window, document, jQuery);

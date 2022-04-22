<script>
    /**
     * A front-end representation of a Backpack field, with its main components.
     *
     * Makes it dead-simple for the developer to perform the most common
     * javascript manipulations, and makes it easy to do custom stuff
     * too, by exposing the main components (name, wrapper, input).
     */
    class CrudField {
        constructor(fieldName) {
            this.name = fieldName;
            this.wrapper = $('[bp-field-name="'+ this.name +'"]');
            this.input = this.wrapper.find("[bp-field-main-input");
            // if no bp-field-main-input has been declared in the field itself,
            // assume it's the first input in that wrapper, whatever it is
            if (this.input.length == 0) {
                this.input = $('[bp-field-name="'+ this.name +'"] input, [bp-field-name="'+ this.name +'"] textarea, [bp-field-name="'+ this.name +'"] select').first();
            }
            this.value = this.input.val();
        }

        change(closure) {
            this.input.change(function(event) {
                var fieldWrapper = $(this).closest('[bp-field-wrapper=true]');
                var fieldName = fieldWrapper.attr('bp-field-name');
                var fieldType = fieldWrapper.attr('bp-field-type');
                var fieldValue = $(this).val();

                // console.log('Changed field ' + fieldName + ' (type '+ fieldType + '), value is now ' + fieldValue);
                closure(event, fieldValue, fieldName, fieldType);
            }).change();

            return this;
        }

        onChange(closure) {
            this.change(closure);
        }

        hide(e) {
            this.wrapper.hide();
            return this;
        }

        show(e) {
            this.wrapper.show();
            return this;
        }

        enable(e) {
            this.input.removeAttr('disabled');
            this.input.trigger('backpack_field.enabled');
            return this;
        }

        disable(e) {
            this.input.attr('disabled', 'disabled');
            this.input.trigger('backpack_field.disabled');
            return this;
        }

        require(e) {
            this.wrapper.removeClass('required').addClass('required');
            return this;
        }

        unrequire(e) {
            this.wrapper.removeClass('required');
            return this;
        }

        check(e) {
            console.log(this.wrapper.find('input[type=checkbox]'));
            this.wrapper.find('input[type=checkbox]').prop('checked', true).trigger('change');
            return this;
        }

        uncheck(e) {
            this.wrapper.find('input[type=checkbox]').prop('checked', false).trigger('change');
            return this;
        }
    }

    /**
     * Window functions that help the developer easily select one or more fields.
     */
    window.crud = {
        field: function(fieldName) {
            return new CrudField(fieldName);
        },
        fields: function(fieldNamesArray) {
            return fieldNamesArray.map(function(fieldName) {
                return new CrudField(fieldName);
            });
        }
    }
</script>

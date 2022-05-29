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
            this.wrapper = $(`[bp-field-name*="${this.name}"][bp-field-wrapper]`);
           
            // search input in ancestors
            this.input = this.wrapper.closest('[bp-field-main-input]');

            // search input in children
            if (this.input.length === 0) {
                this.input = this.wrapper.find('[bp-field-main-input]');
            }

            // if no bp-field-main-input has been declared in the field itself, try to find an input with that name inside wraper
            if (this.input.length === 0) {
                this.input = this.wrapper.find(`input[bp-field-name="${this.name}"], textarea[bp-field-name="${this.name}"], select[bp-field-name="${this.name}"]`).first();
            }

            // if nothing works, use the first input found in field wrapper.
            if(this.input.length === 0) {
                this.input = this.wrapper.find('input, textarea, select').first();
            }
        }

        get value() {
            let value = this.input.val();

            // Parse the value if it's a number
            if (value.length && !isNaN(value)) {
                value = Number(value);
            }

            return value;
        }

        change(closure) {
            const fieldChanged = event => {
                const wrapper = this.input.closest('[bp-field-wrapper=true]');
                const name = wrapper.attr('bp-field-name');
                const type = wrapper.attr('bp-field-type');
                const value = this.input.val();

                closure(event, value, name, type);
            };

            if(this.isSubfield) {
                window.crud.subfieldsCallbacks =  window.crud.subfieldsCallbacks ?? new Array();
                window.crud.subfieldsCallbacks[this.subfieldHolder] = window.crud.subfieldsCallbacks[this.subfieldHolder] ?? new Array();
                if(!window.crud.subfieldsCallbacks[this.subfieldHolder].some( callbacks => callbacks['fieldName'] === this.name )) {
                    window.crud.subfieldsCallbacks[this.subfieldHolder].push({fieldName:  this.name, closure: closure, field: this});
                }
                return this;
            }

            this.input[0]?.addEventListener('input', fieldChanged, false);
            $(this.input).change(fieldChanged);
            fieldChanged();

            return this;
        }

        onChange(closure) {
            this.change(closure);
        }

        show(value = true) {
            this.wrapper.toggleClass('d-none', !value);
            this.input.trigger(`backpack:field.${value ? 'show' : 'hide'}`);
            return this;
        }

        hide() {
            return this.show(false);
        }

        enable(value = true) {
            this.input.attr('disabled', !value && 'disabled');
            this.input.trigger(`backpack:field.${value ? 'enable' : 'disable'}`);
            return this;
        }

        disable() {
            return this.enable(false);
        }

        require(value = true) {
            this.wrapper.toggleClass('required', value);
            this.input.trigger(`backpack:field.${value ? 'require' : 'unrequire'}`);
            return this;
        }

        unrequire() {
            return this.require(false);
        }

        check(value = true) {
            this.wrapper.find('input[type=checkbox]').prop('checked', value).trigger('change');
            return this;
        }

        uncheck() {
            return this.check(false);
        }

        subfield(name, rowNumber = false) {
            let subfield = new CrudField(name);
            if(!rowNumber) {
                subfield.isSubfield = true;
                subfield.subfieldHolder = this.name;
            }else{
                subfield.wrapper = $('[data-repeatable-identifier="'+this.name+'"][data-row-number="'+rowNumber+'"]');
                subfield.input = subfield.wrapper.closest('[data-repeatable-input-name$="'+name+'"][bp-field-main-input]');
                // if no bp-field-main-input has been declared in the field itself,
                // assume it's the first input in that wrapper, whatever it is
                if (subfield.input.length == 0) {
                    subfield.input = subfield.wrapper.find('input[data-repeatable-input-name$="'+name+'"], textarea[data-repeatable-input-name$="'+name+'"], select[data-repeatable-input-name$="'+name+'"]').first();
                }
            }
            return subfield;
        }
    }

    /**
     * Window functions that help the developer easily select one or more fields.
     */
    window.crud = {
        ...window.crud,

        // Create a field from a given name
        field: name => new CrudField(name),

        // Create all fields from a given name list
        fields: names => names.map(window.crud.field),
    };
</script>
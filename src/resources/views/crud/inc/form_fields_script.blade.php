<script>
    /**
     * A front-end representation of a Backpack field, with its main components.
     *
     * Makes it dead-simple for the developer to perform the most common
     * javascript manipulations, and makes it easy to do custom stuff
     * too, by exposing the main components (name, wrapper, input).
     */
    class CrudField {
        constructor(name) {
            this.name = name;
            this.wrapper = $(`[bp-field-name*="${name}"][bp-field-wrapper]`).first();

            if (this.wrapper.length === 0) {
                console.error(`CrudField error! Could not select WRAPPER for "${this.name}"`);
            }

            this.type = this.wrapper.attr('bp-field-type');
            this.$input = this.mainInput;
            this.input = this.$input[0];

            // Validate that the field has been found
            if(!this.input) {
                console.error(`CrudField error! Could not select INPUT for "${this.name}"`);
            }
        }

        get value() {
            let value = this.input.value;

            // Parse the value if it's a number
            if (value.length && !isNaN(value)) {
                value = Number(value);
            }

            return value;
        }

        get mainInput() {
            let input;

            // search input in ancestors
            input = this.wrapper.closest('[bp-field-main-input]');

            // search input in children
            if (input.length === 0) {
                input = this.wrapper.find('[bp-field-main-input]');
            }

            // if no bp-field-main-input has been declared in the field itself, try to find an input with that name inside wraper
            if (input.length === 0) {
                input = this.wrapper.find(`input[bp-field-name="${this.name}"], textarea[bp-field-name="${this.name}"], select[bp-field-name="${this.name}"]`).first();
            }

            // if nothing works, use the first input found in field wrapper.
            if(input.length === 0) {
                input = this.wrapper.find('input, textarea, select').first();
            }

            return input;
        }

        onChange(closure) {
            const bindedClosure = closure.bind(this);
            const fieldChanged = (event, values) => bindedClosure(this, event, values);

            if(this.isSubfield) {
                window.crud.subfieldsCallbacks = window.crud.subfieldsCallbacks ?? [];
                window.crud.subfieldsCallbacks[this.subfieldHolder] = window.crud.subfieldsCallbacks[this.subfieldHolder] ?? [];
                if(!window.crud.subfieldsCallbacks[this.subfieldHolder].some( callbacks => callbacks['fieldName'] === this.name )) {
                    window.crud.subfieldsCallbacks[this.subfieldHolder].push({fieldName: this.name, closure: closure, field: this});
                }
                return this;
            }

            this.input?.addEventListener('input', fieldChanged, false);
            this.$input.change(fieldChanged);

            return this;
        }

        change() {
            this.$input.trigger(`change`);
        }

        show(value = true) {
            this.wrapper.toggleClass('d-none', !value);
            this.$input.trigger(`backpack:field.${value ? 'show' : 'hide'}`);
            return this;
        }

        hide(value = true) {
            return this.show(!value);
        }

        enable(value = true) {
            this.$input.attr('disabled', !value && 'disabled');
            this.$input.trigger(`backpack:field.${value ? 'enable' : 'disable'}`);
            return this;
        }

        disable(value = true) {
            return this.enable(!value);
        }

        require(value = true) {
            this.wrapper.toggleClass('required', value);
            this.$input.trigger(`backpack:field.${value ? 'require' : 'unrequire'}`);
            return this;
        }

        unrequire(value = true) {
            return this.require(!value);
        }

        check(value = true) {
            this.wrapper.find('input[type=checkbox]').prop('checked', value).trigger('change');
            return this;
        }

        uncheck(value = true) {
            return this.check(!value);
        }

        subfield(name, rowNumber = false) {
            let subfield = new CrudField(name);
            if(!rowNumber) {
                subfield.isSubfield = true;
                subfield.subfieldHolder = this.name;
            }else{
                subfield.wrapper = $(`[data-repeatable-identifier="${this.name}"][data-row-number="${rowNumber}"]').find('[bp-field-wrapper][bp-field-name$="${name}"]`);
                subfield.input = subfield.wrapper.find(`[data-repeatable-input-name$="${name}"][bp-field-main-input]`);
                // if no bp-field-main-input has been declared in the field itself,
                // assume it's the first input in that wrapper, whatever it is
                if (subfield.input.length == 0) {
                    subfield.input = subfield.wrapper.find(`input[data-repeatable-input-name$="${name}"], textarea[data-repeatable-input-name$="${name}"], select[data-repeatable-input-name$="${name}"]`).first();
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
import Vue from 'vue';
import Selectize from 'vue2-selectize';

class FormErrors {
    constructor(errors = {}) {
        this.errors = errors;
    }

    add(field, error) {
        if (! this.has(field)) {
            this.errors[field] = [];
        }

        this.errors[field].push(error);
    }

    has(field) {
        return this.errors[field] !== undefined && this.errors[field].length > 0;
    }

    get(field) {
        if (! this.hasErrors(field)) {
            return false;
        }

        return this.errors[field];
    }

    clear(field) {
        if (this.hasErrors(field)) {
            delete this.errors[field];
        }
    }

    count() {
        return Object.keys(this.errors).length;
    }

    all() {
        return this.errors;
    }

    clear() {
        this.errors = {};
    }

}

new Vue({
    el: '#rcp-file-protector-options',
    components: {
        Selectize
    },

    data() {
        return {
            protectionLevels: window.RcpFileProtector['protectionLevels'] ? window.RcpFileProtector['protectionLevels'] : [],
            memberships: window.RcpFileProtector['memberships'] ? window.RcpFileProtector['memberships'] : [],
            membershipSelectSettings: {
                maxItems: null
            },
            errors: new FormErrors(
                window.RcpFileProtector['errors'] ?
                window.RcpFileProtector['errors'] : 
                []
            ),
            successMessage: window.RcpFileProtector['successMessage']
        }
    },

    methods: {
        addProtectionLevel() {
            this.protectionLevels.push({
                memberships: [],
                url: '',
                isRegex: false
            });
        },

        removeProtectionLevel(index) {
            if (confirm('Are you sure?')) {
                this.$delete(this.protectionLevels, index);
            }
        },

        validate() {
            this.errors.clear();

            this.protectionLevels.forEach((level, index) => {
                if (! level['memberships'] || level['memberships'].length == 0) {
                    this.errors.add('levels', `Protection level #${index + 1}: Memberships can't be empty.`)
                }

                if (! level['url']) {
                    this.errors.add('levels', `Protection level #${index + 1}: URL can't be empty.`)
                }

            });

            return this.errors.count() == 0;
        },

        onSubmit(e) {

            if (! this.validate()) {
                e.preventDefault();
            }
        }
    }
});
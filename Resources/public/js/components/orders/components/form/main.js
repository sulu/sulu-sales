/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([], function() {

    'use strict';

    var form = '#order-form',

        constants = {

        },

        setHeaderToolbar = function() {
            this.sandbox.emit('sulu.header.set-toolbar', {
                template: 'default'
            });
        },

        bindCustomEvents = function() {
            // delete contact
            this.sandbox.on('sulu.header.toolbar.delete', function() {
                this.sandbox.emit('sulu.salesorder.order.delete', this.options.data.id);
            }, this);

            // contact saved
            this.sandbox.on('sulu.salesorder.order.saved', function(data) {
//                this.options.data = data;
//                this.initContactData();
//                this.setFormData(data);
//                this.setHeaderBar(true);
            }, this);

            // contact save
            this.sandbox.on('sulu.header.toolbar.save', function() {
                this.submit();
            }, this);

            // back to list
            this.sandbox.on('sulu.header.back', function() {
                this.sandbox.emit('sulu.salesorder.order.list');
            }, this);

            // TODO desired deliverydate
            this.sandbox.on('husky.input.desired-delivery-date.initialized', function() {
                this.dfdDesiredDeliveryDate.resolve();
            }, this);
        },

        /**
         * Sets the title to the username
         * default title as fallback
         */
        setTitle = function() {
            var title = this.sandbox.translate('salesorder.orders.title'),
                breadcrumb = [
                    {title: 'navigation.sales'},
                    {title: 'salesorder.order', event: 'sulu.salesorder.orders.list'}
                ];

            if (!!this.options.data && !!this.options.data.id) {
                title += ' #'+this.options.data.id;
                breadcrumb.push({title: '#' + this.options.data.id});
            }

            this.sandbox.emit('sulu.header.set-title', title);
            this.sandbox.emit('sulu.header.set-breadcrumb', breadcrumb);
        },

        initForm = function(data) {
            var formObject = this.sandbox.form.create(form);
            formObject.initialized.then(function() {
                setFormData.call(this, data, true);
            }.bind(this));
        },

        setFormData = function(data) {
            // add collection filters to form
            this.sandbox.form.setData(form, data).then(function() {
                // TODO: resolve that form is set
            }.bind(this)).fail(function(error) {
                this.sandbox.logger.error("An error occured when setting data!", error);
            }.bind(this));
        },

        startFormComponents = function(data) {

            this.dfdDesiredDeliveryDate.resolve();
//                // starts form components
//                this.sandbox.start([
//                    {
//                        name: 'auto-complete@husky',
//                        options: {
//                            el: '#company',
//                            remoteUrl: '/admin/api/accounts?searchFields=name&flat=true&fields=id,name',
//                            resultKey: 'accounts',
//                            getParameter: 'search',
//                            value: !!data.account ? data.account : '',
//                            instanceName: this.companyInstanceName,
//                            valueName: 'name',
//                            noNewValues: true
//                        }
//                    }
//                ]);
        }
        ;

    return (function() {
        return {

            view: true,

            layout: {
//                sidebar: {
//                    width: 'fixed',
//                    cssClasses: 'sidebar-padding-50'
//                }
            },

            templates: ['/admin/order/template/order/form'],

            initialize: function() {
                this.saved = true;
                this.formId = form;
                this.dfdAllFieldsInitialized = this.sandbox.data.deferred();
                this.dfdDesiredDeliveryDate = this.sandbox.data.deferred();

                // define when all fields are initialized
                this.sandbox.data.when(this.dfdDesiredDeliveryDate).then(function() {
                    this.dfdAllFieldsInitialized.resolve();
                }.bind(this));

                setTitle.call(this);

                this.render();

                setHeaderToolbar.call(this);
                this.listenForChange();

//                if (!!this.options.data && !!this.options.data.id) {
//                    this.initSidebar(
//                        '/admin/widget-groups/contact-detail?contact=',
//                        this.options.data.id
//                    );
//                }

                this.setHeaderBar(true);
            },

            initSidebar: function(url, id) {
                this.sandbox.emit('sulu.sidebar.set-widget', url + id);
            },

            render: function() {
                this.sandbox.dom.html(this.$el, this.renderTemplate(this.templates[0]));

                var data = this.options.data;

                this.companyInstanceName = 'companyContact' + data.id;

                // start components
                startFormComponents.call(this, data);

                // initialize form
                initForm.call(this, data);

                // bind events
                bindCustomEvents.call(this);
            },

            submit: function() {
                this.sandbox.logger.log('save Model');

                if (this.sandbox.form.validate(form)) {
                    var data = this.sandbox.form.getData(form);

                    if (data.id === '') {
                        delete data.id;
                    }

                    // FIXME auto complete in mapper
                    // only get id, if auto-complete is not empty:

                    // TODO: contact + account
//                    data.account = {
//                        id: this.sandbox.dom.attr('#' + this.companyInstanceName, 'data-id')
//                    };

                    this.sandbox.logger.log('log data', data);
                    this.sandbox.emit('sulu.salesorder.order.save', data);
                }
            },

            // @var Bool saved - defines if saved state should be shown
            setHeaderBar: function(saved) {
                if (saved !== this.saved) {
                    var type = (!!this.options.data && !!this.options.data.id) ? 'edit' : 'add';
                    this.sandbox.emit('sulu.header.toolbar.state.change', type, saved, true);
                }
                this.saved = saved;
            },

//            /**
//             * Register events for editable drop downs
//             * @param instanceName
//             */
//            initializeDropDownListender: function(instanceName) {
//                var instance = 'husky.select.' + instanceName;
//                this.sandbox.on(instance + '.selected.item', function(id) {
//                    if (id > 0) {
//                        this.setHeaderBar(false);
//                    }
//                }.bind(this));
//                this.sandbox.on(instance + '.deselected.item', function() {
//                    this.setHeaderBar(false);
//                }.bind(this));
//            },

            // event listens for changes in form
            listenForChange: function() {
                // listen for change after TAGS and BIRTHDAY-field have been set
                this.sandbox.data.when(this.dfdAllFieldsInitialized).then(function() {

                    this.sandbox.dom.on('#order-form', 'change', function() {
                            this.setHeaderBar(false);
                        }.bind(this),
                            '.changeListener select, ' +
                            '.changeListener input, ' +
                            '.changeListener textarea');

                    this.sandbox.dom.on('#order-form', 'keyup', function() {
                            this.setHeaderBar(false);
                        }.bind(this),
                            '.changeListener select, ' +
                            '.changeListener input, ' +
                            '.changeListener textarea');

                }.bind(this));

            }
        };
    })();
});

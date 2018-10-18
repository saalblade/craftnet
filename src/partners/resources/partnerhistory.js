
(function (window, document, jQuery) {

    const apiMixin = {
        data() {
            return {
                currentRequest: null,
            }
        },
        computed: {
            requestPending() {
                return this.currentRequest !== null;
            }
        },
        methods: {
            cancelRequest() {
                if (this.currentRequest) {
                    this.currentRequest.abort();
                }
            },

            clearRequest() {
                this.currentRequest = null;
            },

            fetchHistory() {
                return new Promise((resolve, reject) => {
                    data = {partnerId: Craft.Partners.partnerId};

                    this.currentRequest = $.ajax({
                        url: Craft.getCpUrl() + '/partners/history/' + Craft.Partners.partnerId,
                        method: 'GET',
                        success: (data) => {
                            resolve(data.history);
                            this.clearRequest();
                        }
                    }).fail((jqXHR, textStatus, errorThrown) => {
                        Craft.cp.displayError('Unable to fetch Partner history');
                        reject();
                        this.clearRequest();
                    });
                });
            },

            deleteHistory(id) {
                let data = {
                    [Craft.csrfTokenName]: Craft.csrfTokenValue
                };

                return new Promise((resolve, reject) => {
                    this.currentRequest = $.ajax({
                        url: Craft.getCpUrl() + '/partners/history/' + id,
                        method: 'DELETE',
                        data,
                        success: (response) => {
                            Craft.cp.displayNotice('History message deleted');
                            resolve();
                        }
                    }).fail((jqXHR, textStatus, errorThrown) => {
                        if (errorThrown === 'abort') {
                            Craft.cp.displayError('Cancelled Partner history deletion');
                        } else {
                            Craft.cp.displayError('Unable to delete Partner history: ' + errorThrown);
                        }
                        reject();
                    }).always((jqXHR, textStatus) => {
                        this.clearRequest();
                    });
                });
            },

            /**
             * Saves a history.
             * ```
             * history = {
             *   id: [number] // optional, new message if no id
             *   message: [string], // required
             *   partnerId: [number], // required if no `id`
             * }
             * ```
             * @param {object} history History object
             */
            saveHistory(history) {
                let data = Object.assign(history, {
                    [Craft.csrfTokenName]: Craft.csrfTokenValue
                });

                return new Promise((resolve, reject) => {
                    this.currentRequest = $.ajax({
                        url: Craft.getCpUrl() + '/partners/history',
                        method: 'POST',
                        data,
                        success: (response) => {
                            if (!response.success) {
                                Craft.cp.displayError('Validation error while saving Partner history.');
                                reject(response.errors);
                                return;
                            }

                            Craft.cp.displayNotice('History message saved');
                            resolve(response.history);
                        }
                    }).fail((jqXHR, textStatus, errorThrown) => {
                        if (errorThrown === 'abort') {
                            Craft.cp.displayError('Cancelled Partner history note');
                        } else {
                            Craft.cp.displayError('Unable to save Partner history: ' + errorThrown);
                        }
                        reject();
                    }).always((jqXHR, textStatus) => {
                        this.clearRequest();
                    });
                });
            }
        }
    }

    const HistoryItem = {
        name: 'HistoryItem',
        props: ['value', 'index'],
        mixins: [apiMixin],
        template: `
            <div>
                <div class="partner-history__message"
                    v-if="!isEditing"
                    :class="{'partner-history__message--canEdit': canEdit()}"
                    @click="edit">
                    {{ value.message }}
                </div>
                <div class="partner-history__message-editor" v-if="isEditing">
                    <textarea v-model="editText" :disabled="requestPending"></textarea>
                    <div class="partner-history__button-wrap">
                        <button @click.prevent="cancel" class="btn small">Cancel</button>
                        <button @click.prevent="deleteMe" class="btn red small">Delete</button>
                        <button v-if="!requestPending" @click.prevent="update" class="btn small">Update</button>
                        <span v-show="requestPending" class="spinner"></span>
                    </div>
                </div>
                <div class="partner-history__meta">
                    {{ dateCreated }}
                    <span v-if="value.authorName">by {{ value.authorName }}</span>
                </div>
            </div>
        `,
        data() {
            return {
                editText: '',
                isEditing: false
            }
        },
        methods: {
            cancel() {
                this.isEditing = false;
                this.clearRequest();
            },
            canEdit() {
                return Craft.Partners.currentUserId === this.value.authorId;
            },
            deleteMe() {
                if (!confirm('Are you sure?')) {
                    return;
                }

                this.deleteHistory(this.value.id)
                    .then(() => {
                        this.isEditing = false;
                        this.$emit('deleted', {
                            index: this.index
                        });
                    })
            },
            edit() {
                if (this.canEdit()) {
                    this.isEditing = true;
                    this.editText.trim().length || (this.editText = this.value.message);
                }
            },
            update() {
                this.saveHistory({
                    id: this.value.id,
                    message: this.editText,
                    partnerId: Craft.Partners.partnerId
                }).then((history) => {
                    this.editText = '';
                    this.$emit('updated', {
                        index: this.index,
                        history,
                    });
                }).finally(() => {
                    this.editText = '';
                    this.isEditing = false;
                });
            }
        },
        computed: {
            dateCreated() {
                return Craft.formatDate(new Date(this.value.dateCreated.date));
            }
        }
    }

    /**
     * History is a mini-app separate from the entry form.
     * Major browsers support ES6 and this is for internal use so
     * we'll go with Vue.
     */
    const App = {
        name: 'App',
        components: {HistoryItem},
        mixins: [apiMixin],
        template: `
            <div>
                <h3 v-if="history.length">History</h3>
                <div v-if="!isEditing" class="partner-history__button-wrap">
                    <button class="btn add icon" @click.prevent.stop="isEditing = true">Add Note</button>
                </div>
                <div v-if="isEditing">
                    <textarea v-model="newMessage" :disabled="requestPending"></textarea>
                    <div class="partner-history__button-wrap">
                        <button class="btn"
                            @click.prevent.stop="cancel">
                            Cancel
                        </button>
                        <button class="btn"
                            v-show="!requestPending"
                            @click.prevent.stop="saveNewHistory"
                            :disabled="!newMessage.trim()">
                            Post
                        </button>
                        <span v-show="requestPending" class="spinner"></span>
                    </div>
                </div>
                <div v-for="(item, index) in history" class="partner-history__item">
                    <history-item :value="item" :index="index" @updated="onItemUpdated" @deleted="onItemDeleted"></history-item>
                </div>
            </div>
        `,
        data() {
            return {
                history: [],
                isEditing: false,
                newMessage: '',
            };
        },
        mounted() {
            this.fetchHistory().then((history) => {
                this.history = history;
            });
        },
        methods: {
            cancel() {
                this.isEditing = false;
                this.cancelRequest();
            },
            onItemDeleted(data) {
                this.isEditing = false;
                this.$delete(this.history, data.index);
            },
            onItemUpdated(data) {
                Vue.set(this.history, data.index, data.history);
            },
            saveNewHistory() {
                this.saveHistory({
                    message: this.newMessage,
                    partnerId: Craft.Partners.partnerId
                }).then((history) => {
                    this.history.unshift(history);
                    this.newMessage = '';
                    this.isEditing = false;
                })
            }
        }
    };

    $(function() {
        let el = document.getElementById('partnerHistoryApp');

        if (el) {
            new Vue({
                render: h => h(App),
            }).$mount(el);
        }
    });

})(window, document, jQuery);

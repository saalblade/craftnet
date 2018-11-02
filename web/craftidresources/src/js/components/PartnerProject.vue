<template>
    <div>
        <div class="card mb-4">
            <div class="card-body">
                <div class="flex">
                    <ul class="flex-1 list-reset">
                        <li v-if="project.name"><strong>{{ project.name }}</strong></li>
                        <li v-if="project.role">{{ project.role }}</li>
                        <li v-if="project.url">{{ project.url }}</li>
                        <pre>{{ project.screenshots }}</pre>
                    </ul>
                    <div>
                        <button class="btn btn-secondary" @click="$emit('edit', index)"><i class="fa fa-pencil-alt"></i> Edit</button>
                    </div>
                </div>
            </div>
        </div>
        <modal v-if="isEditing" :show="isEditing" transition="fade" modal-type="wide" >
            <div slot="body" class="p-4">
                <text-field id="name" label="Project Name" v-model="project.name" :errors="localErrors.name" placeholder="Main Office" />
                <text-field id="role" label="Role" v-model="project.role" :errors="localErrors.role" />
                <text-field id="url" label="URL" v-model="project.url" :errors="localErrors.url" />

                <div class="mt-4 flex">
                    <div class="flex-1">
                        <button
                            class="btn btn-secondary"
                            :class="{disabled: requestPending}"
                            :disabled="requestPending"
                            @click="$emit('cancel', index)">Cancel</button>

                        <button
                            class="btn btn-primary"
                            :class="{disabled: requestPending}"
                            :disabled="requestPending"
                            @click="$emit('save')">Save</button>

                        <div class="spinner" :class="{'invisible': !requestPending}"></div>
                    </div>
                    <div>
                        <button
                            v-if="project.id !== 'new'"
                            class="btn btn-danger"
                            :class="{disabled: requestPending}"
                            :disabled="requestPending"
                            @click="$emit('delete', index)">Delete</button>
                    </div>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    import Modal from '../components/Modal'
    import TextField from '../components/fields/TextField'

    export default {
        props: ['index', 'project', 'editIndex', 'requestPending', 'errors'],

        components: {
            Modal,
            TextField,
        },

        data() {
            return {
                draft: {},
            }
        },

        computed: {
            isEditing() {
                this.draft = this.simpleClone
                return this.editIndex === this.index
            },
            localErrors() {
                // this.errors could be 'undefined'
                return this.errors || {}
            }
        },

        mounted() {
            // go straight to the modal form after clicking
            // "Add New Project" button
            if (this.project.id === 'new') {
                this.$emit('edit', this.index)
            }
        },
    }
</script>

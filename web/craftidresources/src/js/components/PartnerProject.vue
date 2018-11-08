<template>
    <div>
        <div class="card mb-4">
            <div v-show="!isEditing" class="card-body">
                <div class="flex">
                    <ul class="flex-1 list-reset">
                        <li v-if="project.name"><strong>{{ project.name }}</strong></li>
                        <li v-if="project.role">{{ project.role }}</li>
                        <li v-if="project.url">{{ project.url }}</li>
                        <li v-if="project.screenshots.length" class="mt-3"><strong>Screenshots</strong></li>
                        <li class="flex">
                            <div v-for="(screenshot, index) in project.screenshots" :key="index" class="p-1 mt-2 inline-block bg-grey-lightest flex align-middle justify-center" style="height: 150px; width: 150px;">
                                <img :src="screenshot.url" style="max-width: 100%; max-height: 100%;">
                            </div>
                        </li>
                    </ul>
                    <div>
                        <button class="btn btn-secondary" @click="$emit('edit', index)"><i class="fa fa-pencil-alt"></i> Edit</button>
                    </div>
                </div>
            </div>
        </div>
        <modal v-if="isEditing" :show="isEditing" transition="fade" modal-type="wide" style="max-height: 100vh; overflow: scroll;">
            <div slot="body" class="p-4">
                <text-field id="name" label="Project Name" v-model="project.name" :errors="localErrors.name" />
                <text-field id="role" label="Role" instructions="e.g. “Craft Commerce with custom Hubspot integration” or “Design and custom plugin development”. Max 55 characters." v-model="project.role" :errors="localErrors.role" />
                <text-field id="url" label="URL" v-model="project.url" :errors="localErrors.url" />

                <label>Screenshots</label>
                <p class="instructions">Up to five screenshots will be displayed. 12:7 aspect ratio. 1200px wide will do. Drag to re-order.</p>

                <draggable v-model="project.screenshots">
                    <div v-for="(screenshot, index) in project.screenshots" :key="index" class="screenshot mt-6">
                        <img :src="screenshot.url" class="img-thumbnail mr-3 mb-3" style="max-width: 200px; max-height: 200px;" />
                        <a href="#" class="remove btn btn-sm btn-danger" @click.prevent="removeScreenshot(index);">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </draggable>

                <!-- JPEG with 12x7 1200 x 700 -->

                <div v-if="project.screenshots.length <= 5">
                    <input type="file" accept=".jp2,.jpeg,.jpg,.jpx" @change="screenshotFileChange" ref="screenshotFiles" class="hidden" multiple=""><br>
                    <button class="btn btn-sm btn-outline-secondary" @click="$refs.screenshotFiles.click()" :disabled="isUploading">
                        <span v-show="!isUploading"><i class="fa fa-plus"></i> Add screenshots</span>
                        <span v-show="isUploading">Uploading: {{ uploadProgress }}%</span>
                        <span v-show="isUploading" class="spinner"></span>
                    </button>
                </div>

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
    import axios from 'axios'
    import draggable from 'vuedraggable'
    import Modal from '../components/Modal'
    import TextField from '../components/fields/TextField'

    export default {
        props: ['index', 'project', 'editIndex', 'requestPending', 'errors'],

        components: {
            draggable,
            Modal,
            TextField,
        },

        data() {
            return {
                uploadProgress: 0,
                isUploading: false
            }
        },

        computed: {
            isEditing() {
                return this.editIndex === this.index
            },
            localErrors() {
                // this.errors could be 'undefined'
                return this.errors || {}
            },
        },

        methods: {
            removeScreenshot(index) {
                this.project.screenshots.splice(index, 1);
            },

            screenshotFileChange(event) {
                let formData = new FormData()

                for( var i = 0; i < event.target.files.length; i++ ){
                    formData.append('screenshots[]', event.target.files[i]);
                }

                this.isUploading = true

                axios.post(Craft.actionUrl + '/craftnet/partners/upload-screenshots', formData, {
                    headers: {
                        'X-CSRF-Token': Craft.csrfTokenValue,
                    },
                    onUploadProgress: (event) => {
                        this.uploadProgress = Math.round(event.loaded / event.total * 100)
                    }
                }).then(response => {
                    this.isUploading = false
                    this.$root.displayNotice('Uploaded')

                    let screenshots = response.data.screenshots || []

                    for (let i in screenshots) {
                        this.project.screenshots.push(screenshots[i])
                    }
                }).catch(error => {
                    this.isUploading = false
                    this.$root.displayNotice(error)
                });
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

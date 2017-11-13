<template>
    <div class="mb-3">
        <template v-if="!pluginId && !this.pluginDraft.repository">
            <div class="card">
                <div class="card-body">
                    <template v-if="connectedAppsCount > 0">
                        <h2>Choose a repository</h2>

                        <div v-for="app, appHandle in apps" class="mb-3">
                            <repositories :appHandle="appHandle" @selectRepository="onSelectRepository"></repositories>
                        </div>

                        <div>
                            <router-link to="/account/settings#connected-apps" class="btn btn-secondary">Manage connected apps</router-link>
                        </div>
                    </template>
                    <template v-else="">
                        <h2>Connect</h2>
                        <p>Connect to GitHub or Bitbucket to retrieve your repositories.</p>

                        <connected-apps></connected-apps>
                    </template>
                </div>
            </div>
        </template>

        <template v-else>
            <form @submit.prevent="save()">
                <div class="card mb-3">
                    <div class="card-header">GitHub Repository</div>
                    <div class="card-body">
                        <div class="d-flex flex-row">
                            <div class="flex-grow">
                                <text-field id="repository" label="Repository URL" v-model="pluginDraft.repository" :errors="errors.repository" />
                            </div>

                            <template v-if="!pluginId">
                                <div class="form-group ml-2">
                                    <label>&nbsp;</label>
                                    <input type="button" class="btn btn-secondary form-control" :disabled="!pluginDraft.repository" @click="loadDetails()" value="Load details">
                                </div>
                                <div class="spinner repository-spinner" :class="{'d-none': !repositoryLoading}"></div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">Plugin Icon</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-2">

                                <div class="form-group">
                                    <img :src="pluginDraft.iconUrl" height="80" />
                                </div>
                            </div>
                            <div class="col-sm-10">
                                <div class="form-group">
                                    <input type="file" ref="iconFile" class="form-control" @change="changeIcon" :class="{'is-invalid': errors.iconId }" />
                                    <div class="invalid-feedback" v-for="error in errors.iconId">{{ error }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">Plugin Details</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <text-field id="name" label="Name" v-model="pluginDraft.name" :errors="errors.name" @input="onInputName" />
                            </div>
                            <div class="col-sm-6">
                                <text-field id="packageName" label="Package Name" v-model="pluginDraft.packageName" :errors="errors.packageName" />
                            </div>
                            <div class="col-sm-6">
                                <text-field id="handle" label="Plugin Handle" v-model="pluginDraft.handle" :errors="errors.handle" />
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="license">License</label>

                                    <select id="license" class="form-control" v-model="pluginDraft.license">
                                        <option value="craft">Craft</option>
                                        <option value="mit">MIT</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <text-field id="shortDescription" label="Short Description" v-model="pluginDraft.shortDescription" :errors="errors.shortDescription" />
                        <textarea-field id="longDescription" label="Long Description" v-model="pluginDraft.longDescription" :errors="errors.longDescription" rows="16" />
                        <text-field id="documentationUrl" label="Documentation URL" v-model="pluginDraft.documentationUrl" :errors="errors.documentationUrl" />
                        <text-field id="changelogUrl" label="Changelog URL" v-model="pluginDraft.changelogUrl" :errors="errors.changelogUrl" />
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">Screenshots</div>
                    <div class="card-body">
                        <div class="form-group">
                            <input type="file" ref="screenshotFiles" class="form-control" multiple="">
                        </div>

                        <div ref="screenshots" class="d-inline">
                            <div v-for="(screenshotUrl, key) in pluginDraft.screenshotUrls" class="screenshot">
                                <img :src="screenshotUrl" class="img-thumbnail mr-3 mb-3" />
                                <a href="#" class="remove btn btn-sm btn-danger" @click.prevent="removeScreenshot(key);">
                                    <i class="fa fa-remove"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="userIsInGroup('staff')" class="card mb-3">
                    <div class="card-header">Pricing</div>
                    <div class="card-body">
                        <text-field id="price" label="License Price" v-model="pluginDraft.price" :errors="errors.price" />
                        <text-field id="renewalPrice" label="Renewal Price" v-model="pluginDraft.renewalPrice" :errors="errors.renewalPrice" />
                    </div>
                </div>

                <div>
                    <input type="submit" class="btn btn-primary" value="Save" :disabled="loading" />
                    <div v-if="loading" class="spinner"></div>
                </div>
            </form>
        </template>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import TextField from '../components/fields/TextField'
    import TextareaField from '../components/fields/TextareaField'
    import ConnectedApps from '../components/ConnectedApps'
    import Repositories from '../components/Repositories'
    import slug from 'limax';

    export default {

        components: {
            TextField,
            TextareaField,
            ConnectedApps,
            Repositories,
        },

        data() {
            return {
                loading: false,
                repositoryLoading: false,
                pluginDraft: {
                    id: null,
                    icon: null,
                    iconId: null,
                    developerId: null,
                    handle: '',
                    packageName: '',
                    name: '',
                    shortDescription: '',
                    longDescription: '',
                    documentationUrl: '',
                    changelogUrl: '',
                    repository: '',
                    license: 'craft',
                    price: 0,
                    renewalPrice: 0,
                    iconUrl: null,
                    categoryIds: [],
                    screenshots: [],
                    screenshotIds: [],
                    screenshotUrls: [],
                },
                errors: {},
            }
        },

        computed: {

            ...mapGetters({
                apps: 'apps',
                plugins: 'plugins',
                userIsInGroup: 'userIsInGroup',
            }),

            pluginId() {
                return this.$route.params.id;
            },

            plugin() {
                return this.plugins.find(p => p.id == this.pluginId);
            },

            connectedAppsCount() {
                return Object.keys(this.apps).length;
            },

        },

        methods: {

            onInputName(name) {
                if(!this.pluginId) {
                    const handle = slug(name);
                    this.pluginDraft.handle = handle;
                }
            },

            onSelectRepository(repository) {
                this.pluginDraft.repository = repository.html_url;
                this.loadDetails();
            },

            removeScreenshot(key) {
                this.pluginDraft.screenshotUrls.splice(key, 1);
                this.pluginDraft.screenshotIds.splice(key, 1);

                let removeBtns = this.$refs.screenshots.getElementsByClassName('btn');

                for (let i = 0; i < removeBtns.length; i++) {
                    removeBtns[i].blur();
                }
            },

            changeScreenshots(ev) {
                this.pluginDraft.screenshotUrls = [];

                let files = this.$refs.screenshotFiles.files;

                for(let i = 0; i < files.length; i++) {
                    let reader = new FileReader();

                    reader.onload = function (e) {
                        let screenshotUrl = e.target.result;
                        this.pluginDraft.screenshotUrls.push(screenshotUrl)
                    }.bind(this);

                    reader.readAsDataURL(files[i]);
                }
            },

            changeIcon(ev) {
                this.pluginDraft.icon = ev.target.value;

                let reader = new FileReader();

                reader.onload = function (e) {
                    this.pluginDraft.iconUrl = e.target.result
                }.bind(this);

                reader.readAsDataURL(this.$refs.iconFile.files[0]);
            },

            loadDetails() {
                this.repositoryLoading = true;

                let body = {
                    repository: encodeURIComponent(url)
                };
                body['action'] = 'craftcom/plugins/load-details';
                body[csrfTokenName] = csrfTokenValue;

                let options = { emulateJSON: true };

                let url = this.pluginDraft.repository;

                this.$http.post(window.craftActionUrl+'/craftcom/plugins/load-details&repository='+encodeURIComponent(url), body, options)
                    .then(response => {
                        this.pluginDraft.changelogUrl = response.body.changelogUrl;
                        this.pluginDraft.documentationUrl = response.body.documentationUrl;
                        this.pluginDraft.name = response.body.name;
                        this.pluginDraft.handle = response.body.handle;
                        this.pluginDraft.shortDescription = response.body.shortDescription;
                        this.pluginDraft.longDescription = response.body.longDescription;
                        this.pluginDraft.packageName = response.body.packageName;
                        this.pluginDraft.iconId = response.body.iconId;
                        this.pluginDraft.iconUrl = response.body.iconUrl;
                        this.repositoryLoading = false;
                    })
                    .catch(response => {
                        this.repositoryLoading = false;
                    });
            },

            save() {
                this.loading = true;

                let formData = new FormData();
                formData.append('siteId', 1);
                formData.append('enabled', 1);

                if(this.pluginDraft.id) {
                    formData.append('pluginId', this.pluginDraft.id);
                }

                formData.append('iconId[]', parseInt(this.pluginDraft.iconId));
                formData.append('icon', this.$refs.iconFile.files[0]);
                formData.append('handle', this.pluginDraft.handle);
                formData.append('packageName', this.pluginDraft.packageName);
                formData.append('name', this.pluginDraft.name);
                formData.append('shortDescription', this.pluginDraft.shortDescription);
                formData.append('longDescription', this.pluginDraft.longDescription);
                formData.append('documentationUrl', this.pluginDraft.documentationUrl);
                formData.append('changelogUrl', this.pluginDraft.changelogUrl);
                formData.append('repository', this.pluginDraft.repository);
                formData.append('license', this.pluginDraft.license);

                if(!this.pluginDraft.price) {
                    this.pluginDraft.price = 0;
                }

                formData.append('price', this.pluginDraft.price);

                if(!this.pluginDraft.renewalPrice) {
                    this.pluginDraft.renewalPrice = 0;
                }

                formData.append('renewalPrice', this.pluginDraft.renewalPrice);

                if(this.pluginDraft.categoryIds.length > 0) {
                    this.pluginDraft.categoryIds.forEach(categoryId => {
                        formData.append('categoryIds[]', categoryId);
                    });
                } else {
                    formData.append('categoryIds', '');
                }

                if(this.$refs.screenshotFiles.files.length > 0) {
                    for (let i = 0; i < this.$refs.screenshotFiles.files.length; i++) {
                        formData.append('screenshots[]', this.$refs.screenshotFiles.files[i]);
                    }
                }

                if(this.pluginDraft.screenshotUrls.length > 0) {
                    this.pluginDraft.screenshotUrls.forEach(screenshotUrl => {
                        formData.append('screenshotUrls[]', screenshotUrl);
                    });
                }

                if(this.pluginDraft.screenshotIds.length > 0) {
                    this.pluginDraft.screenshotIds.forEach(screenshotId => {
                        formData.append('screenshotIds[]', screenshotId);
                    });
                } else {
                    formData.append('screenshotIds', '');
                }

                this.$store.dispatch('savePlugin', formData).then((data) => {
                    this.loading = false;
                    this.$root.displayNotice('Plugin saved.');
                    this.$router.push({path: '/developer/plugins'});
                }).catch((data) => {
                    console.log('error!');
                    this.loading = false;
                    this.$root.displayError('Couldn’t save plugin.');
                    this.errors = (data.errors ? data.errors : []);
                });
            }
        },

        mounted() {
            if(this.plugin) {
                this.pluginDraft = JSON.parse(JSON.stringify(this.plugin));
            }
        },

    }
</script>

<style scoped>
    .d-flex { position: relative; }

    .repository-spinner { position: absolute; top: 36px; right: -24px; }

    .screenshot { position: relative; display: inline-block; width: 230px; margin-right:24px; margin-top: 14px; }
    .screenshot .remove { position: absolute; top: -10px; right: -10px; }
    .screenshot img {  }
</style>
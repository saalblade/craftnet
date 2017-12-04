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
            <div v-if="plugin && !plugin.enabled" role="alert" class="alert alert-secondary">
                <template v-if="plugin.pendingApproval">
                    Your plugin is being reviewed, it will be automatically published once it’s approved.
                </template>
                <template v-else>
                    <template v-if="plugin.lastHistoryNote && plugin.lastHistoryNote.devComments">
                        <h6>Changes requested</h6>
                        <div v-html="plugin.lastHistoryNote.devComments"></div>
                        <a @click.prevent="submit()" href="#" class="btn btn-secondary btn-sm">Re-submit for Approval</a>
                    </template>
                    <template v-else>
                        <a @click.prevent="submit()" href="#" class="btn btn-secondary btn-sm">Submit for Approval</a>
                    </template>


                    <span class="text-secondary">Your plugin will be automatically published once it’s approved.</span>
                </template>
                <div v-if="pluginSubmitLoading" class="spinner"></div>
            </div>

            <form @submit.prevent="save()">
                <div class="card mb-3">
                    <div class="card-header">GitHub Repository</div>
                    <div class="card-body">
                        <div class="d-flex flex-row">
                            <div class="flex-grow">
                                <text-field id="repository" label="Repository URL" v-model="pluginDraft.repository" :errors="errors.repository" disabled="true" />
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
                                <text-field id="packageName" label="Package Name" v-model="pluginDraft.packageName" :errors="errors.packageName" disabled="true" />
                            </div>
                            <div class="col-sm-6">
                                <text-field id="handle" label="Plugin Handle" v-model="pluginDraft.handle" :errors="errors.handle" disabled="true" />
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
                        <p class="text-secondary"><small>Styling with Markdown is supported.</small></p>
                        <text-field id="documentationUrl" label="Documentation URL" v-model="pluginDraft.documentationUrl" :errors="errors.documentationUrl" />
                        <text-field id="changelogPath" label="Changelog Path" instructions="The path to your changelog file, relative to the repository root." v-model="pluginDraft.changelogPath" :errors="errors.changelogPath" />
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">Screenshots</div>
                    <div class="card-body">
                        <div class="form-group">
                            <input type="file" ref="screenshotFiles" class="form-control" multiple="">
                        </div>

                        <div ref="screenshots" class="d-inline">

                            <draggable v-model="screenshots">
                                <div v-for="(screenshot, key) in screenshots" class="screenshot">
                                    <img :src="screenshot.url" class="img-thumbnail mr-3 mb-3" />
                                    <a href="#" class="remove btn btn-sm btn-danger" @click.prevent="removeScreenshot(key);">
                                        <i class="fa fa-remove"></i>
                                    </a>
                                </div>
                            </draggable>

                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">Pricing</div>
                    <div class="card-body">
                        <text-field id="price" label="License Price" v-model="pluginDraft.price" :errors="errors.price" />
                        <text-field id="renewalPrice" label="Renewal Price" v-model="pluginDraft.renewalPrice" :errors="errors.renewalPrice" />

                        <p class="text-secondary"><em>All plugins are free until Craft 3 GA is released.</em></p>
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
    import slug from 'limax'
    import draggable from 'vuedraggable'
    import axios from 'axios'
    import qs from 'qs'

    export default {

        components: {
            TextField,
            TextareaField,
            ConnectedApps,
            Repositories,
            draggable
        },

        data() {
            return {
                loading: false,
                pluginSubmitLoading: false,
                repositoryLoading: false,
                pluginDraft: {
                    id: null,
                    icon: null,
                    iconId: null,
                    developerId: null,
                    enabled: false,
                    handle: '',
                    packageName: '',
                    name: '',
                    shortDescription: '',
                    longDescription: '',
                    documentationUrl: '',
                    changelogPath: '',
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

            screenshots: {
                get() {
                    let screenshots = [];

                    this.pluginDraft.screenshotIds.forEach((screenshotId, index) => {
                        let screenshot = {
                            id: screenshotId,
                            url: this.pluginDraft.screenshotUrls[index],
                        };
                        screenshots.push(screenshot);
                    });

                    return screenshots;
                },

                set(screenshots) {
                    let screenshotIds = [];
                    let screenshotUrls = [];

                    screenshots.forEach(screenshot => {
                        screenshotIds.push(screenshot.id);
                        screenshotUrls.push(screenshot.url);
                    });

                    this.pluginDraft.screenshotIds = screenshotIds;
                    this.pluginDraft.screenshotUrls = screenshotUrls;
                }
            }

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
                body[Craft.csrfTokenName] = Craft.csrfTokenValue;

                let params = qs.stringify(body);
                let url = this.pluginDraft.repository;

                axios.post(Craft.actionUrl+'/craftcom/plugins/load-details&repository='+encodeURIComponent(url), params)
                    .then(response => {
                        this.pluginDraft.changelogPath = response.data.changelogPath;
                        this.pluginDraft.documentationUrl = response.data.documentationUrl;
                        this.pluginDraft.name = response.data.name;
                        this.pluginDraft.handle = response.data.handle;
                        this.pluginDraft.shortDescription = response.data.shortDescription;
                        this.pluginDraft.longDescription = response.data.longDescription;
                        this.pluginDraft.packageName = response.data.packageName;
                        this.pluginDraft.iconId = response.data.iconId;
                        this.pluginDraft.iconUrl = response.data.iconUrl;
                        this.pluginDraft.license = response.data.license;
                        this.repositoryLoading = false;
                    })
                    .catch(response => {
                        this.repositoryLoading = false;
                    });
            },

            save() {
                this.loading = true;

                let plugin = {
                    iconId: [parseInt(this.pluginDraft.iconId)],
                    icon: this.$refs.iconFile.files[0],
                    handle: this.pluginDraft.handle,
                    packageName: this.pluginDraft.packageName,
                    name: this.pluginDraft.name,
                    shortDescription: this.pluginDraft.shortDescription,
                    longDescription: this.pluginDraft.longDescription,
                    documentationUrl: this.pluginDraft.documentationUrl,
                    changelogPath: this.pluginDraft.changelogPath,
                    repository: this.pluginDraft.repository,
                    license: this.pluginDraft.license,
                    price: this.pluginDraft.price,
                    renewalPrice: this.pluginDraft.renewalPrice,
                    categoryIds: '',
                    screenshotIds: '',
                };

                if(this.pluginDraft.id) {
                    plugin.pluginId = this.pluginDraft.id;
                }

                if(this.pluginDraft.categoryIds.length > 0) {
                    plugin.categoryIds = this.pluginDraft.categoryIds;
                }

                if(this.$refs.screenshotFiles.files.length > 0) {
                    plugin.screenshots = this.$refs.screenshotFiles.files;
                }

                if(this.pluginDraft.screenshotUrls.length > 0) {
                    plugin.screenshotUrls = this.pluginDraft.screenshotUrls;
                }

                if(this.pluginDraft.screenshotIds.length > 0) {
                    plugin.screenshotIds = this.pluginDraft.screenshotIds;
                }

                this.$store.dispatch('savePlugin', {plugin}).then(response => {
                    this.loading = false;
                    this.$root.displayNotice('Plugin saved.');
                    this.$router.push({path: '/developer/plugins'});
                }).catch(response => {
                    this.loading = false;
                    this.$root.displayError('Couldn’t save plugin.');
                    this.errors = (data.errors ? data.errors : []);
                });
            },

            submit() {
                this.pluginSubmitLoading = true;
                this.$store.dispatch('submitPlugin', this.plugin.id).then(response => {
                    this.pluginSubmitLoading = false;
                    this.$root.displayNotice('Plugin submitted for approval.');
                }).catch(response => {
                    this.pluginSubmitLoading = false;
                    this.$root.displayError('Couldn’t submit plugin for approval.');
                })
            },
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

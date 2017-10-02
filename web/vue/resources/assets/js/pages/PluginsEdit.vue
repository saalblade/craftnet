<template>
    <div class="mb-3">

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
                            <div class="spinner" :class="{'d-none': !loading}"></div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Screenshots</div>
                <div class="card-body">
                    <div class="form-group">
                        <input type="file" ref="screenshotFiles" class="form-control" @change="changeScreenshots" multiple="">
                    </div>

                    <img v-for="screenshot in pluginDraft.screenshots" :src="screenshot" style="height: 150px;" class="img-thumbnail mr-3 mb-3" />
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
                                <input type="file" ref="iconFile" class="form-control" @change="changeIcon">
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
                            <text-field id="name" label="Name" v-model="pluginDraft.name" :errors="errors.name" />
                        </div>
                        <div class="col-sm-6">
                            <text-field id="packageName" label="Package Name" v-model="pluginDraft.packageName" :errors="errors.packageName" />
                        </div>
                        <div class="col-sm-6">
                            <text-field id="handle" label="Plugin Handle" v-model="pluginDraft.handle" :errors="errors.handle" />
                        </div>
                        <div class="col-sm-6">
                            <text-field id="developerId" label="Developer ID" v-model="pluginDraft.developerId" :errors="errors.developerId" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Categories</h6>
                        <div v-for="category in categories">
                            <input type="checkbox" :id="'category-'+category.id" :value="category.id" v-model="pluginDraft.categoryIds" /> <label :for="'category-'+category.id">{{ category.title }}</label>
                        </div>
                    </div>

                    <text-field id="shortDescription" label="Short Description" v-model="pluginDraft.shortDescription" :errors="errors.shortDescription" />
                    <textarea-field id="longDescription" label="Long Description" v-model="pluginDraft.longDescription" :errors="errors.longDescription" rows="16" />
                    <text-field id="documentationUrl" label="Documentation URL" v-model="pluginDraft.documentationUrl" :errors="errors.documentationUrl" />
                    <text-field id="changelogUrl" label="Changelog URL" v-model="pluginDraft.changelogUrl" :errors="errors.changelogUrl" />

                    <div class="form-group">
                        <label for="license">License</label>

                        <select id="license" class="form-control" v-model="pluginDraft.license">
                            <option value="craft">Craft</option>
                            <option value="mit">MIT</option>
                        </select>
                    </div>
                </div>
            </div>

            <div v-if="userIsInGroup('staff')" class="card mb-3">
                <div class="card-header">
                    Pricing
                </div>
                <div class="card-body">
                    <text-field id="price" label="License Price" v-model="pluginDraft.price" :errors="errors.price" />
                    <text-field id="renewalPrice" label="Renewal Price" v-model="pluginDraft.renewalPrice" :errors="errors.renewalPrice" />
                </div>
            </div>

            <input type="submit" class="btn btn-primary" value="Save" />
        </form>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import TextField from '../components/fields/TextField'
    import TextareaField from '../components/fields/TextareaField'

    export default {
        components: {
            TextField,
            TextareaField,
        },

        data() {
            return {
                loading: false,
                pluginDraft: {
                    id: null,
                    icon: null,
                    iconId: null,
                    developerId: null,
                    handle: null,
                    packageName: null,
                    name: null,
                    shortDescription: null,
                    longDescription: null,
                    documentationUrl: null,
                    changelogUrl: null,
                    repository: null,
                    license: 'craft',
                    price: 0,
                    renewalPrice: 0,
                    iconUrl: null,
                    categoryIds: [],
                    screenshotIds: [],
                    screenshots: [],
                },
                errors: {},
            }
        },

        computed: {
            ...mapGetters({
                plugins: 'plugins',
                categories: 'categories',
                userIsInGroup: 'userIsInGroup',
            }),
            pluginId() {
                return this.$route.params.id;
            },
            plugin() {
                return this.plugins.find(p => p.id == this.pluginId);
            }
        },

        methods: {
            changeScreenshots(ev) {
                this.pluginDraft.screenshots = [];

                let files = this.$refs.screenshotFiles.files;

                for(let i = 0; i < files.length; i++) {
                    let reader = new FileReader();

                    reader.onload = function (e) {
                        let screenshotUrl = e.target.result;
                        this.pluginDraft.screenshots.push(screenshotUrl)
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
                this.loading = true;

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
                        this.loading = false;
                    })
                    .catch(response => {
                        this.loading = false;
                    });
            },
            save() {
                let formData = new FormData();
                formData.append('siteId', 1);
                formData.append('enabled', 1);

                if(this.pluginDraft.id) {
                    formData.append('pluginId', this.pluginDraft.id);
                }
                formData.append('iconId[]', parseInt(this.pluginDraft.iconId));
                formData.append('icon', this.$refs.iconFile.files[0]);
                formData.append('developerId', [parseInt(this.pluginDraft.developerId)]);
                formData.append('handle', this.pluginDraft.handle);
                formData.append('packageName', this.pluginDraft.packageName);
                formData.append('name', this.pluginDraft.name);
                formData.append('shortDescription', this.pluginDraft.shortDescription);
                formData.append('longDescription', this.pluginDraft.longDescription);
                formData.append('documentationUrl', this.pluginDraft.documentationUrl);
                formData.append('changelogUrl', this.pluginDraft.changelogUrl);
                formData.append('repository', this.pluginDraft.repository);
                formData.append('license', this.pluginDraft.license);
                formData.append('price', this.pluginDraft.price);
                formData.append('renewalPrice', this.pluginDraft.renewalPrice);

                this.pluginDraft.categoryIds.forEach(categoryId => {
                    formData.append('categoryIds[]', categoryId);
                });

                formData.append('screenshots', this.$refs.screenshotFiles.files);
                formData.append('screenshotIds', '');


                this.$store.dispatch('savePlugin', formData).then((data) => {
                    this.$root.displayNotice('Plugin saved.');
                    this.$router.push({path: '/developer/plugins'})
                }).catch((data) => {
                    this.$root.displayError('Couldn’t save plugin.');
                    this.errors = data.errors;
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
    .d-flex {
        position: relative;
    }
.spinner {
    position: absolute;
    top: 36px;
    right: -24px;
}
</style>
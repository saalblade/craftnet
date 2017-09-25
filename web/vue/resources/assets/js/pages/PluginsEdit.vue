<template>
    <div class="mb-3">

        <form @submit.prevent="save()">
            <div class="d-flex flex-row">
                <div class="flex-grow">
                    <text-field id="repository" label="Repository URL" v-model="pluginDraft.repository" :errors="errors.repository" />
                </div>
                <div class="form-group ml-2">
                    <label>&nbsp;</label>
                    <input type="button" class="btn btn-primary form-control" :disabled="!pluginDraft.repository" @click="loadDetails()" value="Load details">
                </div>
                <div class="spinner" :class="{'d-none': !loading}"></div>
            </div>

            <text-field id="iconId" label="Icon ID" v-model="pluginDraft.iconId" :errors="errors.iconId" />
            <text-field id="developerId" label="Developer ID" v-model="pluginDraft.developerId" :errors="errors.developerId" />

            <text-field id="name" label="Name" v-model="pluginDraft.name" :errors="errors.name" />
            <text-field id="packageName" label="Package Name" v-model="pluginDraft.packageName" :errors="errors.packageName" />
            <text-field id="handle" label="Plugin Handle" v-model="pluginDraft.handle" :errors="errors.handle" />


            <div class="form-group">
                <label>Icon</label><br />
                <img :src="pluginDraft.iconUrl" height="32" />
            </div>

            <text-field id="shortDescription" label="Short Description" v-model="pluginDraft.shortDescription" :errors="errors.shortDescription" />
            <textarea-field id="longDescription" label="Long Description" v-model="pluginDraft.longDescription" :errors="errors.longDescription" rows="10" />
            <text-field id="documentationUrl" label="Documentation URL" v-model="pluginDraft.documentationUrl" :errors="errors.documentationUrl" />
            <text-field id="changelogUrl" label="Changelog URL" v-model="pluginDraft.changelogUrl" :errors="errors.changelogUrl" />

            <div class="form-group">
                <label for="license">License</label>

                <select id="license" class="form-control" v-model="pluginDraft.license">
                    <option value="craft">Craft</option>
                    <option value="mit">MIT</option>
                </select>
            </div>

            <text-field id="price" label="License Price" v-model="pluginDraft.price" :errors="errors.price" />
            <text-field id="renewalPrice" label="Renewal Price" v-model="pluginDraft.renewalPrice" :errors="errors.renewalPrice" />

            <input type="submit" class="btn btn-primary" value="Save">
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
                    price: null,
                    renewalPrice: null,
                },
                errors: {},
            }
        },

        computed: {
            ...mapGetters({
                plugins: 'plugins',
            }),
            pluginId() {
                return this.$route.params.id;
            },
            plugin() {
                return this.plugins.find(p => p.id == this.pluginId);
            }
        },

        methods: {
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
                        this.loading = false;
                    })
                    .catch(response => {
                        this.loading = false;
                    });
            },
            save() {
                this.$store.dispatch('savePlugin', {
                    id: this.pluginDraft.id,
                    iconId: [parseInt(this.pluginDraft.iconId)],
                    developerId: [parseInt(this.pluginDraft.developerId)],
                    handle: this.pluginDraft.handle,
                    packageName: this.pluginDraft.packageName,
                    name: this.pluginDraft.name,
                    shortDescription: this.pluginDraft.shortDescription,
                    longDescription: this.pluginDraft.longDescription,
                    documentationUrl: this.pluginDraft.documentationUrl,
                    changelogUrl: this.pluginDraft.changelogUrl,
                    repository: this.pluginDraft.repository,
                    license: this.pluginDraft.license,
                    price: this.pluginDraft.price,
                    renewalPrice: this.pluginDraft.renewalPrice,
                    categoryIds: '',
                    screenshotIds: '',
                }).then((data) => {
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
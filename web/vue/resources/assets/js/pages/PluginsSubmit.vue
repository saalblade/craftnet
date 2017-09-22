<template>
    <div v-if="plugin" class="mb-3">
        <form @submit.prevent="save()">
            <text-field id="name" label="Name" v-model="plugin.name" :errors="errors.name" />
            <text-field id="slug" label="Handle" v-model="plugin.slug" :errors="errors.slug" />
            <text-field id="shortDescription" label="Short Description" v-model="plugin.shortDescription" :errors="errors.shortDescription" />
            <textarea-field id="description" label="Description" v-model="plugin.description" :errors="errors.description" rows="10" />
            <text-field id="githubRepoUrl" label="GitHub Repository URL" v-model="plugin.githubRepoUrl" :errors="errors.githubRepoUrl" />
            <text-field id="licensePrice" label="License Price" v-model="plugin.licensePrice" :errors="errors.licensePrice" />
            <text-field id="renewalPrice" label="Update Price" v-model="plugin.renewalPrice" :errors="errors.renewalPrice" />

            <input type="submit" class="btn btn-primary" value="Save">
        </form>
    </div>
</template>

<script>
    import TextField from '../components/fields/TextField'
    import TextareaField from '../components/fields/TextareaField'

    export default {
        components: {
            TextField,
            TextareaField,
        },
        data() {
            return {
                errors: {},
                plugin: {
                    title: '',
                    slug: '',
                    shortDescription: '',
                    description: '',
                    githubRepoUrl: '',
                    licensePrice: '',
                    renewalPrice: '',
                }
            }
        },

        methods: {
            save() {
                this.$store.dispatch('savePlugin', {
                    name: this.plugin.name,
                    shortDescription: this.plugin.shortDescription,
                    description: this.plugin.description,
                    githubRepoUrl: this.plugin.githubRepoUrl,
                    licensePrice: this.plugin.licensePrice,
                    renewalPrice: this.plugin.renewalPrice,
                }).then((data) => {
                    this.$root.displayNotice('Plugin saved.');
                    this.$router.push({path: '/plugins'})
                }).catch((data) => {
                    this.$root.displayError('Couldn’t save plugin.');
                    this.errors = data.errors;
                });

            }
        },
    }
</script>

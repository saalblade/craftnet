<template>
    <div v-if="plugin" class="mb-3">

        <form @submit.prevent="save()">
            <text-field id="title" label="Name" v-model="pluginDraft.title" :errors="errors.title" />
            <text-field id="slug" label="Handle" v-model="pluginDraft.slug" :errors="errors.slug" />
            <text-field id="shortDescription" label="Short Description" v-model="pluginDraft.shortDescription" :errors="errors.shortDescription" />
            <textarea-field id="description" label="Description" v-model="pluginDraft.description" :errors="errors.description" rows="10" />
            <text-field id="githubRepoUrl" label="GitHub Repository URL" v-model="pluginDraft.githubRepoUrl" :errors="errors.githubRepoUrl" />
            <text-field id="price" label="License Price" v-model="pluginDraft.price" :errors="errors.price" />
            <text-field id="updatePrice" label="Update Price" v-model="pluginDraft.updatePrice" :errors="errors.updatePrice" />

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
                pluginDraft: {},
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
            save() {
                this.$store.dispatch('savePlugin', {
                    id: this.pluginDraft.id,
                    title: this.pluginDraft.title,
                    shortDescription: this.pluginDraft.shortDescription,
                    description: this.pluginDraft.description,
                    githubRepoUrl: this.pluginDraft.githubRepoUrl,
                    price: this.pluginDraft.price,
                    updatePrice: this.pluginDraft.updatePrice,
                }).then((data) => {
                    this.$root.displayNotice('Plugin saved.');
                    this.$router.push({path: '/plugins'})
                }).catch((data) => {
                    this.$root.displayError('Couldn’t save plugin.');
                    this.errors = data.errors;
                });
            }
        },

        mounted() {
            this.pluginDraft = JSON.parse(JSON.stringify(this.plugin));
        }
    }
</script>

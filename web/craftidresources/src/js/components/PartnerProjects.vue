<template>
    <div>
        <h4>Projects</h4>

        <p class="text-grey-darker">Add 5 projects.</p>

        <partner-project
            v-for="(project, index) in draftProjects"
            :project="project"
            :key="index"
            :index="index"
            :edit-index="editIndex"
            :request-pending="requestPending"
            :errors="errors[index]"
            @cancel="onCancel"
            @delete="onDelete"
            @edit="onEdit"
            @save="onSave"
        ></partner-project>

        <div class="pl-4">
            <button class="btn btn-secondary btn-sm" @click="onAddProjectClick"><i class="fa fa-plus"></i> Add a Project</button>
        </div>
    </div>
</template>

<script>
    import helpers from '../mixins/helpers'
    import PartnerProject from './PartnerProject'

    export default {
        props: ['partner'],

        mixins: [helpers],

        components: {
            PartnerProject,
        },

        data() {
            return {
                errors: [],
                draftProjects: [],
                draftProjectProps: [
                    'id',
                    'name',
                    'role',
                    'url',
                    'screenshots',
                ],
                editIndex: null,
                requestPending: false,
            }
        },

        methods: {
            onAddProjectClick() {
                this.draftProjects.push({
                    id: 'new',
                    name: '',
                    role: '',
                    url: '',
                    screenshots: [],
                })
            },

            cloneProjects(projects) {
                let clone = []

                for (let i = 0; i < projects.length; i++) {
                    const project = this.partner.projects[i]
                    clone.push(this.simpleClone(project, this.draftProjectProps))
                }

                return clone
            },

            onCancel() {
                // reset
                this.setDraftProjects()
                this.editIndex = null
            },

            onDelete(index) {
                if (this.draftProjects.length === 1) {
                    this.$root.displayError('Must have at least one project');
                    return;
                }

                // we can't splice `draftProjects` or the modal for the
                // spliced out project will disappear
                let projects = this.cloneProjects(this.draftProjects)
                projects.splice(index, 1)
                this.save(projects)
            },

            onEdit(index) {
                this.errors = []
                this.editIndex = index
            },

            onSave() {
                this.save(this.draftProjects);
            },

            save(projects) {
                this.requestPending = true

                this.$store.dispatch('patchPartnerProjects', projects)
                    .then(response => {
                        this.requestPending = false

                        if (!response.data.success) {
                            this.errors = response.data.errors.projects
                            this.$root.displayError('Validation errors')
                        } else {
                            this.setDraftProjects();
                            this.$root.displayNotice('Updated')
                            this.editIndex = null
                        }
                    })
                    .catch(errorMessage => {
                        this.$root.displayError(errorMessage)
                        this.requestPending = false
                    })
            },

            setDraftProjects() {
                this.draftProjects = this.cloneProjects(this.partner.projects)
            }
        },

        mounted() {
            this.setDraftProjects()
        },
    }
</script>

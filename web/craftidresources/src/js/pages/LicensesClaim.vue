<template>
    <div>
        <h1>Claim license</h1>

        <div class="card mb-4">
            <div class="card-body">
                <h3>Claim CMS license</h3>
                <p class="text-secondary">Attach a Craft CMS license to your Craft ID account.</p>

                <form @submit.prevent="claimCmsLicense()">
                    <!--
                    <div class="form-group">
                        <label for="licenseFile" class="block">Craft License Key File</label>
                        <input class="form-control" type="file" id="licenseFile" name="licenseFile" />
                    </div>
                    -->

                    <textarea-field id="cmsLicenseKey" class="mono" spellcheck="false" v-model="cmsLicenseKey" @input="cmsLicenseKeyChange" label="CMS License Key" rows="5" />
                    <input type="submit" class="btn btn-primary" value="Claim License">
                    <div class="spinner" v-if="cmsLicenseLoading"></div>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h3>Claim Plugin license</h3>
                <p class="text-secondary">Attach a plugin license to your Craft ID account.</p>

                <form @submit.prevent="claimPluginLicense()">
                    <text-field id="pluginLicenseKey" class="mono" spellcheck="false" v-model="pluginLicenseKey" label="Plugin License Key" placeholder="XXXX-XXXX-XXXX-XXXX-XXXX-XXXX" :mask="{mask: '****-****-****-****-****-****', placeholder: ' '}" />
                    <input type="submit" class="btn btn-primary" value="Claim License">
                    <div class="spinner" v-if="pluginLicenseLoading"></div>
                </form>
            </div>
        </div>

        <!--
        <div class="card">
            <div class="card-body">
                <h3>By email address</h3>
                <p class="text-secondary">Use an email address to attach Craft CMS and plugin licenses to your Craft ID account.</p>

                <text-field id="email" label="Email Address" placeholder="user@example.com" />
                <input type="submit" class="btn btn-primary" value="Claim Licenses">
            </div>
        </div>
        -->
    </div>
</template>

<script>
    import TextareaField from '../components/fields/TextareaField'
    import TextField from '../components/fields/TextField'

    export default {

        data() {
            return {
                cmsLicenseKey: '',
                cmsLicenseLoading: false,
                pluginLicenseKey: '',
                pluginLicenseLoading: false,
            }
        },

        components: {
            TextareaField,
            TextField,
        },

        methods: {

            claimCmsLicense() {
                this.cmsLicenseLoading = true;

                this.$store.dispatch('claimCmsLicense', this.cmsLicenseKey)
                    .then(response => {
                        this.cmsLicenseLoading = false;
                        this.$store.dispatch('getCmsLicenses');
                        this.$store.dispatch('getPluginLicenses');
                        this.$root.displayNotice('CMS license claimed.');
                        this.$router.push({path: '/account/licenses/craft'});
                    })
                    .catch(response => {
                        this.cmsLicenseLoading = false;
                        const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t claim CMS license.';
                        this.$root.displayError(errorMessage);
                    });
            },

            claimPluginLicense() {
                this.pluginLicenseLoading = true;

                this.$store.dispatch('claimPluginLicense', this.pluginLicenseKey)
                    .then(response => {
                        this.pluginLicenseLoading = false;
                        this.$store.dispatch('getCmsLicenses');
                        this.$store.dispatch('getPluginLicenses');
                        this.$root.displayNotice('Plugin license claimed.');
                        this.$router.push({path: '/account/licenses/plugins'});
                    })
                    .catch(response => {
                        this.pluginLicenseLoading = false;
                        const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t claim plugin license.';
                        this.$root.displayError(errorMessage);
                    });
            },

            cmsLicenseKeyChange(value) {
                this.$nextTick(() => {
                    this.cmsLicenseKey = this.$options.filters.formatCmsLicense(value);
                });
            }

        },

    }
</script>

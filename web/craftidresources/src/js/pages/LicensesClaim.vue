<template>
    <div>
        <h1>Claim license</h1>

        <div class="card mb-4">
            <div class="card-body">
                <h3>Claim CMS license</h3>
                <p class="text-secondary">Attach a Craft CMS license to your Craft ID account.</p>

                <h5>By license key</h5>
                <form class="mb-6" @submit.prevent="claimCmsLicense()">
                    <textarea-field id="cmsLicenseKey" class="mono" spellcheck="false" v-model="cmsLicenseKey" @input="cmsLicenseKeyChange" label="Craft CMS License Key" rows="5" />
                    <input type="submit" class="btn btn-primary" value="Claim License" :class="{disabled: !cmsLicenseValidates }" :disabled="!cmsLicenseValidates" />
                    <div class="spinner" v-if="cmsLicenseLoading"></div>
                </form>

                <hr>

                <h5>By license file</h5>
                <form @submit.prevent="claimCmsLicenseFile()">
                    <div class="form-group">
                        <label for="licenseFile" class="block">Craft CMS License File</label>
                        <input class="form-control" type="file" id="licenseFile" name="licenseFile" ref="licenseFile" @change="cmsLicenseFileChange" />
                    </div>

                    <input type="submit" class="btn btn-primary" value="Claim License" :class="{disabled: !cmsLicenseFileValidates }" :disabled="!cmsLicenseFileValidates" />
                    <div class="spinner" v-if="cmsLicenseFileLoading"></div>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h3>Claim Plugin license</h3>
                <p class="text-secondary">Attach a plugin license to your Craft ID account.</p>

                <form @submit.prevent="claimPluginLicense()">
                    <text-field id="pluginLicenseKey" class="mono" spellcheck="false" v-model="pluginLicenseKey" label="Plugin License Key" placeholder="XXXX-XXXX-XXXX-XXXX-XXXX-XXXX" :mask="{mask: '****-****-****-****-****-****', placeholder: ' '}" />
                    <input type="submit" class="btn btn-primary" value="Claim License" :class="{disabled: !pluginLicenseValidates }" :disabled="!pluginLicenseValidates" />
                    <div class="spinner" v-if="pluginLicenseLoading"></div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3>Claim licenses by email address</h3>
                <p class="text-secondary">Use an email address to attach Craft CMS and plugin licenses to your Craft ID account.</p>

                <form @submit.prevent="claimLicensesByEmail()">
                    <text-field id="email" label="Email Address" v-model="email" placeholder="user@example.com" />
                    <input type="submit" class="btn btn-primary" value="Claim Licenses">
                    <div class="spinner" v-if="emailLoading"></div>
                </form>
            </div>
        </div>
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
                cmsLicenseValidates: false,
                cmsLicenseFile: '',
                cmsLicenseFileLoading: false,
                cmsLicenseFileValidates: false,
                pluginLicenseKey: '',
                pluginLicenseLoading: false,
                pluginLicenseValidates: false,
                email: '',
                emailLoading: false,
            }
        },

        components: {
            TextareaField,
            TextField,
        },

        methods: {
            checkCmsLicense() {
                if(this.cmsLicenseKey.length === 258) {
                    return true;
                }

                return false;
            },

            checkPluginLicense() {
                const normalizedValue = this.pluginLicenseKey.replace(/(\-\ )/gm, "").trim();

                if(normalizedValue.length === 29) {
                    return true;
                }

                return false;
            },

            claimCmsLicense() {
                this.cmsLicenseLoading = true;

                this.$store.dispatch('claimCmsLicense', this.cmsLicenseKey)
                    .then(response => {
                        this.cmsLicenseLoading = false;
                        this.$store.dispatch('getCmsLicenses');
                        this.$store.dispatch('getPluginLicenses');
                        this.$store.dispatch('getInvoices');
                        this.$root.displayNotice('CMS license claimed.');
                        this.$router.push({path: '/account/licenses/cms'});
                    })
                    .catch(response => {
                        this.cmsLicenseLoading = false;
                        const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t claim CMS license.';
                        this.$root.displayError(errorMessage);
                    });
            },

            claimCmsLicenseFile() {
                this.$store.dispatch('claimCmsLicenseFile', this.$refs.licenseFile.files[0])
                    .then(response => {
                        this.cmsLicenseFileLoading = false;
                        this.$store.dispatch('getCmsLicenses');
                        this.$store.dispatch('getPluginLicenses');
                        this.$store.dispatch('getInvoices');
                        this.$root.displayNotice('CMS license claimed.');
                        this.$router.push({path: '/account/licenses/cms'});
                    })
                    .catch(response => {
                        this.cmsLicenseFileLoading = false;
                        const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t claim CMS license.';
                        this.$root.displayError(errorMessage);
                    });
            },

            claimLicensesByEmail() {
                this.emailLoading = true;

                this.$store.dispatch('claimLicensesByEmail', this.email)
                    .then(response => {
                        this.emailLoading = false;
                        this.$store.dispatch('getCmsLicenses');
                        this.$store.dispatch('getPluginLicenses');
                        this.$store.dispatch('getInvoices');
                        this.$root.displayNotice('Verification email sent to ' + this.email + '.');
                    })
                    .catch(response => {
                        this.emailLoading = false;
                        const errorMessage = response.data && response.data.error ? response.data.error : 'Couldn’t claim licenses.';
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
                        this.$store.dispatch('getInvoices');
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
            },

            cmsLicenseFileChange() {
                this.cmsLicenseFileValidates = this.$refs.licenseFile.files.length > 0;
            }

        },

        watch: {

            cmsLicenseKey(key) {
                this.cmsLicenseValidates = this.checkCmsLicense();
            },

            pluginLicenseKey(key) {
                this.pluginLicenseValidates = this.checkPluginLicense();
            }

        }

    }
</script>

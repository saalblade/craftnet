<template>
    <div>
        <h1>Claim license</h1>
        <div class="card">
            <div class="card-body">
                <p>Attach Craft CMS and plugin licenses to your Craft ID account</p>

                <div class="lg:flex">
                    <div class="w-full lg:w-1/2 p-6 border border-grey-light rounded mr-4 mb-4 lg:mb-0">
                        <h3>1. By license key</h3>

                        <form action="">
                            <div class="form-group">
                                <label for="licenseFile">Craft License Key File</label>
                                <input class="form-control" type="file" id="licenseFile" name="licenseFile" />
                            </div>

                            <text-field id="licenseKey" label="Plugin License Key" placeholder="XXXX-XXXX-XXXX-XXXX-XXXX" />

                            <input type="submit" class="btn btn-primary" value="Claim License">
                        </form>
                    </div>

                    <div class="w-full lg:w-1/2 p-6 border border-grey-light rounded">
                        <h3>2. By email address</h3>
                        <text-field id="email" label="Email Address" placeholder="user@example.com" />
                        <input type="submit" class="btn btn-primary" value="Claim Licenses">
                    </div>
                </div>
                </div>
        </div>
    </div>
</template>

<script>
    import TextField from '../components/fields/TextField'

    export default {

        components: {
            TextField,
        },

    }
</script>

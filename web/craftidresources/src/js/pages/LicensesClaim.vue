<template>
    <div>
        <h1>Claim license</h1>
        <div class="card mb-4">
            <div class="card-body">
                <h3>By license key</h3>
                <p class="text-secondary">Use a license key to attach Craft CMS and plugin licenses to your Craft ID account.</p>

                <form action="">
                    <div class="form-group">
                        <label for="licenseFile" class="block">Craft License Key File</label>
                        <input class="form-control" type="file" id="licenseFile" name="licenseFile" />
                    </div>

                    <text-field id="licenseKey" label="Plugin License Key" placeholder="XXXX-XXXX-XXXX-XXXX-XXXX" />

                    <input type="submit" class="btn btn-primary" value="Claim License">
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3>By email address</h3>
                <p class="text-secondary">Use an email address to attach Craft CMS and plugin licenses to your Craft ID account.</p>

                <text-field id="email" label="Email Address" placeholder="user@example.com" />
                <input type="submit" class="btn btn-primary" value="Claim Licenses">
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

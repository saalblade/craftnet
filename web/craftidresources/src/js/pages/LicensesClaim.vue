<template>
    <div>
        <div class="card">
            <div class="card-body">
                <p>Attach Craft CMS and plugin licenses to your Craft ID account</p>

                <div class="lg:flex -mx-4">
                    <div class="lg:w-1/2 px-4">
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

                    <hr>

                    <div class="lg:w-1/2 px-4">
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

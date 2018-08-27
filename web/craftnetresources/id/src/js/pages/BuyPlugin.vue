<template>
    <div>
        <h1>Buy Plugin</h1>

        <div class="card">
            <div class="card-body">
                <ol>
                    <li>Add the <code>{{handle}}</code> plugin to the cart</li>
                    <li>Redirect to the cart</li>
                </ol>
            </div>
        </div>
    </div>
</template>

<script>
    export default {

        computed: {
            handle() {
                return this.$route.params.handle
            }
        }

    }
</script>

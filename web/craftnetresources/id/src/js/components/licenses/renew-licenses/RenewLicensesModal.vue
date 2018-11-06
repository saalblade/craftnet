<template>
    <div v-if="license" id="renew-licenses-modal">
        <modal :show="true" @background-click="$emit('cancel')">
            <template slot="body">
                <renew-licenses-form :license="license" @cancel="$emit('cancel')" />
            </template>
        </modal>
    </div>
</template>

<script>
    import Modal from '../../Modal';
    import RenewLicensesForm from './RenewLicensesForm'

    export default {

        props: ['license'],

        components: {
            Modal,
            RenewLicensesForm,
        },

    }
</script>
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

<style lang="scss">
    #renew-licenses-modal {
        .modal {
            .modal-dialog {
                @apply .relative;
                min-width: 800px;
                min-height: 600px;

                .modal-content {
                    @apply .absolute .pin;
                }
            }
        }
    }
</style>

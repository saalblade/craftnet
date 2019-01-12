<template>
    <div v-if="release" class="changelog-release">
        <div class="version">
            <a :href="'#' + release.version" class="anchor"><font-awesome-icon icon="link" /></a>
            <h2 :id="release.version">Version {{release.version}}</h2>
            <div class="date">{{release.date|moment('l')}}</div>
            <div v-if="release.critical" class="critical">Critical</div>
        </div>

        <div class="details readable" v-html="release.notes"></div>
    </div>
</template>

<script>
    export default {

        props: ['release'],

    }
</script>

<style lang="scss">
    .changelog-release {
        @apply .pt-2 .pb-4 .border-b;

        .version {
            @apply .relative;

            .anchor {
                @apply .absolute .text-white .p-1 .rounded-full;
                left: -24px;
                top: 0px;
                font-size: 14px;
                transform: rotate(45deg);

                &:hover {
                    @apply .text-black;
                }
            }

            &:hover {
                .anchor {
                    @apply .text-black;
                }
            }

            h2 {
                @apply .mt-6 .mb-2;
            }

            .date {
                @apply .text-grey;
            }
        }

        .details {
            h3 {
                @apply .mt-6 .mb-4;
            }

            ul {
                @apply .mb-4 .leading-normal;

                li:not(:first-child) {
                    @apply .mt-1;
                }
            }
        }
    }

    @media (min-width: 992px) {
        .changelog-release {
            @apply .flex;

            .version {
                @apply .w-full .max-w-xs;

                .anchor {
                    top: 24px;
                }
            }

            .details {
                @apply .flex-1;
            }
        }
    }
</style>
<template>
    <div>
        <div class="pt-4 mt-4" :class="{'border-t': index > 0}">
            <div class="flex">
                <ul class="flex-1 list-reset">
                    <li v-if="location.title"><strong>{{ location.title }}</strong></li>
                    <li v-if="location.addressLine1">{{ location.addressLine1 }}</li>
                    <li v-if="location.addressLine2">{{ location.addressLine2 }}</li>
                    <li v-if="cityStateZip">{{ cityStateZip }}</li>
                    <li v-if="location.phone">{{ location.phone }}</li>
                    <li v-if="location.email">{{ location.email }}</li>
                </ul>
                <div>
                    <button class="btn btn-secondary" @click="$emit('editClick', index)"><i class="fa fa-pencil-alt"></i> Edit</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {

        props: ['index', 'location'],

        computed: {
            cityStateZip() {
                let city = (this.location.businessCity || '')
                let state = (this.location.businessState || '')
                let country = (this.location.businessCountry || '')
                let comma = city.length && state.length ? ',' : ''

                return `${city}${comma} ${state} ${country}`.trim()
            }
        }
    }
</script>

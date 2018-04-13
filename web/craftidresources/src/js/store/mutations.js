import Vue from 'vue';

export const RECEIVE_CRAFT_ID_DATA = (state, {response}) => {
    state.craftId = {
        categories: response.data.categories,
        countries: response.data.countries,
        customers: response.data.customers,
        enableRenewalFeatures: response.data.enableRenewalFeatures,
    }
};

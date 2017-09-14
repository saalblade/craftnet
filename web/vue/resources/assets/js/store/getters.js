export const craftId = state => {
    return state.craftId;
};

export const stripeAccount = state => {
    return state.stripeAccount;
};

export const stripeCard = state => {
    return state.stripeCard;
};

export const stripeCustomer = state => {
    return state.stripeCustomer;
};

export const craftLicenses = state => {
    if(state.craftId) {
        return state.craftId.craftLicenses;
    }
};

export const currentUser = state => {
    if(state.craftId) {
        return state.craftId.currentUser;
    }
};

export const customers = state => {
    if(state.craftId) {
        return state.craftId.customers;
    }
};

export const licenses = state => {
    if(state.craftId) {
        return state.craftId.pluginLicenses.concat(state.craftId.craftLicenses);
    }
};

export const payments = state => {
    if(state.craftId) {
        return state.craftId.payments;
    }
};

export const payouts = state => {
    if(state.craftId) {
        return state.craftId.payouts;
    }
};

export const payoutsScheduled = state => {
    if(state.craftId) {
        return state.craftId.payoutsScheduled;
    }
};

export const pluginLicenses = state => {
    if(state.craftId) {
        return state.craftId.pluginLicenses;
    }
};

export const plugins = state => {
    if(state.craftId) {
        return state.craftId.plugins;
    }
};

/**
 * Craft ID
 */

export const craftId = state => {
    return state.craftId;
};

export const enableRenewalFeatures = state => {
    if (state.craftId) {
        return state.craftId.enableRenewalFeatures;
    }
};

export const apps = state => {
    if (state.craftId) {
        return state.craftId.apps;
    }
};

export const countries = state => {
    return state.craftId.countries;
};


/**
 * User
 */

export const currentUser = state => {
    if (state.craftId) {
        return state.craftId.currentUser;
    }
};

export const userIsInGroup = state => {
    return handle => {
        return state.craftId.currentUser.groups.find(g => g.handle === handle)
    }
};

export const billingAddress = state => {
    return state.craftId.billingAddress
}

/**
 * Licenses
 */

export const cmsLicenses = state => {
    if (state.craftId) {
        return state.craftId.cmsLicenses;
    }
};

export const licenses = state => {
    if (state.craftId) {
        return state.craftId.pluginLicenses.concat(state.craftId.cmsLicenses);
    }
};

export const pluginLicenses = state => {
    if (state.craftId) {
        return state.craftId.pluginLicenses;
    }
};


/**
 * Plugins
 */

export const categories = state => {
    if (state.craftId) {
        return state.craftId.categories;
    }
};


/**
 * Stripe
 */

export const stripeAccount = state => {
    return state.stripeAccount;
};

export const stripeCard = state => {
    return state.stripeCard;
};

export const stripeCustomer = state => {
    return state.stripeCustomer;
};


/**
 * Sales
 */

export const sales = state => {
    if (state.craftId) {
        return state.craftId.sales;
    }
};

export const getSaleById = state => {
    return id => {
        if (state.craftId.sales) {
            return state.craftId.sales.find(sale => sale.id == id);
        }
    }
};

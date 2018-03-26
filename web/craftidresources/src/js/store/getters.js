/**
 * Craft ID
 */

export const craftId = state => {
    return state.craftId;
};

export const enableCommercialFeatures = state => {
    if (state.craftId) {
        return state.craftId.enableCommercialFeatures;
    }
};

export const apps = state => {
    if (state.craftId) {
        return state.craftId.apps;
    }
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

export const repositoryIsInUse = state => {
    return repositoryUrl => {
        return state.craftId.plugins.find(plugin => plugin.repository === repositoryUrl)
    }
};

export const plugins = state => {
    if (state.craftId) {
        return state.craftId.plugins;
    }
};

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
 * Invoices
 */

export const upcomingInvoice = state => {
    if (state.craftId) {
        return state.craftId.upcomingInvoice;
    }
};

export const invoices = state => {
    return state.invoices;
};

export const getInvoiceById = state => {
    return id => {
        if (state.craftId.invoices) {
            return state.craftId.invoices.find(inv => inv.id == id);
        }
    }
};

export const getInvoiceByNumber = state => {
    return number => {
        if (state.invoices) {
            return state.invoices.find(inv => inv.number == number);
        }
    }
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

export const craftId = state => {
    return state.craftId;
};

export const enableRenewalFeatures = state => {
    if (state.craftId) {
        return state.craftId.enableRenewalFeatures;
    }
};

export const categories = state => {
    if (state.craftId) {
        return state.craftId.categories;
    }
};

export const countries = state => {
    return state.craftId.countries;
};

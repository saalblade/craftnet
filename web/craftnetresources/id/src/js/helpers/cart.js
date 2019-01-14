export default {

    getCartItemsData(cart) {
        let lineItems = []

        for (let i = 0; i < cart.lineItems.length; i++) {
            let lineItem = cart.lineItems[i]

            switch (lineItem.purchasable.type) {
                case 'plugin-edition':
                    lineItems.push({
                        type: lineItem.purchasable.type,
                        plugin: lineItem.purchasable.plugin.handle,
                        edition: lineItem.purchasable.handle,
                        autoRenew: lineItem.options.autoRenew,
                        cmsLicenseKey: lineItem.options.cmsLicenseKey,
                    })
                    break
                case 'cms-edition':
                    lineItems.push({
                        type: lineItem.purchasable.type,
                        edition: lineItem.purchasable.handle,
                        licenseKey: lineItem.options.licenseKey,
                        autoRenew: lineItem.options.autoRenew,
                    })
                    break
                case 'cms-renewal':
                case 'plugin-renewal':
                    lineItems.push({
                        type: lineItem.purchasable.type,
                        licenseKey: lineItem.options.licenseKey,
                        expiryDate: lineItem.options.expiryDate,
                    })
                    break
            }
        }

        return lineItems
    },

}
export function formatCmsLicense(value) {
    let parts = [];

    parts.push(value.substr(0, 50));
    parts.push(value.substr(50, 50));
    parts.push(value.substr(100, 50));
    parts.push(value.substr(150, 50));
    parts.push(value.substr(200, 50));
    parts.push(value.substr(250));

    let formattedValue = '';

    for(let i = 0; i < parts.length; i++) {
        formattedValue += parts[i];

        if(i < parts.length - 1) {
            formattedValue += '\r';
        }
    }

    return formattedValue;
}

export function formatPluginLicense(value) {
    let parts = [];

    parts.push(value.substr(0, 4));
    parts.push(value.substr(4, 4));
    parts.push(value.substr(8, 4));
    parts.push(value.substr(12, 4));
    parts.push(value.substr(16, 4));
    parts.push(value.substr(20));

    let formattedValue = '';

    for(let i = 0; i < parts.length; i++) {
        formattedValue += parts[i];

        if(i < parts.length - 1) {
            formattedValue += '-';
        }
    }

    return formattedValue;
}
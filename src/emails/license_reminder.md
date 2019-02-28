Hey {{user.friendlyName}},

{% set hasManual = licenses.manual is defined %}
{% set hasAuto = licenses.auto is defined %}

{% if hasManual %}
{% set pl = licenses.manual|length != 1 %}
The following {{ pl ? 'licenses' : 'license' }} will expire soon:

{% for license in licenses.manual %}
- {{ license.getEdition().getDescription() }} [**`{{ license.getShortKey() }}`**]({{ license.getEditUrl() }}) expires on {{ license.expiresOn|date('Y-m-d') }}.

{% endfor %}

When a license expires, you can keep using the version that’s currently installed, however you won’t be able to update it to newer versions until you’ve renewed the license.

To ensure you don’t miss any updates, click on the license key {{ pl ? 'links' : 'link' }} above, and toggle {{ pl ? 'their Auto-Renew settings' : 'its Auto-Renew setting' }}.

{%- if not hasAuto %} (And don’t forget to make sure your [billing info] is up-to-date.){% endif %}
{% endif %}

{% if hasAuto %}
{% set pl = licenses.auto|length != 1 %}
The following {{ pl ? 'licenses are' : 'license is' }} set to auto-renew soon:

{% for license in licenses.auto %}
- {{ license.getEdition().getDescription() }} [**`{{ license.getShortKey() }}`**]({{ license.getEditUrl() }}) will auto-renew on {{ license.expiresOn|date('Y-m-d') }} for {{ license.renewalPrice|currency('USD') }}.

{% endfor %}

Please take a minute to make sure your [billing info] is up-to-date, so there’s no trouble charging your card on the renewal {{ pl ? 'dates' : 'date' }}.

{% endif %}

Have a great day!

[billing info]: https://id.craftcms.com/account/billing

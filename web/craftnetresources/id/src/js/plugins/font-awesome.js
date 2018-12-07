import Vue from 'vue';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faCoffee, faTimes, faTh, faBars, faPlus, faKey, faPlug, faImage, faUser, faPencilAlt, faExclamationTriangle, faBug, faShoppingCart, faDollarSign, faHandshake, faLink } from '@fortawesome/free-solid-svg-icons'
library.add([faCoffee, faTimes, faTh, faBars, faPlus, faKey, faPlug, faImage, faUser, faPencilAlt, faExclamationTriangle, faBug, faShoppingCart, faDollarSign, faHandshake, faLink])

Vue.component('font-awesome-icon', FontAwesomeIcon)
Vue.config.productionTip = false
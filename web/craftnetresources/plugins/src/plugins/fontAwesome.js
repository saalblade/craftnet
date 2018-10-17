import Vue from 'vue'

// Font Awesome
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faBars, faTimes, faSearch, faBook, faCertificate, faCheck, faInfoCircle, faLink, faChevronDown, faChevronUp, faCopy, faTh } from '@fortawesome/free-solid-svg-icons'
library.add([ faBars, faTimes, faSearch, faBook, faCertificate, faCheck, faInfoCircle, faLink, faChevronDown, faChevronUp, faCopy, faTh ])

Vue.component('font-awesome-icon', FontAwesomeIcon)
Vue.config.productionTip = false
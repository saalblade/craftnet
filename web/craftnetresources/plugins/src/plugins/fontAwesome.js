import Vue from 'vue'

// Font Awesome
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faBars, faTimes, faSearch, faBook, faCheck, faInfoCircle, faLink, faChevronDown, faChevronUp, faCopy, faTh, faQuestionCircle } from '@fortawesome/free-solid-svg-icons'
library.add([ faBars, faTimes, faSearch, faBook, faCheck, faInfoCircle, faLink, faChevronDown, faChevronUp, faCopy, faTh, faQuestionCircle ])

Vue.component('font-awesome-icon', FontAwesomeIcon)
Vue.config.productionTip = false
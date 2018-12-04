import Vue from 'vue'
import CraftUi from '@pixelandtonic/craftui'

Object.keys(CraftUi).forEach(name => {
    Vue.component(name, CraftUi[name])
})



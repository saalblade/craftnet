import Vue from 'vue'
import VueRouter from 'vue-router'
import Index from '../pages/Index'
import Category from '../pages/Category'
import Developer from '../pages/Developer'
import FeaturedPlugins from '../pages/FeaturedPlugins'
import Plugin from '../pages/Plugin'
import * as types from '../store/mutation-types'

Vue.use(VueRouter);

const router = new VueRouter({
    mode: 'history',
    linkActiveClass: 'active',
    canReuse: false,
    scrollBehavior (to, from, savedPosition) {
        return savedPosition || { x: 0, y: 0 };
    },
    routes: [
        {
            path: '/',
            component: Index,
        },
        {
            path: '/categories',
            name: 'Category',
            component: Category,
            children: [
                { path: ':id', component: Category },
            ]
        },
        {
            path: '/developer/:id',
            name: 'Developer',
            component: Developer,
        },
        {
            path: '/featured/:id',
            name: 'FeaturedPlugins',
            component: FeaturedPlugins,
        },
        {
            path: '/plugin/:id',
            name: 'Plugin',
            component: Plugin
        },
    ]
});

// Make things happen when changing route
router.beforeEach((to, from, next) => {
    if(router.app.$store) {
        // Reset search query
        router.app.$store.commit(types.UPDATE_SEARCH_QUERY, '')
    }

    next();
});

export default router;

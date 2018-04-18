import Vue from 'vue'
import Vuex from 'vuex'

import AuthUser from './modules/auth-user'
import AuthShop from './modules/auth-shop'
import Login from './modules/login'
import EditProfile from './modules/edit-profile'
import EditPassword from './modules/edit-password'
import EditShop from './modules/edit-shop'
import Notification from './modules/notification'

import AddNews from './modules/add-news'


Vue.use(Vuex)

export default new Vuex.Store({
    modules: {
        AuthUser,
        AuthShop,
        Login,
        EditProfile,
        EditPassword,
        EditShop,
        Notification,
        AddNews
    },
    strict: true
})

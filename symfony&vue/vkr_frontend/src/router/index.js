import { createRouter, createWebHistory } from 'vue-router';
import Register from '../components/UserRegister.vue';
import Login from '../components/UserLogin.vue';
import FileContents from "../components/FileContents.vue";
import Home from "../components/HomePage.vue";
import FileUpload from "../components/FileUpload.vue";
import HistoryFile from '../components/HistoryFilePage.vue';
import AccountPage from '../components/AccountPage.vue';
import EditProfile from '../components/EditProfile.vue';
import SpecificationGenerator from "../components/SpecificationGenerator.vue";
import SpecificationPage from "../components/SpecificationPage.vue";
import SpecificationEditPage from "../components/SpecificationEditPage.vue";
import NotFound from "../components/NotFound.vue";
import UnauthorizedPage from "../components/UnauthorizedPage.vue";
import ServerError from "../components/ServerError.vue";
import HistorySpecificationPage from "../components/HistorySpecificationPage.vue";


const routes = [
    { path: '/register', component: Register},
    { path: '/login', component: Login },
    { path: '/', component: Home },
    {
        path: '/main',
        name: 'uploadForm',
        component: FileUpload,
        meta: { requiresAuth: true }
    },
    {
        path: '/file-contents/:taskId',
        name: 'fileContents',
        component: FileContents,
        props: true,
    },
    { path: '/history/files',
        name: 'historyFile',
        component: HistoryFile,
        meta: { requiresAuth: true }
    },
    { path: '/history/specifications',
        name: 'historySpecification',
        component: HistorySpecificationPage,
        meta: { requiresAuth: true }
    },
    {
        path: '/specification/edit/:id',
        name: 'SpecificationEditPage',
        component: SpecificationEditPage,
        meta: { requiresAuth: true },
        props: true
    },
    {
        path: '/account',
        name: 'Account',
        component: AccountPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/edit-profile',
        name: 'EditProfile',
        component: EditProfile,
        meta: { requiresAuth: true },
    },
    {
        path: '/specification/:id',
        name: 'SpecificationPage',
        component: SpecificationPage,
        meta: { requiresAuth: true },
        props: true
    },
    {
        path: '/specification',
        name: 'SpecificationGenerator',
        component: SpecificationGenerator,
        meta: { requiresAuth: true },
    },
    {
        path: '/404',
        name: '404',
        component: NotFound,
    },
    {
        path: '/401',
        name: '401',
        component: UnauthorizedPage,
    },
    {
        path: '/500',
        name: '500',
        component: ServerError,
    },
    {
        path: '/:catchAll(.*)',
        redirect: '/404',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach(async (to, from, next) => {
    // Если маршрут требует авторизации
    if (to.meta.requiresAuth) {
        const token = localStorage.getItem('jwt_token');
        if (token) {
            try {
                const response = await fetch('http://localhost:8000/check-auth', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                const data = await response.json();
                if (data.authenticated) {
                    next();
                } else {
                    next('/login');
                }
            } catch (error) {
                next('/login');
            }
        } else {
            next('/login');
        }
    } else {
        next();
    }
});
export default router;


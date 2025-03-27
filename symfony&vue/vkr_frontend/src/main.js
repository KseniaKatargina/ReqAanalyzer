import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { createPinia } from "pinia";
import axios from 'axios';

// Создание приложения
const app = createApp(App);

// Настройка axios глобально
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error.response ? error.response.status : null;

        switch (status) {
            case 401:
                router.push('/401');
                break;
            case 404:
                router.push('/404');
                break;
            case 500:
                router.push('/500');
                break;
            default:
                console.error('Ошибка:', error);
                break;
        }

        return Promise.reject(error);
    }
);

app.use(router).use(createPinia()).mount('#app');

import { defineStore } from 'pinia';


export const useNotificationStore = defineStore('notification', {

    state: () => ({
        message: '',
        isProcessing: false,
        isVisible: false,
        taskId: null,
    }),

    actions: {
        setProcessing(message) {
            this.message = message;
            this.isProcessing = true;
            this.isVisible = true;
        },

        setProcessed(message, taskId) {
            console.log('taskId сохранен:', taskId);
            this.message = message;
            this.isProcessing = false;
            this.isVisible = true;
            this.taskId = taskId;
            console.log('Обновление notificationStore:', this.$state);
        },

        clearNotification() {
            this.isProcessing = false;
            this.isVisible = false;
            this.message = '';
            this.taskId = null;
        },
    },
});


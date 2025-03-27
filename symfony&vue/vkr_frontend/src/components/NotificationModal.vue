<template>
  <div>
    <div
        v-if="notificationStore.isVisible"
        class="notification"
        :class="{ clickable: !notificationStore.isProcessing }"
        :style="{ cursor: notificationStore.isProcessing ? 'not-allowed' : 'pointer' }"
        @click="() => {
    console.log('Клик по уведомлению');
    if (!notificationStore.isProcessing) {
      goToFileContents();
    } else {
      console.log('Уведомление пока неактивно.');
    }
  }"
    >
      <p>{{ notificationStore.message }}</p>
    </div>

  </div>
</template>


<script setup>
import { useNotificationStore } from '/store/notificationStore';
import { useRouter } from 'vue-router';

const notificationStore = useNotificationStore();
const router = useRouter();

const goToFileContents = () => {
  console.log('Нажатие на уведомление...');

  if (notificationStore.isProcessing) {
    console.log('Обработка файла еще не завершена.');
    return;
  }

  const taskId = notificationStore.taskId;
  if (!taskId) {
    console.error('taskId отсутствует.');
    return;
  }

  console.log('Переход на страницу с taskId:', taskId);

  router.push({ name: 'fileContents', params: { taskId } })
      .then(() => {
        notificationStore.clearNotification();
        console.log('Уведомление удалено.');
      })
      .catch((error) => {
        console.error('Ошибка при переходе:', error);
      });
};



</script>

<style scoped>
.notification {
  position: fixed;
  top: 70px;
  right: 20px;
  background-color: #a394b9;
  color: white;
  padding: 8px 15px;
  border-radius: 5px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
  cursor: pointer;
  z-index: 1000;
  transition: opacity 0.3s ease-in-out;
  opacity: 1;
  min-width: 150px;
  text-align: center;
}

.notification.clickable {
  background-color: #f1d3d3;
}

.notification:not(.clickable) {
  opacity: 0.6;
}

.notification:hover {
  background-color: #dcd8e1;
}
</style>

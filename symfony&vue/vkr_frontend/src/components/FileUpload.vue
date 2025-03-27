<template>
  <HeaderComponent />
  <div class="container">
    <div class="form-container">
      <form @submit.prevent="handleSubmit">
        <h2>Загрузите файл для анализа</h2>
        <input type="file" @change="handleFileChange" />
        <button type="submit">Отправить</button>
      </form>
      <div v-if="errorMessage" class="error">
        {{ errorMessage }}
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted, ref } from 'vue';
import { useNotificationStore } from '/store/notificationStore';
import router from "@/router";
import HeaderComponent from '@/components/HeaderComponent.vue';

export default {
  components: {
    HeaderComponent,
  },
  setup() {
    const file = ref(null);
    const errorMessage = ref('');
    const notificationStore = useNotificationStore();
    const taskId = ref(null); // Добавляем для хранения taskId

    onMounted(() => {
      const token = localStorage.getItem('jwt_token');
      if (!token) {
        router.push('/login');
      }
    });

    function handleFileChange(event) {
      file.value = event.target.files[0];
    }

    async function handleSubmit() {
      console.log('Отправка файла...');
      notificationStore.setProcessing('Файл обрабатывается...');

      const token = localStorage.getItem('jwt_token');
      console.log('JWT Token:', token);
      if (!token) {
        errorMessage.value = 'Ошибка: отсутствует JWT токен';
        return;
      }

      const formData = new FormData();
      formData.append('file', file.value);

      try {
        const response = await fetch('http://localhost:8000/upload', {
          method: 'POST',
          body: formData,
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });

        console.log('Ответ от сервера:', response.status);

        const data = await response.json();

        if (response.status === 202) {
          taskId.value = data.task_id; // Сохраняем taskId
          console.log('taskId:', taskId.value);
          await checkTaskStatus(taskId.value); // Запускаем проверку статуса
        } else if (response.status === 200) {
          console.log('Файл обработан успешно!');
          notificationStore.setProcessed('Файл обработан! Нажмите для просмотра.', data.text_id);
        } else {
          errorMessage.value = data.error || 'Ошибка обработки запроса';
          notificationStore.clearNotification();
        }
      } catch (error) {
        console.error(error.response ? error.response.data : error);
        alert("Ошибка загрузки файла");
        notificationStore.clearNotification();
      }
    }

    // Функция для проверки статуса задачи
    // async function checkTaskStatus(taskId) {
    //   console.log('Проверка статуса задачи...');
    //
    //   const intervalId = setInterval(async () => {
    //     const response = await fetch(`http://localhost:5000/tasks/${taskId}`);
    //     const data = await response.json();
    //
    //     console.log('Ответ с сервера статуса задачи:', data.status);
    //
    //     if (data.status === 'SUCCESS') {
    //
    //       notificationStore.setProcessed('Файл успешно обработан!', taskId);
    //       console.log('taskId установлен в store:', notificationStore.taskId);
    //       clearInterval(intervalId); // Останавливаем интервал
    //     } else if (data.status !== 'PENDING') {
    //       errorMessage.value = 'Ошибка обработки';
    //       notificationStore.clearNotification();
    //       clearInterval(intervalId); // Останавливаем интервал
    //     }
    //   }, 5000); // Проверяем статус каждые 5 секунд
    // }
    async function checkTaskStatus(taskId) {
      console.log('Проверка статуса задачи...');

      const intervalId = setInterval(async () => {
        const response = await fetch(`http://localhost:5000/tasks/${taskId}`);
        const data = await response.json();

        console.log('Ответ с сервера статуса задачи:', data.status);

        if (data.status === 'SUCCESS') {
          // Отправляем результат на бэкенд для сохранения
          const completeResponse = await fetch('http://localhost:8000/task/complete', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              task_id: taskId,
              result: data.result, // Предположим, что NLP-сервис возвращает результат в data.result
            }),
          });

          const completeData = await completeResponse.json();
          if (completeResponse.status === 200) {
            notificationStore.setProcessed('Файл успешно обработан!', completeData.text_id);
            clearInterval(intervalId); // Останавливаем интервал
          } else {
            errorMessage.value = 'Ошибка сохранения результата';
            notificationStore.clearNotification();
            clearInterval(intervalId); // Останавливаем интервал
          }
        } else if (data.status !== 'PENDING') {
          errorMessage.value = 'Ошибка обработки';
          notificationStore.clearNotification();
          clearInterval(intervalId); // Останавливаем интервал
        }
      }, 5000); // Проверяем статус каждые 5 секунд
    }

    return {
      file,
      errorMessage,
      notificationStore,
      handleFileChange,
      handleSubmit,
    };
  }
}
</script>

<style scoped>
.container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  width: 100%;
  max-width: 100%;
  overflow: hidden;
  box-sizing: border-box;
}

.form-container {
  background-color: white;
  padding: 30px;
  border-radius: 10px;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 500px;
  text-align: center;
  overflow: hidden;
  margin: 0 auto;
}


h2 {
  font-size: 24px;
  color: #333;
  margin-bottom: 20px;
}

input[type="file"] {
  width: 100%;
  padding: 12px;
  margin: 20px 0;
  border: 1px solid #ccc;
  border-radius: 5px;
  background-color: #f9f9f9;
  font-size: 16px;
}

button {
  width: 100%;
  padding: 12px;
  font-size: 16px;
  color: white;
  background-color: #6a11cb;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

button:hover {
  background-color: #5a0fb8;
}
.error {
  color: #93afb7;
  margin-top: 20px;
}
html, body {
  width: 100%;
  max-width: 100%;
  overflow-x: hidden !important;
  margin: 0;
  padding: 0;
}
header, nav {
  max-width: 100%;
  overflow: hidden;
}
</style>

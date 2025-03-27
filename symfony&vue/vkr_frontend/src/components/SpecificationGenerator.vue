<template>
  <Header />
  <div class="container">
    <div class="content-box">
      <div class="file-box">
        <h2>Создание спецификации SRS на основе RUP</h2>
        <form @submit.prevent="saveSpecification">
          <div v-for="(section, index) in specification" :key="index" class="section">
            <h3>{{ section.title }}</h3>
            <div v-for="(subsection, subIndex) in section.content" :key="subIndex" class="subsection">
              <!-- Если это не вложенный раздел, отображаем текстовое поле -->
              <h4 v-if="subsection.title">{{ subsection.title }}</h4>

              <!-- Отображаем текстовое поле только если нет вложенных данных -->
              <textarea
                  v-if="!Array.isArray(subsection.content)"
                  v-model="subsection.content"
                  rows="4"
                  :placeholder="subsection.placeholder"
              ></textarea>

              <!-- Если подразделы вложены (например, для интерфейсов), отображаем их -->
              <div v-if="Array.isArray(subsection.content)">
                <div v-for="(subSubsection, subSubIndex) in subsection.content" :key="subSubIndex" class="subsubsection">
                  <h5>{{ subSubsection.title }}</h5>
                  <textarea
                      v-model="subSubsection.content"
                      rows="4"
                      :placeholder="subSubsection.placeholder"
                  ></textarea>
                </div>
              </div>
            </div>
          </div>
          <button type="submit" class="save-btn">Сохранить спецификацию</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import Header from '@/components/HeaderComponent.vue';
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const fileId = route.params.fileId;

const specification = ref([
  {
    title: 'Введение',
    content: [
      { title: 'Цели', content: '', placeholder: 'Цель создания системы' },
      { title: 'Обзор', content: '', placeholder: 'Общее представление о системе' },
      { title: 'Определения, сокращения, термины', content: '', placeholder: 'Описание терминов и сокращений' },
      { title: 'Ссылки', content: '', placeholder: 'Список использованных источников' }
    ]
  },
  {
    title: 'Общее описание',
    content: [
      { title: '', content: '', placeholder: 'Введите описание системы' }
    ]
  },
  {
    title: 'Требования',
    content: [
      {
        title: 'Функциональность',
        placeholder: 'Описание первого функционального требования'
      },
      {
        title: 'Удобство использования',
        placeholder: 'Описание требования по удобству использования'
      },
      {
        title: 'Надежность',
        placeholder: 'Описание требования к надежности'
      },
      {
        title: 'Производительность',
        placeholder: 'Описание требования к производительности'
      },
      {
        title: 'Поддерживаемость',
        placeholder: 'Описание требования к поддерживаемости'
      },
      {
        title: 'Проектные ограничения',
        placeholder: 'Описание проектного ограничения'
      },
      { title: 'Требования по документированности и поддержке пользователей', content: '', placeholder: 'Описание требований по документированности' },
      { title: 'Заимствованные компоненты', content: '', placeholder: 'Описание заимствованных компонентов' },
      {
        title: 'Интерфейсы',
        content: [
          { title: 'Пользовательские интерфейсы', content: '', placeholder: 'Описание пользовательских интерфейсов' },
          { title: 'Аппаратные интерфейсы', content: '', placeholder: 'Описание аппаратных интерфейсов' },
          { title: 'Программные интерфейсы', content: '', placeholder: 'Описание программных интерфейсов' },
          { title: 'Коммуникационные интерфейсы', content: '', placeholder: 'Описание коммуникационных интерфейсов' }
        ]
      },
      { title: 'Лицензионные соглашения', content: '', placeholder: 'Описание лицензионных соглашений' },
      { title: 'Необходимые замечания по законодательству, авторским правам и прочие', content: '', placeholder: 'Примечания по законодательству' },
      { title: 'Применяемые стандарты', content: '', placeholder: 'Перечень применяемых стандартов' }
    ]
  },
  {
    title: 'Сопроводительная информация',
    content: [
      { title: '', content: '', placeholder: 'Информация, сопутствующая документу' }
    ]
  }
]);

const saveSpecification = async () => {
  try {
    const token = localStorage.getItem('jwt_token');
    const response = await fetch('http://127.0.0.1:8000/specifications', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`},
      body: JSON.stringify({ fileId, specification: specification.value })
    });

    const result = await response.json();
    try {
      await router.push(`/specification/${result.id}`);
    } catch (error) {
      console.error('Ошибка при переходе на страницу:', error);
    }
  } catch (error) {
    alert('Ошибка при сохранении: ' + error.message);
  }
};
</script>

<style scoped>
.container {
  display: flex;
  justify-content: center;
  background: linear-gradient(135deg, #dcd8e1, #a394b9);
  height: 100vh;
  padding: 20px;
  overflow-y: auto;
}
.content-box {
  display: flex;
  gap: 20px;
  max-width: 800px;
  width: 100%;
}
.file-box {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  text-align: center;
  width: 100%;
  height: fit-content;
}
h3 {
  font-size: 18px;
  margin-bottom: 10px;
  color: #333;
}
h4 {
  font-size: 16px;
  margin-top: 10px;
  margin-bottom: 5px;
  color: #555;
}
h5 {
  font-size: 14px;
  margin-top: 5px;
  color: #666;
}
textarea {
  width: 90%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  resize: vertical;
  margin-bottom: 10px;
}
.save-btn {
  background-color: #a394b9;
  color: white;
  padding: 10px 15px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
  margin-top: 10px;
}
.save-btn:hover {
  background-color: #dcd8e1;
}
</style>

<template>
  <Header />
  <div class="container">
    <div class="content-box">
      <div class="file-box">
        <h2>{{ fileTitle || "Без названия" }}</h2>
        <p v-if="fileDate">Дата создания: {{ fileDate }}</p>
        <p v-if="fileDescription">Описание: {{ fileDescription }}</p>

        <textarea v-model="originalText" v-if="originalText !== null" class="text-area" />
        <p v-else>Нет данных для отображения</p>

        <div class="btn-group">
          <button @click="saveChanges" class="save-btn">Сохранить изменения</button>

          <div class="dropdown">
            <button class="save-btn">Сохранить как ▼</button>
            <div class="dropdown-content">
              <a @click.prevent="downloadFile('pdf')">PDF</a>
              <a @click.prevent="downloadFile('txt')">TXT</a>
              <a @click.prevent="downloadFile('docx')">DOCX</a>
            </div>
          </div>
        </div>

        <p v-if="saveMessage" class="save-message">{{ saveMessage }}</p>
      </div>

      <div class="file-box">
        <h2>Результат анализа</h2>
        <div v-if="fileContent !== null">
          <div v-for="item in analysis" :key="item.sentence" class="analysis-item">
            <p><strong>{{ item.sentence }}</strong></p>
            <ul v-if="item.recommendations.length" class="recommendations">
              <li v-for="rec in item.recommendations" :key="rec">{{ rec }}</li>
            </ul>
          </div>
        </div>
        <p v-else>Нет данных для отображения</p>
      </div>

    </div>
  </div>
</template>

<script setup>
import Header from '@/components/HeaderComponent.vue';
import {ref, onMounted, watch} from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const taskId = route.params.taskId;
const fileContent = ref(null);
const originalText = ref(null);
const fileTitle = ref(null);
const fileDate = ref(null);
const fileDescription = ref(null);
const saveMessage = ref("");

const analysis = ref([]);
const contradictions = ref([]);
const uniqueContradictions = ref([]);

const processFileContent = () => {
  try {
    const data = JSON.parse(fileContent.value);
    analysis.value = data.analysis || [];
    contradictions.value = data.contradictions || [];

    // Убираем дублирующиеся противоречия
    uniqueContradictions.value = contradictions.value.filter(
        (con, index, self) =>
            index ===
            self.findIndex(
                (c) => c.sentence1 === con.sentence1 && c.sentence2 === con.sentence2
            )
    );
  } catch (error) {
    console.error("Ошибка обработки данных", error);
  }
};

watch(fileContent, processFileContent, { immediate: true });


watch(fileContent, processFileContent, { immediate: true });

const fetchFileContent = async () => {
  try {
    const response = await fetch(`http://localhost:8000/text/${taskId}`, {
      headers: { 'Authorization': `Bearer ${localStorage.getItem('jwt_token')}` },
    });

    if (!response.ok) alert('Ошибка загрузки данных');

    const data = await response.json();
    fileContent.value = data.processed_text || 'Ошибка загрузки данных.';
    originalText.value = data.original_text || 'Ошибка загрузки данных.';
    fileTitle.value = data.title || "Без названия";
    fileDate.value = data.created_at || "Дата не указана";
    fileDescription.value = data.description || "Нет описания";
  } catch (error) {
    fileContent.value = 'Ошибка соединения с сервером.';
    originalText.value = 'Ошибка соединения с сервером.';
  }
};

const saveChanges = async () => {
  try {
    const response = await fetch(`http://localhost:8000/text/${taskId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('jwt_token')}`,
      },
      body: JSON.stringify({ original_text: originalText.value }),
    });

    if (!response.ok) {
      if (response.status === 404) this.$router.push('/404');
      else if (response.status === 401) this.$router.push('/401');
      else if (response.status >= 500) this.$router.push('/500');
    }

    saveMessage.value = 'Данные успешно сохранены';
    setTimeout(() => {
      saveMessage.value = '';
    }, 3000);
  } catch (error) {
    saveMessage.value = 'Ошибка при сохранении изменений';
    setTimeout(() => {
      saveMessage.value = '';
    }, 3000);
  }
};

const downloadFile = async (format) => {
  try {
    const response = await fetch(`http://localhost:8000/download/${taskId}?format=${format}`, {
      headers: { 'Authorization': `Bearer ${localStorage.getItem('jwt_token')}` },
    });

    if (!response.ok) {
      if (response.status === 404) this.$router.push('/404');
      else if (response.status === 401) this.$router.push('/401');
      else if (response.status >= 500) this.$router.push('/500');
    }

    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${fileTitle.value}.${format}`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    alert('Ошибка скачивания файла');
  }
};

onMounted(fetchFileContent);
</script>

<style scoped>
.btn-group {
  display: flex;
  gap: 10px;
}

.dropdown {
  position: relative;
  display: inline-block;
}

.dropdown-content {
  display: none;
  position: absolute;
  background-color: white;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
  min-width: 100px;
  border-radius: 8px;
  z-index: 1;
}

.dropdown:hover .dropdown-content {
  display: block;
}

.dropdown-content a {
  display: block;
  padding: 10px;
  text-decoration: none;
  color: black;
  cursor: pointer;
}

.dropdown-content a:hover {
  background-color: #dcd8e1;
}
.container {
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #dcd8e1, #a394b9);
  padding: 20px;
}

.content-box {
  display: flex;
  gap: 20px;
  max-width: 1200px;
  width: 100%;
}

.file-box {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  text-align: center;
  transition: transform 0.3s ease-in-out;
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  max-height: 600px;
}

h2 {
  font-size: 22px;
  margin-bottom: 10px;
  color: #333;
}

p {
  font-size: 14px;
  margin-bottom: 10px;
  color: #555;
}

pre {
  white-space: pre-wrap;
  word-wrap: break-word;
  font-size: 14px;
  background: #f5f5f5;
  padding: 15px;
  border-radius: 8px;
  text-align: left;
  overflow-x: auto;
  max-height: 400px;
}

.text-area {
  width: 100%;
  min-height: 350px;
  font-size: 14px;
  border: 1px solid #ccc;
  border-radius: 8px;
  resize: vertical;
}

.save-btn {
  padding: 10px 15px;
  background-color: #a394b9;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
  margin-right: 10px;
}

.save-btn:hover {
  background-color: #dcd8e1;
}

.save-message {
  margin-top: 10px;
  font-size: 14px;
  color: #83bad9;
}
.analysis-item {
  background: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 10px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease-in-out;
}

.analysis-item:hover {
  transform: scale(1.02);
}

.analysis-item p {
  font-size: 16px;
  font-weight: bold;
  color: #333;
  margin-bottom: 5px;
}

.recommendations {
  background: #ffecec;
  padding: 10px;
  border-radius: 6px;
  border-left: 4px solid #ff4d4d;
  margin-top: 5px;
}

.recommendations li {
  font-size: 14px;
  color: #b71c1c;
  list-style: disc;
  margin-bottom: 3px;
}

.contradictions {
  background: #ffeded;
  padding: 15px;
  border-radius: 8px;
  margin-top: 15px;
  box-shadow: 0 2px 5px rgba(255, 0, 0, 0.2);
  border-left: 4px solid #ff4d4d;
}

.contradictions h3 {
  font-size: 18px;
  color: #d32f2f;
  margin-bottom: 10px;
}

.contradictions ul {
  padding-left: 20px;
}

.contradictions li {
  font-size: 14px;
  color: #b71c1c;
  line-height: 1.5;
}

</style>
<template>
  <Header />
  <div class="container">
    <div class="content-box">
      <div class="file-box">
        <h2>{{title}}</h2>
        <form @submit.prevent="saveSpecification">
          <div v-for="(section, index) in specification" :key="index" class="section">
            <h3>{{ section.title }}</h3>
            <div v-for="(subsection, subIndex) in section.content" :key="subIndex" class="subsection">
              <h4 v-if="subsection.title">{{ subsection.title }}</h4>

              <textarea
                  v-if="!Array.isArray(subsection.content)"
                  v-model="subsection.content"
                  rows="4"
                  :placeholder="subsection.placeholder"
              ></textarea>

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
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const fileId = route.params.id;

const specification = ref([]);
const title = ref("");

onMounted(async () => {
  try {
    const token = localStorage.getItem('jwt_token');
    const response = await fetch(`http://localhost:8000/specificationEdit/${fileId}`, {
      method: 'GET',
      headers: { 'Authorization': `Bearer ${token}` }
    });

    if (!response.ok) {
      if (response.status === 404) this.$router.push('/404');
      else if (response.status === 401) this.$router.push('/401');
      else if (response.status >= 500) this.$router.push('/500');
    }

    const data = await response.json();

    if (data && data.content) {
      specification.value = data.content;
      title.value = data.title;
    } else {
      console.log('Пустые данные спецификации');
    }
  } catch (error) {
    console.error('Ошибка при загрузке спецификации:', error);
    alert('Не удалось загрузить спецификацию');
  }
});


const saveSpecification = async () => {
  try {
    const token = localStorage.getItem('jwt_token');
    const response = await fetch(`http://localhost:8000/specificationEdit/${fileId}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
      body: JSON.stringify({ specification: specification.value })
    });

    const result = await response.json();
    if (result && result.success) {
      await router.push(`/history/specifications`);
    } else {
      alert(result.error || 'Ошибка при обновлении спецификации');
    }
  } catch (error) {
    alert('Ошибка при сохранении спецификации');
    console.error('Ошибка при сохранении спецификации:', error);
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

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Header from './HeaderComponent.vue';

const email = ref('');
const message = ref('');

async function loadAccountData() {
  const token = localStorage.getItem('jwt_token');
  const response = await fetch('http://localhost:8000/account', {
    method: 'GET',
    headers: { 'Authorization': `Bearer ${token}` }
  });
  const data = await response.json();

  if (response.ok) {
    email.value = data.email;
  } else {
    if (response.status === 404) this.$router.push('/404');
    else if (response.status === 401) this.$router.push('/401');
    message.value = data.error || 'Ошибка загрузки данных';
  }
}

onMounted(() => {
  loadAccountData();
});

const router = useRouter();
function editProfile() {
  router.push('/edit-profile');
}
</script>

<template>
  <Header />
  <div class="container">
    <h2>Ваш аккаунт</h2>
    <div>
      <p>Email: {{ email }}</p>
      <button @click="editProfile">Редактировать профиль</button>
    </div>

    <p v-if="message">{{ message }}</p>
  </div>
</template>

<style scoped>
.container {
  width: 500px;
  padding: 20px;
  background-color: rgba(255, 255, 255, 0.8);
  border-radius: 8px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
  text-align: center;
  margin: 100px auto;
}

h2 {
  margin-bottom: 20px;
  color: #333;
}

label {
  display: block;
  text-align: left;
  margin: 10px 0 5px;
}

button {
  width: 100%;
  padding: 10px;
  color: white;
  background-color: #6a11cb;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
  margin-top: 15px;
}

button:hover {
  background-color: #2575fc;
}
</style>

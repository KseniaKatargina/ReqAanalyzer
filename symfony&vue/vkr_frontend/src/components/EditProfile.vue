<script setup>
import { onMounted, ref } from 'vue';
import Header from './HeaderComponent.vue';

const email = ref('');
const oldEmail = ref('');
const oldPassword = ref('');
const newPassword = ref('');
const confirmNewPassword = ref('');
const message = ref('');
const showOldPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmNewPassword = ref(false);

async function loadAccountData() {
  const token = localStorage.getItem('jwt_token');
  const response = await fetch('http://localhost:8000/account', {
    method: 'GET',
    headers: { 'Authorization': `Bearer ${token}` }
  });
  const data = await response.json();

  if (response.ok) {
    email.value = data.email;
    oldEmail.value = data.email;
  } else {
    if (response.status === 404) this.$router.push('/404');
    else if (response.status === 401) this.$router.push('/401');
    message.value = data.error || 'Ошибка загрузки данных';
  }
}

onMounted(() => {
  loadAccountData();
});

async function updateAccount() {
  if (email.value !== oldEmail.value) {
    if (!email.value || email.value === oldEmail.value) {
      message.value = "Email не изменился или пустой!";
      return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.value)) {
      message.value = "Введите корректный email";
      return;
    }
  }

  if (newPassword.value && newPassword.value !== confirmNewPassword.value) {
    message.value = "Пароли не совпадают";
    return;
  }

  if (newPassword.value) {
    const passwordRegex = /^(?=.*[A-Z])(?=.*\d).{8,}$/;
    if (!passwordRegex.test(newPassword.value)) {
      message.value = "Пароль должен содержать минимум 8 символов, 1 заглавную букву и 1 цифру";
      return;
    }
  }

  const token = localStorage.getItem('jwt_token');
  const response = await fetch('http://localhost:8000/account', {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      email: email.value !== oldEmail.value ? email.value : undefined,
      oldPassword: oldPassword.value || undefined,
      newPassword: newPassword.value || undefined
    })
  });

  const data = await response.json();

  if (response.ok) {
    message.value = "Данные успешно обновлены!";
    if (email.value !== oldEmail.value) {
      localStorage.setItem('jwt_token', data.token);
    }
  } else {
    if (response.status === 404) this.$router.push('/404');
    else if (response.status === 401) this.$router.push('/401');
    message.value = data.error || 'Ошибка обновления данных';
  }
}
</script>

<template>
  <Header/>
  <div class="container">
    <h2>Редактировать профиль</h2>
    <div>
      <label>Новый Email:</label>
      <input type="email" v-model="email" required />
    </div>

    <div>
      <label>Старый пароль:</label>
      <div class="password-container">
        <input :type="showOldPassword ? 'text' : 'password'" v-model="oldPassword" />
        <i :class="showOldPassword ? 'fa fa-eye' : 'fa fa-eye-slash'" @click="showOldPassword = !showOldPassword"></i>
      </div>

      <label>Новый пароль:</label>
      <div class="password-container">
        <input :type="showNewPassword ? 'text' : 'password'" v-model="newPassword" />
        <i :class="showNewPassword ? 'fa fa-eye' : 'fa fa-eye-slash'" @click="showNewPassword = !showNewPassword"></i>
      </div>

      <label>Подтвердите новый пароль:</label>
      <div class="password-container">
        <input :type="showConfirmNewPassword ? 'text' : 'password'" v-model="confirmNewPassword" />
        <i :class="showConfirmNewPassword ? 'fa fa-eye' : 'fa fa-eye-slash'" @click="showConfirmNewPassword = !showConfirmNewPassword"></i>
      </div>
    </div>

    <button @click="updateAccount">Сохранить изменения</button>

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

.password-container {
  position: relative;
  display: flex;
  align-items: center;
}

input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
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
<script setup>
import { ref } from 'vue';
import router from "@/router";

const email = ref('');
const password = ref('');
const message = ref('');
const showPassword = ref(false);

async function login() {
  try {
    const response = await fetch('http://localhost:8000/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email.value, password: password.value })
    });

    const data = await response.json();

    console.log('Ответ сервера:', data);

    if (response.ok && data.token) {
      localStorage.setItem('jwt_token', data.token);

      await router.push('/main');
    } else {
      message.value = data.error || 'Ошибка авторизации';
    }
  } catch (error) {
    console.error('Ошибка авторизации:', error);
    message.value = 'Ошибка сервера';
  }
}
</script>

<template>
  <div class="container">
    <h2>Авторизация</h2>
    <form @submit.prevent="login">
      <label>Email:</label>
      <input type="email" v-model="email" required />

      <label>Пароль:</label>
      <div class="password-container">
        <input :type="showPassword ? 'text' : 'password'" v-model="password" required />
        <i
            :class="showPassword ? 'fa fa-eye' : 'fa fa-eye-slash'"
            class="toggle-password"
            @click="showPassword = !showPassword"
        ></i>
      </div>

      <button type="submit">Войти</button>
    </form>
    <p v-if="message">{{ message }}</p>
    <p>Нет аккаунта? <router-link to="/register">Зарегистрироваться</router-link></p>
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
  width: 95%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.toggle-password {
  position: absolute;
  right: 10px;
  cursor: pointer;
  font-size: 18px;
  color: #6a11cb;
}

.toggle-password:hover {
  color: #2575fc;
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

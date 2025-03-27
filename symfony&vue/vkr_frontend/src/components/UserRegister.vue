<script setup>
import { ref } from 'vue';
import router from "@/router";

const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const message = ref('');
const showPassword = ref(false);
const showConfirmPassword = ref(false);

async function register() {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email.value)) {
    message.value = "Введите корректный email";
    return;
  }

  const passwordRegex = /^(?=.*[A-Z])(?=.*\d).{8,}$/;
  if (!passwordRegex.test(password.value)) {
    message.value = "Пароль должен содержать минимум 8 символов, 1 заглавную букву и 1 цифру";
    return;
  }

  if (password.value !== confirmPassword.value) {
    message.value = "Пароли не совпадают";
    return;
  }
  try {
    const response = await fetch('http://localhost:8000/register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email.value, password: password.value })
    });
    const data = await response.json();
    if (data.status === 'User created') {
      await router.push('/login');
    } else {
      message.value = data.error || 'Ошибка регистрации';
    }
  } catch (error) {
    console.error('Ошибка регистрации:', error);
    message.value = 'Ошибка сервера';
  }
}
</script>

<template>
  <div class="container">
    <h2>Регистрация</h2>
    <form @submit.prevent="register">
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

      <label>Повторите пароль:</label>
      <div class="password-container">
        <input :type="showConfirmPassword ? 'text' : 'password'" v-model="confirmPassword" required />
        <i
            :class="showConfirmPassword ? 'fa fa-eye' : 'fa fa-eye-slash'"
            class="toggle-password"
            @click="showConfirmPassword = !showConfirmPassword"
        ></i>
      </div>

      <p v-if="message" class="error">{{ message }}</p>
      <button type="submit">Зарегистрироваться</button>
    </form>
    <p>Уже есть аккаунт? <router-link to="/login">Войти</router-link></p>
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

.error {
  color: #93afb7;
  margin-top: 5px;
}
</style>

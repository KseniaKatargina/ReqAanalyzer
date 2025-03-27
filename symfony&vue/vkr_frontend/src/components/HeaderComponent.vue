<template>
  <header class="header">
    <nav class="nav">
      <ul>
        <li class="dropdown" @mouseenter="showDropdown = true" @mouseleave="showDropdown = false">
          <span class="nav-link">История</span>
          <ul v-if="showDropdown" class="dropdown-menu">
            <li><router-link to="/history/files" class="dropdown-item">Файлы</router-link></li>
            <li><router-link to="/history/specifications" class="dropdown-item">Спецификации</router-link></li>
          </ul>
        </li>
        <li><router-link to="/main" class="nav-link">Загрузить файл</router-link></li>
        <li><router-link to="/specification" class="nav-link">Сгенерировать спецификацию</router-link></li>
        <li><router-link to="/account" class="nav-link">Аккаунт</router-link></li>
        <li>
          <button @click="logout" class="nav-link logout-btn">Выйти</button>
        </li>
      </ul>
    </nav>
  </header>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const showDropdown = ref(false);

const logout = () => {
  localStorage.removeItem('jwt_token');
  router.push('/');
};
</script>

<style scoped>
.header {
  width: 100vw;
  background: linear-gradient(90deg, #a394b9, #dcd8e1);
  padding: 20px 0;
  position: relative;
  top: 0;
  z-index: 1000;
  overflow: visible;
}

.nav {
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
  z-index: 1000;
}

.nav ul {
  list-style: none;
  display: flex;
  gap: 20px;
  padding: 0;
  margin: 0;
}

.nav-link {
  color: white;
  text-decoration: none;
  font-size: 18px;
  padding: 10px 15px;
  background-color: rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  transition: background-color 0.3s ease, transform 0.2s ease;
  display: inline-block;
  cursor: pointer;
  border: none;
  font-family: inherit;
}

.nav-link:hover {
  background-color: rgba(255, 255, 255, 0.5);
  transform: scale(1.1);
}

.logout-btn {
  all: unset;
  cursor: pointer;
  font-size: 18px;
  padding: 10px 15px;
  background-color: rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  transition: background-color 0.3s ease, transform 0.2s ease;
  color: white;
}

.logout-btn:hover {
  background-color: rgba(255, 255, 255, 0.5);
  transform: scale(1.1);
}

.dropdown {
  position: relative;
  display: inline-block;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 8px;
  padding: 5px 0;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  min-width: 150px;
  z-index: 9999;
}

.dropdown-item {
  padding: 10px;
  text-decoration: none;
  color: black;
  display: block;
  transition: background 0.3s;
  white-space: nowrap;
}

.dropdown-item:hover {
  background: rgba(0, 0, 0, 0.1);
}
</style>
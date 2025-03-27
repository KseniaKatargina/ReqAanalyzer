<template>
  <div id="app">
    <router-view></router-view>
    <NotificationModal />
  </div>
</template>

<script setup>

import NotificationModal from "./components/NotificationModal.vue";
</script>

<style>
body, html {
  overflow-x: hidden;
  margin: 0;
  font-family: Arial, sans-serif;
  background: linear-gradient(135deg, #dcd8e1, #a394b9);
  display: flex;
  justify-content: center;
  align-items: center;
}

</style>

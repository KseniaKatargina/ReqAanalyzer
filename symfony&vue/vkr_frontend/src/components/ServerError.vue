<template>
  <div class="error-page">
    <h1>500 - Внутренняя ошибка сервера</h1>
    <p>Что-то пошло не так. Пожалуйста, попробуйте позже.</p>
  </div>
</template>

<script>
export default {
  name: 'ServerError',
};
</script>

<style scoped>
.error-page {
  text-align: center;
  margin-top: 50px;
}
</style>

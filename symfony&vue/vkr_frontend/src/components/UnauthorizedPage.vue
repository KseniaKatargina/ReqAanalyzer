<template>
  <div class="error-page">
    <h1>401 - Не авторизован</h1>
    <p>Для доступа к этому ресурсу вам необходимо авторизоваться.</p>
  </div>
</template>

<script>
export default {
  name: 'UnauthorizedPage',
};
</script>

<style scoped>
.error-page {
  text-align: center;
  margin-top: 50px;
}
</style>

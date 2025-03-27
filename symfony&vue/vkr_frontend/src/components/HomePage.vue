<template>
  <div class="wrapper">
    <div class="container">
      <div class="left">
        <h1>Добро пожаловать в ReqAanalyzer</h1>
        <p>
          Наш сервис анализирует файлы и предоставляет детальные отчёты.
          Зарегистрируйтесь или войдите, чтобы начать!
        </p>
      </div>
      <div class="right">
        <div class="buttons">
          <router-link to="/login" class="btn login">Войти</router-link>
          <router-link to="/register" class="btn register">Зарегистрироваться</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}

.container {
  display: flex;
  width: 70vw;
  height: 60vh;
  box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.1);
  border-radius: 20px;
  overflow: hidden;
  position: relative;
  background: white;
}

.left {
  flex: 1;
  background: #6a11cb;
  color: white;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 40px;
  text-align: center;
  z-index: 2;
}

.left h1 {
  font-size: 2rem;
  margin-bottom: 15px;
}

.left p {
  font-size: 1rem;
  max-width: 400px;
}

.right {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
  z-index: 2;
}

.container::before {
  content: "";
  position: absolute;
  top: 0;
  left: 50%;
  width: 200%;
  height: 100%;
  background: white;
  transform: skewX(-20deg);
  z-index: 1;
}

.buttons {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
  z-index: 3;
}

.btn {
  display: inline-block;
  padding: 12px 35px;
  font-size: 16px;
  font-weight: bold;
  text-decoration: none;
  border-radius: 25px;
  transition: 0.3s ease-in-out;
}

.login {
  background: #6a11cb;
  color: white;
}

.login:hover {
  background: #5a0fb8;
}

.register {
  background: white;
  color: #6a11cb;
  border: 2px solid #6a11cb;
}

.register:hover {
  background: #6a11cb;
  color: white;
}

@media (max-width: 768px) {
  .container {
    flex-direction: column;
    width: 90vw;
    height: auto;
  }

  .left, .right {
    flex: none;
    width: 100%;
    padding: 30px;
  }

  .container::before {
    display: none;
  }
}
</style>

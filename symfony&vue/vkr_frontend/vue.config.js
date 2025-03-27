const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  transpileDependencies: true
})
module.exports = {
  devServer: {
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:5000', // Flask-сервер
        changeOrigin: true,
        pathRewrite: { '^/api': '' },
      },
      '/upload': {
        target: 'http://127.0.0.1:8000', // Symfony-сервер
        changeOrigin: true,
      },
    },
  },
};

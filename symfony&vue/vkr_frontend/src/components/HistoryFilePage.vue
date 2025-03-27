<template>
  <HeaderComponent />

  <div class="history-container">
    <div class="filters-section">
      <div class="search-container">
        <input type="text" v-model="searchQuery" placeholder="Поиск по названию..." class="search-input" />
      </div>
      <select v-model="sortOption" class="sort-select">
        <option value="newest">Новые</option>
        <option value="oldest">Старые</option>
        <option value="alpha">По алфавиту (A-Z)</option>
        <option value="alpha-reverse">По алфавиту (Z-A)</option>
      </select>
    </div>

    <div class="files-section">
      <h1>История файлов</h1>
      <div v-if="paginatedFiles.length" class="file-grid">
        <div
            v-for="file in paginatedFiles"
            :key="file.id"
            class="file-card"
            @click="viewFileDetails(file.id)"
        >
          <h2>{{ file.title || 'Без названия' }}</h2>
          <p>{{ file.description || 'Нет описания' }}</p>
          <small class="created-at">Создано: {{ file.createdAt }}</small>
          <button @click.stop="openEditModal(file)" class="edit-btn">Редактировать</button>
          <button @click.stop="openDeleteModal(file.id)" class="delete-btn">Удалить</button>
        </div>
      </div>
      <p v-else class="no-files">Файлы не найдены</p>
      <div v-if="totalPages > 1" class="pagination">
        <button
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="pagination-btn">
          Назад
        </button>
        <span>{{ currentPage }} / {{ totalPages }}</span>
        <button
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="pagination-btn">
          Вперед
        </button>
      </div>
    </div>
  </div>

  <div v-if="isEditModalVisible" class="modal">
    <div class="modal-content">
      <h2>Редактировать файл</h2>
      <input type="text" v-model="editedFile.title" placeholder="Название" class="modal-input" />
      <textarea v-model="editedFile.description" placeholder="Описание" class="modal-textarea"></textarea>
      <button @click="saveFileChanges" class="save-btn">Сохранить</button>
      <button @click="closeEditModal" class="cancel-btn">Отмена</button>
    </div>
  </div>
  <div v-if="isModalVisible" class="modal">
    <div class="modal-content">
      <h2>Вы уверены, что хотите удалить этот файл?</h2>
      <div class="modal-actions">
        <button @click="deleteFile" class="delete">Удалить</button>
        <button @click="closeModal" class="cancel">Отмена</button>
      </div>
    </div>
  </div>
</template>


<style scoped>
.modal-actions {
  display: flex;
  justify-content: space-evenly;
}

.cancel {
  background-color: #888;
  color: white;
}

.cancel:hover {
  background-color: #555;
}

.cancel-btn{
  background-color: #a394b9;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
  margin-right: 10px;
}
.cancel-btn:hover{
  background-color: #decaca;
}
.edit-btn {
  background-color: #a394b9;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
  margin-right: 10px;
  margin-top: 10px;
}

.edit-btn:hover {
  background-color: #decaca;
}

.modal-input,
.modal-textarea {
  width: 90%;
  padding: 10px;
  margin-bottom: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.save-btn {
  background-color: #a394b9;
  color: white;
  border: none;
  padding: 10px;
  border-radius: 5px;
  cursor: pointer;
  margin: 10px;
}

.save-btn:hover {
  background-color: #dcd8e1;
}
.history-container {
  display: flex;
  gap: 20px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  min-height: 650px;
}

.files-section {
  flex: 2;
}

.filters-section {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 15px;
  margin-top: 64px;
  margin-right: 15px;
}

.search-input, .sort-select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 16px;
}
.delete-btn {
  background-color: #a394b9;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
  margin-top: 10px;
}

.delete-btn:hover {
  background-color: #dcd8e1;
}
.history-container {
  display: flex;
  gap: 20px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.filters-section {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.files-section {
  flex: 4;
}

.search-input, .sort-select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 16px;
}

.file-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background-color: white;
  padding: 20px;
  border-radius: 10px;
  text-align: center;
  max-width: 400px;
  width: 100%;
}

.modal-btn {
  padding: 10px 20px;
  margin: 10px;
  border-radius: 5px;
  cursor: pointer;
}

.delete {
  background-color: #a394b9;
  color: white;
  border-radius: 5px;
  padding: 5px;
  border: none;
}

.delete:hover {
  background-color: #dcd8e1;
}

.cancel {
  background-color: #888;
  color: white;
  border: none;
  border-radius: 5px;
  padding: 5px;
}

.cancel:hover {
  background-color: #555;
}
.history {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

h1 {
  text-align: center;
  margin-bottom: 20px;
  font-size: 24px;
  color: #333;
}

.search-input {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 16px;
}

.sort-select {
  width: 100%;
  padding: 10px;
  margin-bottom: 20px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 16px;
}

.file-card {
  background: white;
  border-radius: 10px;
  padding: 15px;
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
  border: 1px solid #ddd;
  transition: transform 0.2s ease-in-out;
}

.file-card:hover {
  transform: scale(1.05);
}

h2 {
  font-size: 18px;
  margin-bottom: 10px;
  color: #222;
}

p {
  font-size: 14px;
  color: #555;
}

.created-at {
  display: block;
  margin-top: 10px;
  font-size: 12px;
  color: #777;
}

.no-files {
  text-align: center;
  font-size: 18px;
  color: #888;
  margin-top: 20px;
}
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
}

.pagination-btn {
  background-color: #a394b9;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
  margin: 0 10px;
}

.pagination-btn:disabled {
  background-color: #ddd;
  cursor: not-allowed;
}

.pagination-btn:hover:not(:disabled) {
  background-color: #decaca;
}

</style>

<script>
import axios from 'axios';
import HeaderComponent from "@/components/HeaderComponent.vue";
  export default {
    components: { HeaderComponent },
    data() {
      return {
        files: [],
        searchQuery: '',
        sortOption: 'newest',
        isEditModalVisible: false,
        editedFile: { id: null, title: '', description: '' },
        isModalVisible: false,
        fileToDelete: null,
        currentPage: 1,
        filesPerPage: 8,
      };
    },
    computed: {
      filteredFiles() {
        let filtered = this.files.filter((file) =>
            file.title.toLowerCase().includes(this.searchQuery.toLowerCase())
        );

        if (this.sortOption === 'newest') {
          filtered = filtered.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
        } else if (this.sortOption === 'oldest') {
          filtered = filtered.sort((a, b) => new Date(a.createdAt) - new Date(b.createdAt));
        } else if (this.sortOption === 'alpha') {
          filtered = filtered.sort((a, b) => a.title.localeCompare(b.title));
        } else if (this.sortOption === 'alpha-reverse') {
          filtered = filtered.sort((a, b) => b.title.localeCompare(a.title));
        }

        return filtered;
      },
      paginatedFiles() {
        const startIndex = (this.currentPage - 1) * this.filesPerPage;
        const endIndex = this.currentPage * this.filesPerPage;
        return this.filteredFiles.slice(startIndex, endIndex);
      },
      totalPages() {
        return Math.ceil(this.filteredFiles.length / this.filesPerPage);
      },
    },
    methods: {
      viewFileDetails(fileId) {
        this.$router.push({ name: 'fileContents', params: { taskId: fileId } });
      },
      openEditModal(file) {
        this.editedFile = { ...file };
        this.isEditModalVisible = true;
      },
      closeEditModal() {
        this.isEditModalVisible = false;
      },
      async saveFileChanges() {
        try {
          const token = localStorage.getItem('jwt_token');
          await axios.put(`http://127.0.0.1:8000/fileEdit/${this.editedFile.id}`, {
            title: this.editedFile.title,
            description: this.editedFile.description,
          }, {
            headers: { Authorization: `Bearer ${token}` },
          });

          this.files = this.files.map(file =>
              file.id === this.editedFile.id ? { ...file, ...this.editedFile } : file
          );
          this.closeEditModal();
        } catch (error) {
          alert('Ошибка сохранения файла')
          console.error('Ошибка сохранения файла:', error);
        }
      },
      openDeleteModal(fileId) {
        console.log("Setting modal visibility to true");
        this.fileToDelete = fileId;
        this.isModalVisible = true;
      },
      closeModal() {
        this.isModalVisible = false;
        this.fileToDelete = null;
      },
      async deleteFile() {
        try {
          const token = localStorage.getItem('jwt_token');
          await axios.delete(`http://127.0.0.1:8000/fileDelete/${this.fileToDelete}`, {
            headers: { Authorization: `Bearer ${token}` },
          });
          this.files = this.files.filter((file) => file.id !== this.fileToDelete);
          this.closeModal();
        } catch (error) {
          alert('Ошибка удаления файла')
          console.error('Ошибка удаления файла:', error);
        }
      },
      goToPage(page) {
        if (page >= 1 && page <= this.totalPages) {
          this.currentPage = page;
        }
      },
    },
    async created() {
      try {
        const token = localStorage.getItem('jwt_token');
        const response = await axios.get('http://127.0.0.1:8000/history/files', {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.files = response.data;
      } catch (error) {
        alert('Ошибка загрузки истории')
        console.error('Ошибка загрузки истории:', error);
      }
    },
  };

</script>

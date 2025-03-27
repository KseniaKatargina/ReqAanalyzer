<template>
  <Header />
  <div class="container">
    <div class="content-box">
      <div class="file-box">
        <h2>{{title}}</h2>

        <div v-if="specification.content.length">
          <div v-for="(section, sectionIndex) in specification.content" :key="sectionIndex" class="section">
            <h3>{{ sectionIndex + 1 }}. {{ section.title }}</h3>

            <div v-if="section.content && Array.isArray(section.content)">
              <div v-for="(subsection, subIndex) in section.content" :key="subIndex" class="subsection">
                <h4 v-if="section.title !== 'Сопроводительная информация' && section.title !== 'Общее описание'">
                  {{ subIndex + 1 }}. {{ subsection.title }}
                </h4>
                <h4 v-else>{{ subsection.title }}</h4>

                <div v-if="subsection.title && subsection.title.toLowerCase() === 'интерфейсы'">
                  <div v-for="(interfaceItem, interfaceIndex) in subsection.content" :key="interfaceIndex" class="interface-item">
                    <h5>{{ interfaceIndex + 1 }}. {{ interfaceItem.title }}</h5>
                    <p v-if="interfaceItem.content">{{ interfaceItem.content }}</p>
                  </div>
                </div>

                <div v-else>
                  <p v-if="subsection.content">{{ subsection.content }}</p>
                </div>

              </div>
            </div>
          </div>
        </div>
        <p v-else>Нет данных для отображения</p>
        <div class="btn-group">
          <div class="dropdown">
            <button class="save-btn">Сохранить как ▼</button>
            <div class="dropdown-content">
              <a @click.prevent="downloadFile('pdf')">PDF</a>
              <a @click.prevent="downloadFile('txt')">TXT</a>
              <a @click.prevent="downloadFile('docx')">DOCX</a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>



<script setup>
import Header from '@/components/HeaderComponent.vue';
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const specification = ref({ content: [] });
const title = ref("")
const formatSpecification = (spec) => {
  return spec.map((section, sectionIndex) => {
    let sectionText = `${sectionIndex + 1}. ${section.title}\n\n`;

    if (Array.isArray(section.content)) {
      section.content.forEach((subsection, subIndex) => {
        // Если раздел НЕ "Сопроводительная информация" или "Общее описание", добавляем нумерацию
        if (section.title !== "Сопроводительная информация" && section.title !== "Общее описание") {
          sectionText += `  ${subIndex + 1}. ${subsection.title}\n`;
        } else {
          sectionText += `  ${subsection.title}\n`;
        }

        // Если это "Интерфейсы", обрабатываем дополнительный уровень вложенности
        if (subsection.title.toLowerCase() === "интерфейсы" && Array.isArray(subsection.content)) {
          subsection.content.forEach((interfaceItem, interfaceIndex) => {
            sectionText += `    ${interfaceIndex + 1}. ${interfaceItem.title}\n`;
            if (interfaceItem.content) {
              sectionText += `      ${interfaceItem.content}\n`;
            }
          });
        } else {
          if (subsection.content) {
            sectionText += `    ${subsection.content}\n`;
          }
        }
      });
    }

    return sectionText;
  }).join("\n");
};

const downloadFile = async (format) => {
  try {
    const formattedText = formatSpecification(specification.value.content);
    const fileName = specification.value.title ? specification.value.title.replace(/\s+/g, '_') : 'specification';
    // Отправка запроса на сервер для скачивания файла в нужном формате
    const response = await fetch(`http://localhost:8000/downloadSpec/${route.params.id}?format=${format}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${localStorage.getItem("jwt_token")}`
      },
      body: JSON.stringify({ formattedText }) // Отправляем форматированный текст
    });

    if (!response.ok) {
      if (response.status === 404) this.$router.push('/404');
      else if (response.status === 401) this.$router.push('/401');
      else if (response.status >= 500) this.$router.push('/500');
    }



    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${fileName}.${format}`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    alert('Ошибка скачивания файла');
  }
};

const fetchSpecification = async () => {
  try {
    const response = await fetch(`http://localhost:8000/specifications/${route.params.id}`, {
      headers: { 'Authorization': `Bearer ${localStorage.getItem('jwt_token')}` },
    });
    if (!response.ok) {
      if (response.status === 404) this.$router.push('/404');
      else if (response.status === 401) this.$router.push('/401');
      else if (response.status >= 500) this.$router.push('/500');
    }
    const data = await response.json();
    specification.value = data;
    title.value = data.title;
  } catch (error) {
    console.error(error);
    specification.value = { content: [] };
  }
};

onMounted(fetchSpecification);
</script>

<style scoped>
.btn-group {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

.dropdown {
  position: relative;
  display: inline-block;
}

.dropdown-content {
  display: none;
  position: absolute;
  background-color: white;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
  min-width: 100px;
  border-radius: 8px;
  z-index: 1;
}

.dropdown:hover .dropdown-content {
  display: block;
}

.dropdown-content a {
  display: block;
  padding: 10px;
  text-decoration: none;
  color: black;
  cursor: pointer;
}

.dropdown-content a:hover {
  background-color: #dcd8e1;
}
.container {
  display: flex;
  justify-content: center;
  background: linear-gradient(135deg, #dcd8e1, #a394b9);
  height: 100vh;
  padding: 20px;
  overflow-y: auto;
}

.content-box {
  display: flex;
  gap: 20px;
  max-width: 800px;
  width: 100%;
}

.file-box {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  text-align: left;
  width: 100%;
  height: fit-content;
}

.section {
  margin-bottom: 20px;
}

.subsection {
  margin-left: 20px; /* отступ для первого уровня вложенности */
}

.subsection .subsection {
  margin-left: 20px; /* отступ для второго уровня вложенности */
}

.subsection .subsection .subsection {
  margin-left: 20px; /* отступ для третьего уровня вложенности */
}

.interface-item {
  margin-left: 30px; /* отступ для элементов интерфейса */
}

h3, h4, h5 {
  margin-top: 10px;
  margin-bottom: 5px;
}

.item {
  padding-left: 15px;
  margin-bottom: 10px;
}

.fields {
  padding-left: 20px;
  font-size: 14px;
  color: #555;
}

.field {
  margin-top: 5px;
}

.save-btn {
  padding: 10px 15px;
  background-color: #a394b9;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
}

.save-btn:hover {
  background-color: #dcd8e1;
}
</style>


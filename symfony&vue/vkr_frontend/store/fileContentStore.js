import { ref } from 'vue';



const fileContent = ref(null);

export function useFileContentStore() {
    function setFileContent(content) {
        fileContent.value = content;
    }

    return { fileContent, setFileContent };
}

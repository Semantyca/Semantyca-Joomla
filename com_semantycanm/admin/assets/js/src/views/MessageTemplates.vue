<template>
  <component :is="currentComponent" v-if="currentComponent === 'MessageTemplateGrid'" @row-click="handleRowClick" />
  <component :is="currentComponent" v-else :id="selectedId" @back="goBack" />
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue';
import { useNewsletterStore } from "../stores/newsletter/newsletterStore";
import MessageTemplateGrid from "../components/MessageTemplateGrid.vue";
import MessageTemplateEditor from "./MessageTemplateEditor.vue";

export default defineComponent({
  name: 'Templates',
  components: {
    MessageTemplateGrid,
    MessageTemplateEditor,
  },
  setup() {
    const newsLetterStore = useNewsletterStore();
    const currentComponent = ref('MessageTemplateGrid');
    const selectedId = ref(null);

    onMounted(() => {
      newsLetterStore.fetchNewsLetters(1, 100);
    });

    const handleRowClick = (row) => {
      selectedId.value = row.key;
      currentComponent.value = 'MessageTemplateEditor';
    };

    const goBack = () => {
      currentComponent.value = 'MessageTemplateGrid';
    };


    return {
      newsLetterStore,
      currentComponent,
      selectedId,
      handleRowClick,
      goBack
    };
  },
});
</script>

<style>
</style>

<template>
  <component :is="currentComponent" v-if="currentComponent === 'MessageTemplateGrid'"
             @row-click="handleRowClick"
             @create-new="handleCreateNew"
  />
  <component :is="currentComponent" v-else :id="selectedId" @back="goBack" />
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue';
import MessageTemplateGrid from "../components/lists/MessageTemplateGrid.vue";
import MessageTemplateEditor from "../components/forms/MessageTemplateEditor.vue";

export default defineComponent({
  name: 'MessageTemplates',
  components: {
    MessageTemplateGrid,
    MessageTemplateEditor,
  },
  setup() {
    const currentComponent = ref('MessageTemplateGrid');
    const selectedId = ref(null);


    const handleRowClick = (row) => {
      selectedId.value = row.key;
      currentComponent.value = 'MessageTemplateEditor';
    };

    const handleCreateNew = () => {
      currentComponent.value = 'MessageTemplateEditor';
    };

    const goBack = () => {
      currentComponent.value = 'MessageTemplateGrid';
    };


    return {
      currentComponent,
      selectedId,
      handleRowClick,
      handleCreateNew,
      goBack
    };
  },
});
</script>

<style>
</style>

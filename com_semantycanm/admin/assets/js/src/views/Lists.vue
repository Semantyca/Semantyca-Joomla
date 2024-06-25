<template>
  <component :is="currentComponent" v-if="currentComponent === 'MailingListGrid'"
             @row-click="handleRowClick"
             @create-new="handleCreateNew"
  />
  <component :is="currentComponent" v-else :id="selectedId" @back="goBack" />
</template>

<script>
import { defineComponent, ref } from 'vue';
import MailingListGrid from '../components/lists/MailingListGrid.vue';
import MailingListEditor from '../components/forms/MailingListEditor.vue';

export default defineComponent({
  name: 'Newsletters',
  components: {
    MailingListGrid,
    MailingListEditor,
  },
  setup() {
    const currentComponent = ref('MailingListGrid');
    const selectedId = ref(null);


    const handleRowClick = (row) => {
      selectedId.value = row.key;
      currentComponent.value = 'MailingListEditor';
    };

    const handleCreateNew = () => {
      currentComponent.value = 'MailingListEditor';
    };

    const goBack = () => {
      currentComponent.value = 'MailingListGrid';
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

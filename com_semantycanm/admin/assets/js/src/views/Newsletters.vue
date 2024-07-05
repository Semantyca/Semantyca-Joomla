<template>
  <component :is="currentComponent" v-if="currentComponent === 'NewsletterGrid'"
             @row-click="handleRowClick"
             @create-new="handleCreateNew"
  />
  <component :is="currentComponent" v-else :id="selectedId" @back="goBack" />
</template>

<script>
import { defineComponent, ref } from 'vue';
import NewsletterGrid from '../components/lists/NewsletterGrid.vue';
import Composer from '../components/forms/Composer.vue';

export default defineComponent({
  name: 'Newsletters',
  components: {
    NewsletterGrid,
    Composer,
  },
  setup() {
    const currentComponent = ref('NewsletterGrid');
    const selectedId = ref(null);


    const handleRowClick = (row) => {
      selectedId.value = row;
      currentComponent.value = 'Composer';
    };

    const handleCreateNew = () => {
      currentComponent.value = 'Composer';
    };

    const goBack = () => {
      currentComponent.value = 'NewsletterGrid';
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

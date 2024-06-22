<template>
  <component :is="currentComponent" v-if="currentComponent === 'NewsletterGrid'" @row-click="handleRowClick" />
  <component :is="currentComponent" v-else :id="selectedId" @back="goBack" />
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue';
import { useNewsletterStore } from "../stores/newsletter/newsletterStore";
import NewsletterGrid from '../components/NewsletterGrid.vue';
import Composer from './Composer.vue';

export default defineComponent({
  name: 'Newsletters',
  components: {
    NewsletterGrid,
    Composer,
  },
  setup() {
    const newsLetterStore = useNewsletterStore();
    const currentComponent = ref('NewsletterGrid');
    const selectedId = ref(null);

    onMounted(() => {
      newsLetterStore.fetchNewsLetters(1, 100);
    });

    const handleRowClick = (row) => {
      selectedId.value = row.key;
      currentComponent.value = 'Composer';
    };

    const goBack = () => {
      currentComponent.value = 'NewsletterGrid';
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

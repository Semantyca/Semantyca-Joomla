<template>
  <component :is="currentComponent" v-if="currentComponent === 'StatsGrid'"
             @row-click="handleRowClick"
             @create-new="handleCreateNew"
  />
  <component :is="currentComponent" v-else :id="selectedId" @back="goBack" />
</template>

<script>
import { defineComponent, ref } from 'vue';
import StatsDetails from '../components/forms/StatsDetails.vue';
import StatsGrid from "../components/lists/StatsGrid.vue";

export default defineComponent({
  name: 'Statistics',
  components: {
    StatsGrid,
    StatsDetails,
  },
  setup() {
    const currentComponent = ref('StatsGrid');
    const selectedId = ref(null);


    const handleRowClick = (row) => {
      selectedId.value = row.key;
      currentComponent.value = 'StatsDetails';
    };

    const handleCreateNew = () => {
      currentComponent.value = 'StatsDetails';
    };

    const goBack = () => {
      currentComponent.value = 'StatsGrid';
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

<template>
  <n-h3>Message statistics</n-h3>
  <n-grid :cols="1" x-gap="5" y-gap="10">
    <n-gi>
      <n-space>
        <n-button type="info" @click="$router.push('/list')">
          <template #icon>
            <n-icon>
              <arrow-big-left/>
            </n-icon>
          </template>
          Back
        </n-button>
        <n-button type="error" @click="handleDelete">
          {{ globalStore.translations.ARCHIVE }}
        </n-button>
      </n-space>
    </n-gi>
    <n-gi>
      <n-form inline ref="formRef" :rules="rules" label-placement="left" label-width="auto">
        <n-grid :cols="8">
          <!-- Add your form fields here -->
        </n-grid>
      </n-form>
    </n-gi>
  </n-grid>
</template>

<script>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useGlobalStore } from "../../stores/globalStore";
import {
  NButton,
  NCheckbox,
  NForm,
  NFormItem,
  NGi,
  NGrid,
  NH3,
  NIcon,
  NInput,
  NSpace,
} from "naive-ui";
import { ArrowBigLeft } from '@vicons/tabler';
import { rules, typeOptions } from '../../stores/template/templateEditorUtils';

export default {
  name: 'StatsDetails',
  components: {
    NButton, NSpace, NInput, NCheckbox, NForm, NFormItem, NGrid, NGi, NH3, NIcon, ArrowBigLeft
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const formRef = ref(null);
    const globalStore = useGlobalStore();

    onMounted(async () => {
      const id = route.params.id;
      // Fetch the stats details using the id
      // ...
    });

    const handleSave = () => {
      // Handle saving the stats details
      // ...
    };

    const handleDelete = async () => {
      // Handle deleting the stats details
      // ...
      await deleteStats(route.params.id);
      router.push('/stats');
    };

    return {
      globalStore,
      handleSave,
      handleDelete,
      rules,
      formRef,
      typeOptions,
    };
  }
};
</script>

<style>
/* Add any additional styles here */
</style>
<template>
  <n-h3>Mailing list editor</n-h3>
  <n-grid :cols="1" x-gap="5" y-gap="10">
    <n-gi>
      <n-space>
        <n-button type="info" @click="$emit('back')">
          <template #icon>
            <n-icon>
              <arrow-big-left/>
            </n-icon>
          </template>
          Back
        </n-button>
        <n-button type="primary" @click="handleSave">
          {{ globalStore.translations.SAVE }}
        </n-button>
        <n-button type="error" @click="handleDelete">
          {{ globalStore.translations.DELETE }}
        </n-button>
      </n-space>
    </n-gi>
    <n-gi>
      <n-form inline ref="formRef" :rules="rules" label-placement="left" label-width="auto">
        <n-grid :cols="8">


        </n-grid>
      </n-form>
    </n-gi>

  </n-grid>
</template>

<script>
import { onMounted, ref} from 'vue';
import {useGlobalStore} from "../../stores/globalStore";
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
import {ArrowBigLeft} from '@vicons/tabler'
import {rules, typeOptions} from '../../stores/template/templateEditorUtils';

export default {
  name: 'MailingListEditor',
  components: {
    NButton, NSpace, NInput, NCheckbox, NForm, NFormItem, NGrid, NGi, NH3, NIcon, ArrowBigLeft
  },
  props: {
    id: {
      type: Number,
      required: false,
    },
  },
  emits: ['back'],
  setup() {
    const formRef = ref(null);
    const globalStore = useGlobalStore();


    onMounted(async () => {
    });

    const handleSave = () => {
    }

    const handleDelete = () => {
    }

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

</style>

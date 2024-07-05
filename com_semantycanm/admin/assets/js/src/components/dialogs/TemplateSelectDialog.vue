<template>
  <n-space vertical size="large" style="width: 50%">
    <n-grid :cols="1" x-gap="5" y-gap="10">
      <n-grid-item>
        <n-data-table
            :columns="columns"
            :data="templateStore.templateSelectOptions"
            :row-key="row => row.id"
            @row-click="handleTemplateSelect"
            style="width: 100%;"
        />
      </n-grid-item>
    </n-grid>
    <n-grid :cols="1">
      <n-grid-item>
        <n-space align="center" justify="end">
          <n-button @click="onClose">Cancel</n-button>
          <n-button @click="handleOk" type="primary">Ok</n-button>
        </n-space>
      </n-grid-item>
    </n-grid>
  </n-space>
</template>

<script setup>
import {ref} from 'vue';
import {NButton, NGrid, NGridItem, NDataTable, NSpace} from 'naive-ui';
import {useMessageTemplateStore} from '../../stores/template/messageTemplateStore';

const props = defineProps({
  id: {
    type: Number,
    default: 0,
    required: false
  },
  onClose: {
    type: Function,
    required: true
  },
  onSelectTemplate: {
    type: Function,
    required: true
  }
});

const formRef = ref(null);
const templateStore = useMessageTemplateStore();
const selectedTemplateId = ref(null);

const columns = [
  {
    title: 'Template Name',
    key: 'name'
  }
];

const handleTemplateSelect = (row) => {
  selectedTemplateId.value = row.id;
};

const handleOk = () => {
  if (selectedTemplateId.value) {
    props.onSelectTemplate(selectedTemplateId.value);
    props.onClose();
  }
};
</script>

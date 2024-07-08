<template>
  <n-h3>All Templates</n-h3>
  <n-grid :cols="1" x-gap="5" y-gap="10">
    <n-gi>
      <n-space>
        <n-button type="primary" @click="createNew">
          {{ globalStore.translations.CREATE }}
        </n-button>
        <n-button type="error" :disabled="!hasCheckedRows" @click="deleteCheckedRows">
          {{ globalStore.translations.DELETE }}
        </n-button>
      </n-space>
    </n-gi>
    <n-gi class="mt-2">
      <n-data-table
          remote
          size="large"
          :row-key="rowKey"
          :columns="columns"
          :data="messageTemplateStore.getCurrentPage"
          :pagination="messageTemplateStore.getPagination"
          :row-props="getRowProps"
          @update:checked-row-keys="handleCheckedRowKeysChange"
      />
    </n-gi>
  </n-grid>
</template>

<script>
import {defineComponent, getCurrentInstance, ref, computed} from 'vue';
import {useGlobalStore} from "../../stores/globalStore";
import {useMessageTemplateStore} from "../../stores/template/messageTemplateStore";
import {NDataTable, NButton, NH3, NGi, NGrid, NSpace} from "naive-ui";

export default defineComponent({
  name: 'MessageTemplateGrid',
  components: {
    NDataTable,
    NButton,
    NSpace,
    NH3,
    NGrid,
    NGi,
  },
  emits: ['row-click', 'create-new'],
  setup() {
    const globalStore = useGlobalStore();
    const messageTemplateStore = useMessageTemplateStore();
    const {emit} = getCurrentInstance();
    const checkedRowKeys = ref([]);

    const fetchInitialData = async () => {
      await messageTemplateStore.fetchTemplates(1, 10);
    };

    fetchInitialData();

    const getRowProps = (row) => {
      return {
        style: 'cursor: pointer;',
        onClick: (event) => {
          if (event.target.type !== 'checkbox' && !event.target.closest('.n-checkbox')) {
            emit('row-click', row);
          }
        }
      };
    };

    const createNew = () => {
      emit('create-new');
    };

    const createColumns = () => {
      return [
        {
          type: "selection",
        },
        {
          title: 'Name',
          key: 'name'
        },
        {
          title: 'Description',
          key: 'description'
        }
      ];
    };

    const handleCheckedRowKeysChange = (keys) => {
      checkedRowKeys.value = keys;
    };

    const hasCheckedRows = computed(() => checkedRowKeys.value.length > 0);

    const deleteCheckedRows = async () => {
      try {
        await messageTemplateStore.deleteApi(checkedRowKeys.value);
        checkedRowKeys.value = [];
        await fetchInitialData();
      } catch (error) {
        console.error('Error deleting templates:', error);
      }
    };

    return {
      globalStore,
      messageTemplateStore,
      getRowProps,
      createNew,
      columns: createColumns(),
      rowKey: (row) => row.id,
      handleCheckedRowKeysChange,
      hasCheckedRows,
      deleteCheckedRows
    };
  },
});
</script>
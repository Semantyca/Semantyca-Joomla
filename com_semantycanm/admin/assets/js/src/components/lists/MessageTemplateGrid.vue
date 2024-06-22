<template>
  <n-h3>All Templates</n-h3>
  <n-grid :cols="1" x-gap="5" y-gap="10">
    <n-gi>
      <n-space>
        <n-button size="large" type="primary" @click="createNew">
          {{ globalStore.translations.CREATE }}
        </n-button>
        <n-button size="large" type="error" disabled>
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
          :data=" messageTemplateStore.getCurrentPage"
          :pagination="messageTemplateStore.getPagination"
          :row-props="getRowProps"
      />
    </n-gi>
  </n-grid>
</template>

<script>
import { defineComponent, getCurrentInstance, onMounted, onUnmounted } from 'vue';
import { useGlobalStore } from "../../stores/globalStore";
import { useMessageTemplateStore } from "../../stores/template/messageTemplateStore";
import { NDataTable, NButton, NH3, NGi, NGrid, NSpace } from "naive-ui";

export default defineComponent({
  name: 'TemplateGrid',
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
    const { emit } = getCurrentInstance();

    const fetchInitialData = async () => {
      await messageTemplateStore.fetchFromApi(1, 10);
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

    const handleKeyDown = (event) => {
      if (event.ctrlKey && event.key === 'n') {
        event.preventDefault();
        createNew();
      } else if (event.key === 'Delete') {
        console.log('Delete key pressed');
      }
    };

    onMounted(() => {
      window.addEventListener('keydown', handleKeyDown);
    });

    onUnmounted(() => {
      window.removeEventListener('keydown', handleKeyDown);
    });

    const createColumns = () => {
      return [
        {
          type: "selection",
          disabled(row) {
            return row.name === "Edward King 3";
          }
        },
        {
          title: 'Name',
          key: 'subject'
        },
        {
          title: 'Created',
          key: 'reg_date'
        }
      ];
    };

    return {
      globalStore,
      messageTemplateStore,
      getRowProps,
      createNew,
      columns: createColumns(),
      rowKey: (row) => row.key,
    };
  },
});

</script>

<style>
</style>

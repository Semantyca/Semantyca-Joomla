<template>
  <n-grid :cols="1" x-gap="5" y-gap="10">
    <n-gi>
      <n-page-header class="mb-3">
        <template #title>
          All Templates
        </template>
      </n-page-header>
    </n-gi>
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
import {defineComponent, ref, computed} from 'vue';
import {useGlobalStore} from "../../stores/globalStore";
import {useTemplateStore} from "../../stores/template/templateStore";
import {NDataTable, NButton, NH3, NGi, NGrid, NSpace, NPageHeader} from "naive-ui";
import {useRouter} from "vue-router";

export default defineComponent({
  name: 'TemplateGrid',
  components: {
    NPageHeader,
    NDataTable,
    NButton,
    NSpace,
    NH3,
    NGrid,
    NGi,
  },
  setup() {
    const globalStore = useGlobalStore();
    const templateStore = useTemplateStore();
    const checkedRowKeys = ref([]);
    const router = useRouter();

    const fetchInitialData = async () => {
      await templateStore.fetchTemplates(1, 10);
    };

    fetchInitialData();

    const getRowProps = (row) => {
      return {
        style: 'cursor: pointer;',
        onClick: (event) => {
          if (event.target.type !== 'checkbox' && !event.target.closest('.n-checkbox')) {
            templateStore.fetchTemplate(row.id);
            router.push(`/form/${row.id}`);
          }
        }
      };
    };

    const createNew = () => {
      router.push('/form');
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
        await templateStore.deleteApi(checkedRowKeys.value);
        checkedRowKeys.value = [];
        await fetchInitialData();
      } catch (error) {
        console.error('Error deleting templates:', error);
      }
    };

    return {
      globalStore,
      messageTemplateStore: templateStore,
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
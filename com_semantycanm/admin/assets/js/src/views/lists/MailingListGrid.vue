<template>
  <n-grid :cols="1" x-gap="5" y-gap="10">
    <n-gi>
      <n-h3>Mailing lists</n-h3>
    </n-gi>
    <n-gi>
      <n-space>
        <n-button type="primary" @click="$router.push('/form')">
          {{ globalStore.translations.CREATE }}
        </n-button>
        <n-button type="error" @click="handleDeleteSelected" :disabled="!checkedRowKeysRef.length">
          {{ globalStore.translations.DELETE }}
        </n-button>
      </n-space>
    </n-gi>
    <n-gi class="mt-2">
      <n-data-table
          remote
          size="large"
          :columns="columns"
          :data="mailingListStore.getMailingListPage"
          :pagination="mailingListStore.getPagination"
          :row-key="rowKey"
          :row-props="getRowProps"
          @update:page="handlePageChange"
          @update:page-size="handlePageSizeChange"
          @update:checked-row-keys="handleCheck"

      />
    </n-gi>
  </n-grid>
</template>

<script>
import {defineComponent, ref} from 'vue';
import {useGlobalStore} from "../../stores/globalStore";
import {NButton, NDataTable, NGi, NGrid, NH3, NSpace, useLoadingBar, useMessage} from "naive-ui";
import {useMailingListStore} from "../../stores/mailinglist/mailinglistStore";
import {useRouter} from "vue-router";

export default defineComponent({
  name: 'MailingListGrid',
  components: {
    NDataTable,
    NButton,
    NSpace,
    NH3,
    NGrid,
    NGi,
  },
  setup() {
    const router = useRouter();
    const globalStore = useGlobalStore();
    const mailingListStore = useMailingListStore();
    const checkedRowKeysRef = ref([]);
    const isLoading = ref(true);
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();

    const fetchInitialData = async () => {
      loadingBar.start();
      try {
        await mailingListStore.fetchMailingList(1, 10, true);
      } catch (error) {
        loadingBar.error();
        msgPopup.error(error.message);
        throw error;
      } finally {
        loadingBar.finish();
      }
    };

    fetchInitialData();

    const getRowProps = (row) => {
      return {
        style: 'cursor: pointer;',
        onClick: () => {
          router.push(`/form/${row.id}`);
        }
      };
    };

    function handlePageChange(page) {
      mailingListStore.fetchMailingList(page, mailingListStore.getPagination.pageSize, true);
    }

    function handlePageSizeChange(pageSize) {
      mailingListStore.fetchMailingList(mailingListStore.getPagination.page, pageSize, true);
    }

    const handleDeleteSelected = async () => {
      if (checkedRowKeysRef.value.length > 0) {
        await mailingListStore.deleteMailingList(checkedRowKeysRef.value);
        await mailingListStore.fetchMailingList(mailingListStore.getPagination.page, mailingListStore.getPagination.pageSize, true);
        checkedRowKeysRef.value = [];
      }
    };

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
          key: 'name'
        },
        {
          title: 'Created',
          key: 'reg_date'
        }
      ];
    };

    return {
      globalStore,
      mailingListStore,
      getRowProps,
      isLoading,
      columns: createColumns(),
      rowKey: (row) => row.id,
      handlePageChange,
      handlePageSizeChange,
      handleDeleteSelected,
      handleCheck(rowKeys) {
        checkedRowKeysRef.value = rowKeys;
      },
      checkedRowKeysRef,
    };
  },
});
</script>
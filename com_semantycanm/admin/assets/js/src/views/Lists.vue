<template>
  <n-grid :cols="1" x-gap="5" y-gap="5" class="mt-1">
    <n-gi :span="24" class="mt-3">
      <n-button type="primary" @click="() => showGroupEditor()" class="button-margin">
        Create
      </n-button>
      <n-button type="error" disabled class="button-margin">
        {{ globalStore.translations.DELETE }}
      </n-button>
    </n-gi>
    <n-gi :span="24">
      <n-data-table
          remote
          size="large"
          :columns="columns"
          :data="mailingListStore.docsListPage.docs"
          :bordered="false"
          :pagination="pagination"
          :row-key="rowKey"
          @update:page="handlePageChange"
          @update:page-size="handlePageSizeChange"
          @update:checked-row-keys="handleCheck"
      />
    </n-gi>
  </n-grid>
</template>


<script>
import {h, onMounted, reactive, ref} from 'vue';
import {
  NButton,
  NDataTable,
  NDivider,
  NGi,
  NGrid,
  NGridItem,
  NInput,
  NScrollbar,
  NSpace,
  useDialog,
  useLoadingBar,
  useMessage,
} from "naive-ui";
import {useUserGroupStore} from "../stores/userGroupStore";
import {useMailingListStore} from "../stores/mailinglistStore";
import {useGlobalStore} from "../stores/globalStore";
import GroupEditorDialog from "../components/GroupEditorDialog.vue";

export default {
  name: 'Lists',
  components: {
    NGrid,
    NGi,
    NScrollbar,
    NGridItem,
    NButton,
    NSpace,
    NInput,
    NDataTable,
    NDivider,
    GroupEditorDialog
  },
  setup() {
    const editorDialogRef = ref(null);
    const checkedRowKeysRef = ref([]);
    const globalStore = useGlobalStore();
    const userGroupStore = useUserGroupStore();
    const mailingListStore = useMailingListStore();
    const msgPopup = useMessage();
    const dialog = useDialog();
    const loadingBar = useLoadingBar()
    const pagination = reactive({
      page: 1,
      pageSize: 5,
      pageCount: 1,
      itemCount: 0,
      size: 'large'
    });

    onMounted(async () => {
      await mailingListStore.fetchMailingList(1, 5, pagination, msgPopup, loadingBar);
    });

    function handlePageChange(page) {
      mailingListStore.fetchMailingList(page, pagination.pageSize, pagination, msgPopup, loadingBar);
    }

    function handlePageSizeChange(pageSize) {
      mailingListStore.fetchMailingList(pagination.page, pageSize, pagination, msgPopup, loadingBar);
    }

    const handleDelete = async (id) => {
      try {
        await mailingListStore.deleteMailingListEntries([id], msgPopup, loadingBar);
        msgPopup.success('The mailing list deleted successfully');
        await mailingListStore.fetchMailingList(1, 5, pagination, msgPopup, loadingBar);
      } catch (error) {
        msgPopup.error(error.message, {
          closable: true,
          duration: 10000
        });
      }
    };

    const showGroupEditor = (id = 0) => {
      const handleClose = () => {
        dialog.destroyAll();
      };

      dialog.create({
        title: 'Edit Group',
        content: () => h(GroupEditorDialog, { id, onClose: handleClose }),
        style: 'width: 40%'
      });
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
        },
        {
          title: "Action",
          key: "actions",
          render(row) {
            return [
              h(NButton, {
                onClick: () => showGroupEditor(row.id),
                style: 'margin-right: 8px;',
                strong: true,
                secondary: true,
                type: "primary",
                size: "small",
              }, {default: () => 'Edit'}),
              h(NButton, {
                onClick: () => handleDelete(row.id),
                strong: true,
                secondary: true,
                type: "error",
                size: "small",
              }, {default: () => 'Delete'})
            ];
          }
        }
      ];
    };
    return {
      globalStore,
      userGroupStore,
      mailingListStore,
      columns: createColumns(),
      rowKey: (row) => row.id,
      handleCheck(rowKeys) {
        checkedRowKeysRef.value = rowKeys;
      },
      pagination,
      editorDialogRef,
      handlePageSizeChange,
      handlePageChange,
      showGroupEditor
    };
  }
};
</script>

<style scoped>
.button-margin {
  margin-left: 8px;
  margin-bottom: 12px;
}
</style>

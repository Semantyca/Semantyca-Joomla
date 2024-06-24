<template>
  <n-h3>Mailing lists</n-h3>
  <n-grid :cols="1" x-gap="5" y-gap="10" >
    <n-gi>
      <n-button type="primary" @click="() => showGroupEditor()" class="button-margin">
        Create
      </n-button>
      <n-button  type="error" @click="handleDeleteSelected" :disabled="!checkedRowKeysRef.length" class="button-margin">
        {{ globalStore.translations.DELETE }}
      </n-button>
    </n-gi>
    <n-gi class="mt-2">
      <n-data-table
          remote
          size="large"
          :columns="columns"
          :data="mailingListStore.getCurrentPage"
          :bordered="false"
          :pagination="mailingListStore.getPagination"
          :row-key="rowKey"
          @update:page="handlePageChange"
          @update:page-size="handlePageSizeChange"
          @update:checked-row-keys="handleCheck"
          @row-click="handleRowClick"
          :row-props="getRowProps"
      />
    </n-gi>
  </n-grid>
</template>

<script>
import {h, onMounted, ref} from 'vue';
import { NButton, NDataTable, NGi, NGrid, NGridItem, NH3, useDialog, useLoadingBar, useMessage } from "naive-ui";
import {useUserGroupStore} from "../stores/mailinglist/userGroupStore";
import {useMailingListStore} from "../stores/mailinglist/mailinglistStore";
import {useGlobalStore} from "../stores/globalStore";
import GroupEditorDialog from "../components/GroupEditorDialog.vue";

export default {
  name: 'Lists',
  components: { NGrid, NH3, NGi, NGridItem, NButton, NDataTable, GroupEditorDialog },
  setup() {
    const editorDialogRef = ref(null);
    const checkedRowKeysRef = ref([]);
    const globalStore = useGlobalStore();
    const userGroupStore = useUserGroupStore();
    const mailingListStore = useMailingListStore();
    const msgPopup = useMessage();
    const dialog = useDialog();
    const loadingBar = useLoadingBar();

    onMounted(async () => {
      await mailingListStore.getDocs(1, 10,true, msgPopup, loadingBar);
    });

    function handlePageChange(page) {
      mailingListStore.getDocs(page, mailingListStore.getPagination.pageSize, true, msgPopup, loadingBar);
    }

    function handlePageSizeChange(pageSize) {
      mailingListStore.getDocs(mailingListStore.getPagination.page, pageSize, true, msgPopup, loadingBar);
    }

    const handleDeleteSelected = async () => {
      try {
        await mailingListStore.deleteDocs(checkedRowKeysRef.value, msgPopup, loadingBar);
        msgPopup.success('The selected mailing list entries were deleted successfully');
        await mailingListStore.getDocs(1, 10, true, msgPopup, loadingBar);
        checkedRowKeysRef.value = [];
      } catch (error) {
        msgPopup.error(error.message, {
          closable: true,
          duration: 10000
        });
      }
    };

    const showGroupEditor = (id = -1) => {
      const handleClose = async (update) => {
        if (update) {
          try {
            await mailingListStore.getDocs(1, 10, true, msgPopup, loadingBar);
          } catch (e) {
            msgPopup.error(e.message, {
              closable: true,
              duration: 10000
            });
          }
        }
        dialog.destroyAll();
      };

      dialog.create({
        title: 'Edit Group',
        content: () => h(GroupEditorDialog, {id, onClose: handleClose}),
        style: 'width: 60%'
      });
    };

    const handleRowClick = (row) => {
      showGroupEditor(row.id);
    };


    const getRowProps = (row) => {
      return {
        style: 'cursor: pointer;',
        onClick: (event) => {
          if (event.target.type !== 'checkbox' && !event.target.closest('.n-checkbox')) {
            handleRowClick(row);
          }
        }
      };
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
      userGroupStore,
      mailingListStore,
      columns: createColumns(),
      rowKey: (row) => row.id,
      handleCheck(rowKeys) {
        checkedRowKeysRef.value = rowKeys;
      },
      editorDialogRef,
      handlePageSizeChange,
      handlePageChange,
      showGroupEditor,
      handleDeleteSelected,
      handleRowClick,
      getRowProps,
      checkedRowKeysRef
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

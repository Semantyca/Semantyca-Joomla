<template>
  <n-grid :cols="1" x-gap="12" y-gap="12" class="mt-1">
    <n-gi>
      <n-space>
        <n-button type="primary" class="button-margin">
          Export to Excel
        </n-button>
        <n-button type="error" @click="handleDeleteSelected" :disabled="!checkedRowKeysRef.length"
                  class="button-margin">
          {{ globalStore.translations.DELETE }}
        </n-button>
      </n-space>
    </n-gi>
    <n-gi>
      <n-data-table
          remote
          size="large"
          :columns="columns"
          :data="statStore.getCurrentPage"
          allow-checking-not-loaded
          :bordered="false"
          :pagination="statStore.getPagination"
          @update:page="handlePageChange"
          @update:page-size="handlePageSizeChange"
          :row-props="getRowProps"
      />
    </n-gi>
  </n-grid>
</template>

<script>
import {defineComponent, h, onMounted, reactive, ref} from 'vue';
import {
  NDataTable,
  NGi,
  NGrid,
  NPagination,
  NTag,
  useLoadingBar,
  useMessage,
  NButton,
  NSpace,
  NEllipsis,
  useDialog
} from 'naive-ui';
import {useStatStore} from "../stores/statistics/statStore";
import {useGlobalStore} from "../stores/globalStore";
import EventTable from "../components/EventTable.vue";
import NewsletterDialog from "../components/NewsletterDialog.vue";

export default defineComponent({
  name: 'Statistics',
  props: {
    spinnerIconUrl: String
  },
  components: {
    NGrid,
    NGi,
    NDataTable,
    NPagination,
    NButton,
    NSpace,
    NEllipsis
  },

  setup() {
    const statsTabRef = ref(null);
    const checkedRowKeysRef = ref([]);
    const globalStore = useGlobalStore();
    const statStore = useStatStore();
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar()
    const dialog = useDialog();

    onMounted(() => {
      statStore.fetchNewsletterData(1, 10, msgPopup, loadingBar);
    });

    function handlePageChange(page) {
      statStore.fetchNewsletterData(page, statStore.getPagination.pageSize, msgPopup, loadingBar);
    }

    function handlePageSizeChange(pageSize) {
      statStore.fetchNewsletterData(statStore.getPagination.page, pageSize, msgPopup, loadingBar);
    }

    const handleDeleteSelected = async () => {
      try {
        await statStore.deleteDocs(checkedRowKeysRef.value, msgPopup, loadingBar);
        msgPopup.success('The selected mailing list entries were deleted successfully');
        await statStore.fetchNewsletterData(1, 10, msgPopup, loadingBar);
        checkedRowKeysRef.value = [];
      } catch (error) {
        msgPopup.error(error.message, {
          closable: true,
          duration: 10000
        });
      }
    };

    const getRowProps = (row) => {
      return {
        style: 'cursor: pointer;',
        /*onClick: (event) => {
          if (event.target.type !== 'checkbox' && !event.target.closest('.n-checkbox')) {
           showNewsLetter(row);
          }
        }*/
      };
    };

    const showNewsLetter = (id = -1) => {

      dialog.create({
        title: 'Newsletter',
        content: () => h(NewsletterDialog, {id}),
        style: 'width: 60%'
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
          title: 'Created',
          key: 'reg_date',
          width: 120,
        },
        {
          type: 'expand',
          width: 50,
          renderExpand: (rowData) => {
            if (!statStore.eventListPage.docs[rowData.key]) {
              console.log('Fetching data for row', rowData.key);
              statStore.fetchEvents(rowData.key, msgPopup, loadingBar);
            }
            return h(EventTable, {
              data: statStore.eventListPage.docs[rowData.key],

            });
          },
        },
        {
          title: 'Status',
          key: 'status',
          width: 100,
          render(row) {
            let type;
            let title;
            switch (row.status) {
              case -1:
                type = 'error';
                title = 'Not fulfilled';
                break;
              case 1:
                type = 'warning';
                title = 'Processing';
                break;
              case 2:
                type = 'success';
                title = 'Done';
                break;
              default:
                type = 'tertiary';
                title = 'Not sent';
                break;
            }
            return h(NTag, {type}, {default: () => title});
          }
        },
        {
          title: 'Subject',
          key: 'subject',
          width: 200,
          ellipsis: true
        },
        {
          title: 'Content',
          key: 'message_content',
          width: 500,
          ellipsis: true
        }
      ]
    }
    return {
      globalStore,
      statsTabRef,
      columns: createColumns(),
      getRowProps,
      statStore,
      handlePageSizeChange,
      handlePageChange,
      handleDeleteSelected,
      checkedRowKeysRef
    };
  },
});
</script>
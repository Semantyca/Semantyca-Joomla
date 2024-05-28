<template>
  <n-grid :cols="1" x-gap="12" y-gap="12" class="mt-1">
    <n-gi>
      <n-space>
      <n-button type="primary" class="button-margin">
        Export to Excel
      </n-button>
      </n-space>
    </n-gi>
    <n-gi>
      <n-data-table
          remote
          size="large"
          :columns="columns"
          :data="statStore.docsListPage.docs"
          allow-checking-not-loaded
          :bordered="false"
          :pagination="pagination"
          @update:page="handlePageChange"
          @update:page-size="handlePageSizeChange"
      />
    </n-gi>
  </n-grid>
</template>

<script>
import {defineComponent, h, onMounted, reactive, ref} from 'vue';
import {NDataTable, NGi, NGrid, NPagination, NTag, useLoadingBar, useMessage, NButton, NSpace} from 'naive-ui';
import {useStatStore} from "../stores/statStore";
import {useGlobalStore} from "../stores/globalStore";
import EventTable from "../components/EventTable.vue";

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
    NSpace
  },

  setup() {
    const statsTabRef = ref(null);
    const globalStore = useGlobalStore();
    const statStore = useStatStore();
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar()
    const pagination = reactive({
      page: 1,
      pageSize: 10,
      pageCount: 1,
      itemCount: 0,
      size: 'large',
      showSizePicker: true,
      pageSizes: [10, 20, 50]
    });

    onMounted(() => {
      statStore.fetchStatisticsData(1, 10, pagination, msgPopup, loadingBar);
    });

    function handlePageChange(page) {
      statStore.fetchStatisticsData(page, pagination.pageSize, pagination, msgPopup, loadingBar);
    }

    function handlePageSizeChange(pageSize) {
      statStore.fetchStatisticsData(pagination.page, pageSize, pagination, msgPopup, loadingBar);
    }

    const createColumns = () => {
      return [
        {
          title: 'Created',
          key: 'reg_date'
        },
        {
          type: 'expand',
          renderExpand: (rowData) => {
            if (!statStore.eventListPage.docs[rowData.key]) {
              console.log('Fetching data for row', rowData.key);
              statStore.fetchEvents(rowData.key, msgPopup, loadingBar);
            }
            return h(EventTable, {
              data: statStore.eventListPage.docs[rowData.key],

            });
          }

        },
        {
          title: 'News letter',
          key: 'newsletter_id'
        },
        {
          title: 'Status',
          key: 'status',
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
                title = 'Unknown';
                break;
            }
            return h(NTag, {type}, {default: () => title});
          }
        }
      ]
    }
    return {
      globalStore,
      statsTabRef,
      columns: createColumns(),
      pagination,
      statStore,
      handlePageSizeChange,
      handlePageChange
    };
  },
});
</script>
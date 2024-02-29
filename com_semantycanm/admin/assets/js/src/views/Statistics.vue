<template>

  <div class="container mt-1">
    <div class="row">
      <div class="col-md-12">
        <div class="header-container d-flex justify-content-between align-items-center">
          <h3>{{ globalStore.translations.STATISTICS }}</h3>
        </div>
      </div>
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
    </div>
  </div>

</template>

<script>
import {defineComponent, h, onMounted, reactive, ref} from 'vue';
import {NButton, NDataTable, NPagination, NTag} from 'naive-ui';
import {useStatStore} from "../stores/statStore";
import {useGlobalStore} from "../stores/globalStore";

export default defineComponent({
  name: 'Statistics',
  props: {
    spinnerIconUrl: String
  },
  components: {
    NDataTable,
    NPagination
  },

  setup() {
    const statsTabRef = ref(null);
    const globalStore = useGlobalStore();
    const statStore = useStatStore();

    const pagination = reactive({
      page: 1,
      pageSize: 10,
      pageCount: 1,
      itemCount: 0,
      size: 'large',
      showSizePicker: true,
      pageSizes: [10, 20, 50]
    });

    function handlePageChange(page) {
      statStore.fetchStatisticsData(page, pagination.pageSize, pagination);
    }

    function handlePageSizeChange(pageSize) {
      statStore.fetchStatisticsData(pagination.page, pageSize, pagination);
    }

    onMounted(() => {
      statStore.fetchStatisticsData(1, 10, pagination);
    });

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
              statStore.fetchEvents(rowData.key);
            }
            return h(
                NButton,
                {
                  strong: true,
                  tertiary: true,
                  size: 'small',
                  onClick: () => play(row)
                },
                {default: () => 'Play'}
            )
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
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
import {NDataTable, NPagination, NTag} from 'naive-ui';
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
                title = 'Error';
                break;
              case 1:
                type = 'warning';
                title = 'Sending';
                break;
              case 2:
                type = 'info';
                title = 'Sent';
                break;
              case 3:
                type = 'success';
                title = 'Read';
                break;
              default:
                type = 'default';
                title = 'Unknown';
                break;
            }
            return h(NTag, {type}, {default: () => title});
          }
        },
        {
          title: 'Send time',
          key: 'sent_time'
        },
        {
          title: 'Recipients',
          key: 'recipients',
          render(row) {
            return row.recipients.length;
          }
        },
        {
          title: 'Opens',
          key: 'opens'
        },
        {
          title: 'Clicks',
          key: 'clicks'
        },
        {
          title: 'Unsubs.',
          key: 'unsubs'
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
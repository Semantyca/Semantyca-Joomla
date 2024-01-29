<template>

  <div class="container mt-1">
    <div class="row">
      <div class="col-md-12">
        <div class="header-container d-flex justify-content-between align-items-center">
          <h3>{{ store.translations.STATISTICS }}</h3>
          </div>
        </div>
        <n-data-table
            :columns="columns"
            :data="store.statisticView.docs"
            :bordered="false"
            :pagination="pagination"
        />
      </div>
    </div>

</template>

<script>
import {defineComponent, h, onMounted, reactive, ref} from 'vue';
import {useGlobalStore} from '../stores/globalStore';
import {NDataTable, NTag} from 'naive-ui';

export default defineComponent({
  name: 'Statistics',
  props: {
    spinnerIconUrl: String
  },
  components: {
    NDataTable,
  },

  setup() {
    const statsTabRef = ref(null);
    const store = useGlobalStore();

    const refreshStats = async (currentPage) => {
      await store.fetchStatisticsData(currentPage);
    };

    onMounted(() => {
      store.fetchStatisticsData(1);
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
          key: 'recipients'
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

    const paginationReactive = reactive({
      page: 1,
      pageSize: 10,
      onChange: (page) => {
        paginationReactive.page = page;
      },
      onUpdatePageSize: (pageSize) => {
        paginationReactive.pageSize = pageSize;
        paginationReactive.page = 1;
      }
    });

    return {
      statsTabRef,
      refreshStats,
      columns: createColumns(),
      data: [],
      pagination: paginationReactive,
      store
    };
  },
});
</script>

<style>
.n-data-table {
  font-size: 16px;
}

</style>
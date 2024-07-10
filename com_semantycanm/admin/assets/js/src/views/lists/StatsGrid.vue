<template>
  <n-grid :cols="1" x-gap="5" y-gap="10">
    <n-gi>
      <n-page-header class="mb-3">
        <template #title>
          Statistics
        </template>
      </n-page-header>
    </n-gi>
    <n-gi>
      <n-space>
        <n-button type="error" disabled>
          {{ globalStore.translations.ARCHIVE }}
        </n-button>
      </n-space>
    </n-gi>
    <n-gi class="mt-2">
      <n-data-table
          remote
          size="large"
          :columns="columns"
          :data="newsLetterStore.getCurrentPage"
          allow-checking-not-loaded
          :bordered="false"
          :pagination="newsLetterStore.getPagination"
          @update:page="handlePageChange"
          @update:page-size="handlePageSizeChange"
          :row-props="getRowProps"
          v-model:checked-row-keys="checkedRowKeysRef"
      />
    </n-gi>
  </n-grid>
</template>

<script>
import {defineComponent, getCurrentInstance, h, ref} from 'vue';
import {useGlobalStore} from "../../stores/globalStore";
import {NButton, NDataTable, NGi, NGrid, NH3, NPageHeader, NSpace} from "naive-ui";
import {useStatStore} from "../../stores/statistics/statStore";
import {useNewsletterStore} from "../../stores/newsletter/newsletterStore";

export default defineComponent({
  name: 'StatsGrid',
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
    const newsLetterStore = useNewsletterStore();
    const statStore = useStatStore();
    const {emit} = getCurrentInstance();
    const checkedRowKeysRef = ref([]);
    const statsTabRef = ref(null);

    const fetchInitialData = async () => {
      await newsLetterStore.fetchNewsLetters(1, 10);
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


    function handlePageChange(page) {
      newsLetterStore.fetchNewsLetters(page, newsLetterStore.getPagination.pageSize);
    }

    function handlePageSizeChange(pageSize) {
      newsLetterStore.fetchNewsLetters(1, pageSize);
    }

    const handleDeleteSelected = async () => {

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
     /*   {
          type: 'expand',
          width: 50,
          renderExpand: (rowData) => {
            if (!statStore.eventListPage.docs[rowData.key]) {
              statStore.fetchEvents(rowData.key, msgPopup, loadingBar);
            }
            return h(EventTable, {
              data: statStore.eventListPage.docs[rowData.key],
            });
          },
        },*/
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
    };

    const jsonToCsv = (json) => {
      const csvRows = [];
      const headers = Object.keys(json[0]);
      csvRows.push(headers.join(','));

      for (const row of json) {
        const values = headers.map(header => {
          const escaped = ('' + row[header]).replace(/"/g, '\\"');
          return `"${escaped}"`;
        });
        csvRows.push(values.join(','));
      }

      return csvRows.join('\n');
    };

    const downloadCsvFile = (csvContent, fileName) => {
      const blob = new Blob([csvContent], {type: 'text/csv'});
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.setAttribute('hidden', '');
      a.setAttribute('href', url);
      a.setAttribute('download', fileName);
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
    };

    const downloadCsv = () => {
      const data = statStore.getCurrentPage;
      if (!data.length) {
        //  msgPopup.warning('No data available to export');
        return;
      }

      const csvContent = jsonToCsv(data);
      downloadCsvFile(csvContent, 'newsletter_data.csv');
    };

    return {
      globalStore,
      statsTabRef,
      columns: createColumns(),
      getRowProps,
      newsLetterStore,
      statStore,
      handlePageSizeChange,
      handlePageChange,
      handleDeleteSelected,
      checkedRowKeysRef,
      exportLog: downloadCsv
    };
  },
});

</script>

<style>
</style>

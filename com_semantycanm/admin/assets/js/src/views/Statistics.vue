<template>
  <n-h3>Statistics</n-h3>
  <n-grid :cols="1" x-gap="5" y-gap="10">
    <n-gi>
      <n-space>
        <n-button type="primary" class="button-margin" @click="exportLog">
          Export to Excel
        </n-button>
        <n-button type="error" @click="handleDeleteSelected" :disabled="!checkedRowKeysRef.length"
                  class="button-margin">
          {{ globalStore.translations.DELETE }}
        </n-button>
      </n-space>
    </n-gi>
    <n-gi class="mt-2">
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
          v-model:checked-row-keys="checkedRowKeysRef"
      />
    </n-gi>
  </n-grid>
</template>

<script>
import { defineComponent, h, onMounted, ref } from 'vue';
import {
  NDataTable, NGi, NGrid, NPagination, useLoadingBar, useMessage, NButton, NSpace, NEllipsis, useDialog, NH3
} from 'naive-ui';
import { useStatStore } from "../stores/statistics/statStore";
import { useGlobalStore } from "../stores/globalStore";
import EventTable from "../components/EventTable.vue";
import NewsletterDialog from "../components/NewsletterDialog.vue";
import NewsletterApiManager from "../stores/newsletter/NewsletterApiManager";

export default defineComponent({
  name: 'Statistics',
  props: {
    spinnerIconUrl: String
  },
  components: {NGrid, NGi, NDataTable, NPagination, NButton, NSpace, NEllipsis, NH3 },
  setup() {
    const statsTabRef = ref(null);
    const checkedRowKeysRef = ref([]);
    const globalStore = useGlobalStore();
    const statStore = useStatStore();
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();
    const dialog = useDialog();

    const apiManager = new NewsletterApiManager(msgPopup, loadingBar);

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
      if (checkedRowKeysRef.value.length > 0) {
        try {
          await apiManager.deleteNewsletters(checkedRowKeysRef.value);
          msgPopup.success('Newsletters deleted successfully');
          // Refresh the data
          statStore.fetchNewsletterData(statStore.getPagination.page, statStore.getPagination.pageSize, msgPopup, loadingBar);
        } catch (error) {
          msgPopup.error('Failed to delete newsletters');
        }
      }
    };

    const getRowProps = (row) => {
      return {
        style: 'cursor: pointer;',
      };
    };

    const showNewsLetter = (id = -1) => {
      dialog.create({
        title: 'Newsletter',
        content: () => h(NewsletterDialog, { id }),
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
              statStore.fetchEvents(rowData.key, msgPopup, loadingBar);
            }
            return h(EventTable, {
              data: statStore.eventListPage.docs[rowData.key],
            });
          },
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
      const blob = new Blob([csvContent], { type: 'text/csv' });
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
        msgPopup.warning('No data available to export');
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

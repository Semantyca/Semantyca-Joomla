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
        <n-button type="primary" @click="exportLog">
          Export CSV
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
          :data="newsLetterStore.getCurrentPage"
          allow-checking-not-loaded
          :bordered="false"
          :pagination="newsLetterStore.getPagination"
          :row-key="rowKey"
          @update:page="handlePageChange"
          @update:page-size="handlePageSizeChange"
          :row-props="getRowProps"
          v-model:checked-row-keys="checkedRowKeysRef"
      />
    </n-gi>
  </n-grid>
</template>

<script>
import {defineComponent, ref} from 'vue';
import {useGlobalStore} from "../../stores/globalStore";
import {NButton, NDataTable, NGi, NGrid, NPageHeader, NSpace} from "naive-ui";
import {useStatStore} from "../../stores/statistics/statStore";
import {useNewsletterStore} from "../../stores/newsletter/newsletterStore";
import {useRouter} from "vue-router";

export default defineComponent({
  name: 'StatsGrid',
  components: {
    NPageHeader,
    NDataTable,
    NButton,
    NSpace,
    NGrid,
    NGi,
  },
  setup() {
    const globalStore = useGlobalStore();
    const newsLetterStore = useNewsletterStore();
    const statStore = useStatStore();
    const checkedRowKeysRef = ref([]);
    const router = useRouter();

    const fetchInitialData = async () => {
      await newsLetterStore.fetchNewsLetters(1, 10);
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
      newsLetterStore.fetchNewsLetters(page, newsLetterStore.getPagination.pageSize);
    }

    function handlePageSizeChange(pageSize) {
      newsLetterStore.fetchNewsLetters(1, pageSize);
    }

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
      ];
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

    const handleDeleteSelected = async () => {
      if (checkedRowKeysRef.value.length > 0) {
        await statStore.deleteDocs(checkedRowKeysRef.value);
        await newsLetterStore.fetchNewsLetters(1, 10);
        checkedRowKeysRef.value = [];
      }
    };
    return {
      globalStore,
      columns: createColumns(),
      handleDeleteSelected,
      getRowProps,
      newsLetterStore,
      statStore,
      handlePageSizeChange,
      handlePageChange,
      rowKey: (row) => row.id,
      checkedRowKeysRef,
      exportLog: downloadCsv
    };
  },
});
</script>
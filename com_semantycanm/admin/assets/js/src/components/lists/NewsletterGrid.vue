<template>
  <n-h3>All Newsletters</n-h3>
  <n-grid :cols="1" x-gap="5" y-gap="10">
    <n-gi>
      <n-space>
        <n-button  type="primary"  @click="createNew">
          {{ globalStore.translations.CREATE }}
        </n-button>
        <n-button  type="error" disabled>
          {{ globalStore.translations.DELETE }}
        </n-button>
      </n-space>
    </n-gi>
    <n-gi class="mt-2">
      <n-data-table
          remote
          size="large"
          :row-key="rowKey"
          :columns="columns"
          :data="newsLetterStore.getCurrentPage"
          :pagination="newsLetterStore.getPagination"
          :row-props="getRowProps"
      />
    </n-gi>
  </n-grid>
</template>

<script>
import {defineComponent, getCurrentInstance, onMounted} from 'vue';
import { useGlobalStore } from "../../stores/globalStore";
import { useNewsletterStore } from "../../stores/newsletter/newsletterStore";
import {
  NDataTable,
  NButton,
  NH3,
  NGi,
  NGrid,
  NSpace,
} from "naive-ui";

export default defineComponent({
  name: 'NewsletterGrid',
  components: {
    NDataTable,
    NButton,
    NSpace,
    NH3,
    NGrid,
    NGi,
  },
  emits: ['row-click', 'create-new'],
  setup() {
    const globalStore = useGlobalStore();
    const newsLetterStore = useNewsletterStore();
    const { emit } = getCurrentInstance();

    onMounted(() => {
      newsLetterStore.fetchNewsLetters(1, 100);
    });

    const getRowProps = (row) => {
      return {
        style: 'cursor: pointer;',
        onClick: (event) => {
          if (event.target.type !== 'checkbox' && !event.target.closest('.n-checkbox')) {
            emit('row-click', row.id);
          }
        }
      };
    };

    const createNew = () => {
      emit('create-new');
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
          key: 'subject'
        },
        {
          title: 'Created',
          key: 'reg_date'
        }
      ];
    };

    return {
      globalStore,
      newsLetterStore,
      getRowProps,
      createNew,
      columns: createColumns(),
      rowKey: (row) => row.id,
    };
  },
});
</script>

<style>
</style>

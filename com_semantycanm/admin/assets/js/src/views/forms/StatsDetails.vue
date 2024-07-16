<template>
  <n-grid :cols="1" x-gap="5" y-gap="10">
    <n-gi>
      <n-page-header class="mb-3">
        <template #title>
          Message statistics
        </template>
      </n-page-header>
    </n-gi>
    <n-gi>
      <n-space>
        <n-button type="info" @click="$router.push('/list')">
          <template #icon>
            <n-icon>
              <arrow-big-left/>
            </n-icon>
          </template>
          Back
        </n-button>
        <n-button type="error" @click="handleDelete">
          {{ globalStore.translations.DELETE }}
        </n-button>
      </n-space>
    </n-gi>
    <n-gi>
      <n-data-table
          :columns="columns"
          :data="statsStore.getCurrentEventPage"/>
    </n-gi>
  </n-grid>
</template>

<script>
import {h, onMounted, ref} from 'vue';
import {useRoute, useRouter} from 'vue-router';
import {useGlobalStore} from "../../stores/globalStore";
import {
  NButton, NDataTable,
  NCheckbox,
  NForm,
  NFormItem,
  NGi,
  NGrid,
  NH3,
  NIcon,
  NInput,
  NSpace,
  NPageHeader, NTag
} from "naive-ui";
import {ArrowBigLeft, Check} from '@vicons/tabler';
import {rules, typeOptions} from '../../stores/template/templateEditorUtils';
import {useStatStore} from "../../stores/statistics/statStore";

export default {
  name: 'StatsDetails',
  components: {
    NButton, NSpace, NInput, NCheckbox, NForm, NFormItem, NGrid, NGi, NH3, NIcon, ArrowBigLeft, NPageHeader, NDataTable
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const formRef = ref(null);
    const globalStore = useGlobalStore();
    const statsStore = useStatStore();

    onMounted(async () => {
      const id = route.params.id;
      await statsStore.fetchEvents(id);
    });

    const handleDelete = async () => {
      await statStore.deleteDocs(id);
      router.push('/list');
    };

    const columns = [
      {
        title: 'Sending Date',
        key: 'sending_reg_date',
        width: 200,
        render(row) {
          return row.sending_reg_date ? new Date(row.sending_reg_date).toLocaleString() : 'No Sending Date Available';
        }
      },
      {
        title: 'Subscriber Email',
        key: 'subscriber_email',
        width: 200,
      },
      {
        title: 'Events',
        key: 'events',
        width: 350,
        render(row) {
          return h('div', {}, row.events.map(event => {
            let title;
            switch (event.event_type) {
              case 100:
                title = 'Dispatched';
                break;
              case 101:
                title = 'Read';
                break;
              case 102:
                title = 'Unsubscribed';
                break;
              case 103:
                title = 'Click';
                break;
              default:
                title = 'Unknown';
                break;
            }
            return h(NTag, {
              type: event.fulfilled === 2 ? 'success' : 'default',
              style: 'margin-right: 8px;',
            }, {
              default: () => [
                title,
                event.fulfilled === 2 ? h(NIcon, {component: Check, style: 'margin-left: 8px;'}) : null
              ]
            });
          }));
        }
      },
      {
        title: 'Errors',
        key: 'errors',
        render(row) {
          return h('div', {}, row.errors.map(error => h('div', {style: 'color: red;'}, error)));
        }
      }
    ]

    return {
      globalStore,
      statsStore,
      handleDelete,
      rules,
      formRef,
      typeOptions,
      columns,
    };
  }
}
;
</script>

<style>
</style>
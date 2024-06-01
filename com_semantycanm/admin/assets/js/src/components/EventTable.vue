```javascript
// Updated script to remove 'Fulfilled' column and make NTag green if row.fulfilled is true
<template>
  <div class="table-container">
    <n-data-table :columns="columns" :data="data"/>
  </div>
</template>

<script>
import { NDataTable, NPagination, NTag, NIcon } from "naive-ui";
import { h } from "vue";
import { Check } from '@vicons/tabler';

export default {
  props: {
    data: Array
  },
  components: {
    NDataTable,
    NPagination,
    NIcon
  },
  data() {
    return {
      columns: [
        {
          title: 'Attempt time',
          key: 'reg_date',
          width: 200,
          render(row) {
            if (row.event_type === 100) {
              return row.reg_date ? new Date(row.reg_date).toLocaleString() : 'No Attempt Time Available';
            }
            return '';
          }
        },
        {
          title: 'Event Name',
          key: 'event_type',
          width: 200,
          render(row) {
            let type;
            let title;
            switch (row.event_type) {
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
            return h('div', {}, [
              h(NTag, { type: row.fulfilled === 2 ? 'success' : type }, { default: () => [
                  title,
                  row.fulfilled === 2 ? h(NIcon, { component: Check, style: 'margin-left: 8px;' }) : null
                ]})
            ]);
          }
        },
        {
          title: 'Subscriber',
          key: 'subscriber_email',
          width: 200,
        },
        {
          title: 'Errors',
          key: 'errors',
          render(row) {
            let errors = [];
            try {
              errors = JSON.parse(row.errors);
            } catch (e) {
              console.error('Error parsing JSON from errors field', e);
              return '';
            }

            if (!errors.length || !errors[0].error) {
              return '';
            }

            return h('div', {}, errors.map(errorObj => h('div', { style: 'color: red;' }, errorObj.error)));
          }
        },
      ]
    };
  },
};
</script>

<style>
.table-container {
  margin-left: 30px; /* Adjust the margin as needed */
}
</style>

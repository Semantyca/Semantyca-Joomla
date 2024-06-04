<template>
  <div class="table-container">
    <n-data-table :columns="columns" :data="formattedData"/>
  </div>
</template>

<script>
import { NDataTable, NTag, NIcon } from "naive-ui";
import { h } from "vue";
import { Check } from '@vicons/tabler';

export default {
  props: {
    data: {
      type: Array,
      default: () => []
    }
  },
  components: {
    NDataTable,
    NIcon
  },
  data() {
    return {
      columns: [
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
              }, { default: () => [
                  title,
                  event.fulfilled === 2 ? h(NIcon, { component: Check, style: 'margin-left: 8px;' }) : null
                ]});
            }));
          }
        },
        {
          title: 'Errors',
          key: 'errors',
          render(row) {
            return h('div', {}, row.errors.map(error => h('div', { style: 'color: red;' }, error)));
          }
        }
      ]
    };
  },
  computed: {
    formattedData() {
      return this.data.map(doc => ({
        sending_reg_date: doc.sending_reg_date,
        subscriber_email: doc.subscriber_email,
        events: doc.events,
        errors: doc.errors
      }));
    }
  }
};
</script>

<style>
.table-container {
  margin-left: 30px; /* Adjust the margin as needed */
}
</style>

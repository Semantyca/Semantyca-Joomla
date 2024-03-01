<template>
  <div class="table-container">
    <n-data-table :columns="columns" :data="data"/>
  </div>
</template>

<script>
import {NDataTable, NPagination, NTag} from "naive-ui";
import {h} from "vue";

export default {

  props: {
    data: Array
  },
  components: {
    NDataTable,
    NPagination
  },
  data() {
    return {
      columns: [
        {
          title: 'Event Name',
          key: 'event_type',
          render(row) {
            let type;
            let title;
            switch (row.event_type) {
              case 100:
                type = 'info';
                title = 'Dispatched';
                break;
              case 101:
                type = 'info';
                title = 'Read';
                break;
              case 102:
                type = 'info';
                title = 'Unsubscribed';
                break;
              case 103:
                type = 'info';
                title = 'Click';
                break;
              default:
                type = 'tertiary';
                title = 'Unknown';
                break;
            }
            return h(NTag, {type}, {default: () => title});
          }
        },
        {
          title: 'Fulfilled',
          key: 'fulfilled',
          render(row) {
            if (row.fulfilled) {
              return "YES";
            }
            return "";
          }
        },
        {
          title: 'Subscriber',
          key: 'subscriber_email',
        },
        {
          title: 'Date',
          key: 'event_date',
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

            return h('div', {}, errors.map(errorObj => h('div', {style: 'color: red;'}, errorObj.error)));

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
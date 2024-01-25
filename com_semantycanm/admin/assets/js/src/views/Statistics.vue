<template>
  <div class="container mt-1">
    <div class="row">
      <div class="col-md-12">
        <div class="header-container d-flex justify-content-between align-items-center">
          <h3>{{ store.translations.STATISTICS }}</h3>
          <div id="statSpinner" class="spinner">
            <img src="components/com_semantycanm/assets/images/spinner.svg" alt="Loading" class="spinner-icon">
          </div>
          <div style="display: flex; justify-content: space-between; align-items: start;">
            <div style="color: gray; display: flex; gap: 5px; align-items: center;">
              <label for="totalStatList">Total:</label>
              <input type="text" id="totalStatList" v-model="store.statisticView.totalStatList" readonly
                     style="width: 30px; border: none; background-color: transparent; color: inherit;"/>
              <label for="currentStatList">Page:</label>
              <input type="text" id="currentStatList" v-model="store.statisticView.currentStatList" readonly
                     style="width: 20px; border: none; background-color: transparent; color: inherit;"/>
              <label for="maxStatList">of</label>
              <input type="text" id="maxStatList" v-model="store.statisticView.maxStatList" readonly
                     style="width: 30px; border: none; background-color: transparent; color: inherit;"/>
            </div>
            <div class="pagination-container mb-3 me-2">
              <a class="btn btn-primary btn-sm" href="#"
                 @click.prevent="refreshStats(1)">{{ store.translations.FIRST }}</a>
              <a class="btn btn-primary btn-sm" href="#"
                 @click.prevent="refreshStats(store.statisticView.currentStatList - 1)">{{
                  store.translations.PREVIOUS
                }}</a>
              <a class="btn btn-primary btn-sm" href="#"
                 @click.prevent="refreshStats(store.statisticView.currentStatList + 1)">{{
                  store.translations.NEXT
                }}</a>
              <a class="btn btn-primary btn-sm" href="#" @click.prevent="refreshStats(store.statisticView.maxStatList)">{{
                  store.translations.LAST
                }}</a>
            </div>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
            <tr>
              <th style="width: 5%;">
                <button class="btn btn-outline-secondary refresh-button" type="button" @click="refreshStats(1)">
                  <img src="components/com_semantycanm/assets/images/refresh.png" alt="Refresh" class="refresh-icon">
                </button>
              </th>
              <th style="width: 20%;">{{ store.translations.NEWSLETTERS_LIST }}</th>
              <th style="width: 10%;">{{ store.translations.STATUS }}</th>
              <th style="width: 15%;">{{ store.translations.SEND_TIME }}</th>
              <th style="width: 10%;">{{ store.translations.RECIPIENTS }}</th>
              <th style="width: 10%;">{{ store.translations.OPENS }}</th>
              <th style="width: 10%;">{{ store.translations.CLICK }}</th>
              <th style="width: 10%;">{{ store.translations.UNSUBS }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="stat in store.statisticView.docs" :key="stat.id">
              <td style="width: 5%;"><input type="checkbox" :value="stat.id"></td>
              <td style="width: 20%;">{{ stat.newsletter_id }}</td>
              <td style="width: 10%;" v-html="getBadge(stat.status)"></td>
              <td style="width: 15%;">{{ stat.sent_time || 'N/A' }}</td>
              <td style="width: 10%;" v-html="stat.recipients.length"></td>
              <td style="width: 10%;">{{ stat.opens }}</td>
              <td style="width: 10%;">{{ stat.clicks }}</td>
              <td style="width: 10%;">{{ stat.unsubs }}</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {defineComponent, onMounted} from 'vue';
import {useGlobalStore} from '../stores/globalStore';

export default defineComponent({

  props: {
    spinnerIconUrl: String
  },

  setup() {
    const store = useGlobalStore();

    const refreshStats = async (currentPage) => {
      showSpinner('statSpinner');
      await store.fetchStatisticsData(currentPage);
    };

    const getBadge = (status) => {
      switch (status) {
        case -1:
          return `<span class="badge bg-danger">error</span>`;
        case 1:
          return `<span class="badge bg-warning text-dark">sending</span>`;
        case 2:
          return `<span class="badge bg-info text-light">sent</span>`;
        case 3:
          return `<span class="badge bg-success">read</span>`;
        default:
          return `<span class="badge bg-secondary">unknown</span>`;
      }
    };

    onMounted(() => {
      refreshStats(1);
    });

    return {
      refreshStats,
      getBadge,
      store,
    };
  },
});
</script>

<style>
.pagination-container a.btn {
  margin-right: 5px;
}

</style>
<template>
  <div class="container mt-5">
    <div class="row">
      <div class="col-md-12">
        <div class="header-container d-flex justify-content-between align-items-center">
          <h3>{{ statisticsText }}</h3>
          <div id="statSpinner" class="spinner">
            <img :src="spinnerIconUrl" alt="Loading" class="spinner-icon">
          </div>
          <div style="display: flex; justify-content: space-between; align-items: start;">
            <div style="color: gray; display: flex; gap: 5px; align-items: center;">
              <label for="totalStatList">Total:</label>
              <input type="text" id="totalStatList" v-model="totalStatList" readonly
                     style="width: 30px; border: none; background-color: transparent; color: inherit;"/>
              <label for="currentStatList">Page:</label>
              <input type="text" id="currentStatList" v-model="currentStatList" readonly
                     style="width: 20px; border: none; background-color: transparent; color: inherit;"/>
              <label for="maxStatList">of</label>
              <input type="text" id="maxStatList" v-model="maxStatList" readonly
                     style="width: 30px; border: none; background-color: transparent; color: inherit;"/>
            </div>
            <div class="pagination-container mb-3 me-2">
              <a class="btn btn-primary btn-sm" href="#" @click.prevent="refreshStats(1)">{{ firstText }}</a>
              <a class="btn btn-primary btn-sm" href="#"
                 @click.prevent="refreshStats(currentStatList - 1)">{{ previousText }}</a>
              <a class="btn btn-primary btn-sm" href="#" @click.prevent="refreshStats(currentStatList + 1)">{{
                  nextText
                }}</a>
              <a class="btn btn-primary btn-sm" href="#" @click.prevent="refreshStats(maxStatList)">{{ lastText }}</a>
            </div>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
            <tr>
              <th style="width: 5%;">
                <button class="btn btn-outline-secondary refresh-button" type="button" @click="refreshStats(1)">
                  <img :src="refreshIconUrl" alt="Refresh" class="refresh-icon">
                </button>
              </th>
              <th style="width: 20%;">{{ newsletterText }}</th>
              <th style="width: 10%;">{{ statusText }}</th>
              <th style="width: 15%;">{{ sendTimeText }}</th>
              <th style="width: 10%;">{{ recipientsText }}</th>
              <th style="width: 10%;">{{ opensText }}</th>
              <th style="width: 10%;">{{ clicksText }}</th>
              <th style="width: 10%;">{{ unsubsText }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="stat in stats" :key="stat.id">
              <td style="width: 5%;"><input type="checkbox" :value="stat.id"></td>
              <td style="width: 20%;">{{ stat.newsletter_id }}</td>
              <td style="width: 10%;">{{ getBadge(stat.status) }}</td>
              <td style="width: 15%;">{{ stat.sent_time || 'N/A' }}</td>
              <td style="width: 10%;">{{ stat.recipients.length }}</td>
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
import {defineComponent} from 'vue';

export default defineComponent({
  name: 'StatisticsComponent',
  props: {
    statisticsText: String,
    newsletterText: String,
    statusText: String,
    sendTimeText: String,
    recipientsText: String,
    opensText: String,
    clicksText: String,
    unsubsText: String,
    firstText: String,
    previousText: String,
    nextText: String,
    lastText: String,
    spinnerIconUrl: String,
    refreshIconUrl: String
  },
  data() {
    return {
      stats: [],
      totalStatList: 0,
      currentStatList: 1,
      maxStatList: 1
    };
  },
  methods: {
    async refreshStats(currentPage) {
      // AJAX call to fetch stats
    },
    getBadge(status) {
      // Status badge logic
    }
  },
  mounted() {
    this.refreshStats(1);
  }
});
</script>

<style scoped>
/* Component-specific styles */
</style>

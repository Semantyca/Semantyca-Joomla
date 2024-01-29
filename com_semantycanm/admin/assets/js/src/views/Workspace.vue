<template>
  <div id="alertPlaceholder"></div>
  <div id="loadingSpinner" class="loading-spinner">yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy</div>
  <n-tabs>
    <n-tab-pane name="Lists">
      <Lists></Lists>
    </n-tab-pane>
    <n-tab-pane name="Composer">
      <Composer></Composer>
    </n-tab-pane>
    <n-tab-pane name="Newsletter">
      <NewsletterDashboard></NewsletterDashboard>
    </n-tab-pane>
    <n-tab-pane name="Statistics">
      <Statistics></Statistics>
    </n-tab-pane>
    <n-tab-pane name="Template">
      <TemplateEditor></TemplateEditor>
    </n-tab-pane>
  </n-tabs>

</template>

<script>
import {NCard, NTabPane, NTabs} from 'naive-ui'
import Lists from "./Lists.vue";
import Composer from "./Composer.vue";
import NewsletterDashboard from "./NewsletterDashboard.vue";
import Statistics from "./Statistics.vue";
import TemplateEditor from "./TemplateEditor.vue";

export default {
  components: {
    NCard,
    NTabs,
    NTabPane,
    Lists,
    Composer,
    NewsletterDashboard,
    Statistics,
    TemplateEditor
  }
}

</script>

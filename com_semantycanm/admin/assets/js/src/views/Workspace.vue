<template>
  <div id="alertPlaceholder"></div>
  <n-tabs v-model:value="activeTab">
    <n-tab-pane name="Lists" tab="Lists">
      <Lists></Lists>
    </n-tab-pane>
    <n-tab-pane name="Composer" tab="Composer">
      <Composer @change-tab="handleChangeTab"></Composer>
    </n-tab-pane>
    <n-tab-pane name="Newsletter" tab="Newsletter">
      <NewsletterDashboard></NewsletterDashboard>
    </n-tab-pane>
    <n-tab-pane name="Statistics" tab="Statistics">
      <Statistics></Statistics>
    </n-tab-pane>
    <n-tab-pane name="Template" tab="Template">
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
import {ref} from "vue";

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
  },
  setup() {
    const activeTab = ref('Lists');
    const handleChangeTab = (tabName) => {
      activeTab.value = tabName;
    };

    return {
      activeTab,
      handleChangeTab,
    };
  },
}

</script>

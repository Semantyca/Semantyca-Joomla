<template>
  <div id="alertPlaceholder"></div>
  <n-tabs v-model:value="activeTab">
    <n-tab-pane name="Lists" tab="Lists">
      <Lists></Lists>
    </n-tab-pane>
    <n-tab-pane name="Composer" tab="Composer">
      <Composer @change-tab="handleChangeTab" @content-changed="handleContentChanged"></Composer>
    </n-tab-pane>
    <n-tab-pane name="Newsletter" tab="Newsletter">
      <NewsletterDashboard :messageContent="messageContent"></NewsletterDashboard>
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
import {ref} from "vue";
import Lists from "./Lists.vue";
import Composer from "./Composer.vue";
import NewsletterDashboard from "./NewsletterDashboard.vue";
import Statistics from "./Statistics.vue";
import TemplateEditor from "./TemplateEditor.vue";
import {NTabPane, NTabs} from 'naive-ui';

export default {
  components: {
    NTabPane,
    NTabs,
    Lists,
    Composer,
    NewsletterDashboard,
    Statistics,
    TemplateEditor,
  },
  setup() {
    const activeTab = ref('Lists');
    const messageContent = ref('');

    const handleChangeTab = (tabName) => {
      activeTab.value = tabName;
    };

    const handleContentChanged = (content) => {
      messageContent.value = content;
    };

    return {
      activeTab,
      messageContent,
      handleChangeTab,
      handleContentChanged,
    };
  },
}
</script>

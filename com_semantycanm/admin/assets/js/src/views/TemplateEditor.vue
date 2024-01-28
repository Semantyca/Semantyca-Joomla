<template>
  <div id="templateSpinner" class="loading-spinner"></div>
  <editor
      :api-key="store.tinyMceLic"
      :init="templateEditorConfig"
      v-model="store.template.html"></editor>

  <div class="col-mt-3" style="margin-top: 10px;">
    <button class="btn btn-success" style="margin-right: 10px;" @click="saveTemplate">Save</button>
  </div>
</template>

<script>
import {onMounted} from 'vue';
import Editor from '@tinymce/tinymce-vue';
import {useGlobalStore} from "../stores/globalStore";

const TEMPLATE_SPINNER = 'templateSpinner';
export default {
  name: 'TemplateEditor',
  components: {Editor},

  setup() {
    const store = useGlobalStore();

    const templateEditorConfig = {
      model_url: 'components/com_semantycanm/assets/bundle/models/dom/model.js',
      skin_url: 'components/com_semantycanm/assets/bundle/skins/ui/oxide',
      content_css: 'components/com_semantycanm/assets/bundle/skins/content/default/content.css',
      menubar: false,
      statusbar: false,
      plugins: 'code',
      toolbar: 'code'
    };

    const saveTemplate = async () => {
      startLoading(TEMPLATE_SPINNER);
      const endpoint = `index.php?option=com_semantycanm&task=Template.update&id=${encodeURIComponent(store.template.id)}`;
      const data = {
        // html: state['html']
      };

      try {
        const response = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(data)
        });

        if (!response.ok) {
          throw new Error(`HTTP error, status = ${response.status}`);
        }

        console.log('Template saved successfully');
        stopLoading(TEMPLATE_SPINNER);
      } catch (error) {
        showErrorBar(`${endpoint}`, error.message);
        stopLoading(TEMPLATE_SPINNER);
      }
    };

    onMounted(async () => {
      await store.getTemplate('classic');
    });

    return {
      store,
      templateEditorConfig,
      saveTemplate
    };
  }
}
</script>

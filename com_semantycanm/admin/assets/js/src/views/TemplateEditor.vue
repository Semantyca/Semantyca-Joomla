<template>
  <div id="templateSpinner" class="loading-spinner"></div>
  <editor
      :init="templateEditorConfig"
      v-model="state.html"></editor>

  <div class="col-mt-3" style="margin-top: 10px;">
    <button class="btn btn-success" style="margin-right: 10px;" @click="saveTemplate">Save</button>
  </div>
</template>

<script>
import {onMounted, reactive} from 'vue';
import Editor from '@tinymce/tinymce-vue';

const TEMPLATE_SPINNER = 'templateSpinner';
export default {
  components: {Editor},

  setup() {
    const state = reactive({
      id: 0,
      name: '',
      maxArticles: '',
      maxArticlesShort: '',
      html: '',
      banner: '',
      wrapper: ''
    });

    window.myVueState = state;

    const templateEditorConfig = {
      apiKey: window.tinymceLic,
      model_url: 'components/com_semantycanm/assets/bundle/models/dom/model.js',
      skin_url: 'components/com_semantycanm/assets/bundle/skins/ui/oxide',
      content_css: 'components/com_semantycanm/assets/bundle/skins/content/default/content.css',
      menubar: false,
      statusbar: false,
      plugins: 'code',
      toolbar: 'code'
    };

    const loadContent = async (name) => {
      startLoading(TEMPLATE_SPINNER);
      try {
        const url = `index.php?option=com_semantycanm&task=Template.find&name=${name}`;
        const response = await fetch(url);
        if (!response.ok) {
          throw new Error(`Network response was not 200 for name ${name}`);
        }
        const {data} = await response.json();
        state['id'] = data.id;
        state['name'] = data.name;
        state['maxArticles'] = data.maxArticles;
        state['maxArticlesShort'] = data.maxArticlesShort;
        state['html'] = data.content;
        state['banner'] = data.banner;
        state['wrapper'] = data.wrapper;
        stopLoading(TEMPLATE_SPINNER);
      } catch (error) {
        console.error(`Problem fetching content for type ${name}:`, error);
        showErrorBar('Template.find&name', error.message);
        stopLoading(TEMPLATE_SPINNER);
      }
    };

    const saveTemplate = async () => {
      startLoading(TEMPLATE_SPINNER);
      const endpoint = `index.php?option=com_semantycanm&task=Template.update&id=${encodeURIComponent(state['id'])}`;
      const data = {
        html: state['html']
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
      await loadContent('classic');
      // addSaveButtonListener();
    });

    return {
      state,
      templateEditorConfig,
      saveTemplate
    };
  }
}
</script>

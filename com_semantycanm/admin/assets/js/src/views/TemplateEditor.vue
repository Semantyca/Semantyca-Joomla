<template>
  <div id="templateSpinner" class="loading-spinner"></div>
  <div class="col-md-12 d-flex flex-column">
    <div ref="templateRef" style="border: 1px solid gray"></div>
  </div>
  <div class="col-mt-3" style="margin-top: 10px;">
    <button id="saveTemplate" class="btn btn-success" style="margin-right: 10px;">Save</button>
  </div>
  <editor
      api-key="fj3ut1c5sv7m3b46h1o6hsfym8omlfux20ksth5ckbihtbaf"
      :init="templateEditorConfig"
      v-model="state.html"></editor>

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
      wrapper: ''
    });

    window.myVueState = state;

    const templateEditorConfig = {
      model_url: '/joomla/administrator/components/com_semantycanm/assets/bundle/models/dom/model.js',
      skin_url: '/joomla/administrator/components/com_semantycanm/assets/bundle/skins/ui/oxide',
      content_css: '/joomla/administrator/components/com_semantycanm/assets/bundle/skins/content/default/content.css',
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
          throw new Error(`Network response was not ok for name ${name}`);
        }
        const {data} = await response.json();
        state['id'] = data.id;
        state['name'] = data.name;
        state['maxArticles'] = data.maxArticles;
        state['maxArticlesShort'] = data.maxArticlesShort;
        state['html'] = data.content;
        state['wrapper'] = data.wrapper;
        stopLoading(TEMPLATE_SPINNER);
      } catch (error) {
        console.error(`Problem fetching content for type ${name}:`, error);
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

    const addSaveButtonListener = () => {
      const saveButton = document.getElementById('saveTemplate');
      if (saveButton) {
        saveButton.addEventListener('click', saveTemplate);
      }
    };

    onMounted(async () => {
      await loadContent('classic');
      addSaveButtonListener();
    });

    return {
      state,
      templateEditorConfig
    };
  }
}
</script>

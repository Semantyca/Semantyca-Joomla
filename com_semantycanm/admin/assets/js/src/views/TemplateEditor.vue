<template>
  <div class="container mt-3">
    <div class="row">
      <div class="col">
        <h3>{{ globalStore.translations.TEMPLATE }}</h3>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <editor
            :api-key="globalStore.tinyMceLic"
            :init="templateEditorConfig"
            v-model="store.doc.html"></editor>
      </div>
    </div>
    <div class="row mt-3">
      <div class="col  d-flex align-items-center">
        <n-button-group>
          <n-button type="primary"
                    size="large"
                    @click="saveTemplate">{{ globalStore.translations.SAVE }}
          </n-button>
          <n-button size="large"
                    strong
                    error
                    seconadry
                    @click="cancelTemplate">{{ globalStore.translations.CANCEL }}
          </n-button>
        </n-button-group>
      </div>
    </div>
  </div>
</template>

<script>
import {onMounted} from 'vue';
import Editor from '@tinymce/tinymce-vue';
import {useGlobalStore} from "../stores/globalStore";
import {useTemplateStore} from "../stores/templateStore";
import {NButton, NButtonGroup} from "naive-ui";

const TEMPLATE_SPINNER = 'templateSpinner';
export default {
  name: 'TemplateEditor',
  components: {Editor, NButton, NButtonGroup},

  setup() {
    const globalStore = useGlobalStore();
    const store = useTemplateStore();

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

    const cancelTemplate = async () => {

    }

    onMounted(async () => {
      await store.getTemplate('classic');
    });

    return {
      globalStore,
      store,
      templateEditorConfig,
      saveTemplate,
      cancelTemplate
    };
  }
}
</script>

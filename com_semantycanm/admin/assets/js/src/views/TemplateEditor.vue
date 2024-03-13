<template>
  <n-form inline ref="formRef" :rules="rules" :model="formValue">
    <div class="container mt-3">
      <div class="row">
        <div class="col-3 d-flex align-items-center">
          <n-space vertical>
            <n-form-item label="Template name" path="templateName" class="w-100">

              <n-input v-model:value="formValue.templateName"
                       disabled
                       size="large"
                       style="width: 100%"
                       id="templateName"
                       type="text"
                       placeholder="Template name"/>
            </n-form-item>
            <n-form-item label="Colors" path="colors" class="w-100">
              <n-input v-model:value="formValue.permittedColor"
                       disabled
                       size="large"
                       style="width: 100%"
                       id="permittedColor"
                       type="text"/>
            </n-form-item>
          </n-space>
        </div>
      </div>
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
  </n-form>
</template>

<script>
import {onMounted, ref, watch} from 'vue';
import Editor from '@tinymce/tinymce-vue';
import {useGlobalStore} from "../stores/globalStore";
import {useTemplateStore} from "../stores/templateStore";
import {NButton, NButtonGroup, NForm, NFormItem, NInput, useMessage} from "naive-ui";

const TEMPLATE_SPINNER = 'loadingSpinner';
export default {
  name: 'TemplateEditor',
  components: {
    Editor,
    NButton,
    NButtonGroup,
    NInput,
    NForm,
    NFormItem,
  },

  setup() {
    const globalStore = useGlobalStore();
    const store = useTemplateStore();

    const formValue = ref({
      templateName: '',
      permittedColor: ['#152E52', '#AEC127', '#5CA550', '#DF5F5A', '#F9AE4F'],
      permittedColorAdd: ['#053682'],
    });

    const templateEditorConfig = {
      model_url: 'components/com_semantycanm/assets/bundle/models/dom/model.js',
      skin_url: 'components/com_semantycanm/assets/bundle/skins/ui/oxide',
      content_css: 'components/com_semantycanm/assets/bundle/skins/content/default/content.css',
      menubar: false,
      statusbar: false,
      plugins: 'code',
      toolbar: 'code'
    };
    const message = useMessage();
    const saveTemplate = async () => {
      startLoading(TEMPLATE_SPINNER);
      await store.saveTemplate(store.doc.id, store.doc.html,
          () => {
            console.log('Template saved successfully');
            stopLoading(TEMPLATE_SPINNER);
          },
          (error) => {
            message.warning(error.message);
            stopLoading(TEMPLATE_SPINNER);
          }
      );
    };

    onMounted(async () => {
      await store.getTemplate('classic', message);
    });

    watch(() => store.doc.name, (newName) => {
      formValue.value.templateName = newName;
    }, {
      immediate: true
    });

    const cancelTemplate = async () => {

    }

    const rules = {
      templateName: {
        required: true,
        message: 'Template name cannot be empty'
      },
      colors: {
        required: false
      }
    };


    return {
      globalStore,
      store,
      templateEditorConfig,
      saveTemplate,
      cancelTemplate,
      rules,
      formValue
    };
  }
}
</script>

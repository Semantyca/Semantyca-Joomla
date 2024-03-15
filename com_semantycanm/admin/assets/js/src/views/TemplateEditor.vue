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
          </n-space>
        </div>
      </div>
      <div class="row">
        <div class="col">
          <n-data-table
              remote
              size="large"
              :columns="columns"
              :data="store.doc.customFields"
              :bordered="false"
          />
        </div>
      </div>
      <div class="row mt-5">
        <div class="col">
          <code-mirror v-model="store.doc.html"
                       basic
                       :lang="lang"
                       :dark="dark"
          />
        </div>
      </div>
      <div class="row mt-3">
        <div class="col d-flex align-items-center">
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
import {h, onMounted, reactive, ref, watch} from 'vue';
import Editor from '@tinymce/tinymce-vue';
import {useGlobalStore} from "../stores/globalStore";
import {useTemplateStore} from "../stores/templateStore";
import {NButton, NButtonGroup, NDataTable, NForm, NFormItem, NInput, NSpace, useMessage} from "naive-ui";
import CodeMirror from 'vue-codemirror6';
import {html} from '@codemirror/lang-html';

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
    NSpace,
    CodeMirror,
    NDataTable,
  },

  setup() {
    const globalStore = useGlobalStore();
    const store = useTemplateStore();
    const lang = html();
    const dark = ref(false);
    const formValue = ref({
      templateName: ''
    });
    const state = reactive({
      values: []
    });
    const message = useMessage();
    const saveTemplate = async () => {
      startLoading(TEMPLATE_SPINNER);
      await store.saveTemplate(store.doc.id, store.doc.html,
          () => {
            message.info('Template saved successfully');
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

    const createColumns = () => {
      return [
        {
          title: 'Variable name',
          key: 'name'
        },
        {
          title: 'Type',
          key: 'type'
        },
        {
          title: 'Caption',
          key: 'caption'
        },
        {
          title: 'Value',
          key: 'value',
          render(row, index) {
            return h(NInput, {
              value: row.value,
              onUpdateValue(v) {
                state.value[index] = v;
              }
            });
          }
        }
      ];
    };

    return {
      globalStore,
      store,
      saveTemplate,
      cancelTemplate,
      rules,
      columns: createColumns(),
      formValue,
      lang,
      dark
    };
  }
}
</script>

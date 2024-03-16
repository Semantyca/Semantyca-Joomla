<template>
  <n-divider class="custom-divider" title-placement="left">Template properties</n-divider>
  <n-form inline ref="formRef" :rules="rules" :model="formValue">
    <div class="container">
      <div class="row">
        <div class="col-8">
          <n-form-item label="Template name" label-placement="left" path="templateName" class="w-100">
            <n-input v-model:value="formValue.templateName"
                     disabled
                     size="large"
                     style="width: 100%"
                     id="templateName"
                     type="text"
                     placeholder="Template name"/>
          </n-form-item>
        </div>
      </div>
      <n-divider title-placement="left">Custom fields</n-divider>
      <div class="row">
        <div class="col-8">
          <n-form-item
              label-placement="left"
              require-mark-placement="right-hanging"
              label-width="auto"
              :style="{
                    maxWidth: '800px'
                }"
              v-for="(field, index) in customFields"
              :key="field.id"
              :label="field.caption"
          >
            <n-input v-model:value="field.value"/>&nbsp;
            <n-input v-model:value="field.name"/>&nbsp;
            <n-select v-model:value="field.type" :options="options"/>&nbsp;&nbsp;
            <n-checkbox :checked="field.isAvailable === 1"
                        @update:checked="value => updateFieldIsAvailable(index, value)"/>

          </n-form-item>
        </div>
      </div>
      <n-divider title-placement="left">Template source</n-divider>
      <div class="row">
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
import {computed, onMounted, reactive, ref, watch} from 'vue';
import Editor from '@tinymce/tinymce-vue';
import {useGlobalStore} from "../stores/globalStore";
import {useTemplateStore} from "../stores/templateStore";
import {
  NButton,
  NButtonGroup,
  NCheckbox,
  NDivider,
  NForm,
  NFormItem,
  NInput,
  NSelect,
  NSpace,
  useMessage
} from "naive-ui";
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
    NSelect,
    NCheckbox,
    NForm,
    NFormItem,
    NSpace,
    CodeMirror,
    NDivider
  },

  setup() {
    const formRef = ref(null);
    const globalStore = useGlobalStore();
    const store = useTemplateStore();
    const lang = html();
    const dark = ref(false);
    const formValue = ref({
      templateName: ''
    });
    const message = useMessage();

    const customFields = computed(() => store.doc.customFields);

    const formRules = reactive({
      'fields': [
        {required: true, message: 'This field is required', trigger: 'blur'}
      ]
    });

    onMounted(async () => {
      await store.getTemplate('classic', message);
    });

    watch(() => store.doc.name, (newName) => {
      formValue.value.templateName = newName;
    }, {
      immediate: true
    });

    const updateFieldIsAvailable = (index, value) => {
      // Assuming `customFields` is reactive and directly modifies the store's state
      store.doc.customFields[index].isAvailable = value ? 1 : 0;
    };


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

    const typeOptions = [
      {label: "Number", value: 501},
      {label: "String", value: 502},
      {label: "List of Colors", value: 503},
      {label: "URL", value: 504},
    ];

    return {
      globalStore,
      store,
      saveTemplate,
      cancelTemplate,
      updateFieldIsAvailable,
      rules,
      formValue,
      customFields,
      lang,
      dark,
      formRef,
      formRules,
      options: typeOptions
    };
  }
}
</script>

<style>

.n-divider:not(.n-divider--dashed) .n-divider__line {
  background-color: #a1bce0;
}
</style>
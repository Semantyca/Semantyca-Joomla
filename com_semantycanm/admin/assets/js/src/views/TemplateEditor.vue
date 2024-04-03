<template>
  <div class="container">
    <div class="row mt-3">
      <n-space>
        <n-button type="primary" @click="saveTemplate">
          {{ globalStore.translations.SAVE }}
        </n-button>
        <n-button type="primary" @click="exportTemplate">
          Export
        </n-button>
        <n-button type="primary" @click="importTemplate">
          Import
        </n-button>
      </n-space>
    </div>
  </div>
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
          <n-dynamic-input v-model:value="customFormFields"
                           :on-create="addCustomField"
                           :on-remove="removeCustomField"
                           item-style="margin-bottom: 0;"
                           #="{ index }"
          >
            <n-form-item :show-label="false" path="valueType">
              <n-select v-model:value="customFormFields[index].type"
                        :options="options"
                        style="margin-right: 12px; width: 160px"/>
            </n-form-item>
            <n-form-item :show-label="false" path="variableName">
              <n-input v-model:value="customFormFields[index].name"
                       placeholder="Variable name"
                       style="margin-right: 12px;"/>
            </n-form-item>
            <n-form-item :show-label="false" path="caption">
              <n-input v-model:value="customFormFields[index].caption"
                       placeholder="Caption"
                       style="margin-right: 12px; width: 260px"/>
            </n-form-item>
            <n-form-item :show-label="false" path="defaultValue">
              <n-input :value="customFormFields[index].defaultValue.toString()"
                       @update:modelValue="newValue => updateDefaultValueAsString(index, newValue)"
                       placeholder="Default value"
                       style="margin-right: 12px; width: 360px"/>

            </n-form-item>
            <n-form-item :show-label="false" path="valueType">
              <n-checkbox :checked="customFormFields[ index ].isAvailable === 1"
                          @update:checked="value => updateFieldIsAvailable(index, value)"/>
            </n-form-item>
          </n-dynamic-input>
        </div>
      </div>
      <n-divider title-placement="left">Template source</n-divider>
      <div class="row">
        <div class="col">
          <code-mirror v-model="store.doc.html"
                       basic
                       :lang="lang"
                       :dark="dark"
                       :style="{ width: '100%'}"
          />
        </div>
      </div>

    </div>
  </n-form>
</template>

<script>
import {computed, onMounted, ref, watch} from 'vue';
import Editor from '@tinymce/tinymce-vue';
import {useGlobalStore} from "../stores/globalStore";
import {useTemplateStore} from "../stores/templateStore";
import {
  NButton,
  NCheckbox,
  NDivider,
  NDynamicInput,
  NForm,
  NFormItem,
  NInput,
  NSelect,
  NSpace,
  useMessage
} from "naive-ui";
import CodeMirror from 'vue-codemirror6';
import {html} from '@codemirror/lang-html';

export default {
  name: 'TemplateEditor',
  components: {
    Editor,
    NButton,
    NSpace,
    NInput,
    NSelect,
    NCheckbox,
    NForm,
    NFormItem,
    CodeMirror,
    NDivider,
    NDynamicInput
  },

  setup() {
    const formRef = ref(null);
    const globalStore = useGlobalStore();
    const templateStore = useTemplateStore();
    const lang = html();
    const dark = ref(false);
    const formValue = ref({
      templateName: ''
    });
    const message = useMessage();

    const customFormFields = computed(() => templateStore.doc.customFields);

    onMounted(async () => {
      await templateStore.getTemplate('classic', message);
    });

    watch(() => templateStore.doc.name, (newName) => {
      formValue.value.templateName = newName;
    }, {
      immediate: true
    });

    const updateFieldIsAvailable = (index, value) => {
      templateStore.doc.customFields[index].isAvailable = value ? 1 : 0;
    };

    const updateDefaultValueAsString = (index, newValue) => {
      templateStore.doc.customFields[index].defaultValue = String(newValue);
    };


    const saveTemplate = async () => {
      await templateStore.saveTemplate(message);
    };

    const addCustomField = () => {
      const newField = {
        type: 502,
        name: '',
        caption: '',
        defaultValue: '',
        isAvailable: 0
      };
      templateStore.addCustomField(newField);
    };

    const removeCustomField = (index) => {
      templateStore.removeCustomField(index);
    };

    const exportTemplate = () => {
      const filename = `${templateStore.doc.name || 'template'}.json`;
      const jsonStr = JSON.stringify(templateStore.doc, (key, value) => {
        if (key === "availableCustomFields") return undefined;
        return value;
      }, 2);
      const blob = new Blob([jsonStr], {type: "application/json"});
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    };

    const importTemplate = () => {
      const fileInput = document.createElement('input');
      fileInput.type = 'file';
      fileInput.accept = '.json';
      fileInput.onchange = async (e) => {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = async (e) => {
            try {
              const jsonObj = JSON.parse(e.target.result);
              templateStore.setTemplate(jsonObj);
              await templateStore.saveTemplate(message);
              message.success('Template imported successfully');
            } catch (err) {
              message.error('Failed to import template');
            }
          };
          reader.readAsText(file);
        }
      };
      fileInput.click();
    };


    const rules = {
      templateName: {
        required: true,
        message: 'Template name cannot be empty'
      },
      defaultValue: {
        required: true,
        message: 'Default value cannot be empty'
      },
      variableName: {
        required: true,
        message: 'Variable name cannot be empty'
      },
      valueType: {
        required: true,
        message: 'Value type cannot be empty'
      },
      caption: {
        required: true,
        message: 'Caption cannot be empty'
      },
    };

    const typeOptions = [
      {label: "Number", value: 501},
      {label: "String", value: 502},
      {label: "Colors", value: 503},
      {label: "URL", value: 504},
    ];

    return {
      globalStore,
      store: templateStore,
      saveTemplate,
      updateFieldIsAvailable,
      updateDefaultValueAsString,
      rules,
      formValue,
      customFormFields,
      lang,
      dark,
      formRef,
      options: typeOptions,
      addCustomField,
      removeCustomField,
      exportTemplate,
      importTemplate
    };
  }
}
</script>

<style>

.n-divider:not(.n-divider--dashed) .n-divider__line {
  background-color: #a1bce0;
}
</style>
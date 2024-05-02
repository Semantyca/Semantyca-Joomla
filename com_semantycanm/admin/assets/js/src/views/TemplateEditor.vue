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
        <n-button type="error" @click="deleteTemplate">
          {{ globalStore.translations.DELETE }}
        </n-button>
      </n-space>
    </div>
  </div>
  <n-divider class="custom-divider" title-placement="left">Template properties</n-divider>
  <n-form inline ref="formRef" :rules="rules" :model="formValue" label-placement="left" label-width="auto">
    <div class="container">
      <div class="row">
        <div class="col-8">
          <n-form-item label="Template name" label-placement="left" path="templateName">
            <n-select v-model:value="formValue.templateName"
                      :options="templateSelectList"
                      size="large"
                      style="width: 100%; max-width: 600px;"
                      placeholder="Select a template"
            />
          </n-form-item>
          <n-form-item label="Description" label-placement="left" path="description">
            <n-input v-model:value="formValue.description"
                     type="textarea"
                     placeholder="Enter template description"
                     style="width: 100%; max-width: 600px; height: 50px;"
                     autosize/>
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
                        style="margin-right: 12px; width: 160px"
                        @update:value="() => handleTypeChange(index)"/>

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
              <n-input :value="getTypedValue(customFormFields[index].defaultValue, customFormFields[index].type)"
                       @input="value => setTypedValue(index, value)"
                       placeholder="Value"
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
          <code-mirror v-model="store.doc.content"
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
      id: '',
      templateName: '',
      description: ''
    });

    const message = useMessage();

    const customFormFields = computed(() => templateStore.doc.customFields);

    onMounted(async () => {
      await templateStore.fetchTemplates();
      const defaultTemplate = templateStore.templatesList.find(template => template.is_default === 1);
      if (defaultTemplate) {
        formValue.value.id = defaultTemplate.id;
        formValue.value.description = defaultTemplate.description;
        templateStore.setTemplate(defaultTemplate);
      }
    });

    const templateSelectList = computed(() => templateStore.templatesList.map(template => ({
      label: template.name,
      value: template.id
    })));

    watch(() => formValue.value.templateName, (newVal) => {
      const selectedTemplate = templateStore.templatesList.find(t => t.id === newVal);
      if (selectedTemplate) {
        formValue.value.id = selectedTemplate.id;
        formValue.value.description = selectedTemplate.description;
        templateStore.setTemplate(selectedTemplate);
      }
    });

    const updateFieldIsAvailable = (index, value) => {
      templateStore.doc.customFields[index].isAvailable = value ? 1 : 0;
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
        if (key === "id" || key === "availableCustomFields" || key === "regDate") return undefined;
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
              await templateStore.saveTemplate(message, true);
              message.success('Template imported and saved successfully.');
            } catch (err) {
              message.error('Failed to import template: ' + err.message);
            }
          };
          reader.readAsText(file);
        }
      };
      fileInput.click();
    };

    const deleteTemplate = async () => {
      if (templateStore.doc.id) {
        await templateStore.deleteTemplate(message);
      } else {
        message.error("No template selected for deletion.");
      }
    };

    function getTypedValue(value, type) {
      switch (type) {
        case 501:
          return String(value);
        case 503:
          return String(value);
        case 502:
        default:
          return value;
      }
    }

    function setTypedValue(index, stringValue) {
      const fieldType = customFormFields.value[index].type;
      let convertedValue = stringValue;
      switch (fieldType) {
        case 501:
          convertedValue = Number(stringValue);
          break;
        case 503:
          convertedValue = stringValue;
          break;
        case 502:
        default:
          convertedValue = stringValue;
      }

      customFormFields.value[index].defaultValue = convertedValue;
    }

    function handleTypeChange(index) {
      customFormFields.value[index].defaultValue = '';
    }


    function isValidJson(value) {
      try {
        JSON.parse(value);
        return true;
      } catch (error) {
        return false;
      }
    }

    const rules = {
      templateName: {
        required: true,
        message: 'Template name cannot be empty',
      },
      defaultValue: [
        {required: true, message: 'Default value cannot be empty'},
        {validator: (rule, value) => isValidJson(value), message: 'Please enter a valid JSON string'},
      ],
      variableName: {
        required: true,
        message: 'Variable name cannot be empty',
      },
      valueType: {
        required: true,
        message: 'Value type cannot be empty',
      },
      caption: {
        required: true,
        message: 'Caption cannot be empty',
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
      importTemplate,
      deleteTemplate,
      getTypedValue,
      setTypedValue,
      handleTypeChange,
      templateSelectList,
    };
  }
}
</script>

<style>

.n-divider:not(.n-divider--dashed) .n-divider__line {
  background-color: #a1bce0;
}
</style>
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
  <n-form inline ref="formRef" :rules="rules" label-placement="left" label-width="auto">
    <div class="container">
      <div class="row">
        <div class="col-8">
          <n-form-item label="Template name" label-placement="left" path="templateName">
            <n-select v-model:value="selectedTemplateId"
                      :options="templateSelectList"
                      size="large"
                      style="width: 100%; max-width: 600px;"
                      placeholder="Select a template"
                      @update:value="handleTemplateChange"
            />
          </n-form-item>
          <n-form-item label="Description" label-placement="left" path="description">
            <n-input v-model:value="description"
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
import {computed, onMounted, ref} from 'vue';
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
import {getTypedValue, rules, setTypedValue, typeOptions} from '../utils/templateEditorUtils';
import {
  addCustomField,
  deleteCurrentTemplate,
  exportTemplate,
  handleTypeChange,
  importTemplate,
  removeCustomField,
  saveTemplate
} from '../utils/templateEditorHandlers';

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
    const selectedTemplateIdRef = ref(null);
    const globalStore = useGlobalStore();
    const templateStore = useTemplateStore();
    const msgPopup = useMessage();
    const customFormFields = computed(() => templateStore.doc.customFields);

    const templateSelectList = computed(() => {
      return Object.values(templateStore.templatesMap).map(template => {
        return {
          label: template.name,
          value: template.id
        };
      });
    });

    const updateSelectedTemplateId = (newId) => {
      selectedTemplateIdRef.value = newId;
    };

    const handleTemplateChange = (newTemplateId) => {
      templateStore.changeTemplate(templateStore.templatesMap[newTemplateId]);
      updateSelectedTemplateId(newTemplateId);
    };

    const updateFieldIsAvailable = (index, value) => {
      templateStore.doc.customFields[index].isAvailable = value ? 1 : 0;
    };

    onMounted(async () => {
      await templateStore.getTemplates(msgPopup);
      for (const id in templateStore.templatesMap) {
        if (templateStore.templatesMap[id].isDefault) {
          selectedTemplateIdRef.value = Number(id);
          break;
        }
      }
    });

    return {
      globalStore,
      store: templateStore,
      saveTemplate: () => saveTemplate(templateStore, msgPopup),
      deleteTemplate: () => deleteCurrentTemplate(templateStore, msgPopup),
      exportTemplate: () => exportTemplate(templateStore),
      importTemplate: () => importTemplate(templateStore, msgPopup),
      addCustomField: () => addCustomField(templateStore),
      removeCustomField: (index) => removeCustomField(templateStore, index),
      handleTypeChange: (index) => handleTypeChange(customFormFields, index),
      updateSelectedTemplateId,
      selectedTemplateId: selectedTemplateIdRef,
      handleTemplateChange,
      updateFieldIsAvailable,
      rules,
      customFormFields,
      lang: html(),
      dark: ref(false),
      formRef,
      options: typeOptions,
      getTypedValue,
      setTypedValue,
      templateSelectList,
      description: computed(() => templateStore.doc.description)
    };
  }
}
</script>

<style>

.n-divider:not(.n-divider--dashed) .n-divider__line {
  background-color: #a1bce0;
}
</style>
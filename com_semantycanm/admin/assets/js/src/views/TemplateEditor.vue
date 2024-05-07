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
            <n-select v-model:value="selectedTemplateRef"
                      :options="templateSelectOptions"
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
                        :options="typeOptions"
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
          <n-tabs v-model:value="activeTabRef">
            <n-tab-pane name="content" tab="Content">
              <code-mirror v-model="store.doc.content"
                           @change="debouncedAutosave"
                           basic
                           :lang="lang"
                           :dark="dark"
                           :style="{ width: '100%'}"
              />
            </n-tab-pane>
            <n-tab-pane name="wrapper" tab="Wrapper" style="background-color: #e3f1d4;">
              <code-mirror v-model="store.doc.wrapper"
                           @change="debouncedAutosave"
                           basic
                           :lang="lang"
                           :dark="dark"
                           :style="{ width: '100%'}"
              />
            </n-tab-pane>
          </n-tabs>
        </div>
      </div>

    </div>
  </n-form>
</template>

<script>
import {computed, onMounted, onUnmounted, ref} from 'vue';
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
  NTabPane,
  NTabs,
  useMessage
} from "naive-ui";
import CodeMirror from 'vue-codemirror6';
//import {ejs} from 'codemirror-lang-ejs';
import {html} from '@codemirror/lang-html';
import {getTypedValue, rules, setTypedValue, typeOptions} from '../utils/templateEditorUtils';
import {addCustomField, handleTypeChange, removeCustomField} from '../utils/templateEditorHandlers';
import TemplateManager from "../utils/TemplateManager";
import {debounce} from 'lodash-es';

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
    NDynamicInput,
    NTabPane,
    NTabs
  },

  setup() {
    const formRef = ref(null);
    const selectedTemplateRef = ref(null);
    const globalStore = useGlobalStore();
    const templateStore = useTemplateStore();
    const msgPopup = useMessage();
    const customFormFields = computed(() => templateStore.doc.customFields);
    const templateManager = new TemplateManager(templateStore, msgPopup);
    const autosaveTemplate = () => {
      if (templateStore.doc && templateStore.doc.content && templateStore.doc.content.length > 0) {
        templateManager.autoSave('content', templateStore.doc.content);
      }
    };
    const debouncedAutosave = debounce(autosaveTemplate, 2000);

    const updateFieldIsAvailable = (index, value) => {
      templateStore.doc.customFields[index].isAvailable = value ? 1 : 0;
    };

    onMounted(async () => {
      await templateManager.getTemplates(msgPopup);
      selectedTemplateRef.value = templateStore.doc.id;
    });

    onUnmounted(() => {
      debouncedAutosave.cancel();
    });


    return {
      globalStore,
      store: templateStore,
      saveTemplate: () => templateManager.saveTemplate(templateStore.doc, false),
      deleteTemplate: () => templateManager.deleteCurrentTemplate(),
      exportTemplate: () => templateManager.exportCurrentTemplate(),
      importTemplate: () => templateManager.importTemplate(),
      addCustomField: () => addCustomField(templateStore),
      removeCustomField: (index) => removeCustomField(templateStore, index),
      handleTypeChange: (index) => handleTypeChange(customFormFields, index),
      templateSelectOptions: computed(() => {
        return templateStore.templateOptions.map(item => ({
          label: item.name,
          value: item.id
        }))
      }),
      description: computed(() => templateStore.doc.description),
      selectedTemplateRef: computed(() => templateStore.doc.name),
      handleTemplateChange: (index) => templateManager.handleTemplateChange(index),
      updateFieldIsAvailable,
      rules,
      customFormFields,
      lang: html(),
      dark: ref(false),
      formRef,
      typeOptions,
      getTypedValue,
      setTypedValue,
      activeTabRef: ref('content'),
      debouncedAutosave
    };
  }
}
</script>

<style>

.n-divider:not(.n-divider--dashed) .n-divider__line {
  background-color: #a1bce0;
}
</style>
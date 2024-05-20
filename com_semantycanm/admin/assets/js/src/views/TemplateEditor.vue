<template>
  <n-grid :cols="1" x-gap="12" y-gap="12" class="mt-1">
    <n-gi>
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
    </n-gi>

    <n-gi>
      <n-divider class="custom-divider" title-placement="left">Template properties</n-divider>
    </n-gi>

    <n-gi>
      <n-form inline ref="formRef" :rules="rules" label-placement="left" label-width="auto">
        <n-grid :cols="8">
          <n-gi span="8">
            <n-form-item label="Template name" label-placement="left" path="templateName">
              <n-select
                  v-model:value="selectedTemplateRef"
                  :options="templateSelectOptions"
                  size="large"
                  style="width: 100%; max-width: 600px;"
                  placeholder="Select a template"
                  @update:value="handleTemplateChange"
              />
            </n-form-item>
            <n-form-item label="Description" label-placement="left" path="description">
              <n-input
                  v-model:value="description"
                  type="textarea"
                  placeholder="Enter template description"
                  style="width: 100%; max-width: 600px; height: 50px;"
                  autosize
              />
            </n-form-item>
          </n-gi>
        </n-grid>
      </n-form>
    </n-gi>

    <n-gi>
      <n-divider title-placement="left">Custom fields</n-divider>
    </n-gi>

    <n-gi>
      <n-form inline ref="formRef" :rules="rules" label-placement="left" label-width="auto">
        <n-grid :cols="8">
          <n-gi span="8">
            <n-dynamic-input
                v-model:value="customFormFields"
                :on-create="addCustomField"
                :on-remove="removeCustomField"
                item-style="margin-bottom: 0;"
                #="{ index }"
            >
              <n-form-item :show-label="false" path="valueType">
                <n-select
                    v-model:value="customFormFields[index].type"
                    :options="typeOptions"
                    style="margin-right: 12px; width: 160px"
                    @update:value="() => handleTypeChange(index)"
                />
              </n-form-item>
              <n-form-item :show-label="false" path="variableName">
                <n-input
                    v-model:value="customFormFields[index].name"
                    placeholder="Variable name"
                    style="margin-right: 12px;"
                />
              </n-form-item>
              <n-form-item :show-label="false" path="caption">
                <n-input
                    v-model:value="customFormFields[index].caption"
                    placeholder="Caption"
                    style="margin-right: 12px; width: 260px"
                />
              </n-form-item>
              <n-form-item :show-label="false" path="defaultValue">
                <n-input
                    v-model:value="customFormFields[index].defaultValue"
                    placeholder="Value"
                    style="margin-right: 12px; width: 360px"
                />
              </n-form-item>
              <n-form-item :show-label="false" path="valueType">
                <n-checkbox
                    :checked="customFormFields[index].isAvailable === 1"
                    @update:checked="value => updateFieldIsAvailable(index, value)"
                />
              </n-form-item>
            </n-dynamic-input>
          </n-gi>
        </n-grid>
      </n-form>
    </n-gi>
    <n-gi>
      <n-divider title-placement="left">Template source</n-divider>
    </n-gi>
    <n-gi>
      <n-select
          size="tiny"
          v-model:value="selectedMode"
          :options="modeOptions"
          style="width: 100px"
          @update:value="updateEditorMode"
      />
      <n-tabs v-model:value="activeTabRef">
        <n-tab-pane name="content" tab="Content">
          <code-mirror
              v-model="templateStore.currentTemplate.content"
              @focus="handleEditorFocus"
              @change="debouncedAutosave"
              basic
              :lang="lang"
              :dark="dark"
              :style="{ width: '100%' }"
          />
        </n-tab-pane>
        <n-tab-pane name="wrapper" tab="Wrapper" style="background-color: #e3f1d4;">
          <code-mirror
              v-model="store.doc.wrapper"
              @focus="handleEditorFocus"
              basic
              :lang="lang"
              :dark="dark"
              :style="{ width: '100%' }"
          />
        </n-tab-pane>
      </n-tabs>
    </n-gi>
  </n-grid>
</template>

<script>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useGlobalStore } from "../stores/globalStore";
import { useTemplateStore } from "../stores/templateStore";
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
  useMessage,
  NGrid,
  NGi
} from "naive-ui";
import CodeMirror from 'vue-codemirror6';
import { html } from '@codemirror/lang-html';
import { ejs } from 'codemirror-lang-ejs';
import { rules, typeOptions } from '../utils/templateEditorUtils';
import { addCustomField, handleTypeChange, removeCustomField } from '../utils/templateEditorHandlers';
import TemplateManager from "../utils/TemplateManager";
import { debounce } from 'lodash-es';

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
    NTabs,
    NGrid,
    NGi
  },

  setup() {
    const formRef = ref(null);
    const selectedTemplateRef = ref(null);
    const globalStore = useGlobalStore();
    const templateStore = useTemplateStore();
    const msgPopup = useMessage();
    const customFormFields = computed(() => templateStore.currentTemplate.customFields);
    const templateManager = new TemplateManager(templateStore, msgPopup);
    const editorFocused = ref(false);
    const autosaveTemplate = () => {
      if (editorFocused.value) {
        templateManager.autoSave('content', templateStore.currentTemplate.content);
      }
    };
    const debouncedAutosave = debounce(autosaveTemplate, 2000);

    const updateFieldIsAvailable = (index, value) => {
      templateStore.currentTemplate.customFields[index].isAvailable = value ? 1 : 0;
    };

    onMounted(async () => {
      await templateManager.getTemplates();
      selectedTemplateRef.value = templateStore.currentTemplate.id;
    });

    onUnmounted(() => {
      debouncedAutosave.cancel();
    });

    watch(() => templateStore.currentTemplate.content, (newVal, oldVal) => {
      if (newVal !== oldVal && editorFocused.value) {
        debouncedAutosave();
      }
    });

    const handleEditorFocus = () => {
      editorFocused.value = true;
    };

    const selectedMode = ref('html');
    const modeOptions = [
      {label: 'HTML', value: 'html'},
      {label: 'EJS', value: 'ejs'}
    ];

    const lang = ref(html());

    const updateEditorMode = (mode) => {
      if (mode === 'html') {
        lang.value = html();
      } else if (mode === 'ejs') {
         lang.value = ejs();
      }
    };

    return {
      globalStore,
      templateStore,
      saveTemplate: () => templateManager.saveTemplate(templateStore.currentTemplate, false),
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
        }));
      }),
      description: computed({
        get: () => templateStore.currentTemplate.description,
        set: (value) => {
          templateStore.currentTemplate.description = value;
        }
      }),
      selectedTemplateRef: computed(() => templateStore.currentTemplate.name),
      handleTemplateChange: (index) => templateManager.handleTemplateChange(index),
      updateFieldIsAvailable,
      rules,
      customFormFields,
      lang,
      dark: ref(false),
      formRef,
      typeOptions,
      activeTabRef: ref('content'),
      debouncedAutosave,
      handleEditorFocus,
      selectedMode,
      modeOptions,
      updateEditorMode
    };
  }
};
</script>

<style>
.n-divider:not(.n-divider--dashed) .n-divider__line {
  background-color: #a1bce0;
}
</style>

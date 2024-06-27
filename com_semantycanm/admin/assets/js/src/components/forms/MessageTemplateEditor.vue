<template>
  <n-h3>Template editor</n-h3>
  <n-grid :cols="1" x-gap="5" y-gap="10">
    <n-gi>
      <n-space>
        <n-button type="info" @click="$emit('back')">
          <template #icon>
            <n-icon>
              <arrow-big-left/>
            </n-icon>
          </template>
          Back
        </n-button>
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
    <n-gi class="mt-4">
      <n-form inline ref="formRef" :rules="rules" label-placement="left" label-width="auto">

        <n-gi span="8">
          <n-form-item label="Template name" label-placement="left" path="templateName" size="large">
            <n-select
                v-model:value="selectedTemplateRef"
                :options="templateStore.templateSelectOptions"
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
          <n-form-item label="Custom fields" label-placement="left" path="templateName">
            <n-dynamic-input
                v-model:value="customFormFields"
                :on-create="addCustomField"
                :on-remove="removeCustomField"
                item-style="margin-bottom: 0;"
                #="{ index }"
                :show-sort-button="false"
                @update:value="updateSequence"
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
          </n-form-item>
          <n-form-item label="Source" label-placement="left" path="description">
            <n-tabs v-model:value="activeTabRef" size="small">
              <template #prefix>&nbsp;&nbsp;
                <n-select
                    size="tiny"
                    v-model:value="selectedMode"
                    :options="modeOptions"
                    style="width: 100px"
                    @update:value="updateEditorMode"
                />
              </template>
              <n-tab-pane name="content" tab="Content">
                <code-mirror
                    v-model="templateStore.currentTemplate.content"
                    @focus="handleEditorFocus"
                    @blur="handleEditorBlur"
                    basic
                    :lang="lang"
                    :dark="dark"
                    :style="{ width: '100%'}"
                />
              </n-tab-pane>
              <n-tab-pane name="wrapper" tab="Wrapper" style="background-color: #e5f2dc;">
                <code-mirror
                    v-model="templateStore.currentTemplate.wrapper"
                    @focus="handleEditorFocus"
                    @blur="handleEditorBlur"
                    basic
                    :lang="lang"
                    :dark="dark"
                    :style="{ width: '100%' }"
                />
              </n-tab-pane>
            </n-tabs>
          </n-form-item>
        </n-gi>

      </n-form>
    </n-gi>
  </n-grid>

</template>

<script>
import {computed, onMounted, ref, watch} from 'vue';
import {useGlobalStore} from "../../stores/globalStore";
import {useMessageTemplateStore} from "../../stores/template/messageTemplateStore";
import {
  NButton, NCheckbox, NDivider, NDynamicInput, NForm, NFormItem, NInput, NSelect, NSpace, NTabPane,
  NTabs, useMessage, useLoadingBar, NGrid, NGi, NH3, NIcon
} from "naive-ui";
import {ArrowBigLeft} from '@vicons/tabler'
import CodeMirror from 'vue-codemirror6';
import {html} from '@codemirror/lang-html';
import {ejs} from 'codemirror-lang-ejs';
import {rules, typeOptions} from '../../stores/template/templateEditorUtils';
import {addCustomField, handleTypeChange, removeCustomField} from '../../stores/template/templateEditorHandlers';
import TemplateManager from "../../stores/template/TemplateManager";
import {setCurrentTemplate} from "../../stores/storeUtils";

export default {
  name: 'MessageTemplateEditor',
  components: {
    ArrowBigLeft, NButton, NSpace, NInput, NSelect, NCheckbox, NForm, NFormItem, CodeMirror, NDivider, NDynamicInput,
    NTabPane, NTabs, NGrid, NGi, NH3, NIcon
  },
  props: {
    id: {
      type: Number,
      required: false,
    },
  },
  emits: ['back'],
  setup() {
    const formRef = ref(null);
    const selectedTemplateRef = ref(null);
    const globalStore = useGlobalStore();
    const templateStore = useMessageTemplateStore();
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar()
    const customFormFields = computed(() => templateStore.currentTemplate.customFields);
    const templateManager = new TemplateManager(templateStore, msgPopup, loadingBar);
    const editorFocused = ref(false);

    const updateFieldIsAvailable = (index, value) => {
      templateStore.currentTemplate.customFields[index].isAvailable = value ? 1 : 0;
    };

    onMounted(async () => {
      await templateManager.getTemplates();
      selectedTemplateRef.value = templateStore.currentTemplate.name;
    });

    watch(() => templateStore.currentTemplate.id, (newVal) => {
      selectedTemplateRef.value = templateStore.templateMap[newVal].name;
    });

    const handleTemplateChange = (newTemplateId) => {
      setCurrentTemplate(templateStore, newTemplateId);
      templateManager.setDefaultTemplate(newTemplateId);
      editorFocused.value = false;
    };

    const handleEditorBlur = () => {
      editorFocused.value = false;
    };

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

    const updateSequence = (newValue) => {
      //  console.log('Old Order:', items.value);
      console.log('New Order:', newValue);
      //  items.value = newValue;
      //  console.log('Updated Order:', items.value);
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
      description: computed({
        get: () => templateStore.currentTemplate.description,
        set: (value) => {
          templateStore.currentTemplate.description = value;
        }
      }),
      selectedTemplateRef,
      handleTemplateChange,
      updateFieldIsAvailable,
      rules,
      customFormFields,
      lang,
      dark: ref(false),
      formRef,
      typeOptions,
      activeTabRef: ref('content'),
      handleEditorFocus,
      handleEditorBlur,
      selectedMode,
      modeOptions,
      updateEditorMode,
      updateSequence
    };
  }
};
</script>

<style>

</style>

<template>
  <n-page-header :subtitle="modelRef.templateName" class="mb-3">
    <template #title>
      Template
    </template>
  </n-page-header>
  <n-grid :cols="1" x-gap="5" y-gap="10">
    <n-gi>
      <n-space>
        <n-button type="info" @click="router.push('/list');">
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
        <n-button type="primary" @click="cloneTemplate">
          Clone
        </n-button>
      </n-space>
    </n-gi>
    <n-gi class="mt-4">
      <n-form inline ref="formRef" :model="modelRef" :rules="rules" label-placement="left" label-width="auto">
        <n-gi span="8">
          <n-form-item label="Template name" label-placement="left" path="templateName" size="large">
            <n-input
                v-model:value="modelRef.templateName"
                style="width: 100%; max-width: 372px; min-width: 372px;"
                placeholder="Enter template name"
            />
          </n-form-item>
          <n-form-item label="Description" label-placement="left" path="description">
            <n-input
                v-model:value="modelRef.description"
                type="textarea"
                placeholder="Enter template description"
                style="width: 100%; max-width: 372px; min-width: 372px; height: 50px;"
                autosize
            />
          </n-form-item>
          <n-form-item label="Dynamic fields" label-placement="left" path="templateName">
            <n-dynamic-input
                v-model:value="modelRef.customFields"
                :on-create="addCustomField"
                :on-remove="removeCustomField"
                item-style="margin-bottom: 0;"
                #="{ index }"
                :show-sort-button="false"
                @update:value="updateSequence"
            >
              <n-form-item :show-label="false" path="valueType">
                <n-select
                    v-model:value="modelRef.customFields[index].type"
                    :options="typeOptions"
                    style="margin-right: 12px; width: 160px;"
                    @update:value="() => handleTypeChange(index)"
                />
              </n-form-item>
              <n-form-item :show-label="false" path="variableName">
                <n-input
                    v-model:value="modelRef.customFields[index].name"
                    placeholder="Variable name"
                    style="margin-right: 12px; width: 180px; min-width: 200px;"
                />
              </n-form-item>
              <n-form-item :show-label="false" path="caption">
                <n-input
                    v-model:value="modelRef.customFields[index].caption"
                    placeholder="Caption"
                    style="margin-right: 12px; width: 260px; min-width: 200px;"
                />
              </n-form-item>
              <n-form-item :show-label="false" path="defaultValue">
                <n-input
                    v-model:value="modelRef.customFields[index].defaultValue"
                    placeholder="Value"
                    style="margin-right: 12px; width: 360px; min-width: 200px;"
                />
              </n-form-item>
              <n-form-item :show-label="false" path="valueType">
                <n-checkbox
                    :checked="modelRef.customFields[index].isAvailable === 1"
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
                    style="width: 100px;"
                    @update:value="updateEditorMode"
                />
              </template>
              <n-tab-pane name="content" tab="Content">
                <code-mirror
                    v-model="modelRef.content"
                    @focus="handleEditorFocus"
                    @blur="handleEditorBlur"
                    basic
                    :lang="lang"
                    :dark="dark"
                    :style="{ width: '100%' }"
                />
              </n-tab-pane>
              <n-tab-pane name="wrapper" tab="Wrapper" style="background-color: #e5f2dc;">
                <code-mirror
                    v-model="modelRef.wrapper"
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
import {onMounted, ref, watch} from 'vue';
import {useGlobalStore} from "../../stores/globalStore";
import {useTemplateStore} from "../../stores/template/templateStore";
import {
  NButton,
  NCheckbox,
  NDivider,
  NDynamicInput,
  NForm,
  NFormItem,
  NGi,
  NGrid,
  NH3,
  NIcon,
  NInput,
  NSelect,
  NSpace,
  NTabPane,
  NTabs,
  NPageHeader,
  useLoadingBar,
  useMessage
} from "naive-ui";
import {ArrowBigLeft} from '@vicons/tabler';
import CodeMirror from 'vue-codemirror6';
import {html} from '@codemirror/lang-html';
import {ejs} from 'codemirror-lang-ejs';
import {rules, typeOptions} from '../../stores/template/templateEditorUtils';
import {addCustomField, handleTypeChange, removeCustomField} from '../../stores/template/templateEditorHandlers';
import TemplateManager from "../../stores/template/TemplateManager";
import {useRoute, useRouter} from "vue-router";
import {defaultEmailTemplate} from "../../templates/defaultEmailTemplate";

export default {
  name: 'TemplateEditor',
  components: {
    ArrowBigLeft, NButton, NSpace, NInput, NSelect, NCheckbox, NForm, NFormItem, CodeMirror, NDivider, NDynamicInput,
    NTabPane, NTabs, NGrid, NGi, NH3, NIcon, NPageHeader
  },
  setup() {
    const formRef = ref(null);
    const globalStore = useGlobalStore();
    const templateStore = useTemplateStore();
    const route = useRoute();
    const router = useRouter();
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();

    const modelRef = ref({
      id: null,
      templateName: '',
      description: '',
      templateType: '',
      customFields: [],
      content: '',
      wrapper: ''
    });

    const templateManager = new TemplateManager(templateStore);

    const editorFocused = ref(false);

    const updateFieldIsAvailable = (index, value) => {
      modelRef.value.customFields[index].isAvailable = value ? 1 : 0;
    };

    onMounted(async () => {
      if (route.params.id != null) {
        await templateStore.fetchTemplate(route.params.id);
      } else {
        modelRef.value = {
          id: null,
          templateName: '',
          description: 'A HTML email template',
          templateType: '',
          customFields: [],
          content: defaultEmailTemplate,
          wrapper: ''
        };
      }
    });

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
      console.log('New Order:', newValue);
    };

    const saveTemplate = () => {
      const updatedTemplate = {
        ...templateStore.currentTemplate,
        ...modelRef.value
      };

      // Use the ID from modelRef instead of route.params.id
      const templateId = modelRef.value.id;

      templateManager.saveTemplate(updatedTemplate, templateId);
    };

    const cloneTemplate = () => {
      // Create a deep copy of the current template
      const clonedTemplate = JSON.parse(JSON.stringify(modelRef.value));

      // Modify the name and description to reflect it's a clone
      clonedTemplate.templateName = `${clonedTemplate.templateName} (Clone)`;
      clonedTemplate.description = `Clone of: ${clonedTemplate.description}`;

      // Reset the ID to ensure it's treated as a new template
      clonedTemplate.id = null;

      // Update the modelRef with the cloned data
      modelRef.value = clonedTemplate;

      // Reset the route parameter
      router.replace({ params: { id: null } });

      // Optionally, show a success message
      msgPopup.success("Template cloned successfully. You're now editing the clone.");
    };

    watch(
        () => templateStore.templateDoc,
        (newValue) => {
          modelRef.value = {
            id: newValue.id,
            templateName: newValue.name,
            description: newValue.description,
            templateType: newValue.type,
            customFields: newValue.customFields,
            content: newValue.content,
            wrapper: newValue.wrapper
          };
        },
        { deep: true, immediate: true }
    );

    return {
      globalStore,
      modelRef,
      router,
      saveTemplate,
      exportTemplate: () => templateManager.exportCurrentTemplate(),
      importTemplate: () => templateManager.importTemplate(),
      cloneTemplate,
      addCustomField: () => addCustomField(modelRef),
      removeCustomField: (index) => removeCustomField(modelRef, index),
      handleTypeChange: (index) => handleTypeChange(modelRef.value.customFields, index),
      updateFieldIsAvailable,
      rules,
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
/* ... */
</style>
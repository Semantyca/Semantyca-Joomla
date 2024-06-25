<template>
  <n-h3>Newsletter</n-h3>
  <n-grid :cols="1" x-gap="5" y-gap="10">
    <n-gi>
      <n-space>
        <n-button type="info" @click="$emit('back')">
          <template #icon>
            <n-icon>
              <ArrowBigLeft/>
            </n-icon>
          </template>
          &nbsp;Back
        </n-button>
        <n-button type="success" @click="handleSendNewsletter(false)">
          {{ globalStore.translations.SEND }} & {{ globalStore.translations.SAVE }}
        </n-button>
        <n-button type="primary" @click="handleSendNewsletter(true)">
          {{ globalStore.translations.SAVE }}
        </n-button>
      </n-space>
    </n-gi>
    <n-gi class="mt-4">
      <n-form
          label-placement="left"
          label-width="auto"
          require-mark-placement="right-hanging"
      >
        <n-form-item label="Template name" path="templateName">
          <n-select
              v-model:value="messageTemplateStore.currentTemplate.key"
              :options="messageTemplateStore.templateSelectOptions"
              @update:value="handleTemplateChange"
              style="width: 80%; max-width: 600px;"
          />
        </n-form-item>
        <n-form-item
            v-for="(field, fieldName) in customFields"
            :key="field.id"
            :label="field.caption"
        >
          <dynamic-form-field
              :field="field"
              @update:field="(updatedField) => handleFieldChange(fieldName, updatedField)"
          />
        </n-form-item>
        <n-form-item label="Articles" path="templateName">
          <n-transfer
              ref="transfer"
              size="large"
              :options="composerStore.articleSelectOptions"
              source-filterable
              style="width: 80%; max-width: 600px;"

          />
        </n-form-item>
        <n-form-item label="Test message" :show-require-mark="false" label-placement="left"
                     path="mailing_list" :show-feedback="false"
                     class="form-item">
          <n-checkbox size="large" v-model:checked="isTestMessage" @update:checked="handleCheckedChange"/>
        </n-form-item>
        <n-form-item
            :label="isTestMessage ? 'Test user' : 'Mailing List'"
            label-placement="left"
            path="mailing_list"
        >
          <template v-if="isTestMessage">
            <n-input v-model:value="testUserEmail"
                     placeholder="Enter test user email"
                     style="width: 80%; max-width: 600px;"
            />
          </template>
          <template v-else>
            <n-select multiple
                      checkable
                      v-model:value="modelRef.mailingList"
                      :options="mailingListStore.firstPageOptions"
                      placeholder="Select mailing list"
                      style="width: 80%; max-width: 600px;"
            />
          </template>
        </n-form-item>
        <n-form-item label="Subject" path="subject">
          <n-input v-model:value="modelRef.subject"
                   type="text"
                   id="subject"
                   style="width: 80%; max-width: 600px;"
                   placeholder="Subject"/>
          <n-button type="tertiary" @click="handleFetchSubject">{{ globalStore.translations.FETCH_SUBJECT }}
          </n-button>
        </n-form-item>

        <n-form-item label="Review" path="subject">
          <n-space vertical>
            <formatting-buttons/>
            <div id="squire-editor"
                 style="height: 400px; overflow-y: auto; border: 1px solid #ffffff; min-height: 200px;"
                 v-html="composerStore.cont"
            ></div>
          </n-space>
        </n-form-item>
      </n-form>
    </n-gi>
  </n-grid>
</template>

<script>
import {computed, h, nextTick, onMounted, provide, ref} from 'vue';
import {useGlobalStore} from "../../stores/globalStore";
import {debounce} from 'lodash';
import HtmlWrapper from '../HtmlWrapper.vue';
import {
  NButton, NButtonGroup, NCheckbox, NColorPicker, NForm, NFormItem, NGi,
  NGrid, NH3, NH4, NIcon, NInput, NInputNumber, NSelect, NSkeleton, NSpace, NTransfer, useLoadingBar, useMessage
} from "naive-ui";
import {useMessageTemplateStore} from "../../stores/template/messageTemplateStore";
import draggable from 'vuedraggable';
import {useComposerStore} from "../../stores/composer/composerStore";
import Squire from 'squire-rte';
import DOMPurify from 'dompurify';
import {ArrowBigLeft, Bold, ClearFormatting, Code, Italic, Photo, Strikethrough, Underline} from '@vicons/tabler';
import {useMailingListStore} from "../../stores/mailinglist/mailinglistStore";
import {composerFormRules} from "../../stores/composer/composerUtils";
import DynamicFormField from "./DynamicFormField.vue";
import FormattingButtons from "../buttons/FormattingButtons.vue";
import {fetchSubject, sendNewsletter} from "../../utils/newsletterUtils";
import {useNewsletterStore} from "../../stores/newsletter/newsletterStore";

export default {
  name: 'Composer',
  methods: {useMailingListStore},
  components: {
    FormattingButtons, DynamicFormField,
    NSkeleton, NButtonGroup, NButton, NSpace, NForm, NFormItem, NInput, NInputNumber,
    NColorPicker, NIcon, NGrid, NGi, NSelect, draggable, NCheckbox, NH3, NH4, NTransfer,
    Bold, Italic, Underline, Strikethrough, Code, Photo, ClearFormatting, ArrowBigLeft
  },
  props: {
    id: {
      type: Number,
      required: false,
    },
  },
  emits: ['back'],
  setup(props) {
    console.log('Composer component initialized with id:', props.id);
    const articles = ref([]);
    const formRef = ref(null);
    const articlesListRef = ref(null);
    const selectedArticlesListRef = ref(null);
    const composerRef = ref(null);
    const activeTabName = ref('Composer');
    const globalStore = useGlobalStore();
    const composerStore = useComposerStore();
    const mailingListStore = useMailingListStore();
    const messageTemplateStore = useMessageTemplateStore();
    const newsLetterStore = useNewsletterStore();
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();
    const customFields = computed(() => messageTemplateStore.availableCustomFields);
    const squireEditor = ref(null);
    provide('squireEditor', squireEditor)
    const loading = ref(true);
    const isTestMessage = ref(false);
    const testUserEmail = ref('');

    const modelRef = ref({
      mailingList: [],
      testEmail: '',
      subject: '',
      content: composerStore.cont
    });

    const fetchInitialData = async () => {
      try {
        loading.value = true;
        await Promise.all([
          messageTemplateStore.fetchFromApi(1, 10),
          composerStore.fetchEverything('', false),
          //  mailingListStore.getDocs(1, 10, true, msgPopup, loadingBar)
        ]);

        loading.value = false;
      } catch (error) {
        console.error("Error fetching initial data:", error);
      }
    };

    fetchInitialData();

    onMounted(() => {
      window.DOMPurify = DOMPurify;
      nextTick(() => {
        squireEditor.value = new Squire(document.getElementById('squire-editor'));
      });
    });

    const resetFunction = async () => {
      composerStore.selectedArticles = [];
      composerStore.editorCont = '';
      await composerStore.fetchArticles('', true);
    };

    const copyContentToClipboard = () => {
      const completeHTML = composerStore.cont;
      const tempTextArea = document.createElement('textarea');
      tempTextArea.value = completeHTML;
      document.body.appendChild(tempTextArea);
      tempTextArea.select();
      const successful = document.execCommand('copy');
      if (successful) {
        msgPopup.info('HTML code copied to clipboard!');
      } else {
        msgPopup.warning('Failed to copy. Please try again.');
      }
      document.body.removeChild(tempTextArea);
    };

    const preview = () => {
      dialog.create({
        title: 'Preview',
        style: 'width: 800px',
        bordered: true,
        content: () => h(HtmlWrapper, {
          html: squireEditor.value.getHTML()
        }),
      });
    };

    const handleTemplateChange = (newTemplateId) => {
      messageTemplateStore.setCurrentTemplateById(newTemplateId);
    };

    const handleColorChange = (fieldName, index, newValue) => {
      customFields.value[fieldName].defaultValue[index] = newValue;
    };

    const handleFieldChange = (fieldName, newValue) => {
      customFields.value[fieldName].defaultValue = newValue;
    };

    const fetchArticlesDebounced = debounce(composerStore.fetchEverything, 300);

    const debouncedFetchArticles = (val) => {
      fetchArticlesDebounced(val);
    };

    const handleCheckedChange = (checked) => {
      isTestMessage.value = checked;
      if (!checked) {
        testUserEmail.value = '';
      }
    };

    const handleSendNewsletter = (onlySave) => {
      sendNewsletter(modelRef.value, isTestMessage.value, testUserEmail.value, formRef, msgPopup, loadingBar, newsLetterStore, onlySave)
    }

    const handleFetchSubject = async () => {
      try {
        modelRef.value.subject = await fetchSubject(loadingBar)
      } catch (error) {
        msgPopup.error(error.message)
      }
    }

    return {
      composerStore,
      articles,
      globalStore,
      mailingListStore,
      messageTemplateStore,
      customFields,
      articlesListRef,
      selectedArticlesListRef,
      composerRef,
      modelRef,
      formRef,
      activeTabName,
      composerFormRules,
      debouncedFetchArticles,
      resetFunction,
      copyContentToClipboard,
      preview,
      handleSendNewsletter,
      handleTemplateChange,
      handleColorChange,
      handleFieldChange,
      loading,
      handleFetchSubject,
      isTestMessage,
      testUserEmail,
      handleCheckedChange
    };
  }
};
</script>

<style scoped>

n-layout-footer {
  flex-shrink: 0;
  padding: 12px 20px;
}
</style>

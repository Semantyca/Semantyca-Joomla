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
      <n-form ref="formRef"
              :model="modelRef"
              :rules="composerFormRules"
              label-placement="left"
              label-width="auto"
              require-mark-placement="right-hanging"
      >
        <n-form-item label="Template name" path="templateName">
          <n-select
              v-model:value="modelRef.templateId"
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
          <n-select
              v-model:value="selectedArticleIds"
              multiple
              filterable
              placeholder="Search articles"
              :options="composerStore.articleOptions"
              :render-label="renderArticleOption"
              :render-tag="renderSelectedArticle"
              :clear-filter-after-select="true"
              style="width: 80%; max-width: 600px;"
              @update:value="updateSelectedArticles"
          />
        </n-form-item>
        <n-form-item label="Test message" :show-require-mark="false" label-placement="left"
                     path="mailing_list" :show-feedback="false"
                     class="form-item">
          <n-checkbox size="large" v-model:checked="modelRef.isTestMessage" @update:checked="handleCheckedChange"/>
        </n-form-item>
        <n-form-item
            :label="modelRef.isTestMessage ? 'Test user' : 'Mailing List'"
            label-placement="left"
            path="mailing_list"
        >
          <template v-if="modelRef.isTestMessage">
            <n-input v-model:value="modelRef.testEmail"
                     placeholder="Enter test user email"
                     style="width: 80%; max-width: 600px;"
            />
          </template>
          <template v-else>
            <n-select multiple
                      checkable
                      v-model:value="modelRef.mailingList"
                      :options="mailingListStore.getMailingListOptions"
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

        <n-form-item label="Review">
          <n-space vertical>
            <formatting-buttons/>
            <div id="squire-editor"
                 style="height: 400px; overflow-y: auto; border: 1px solid #ffffff; min-height: 200px;"
                 v-html="modelRef.content"
            ></div>
          </n-space>
        </n-form-item>
      </n-form>
    </n-gi>
  </n-grid>
</template>

<script>
import {computed, h, nextTick, onMounted, provide, ref, watch} from 'vue';
import {useGlobalStore} from "../../stores/globalStore";
import {debounce} from 'lodash';
import HtmlWrapper from '../HtmlWrapper.vue';
import {
  NButton,
  NButtonGroup,
  NCheckbox,
  NColorPicker,
  NForm,
  NFormItem,
  NGi,
  NGrid,
  NH3,
  NH4,
  NH6,
  NIcon,
  NInput,
  NInputNumber,
  NSelect,
  NSkeleton,
  NSpace,
  NTag,
  NTransfer,
  useDialog,
  useLoadingBar,
  useMessage
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
import {useNewsletterStore} from "../../stores/newsletter/newsletterStore";
import {MessagingHandler} from "../../utils/MessagingHandler";
import DynamicBuilder from "../../utils/DynamicBuilder";

export default {
  name: 'Composer',
  components: {
    FormattingButtons, DynamicFormField,
    NSkeleton, NButtonGroup, NButton, NSpace, NForm, NFormItem, NInput, NInputNumber,
    NColorPicker, NIcon, NGrid, NGi, NSelect, draggable, NCheckbox, NH3, NH4, NH6, NTransfer,
    Bold, Italic, Underline, Strikethrough, Code, Photo, ClearFormatting, ArrowBigLeft,
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
    const formRef = ref(null);
    const selectedArticleIds = ref([]);
    const composerRef = ref(null);
    const globalStore = useGlobalStore();
    const composerStore = useComposerStore();
    const messageTemplateStore = useMessageTemplateStore();
    const newsLetterStore = useNewsletterStore();
    const mailingListStore = useMailingListStore();
    const templateStore = useMessageTemplateStore();
    const msgPopup = useMessage();
    const dialog = useDialog();
    const loadingBar = useLoadingBar();
    const customFields = computed(() => messageTemplateStore.availableCustomFields);
    const squireEditor = ref(null);
    provide('squireEditor', squireEditor)

    const modelRef = ref({
      templateId: null,
      selectedArticles: [],
      mailingList: [],
      testEmail: '',
      subject: '',
      content: '',
      isTestMessage: false
    });

    const fetchInitialData = async () => {
      try {
        await Promise.all([
          messageTemplateStore.fetchFromApi(1, 10),
          composerStore.fetchArticlesApi(''),
          composerStore.fetchMailingListsApi(1, 20, true),
          mailingListStore.fetchMailingList(1, 10, true)
        ]);
      } catch (error) {
        console.error("Error fetching initial data:", error);
      }
    };

    const copyContentToClipboard = () => {
      const tempTextArea = document.createElement('textarea');
      tempTextArea.value = modelRef.value.content;
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
      modelRef.value.templateId = newTemplateId;
      messageTemplateStore.setCurrentTemplateById(newTemplateId);
    };

    const handleColorChange = (fieldName, index, newValue) => {
      customFields.value[fieldName].defaultValue[index] = newValue;
    };

    const handleFieldChange = (fieldName, newValue) => {
      customFields.value[fieldName].defaultValue = newValue;
    };

    const fetchArticlesDebounced = debounce(composerStore.fetchArticlesApi, 300);

    const debouncedFetchArticles = (val) => {
      fetchArticlesDebounced(val);
    };


    const handleCheckedChange = (checked) => {
      modelRef.value.isTestMessage = checked;
      if (checked) {
        modelRef.value.mailingList = [];
      } else {
        modelRef.value.testUserEmail = '';
      }
    };

    const composerMsg = new MessagingHandler(newsLetterStore);

    const handleSendNewsletter = (onlySave) => {
      formRef.value.validate((errors) => {
        if (!errors) {
          loadingBar.start(); // Start the loading bar

          const subj = modelRef.value.subject;
          const msgContent = modelRef.value.content;
          const templateId = modelRef.value.templateId;
          const customFieldsValues = modelRef.value.customFieldsValues;
          const selectedArticleIds = modelRef.value.selectedArticles.map(article => article.value);
          const isTestMessage = modelRef.value.isTestMessage;
          const mailingList = modelRef.value.mailingList;
          const testEmail = modelRef.value.testEmail;

          composerMsg.send(subj, msgContent, templateId, customFieldsValues, selectedArticleIds, isTestMessage, mailingList, testEmail, onlySave)
              .then(() => {
                if (onlySave) {
                  msgPopup.success('Newsletter saved successfully', {
                    closable: true,
                    duration: 5000
                  });
                } else {
                  msgPopup.success('Newsletter sent successfully', {
                    closable: true,
                    duration: 5000
                  });
                }
              })
              .catch(error => {
                console.error('Error sending/saving newsletter:', error);
                msgPopup.error('An error occurred while sending/saving the newsletter', {
                  closable: true,
                  duration: 10000
                });
              })
              .finally(() => {
                loadingBar.finish(); // Finish the loading bar
              });
        } else {
          Object.keys(errors).forEach(fieldName => {
            const fieldError = errors[fieldName];
            if (fieldError && fieldError.length > 0) {
              msgPopup.error(fieldError[0].message, {
                closable: true,
                duration: 10000
              });
            }
          });
        }
      });
    };

    const handleFetchSubject = async () => {
      try {
        modelRef.value.subject = await composerMsg.fetchSubject();
      } catch (error) {
        msgPopup.error(error.message)
      }
    }

    const renderArticleOption = (option) => {
      return h('div', [
        h('div', {style: 'font-weight: bold;'}, option.category),
        h('div', option.title)
      ]);
    };

    const updateSelectedArticles = (selectedIds) => {
      modelRef.value.selectedArticles = selectedIds.map(id => {
        return composerStore.articleOptions.find(article => article.value === id);
      });
    };

    const renderSelectedArticle = ({option, handleClose}) => {
      return h(NTag, {
        closable: true,
        onClose: handleClose,
      }, {
        default: () => option.title
      });
    };


    fetchInitialData();

    onMounted(() => {
      window.DOMPurify = DOMPurify;
      nextTick(() => {
        squireEditor.value = new Squire(document.getElementById('squire-editor'));
      });

      if (messageTemplateStore.templatesPage.itemCount === 0) {
        msgPopup.warning('At least one template should exist.', {
          closable: true,
          duration: 10000
        });
      }
    });

    watch(
        [() => messageTemplateStore.currentTemplate, () => messageTemplateStore.availableCustomFields],
        () => {
          modelRef.value.templateId = templateStore.currentTemplate.key;
          const dynamicBuilder = new DynamicBuilder(templateStore.currentTemplate);
          dynamicBuilder.addVariable("articles", modelRef.value.selectedArticles.value);

          Object.keys(templateStore.availableCustomFields).forEach((key) => {
            const field = templateStore.availableCustomFields[key];
            const fieldValue = field.defaultValue;
            dynamicBuilder.addVariable(key, fieldValue);
          });

          try {
            modelRef.value.content = dynamicBuilder.buildContent();
          } catch (e) {
            console.log(e.message);
            msgPopup.error(e.message, {
              closable: true,
              duration: 10000
            });
          }
        },
        { deep: true }
    );

    return {
      composerStore,
      globalStore,
      messageTemplateStore,
      mailingListStore,
      customFields,
      composerRef,
      modelRef,
      formRef,
      selectedArticleIds,
      composerFormRules,
      debouncedFetchArticles,
      copyContentToClipboard,
      preview,
      handleSendNewsletter,
      handleTemplateChange,
      handleColorChange,
      handleFieldChange,
      handleFetchSubject,
      handleCheckedChange,
      renderArticleOption,
      renderSelectedArticle,
      updateSelectedArticles
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

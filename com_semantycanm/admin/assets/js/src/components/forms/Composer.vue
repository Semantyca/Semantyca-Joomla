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
              :rules="rules"
              label-placement="left"
              label-width="auto"
              require-mark-placement="right-hanging"
      >
        <n-form-item label="Template name" path="templateName">
          <n-select
              v-model:value="modelRef.templateId"
              :options="templateStore.templateSelectOptions"
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
        <n-form-item label="Articles" path="selectedArticles">
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
                     path="recipientField" :show-feedback="false">
          <n-checkbox v-model:checked="modelRef.isTestMessage"/>
        </n-form-item>
        <n-form-item
            :label="modelRef.isTestMessage ? 'Test user' : 'Mailing List'"
            label-placement="left"
            path="recipientField"
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
                      v-model:value="modelRef.mailingListIds"
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
import DynamicFormField from "./DynamicFormField.vue";
import FormattingButtons from "../buttons/FormattingButtons.vue";
import {useNewsletterStore} from "../../stores/newsletter/newsletterStore";
import {MessagingHandler} from "../../utils/MessagingHandler";
import DynamicBuilder from "../../utils/DynamicBuilder";
import {isEmail} from "validator";

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
    const newsLetterStore = useNewsletterStore();
    const mailingListStore = useMailingListStore();
    const templateStore = useMessageTemplateStore();
    const msgPopup = useMessage();
    const dialog = useDialog();
    const loadingBar = useLoadingBar();
    const currentTemplateId = computed(() => modelRef.value.templateId);
    const customFields = computed(() => templateStore.availableCustomFields);
    const squireEditor = ref(null);
    provide('squireEditor', squireEditor)

    const modelRef = ref({
      templateId: null,
      articleIds: [],
      mailingListIds: [],
      testEmail: '',
      subject: '',
      content: '',
      useWrapper: true,
      isTestMessage: false
    });

    const fetchInitialData = async () => {
      try {
        if (props.id) {
          await composerStore.fetchNewsletter(props.id);
          const newsletter = composerStore.newsletterDoc;

          modelRef.value = {
            templateId: newsletter.templateId,
            articleIds: newsletter.articlesIds,
            mailingListIds: newsletter.mailingListIds,
            testEmail: newsletter.testEmail,
            subject: newsletter.subject,
            content: newsletter.messageContent,
            useWrapper: newsletter.useWrapper,
            isTestMessage: newsletter.isTest,
            customFieldsValues: newsletter.customFieldsValues
          };

          selectedArticleIds.value = newsletter.articlesIds;

          //     await templateStore.setCurrentTemplateById(newsletter.templateId);
        }

        await Promise.all([
         // composerStore.fetchTemplate(modelRef.value.templateId),
          composerStore.searchArticles(''),
          composerStore.fetchMailingLists(1, 20, true),
        ]);

        /* if (templateStore.currentTemplate) {
           const dynamicBuilder = new DynamicBuilder(templateStore.currentTemplate);
           dynamicBuilder.addVariable("articles", modelRef.value.articleIds);

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
         }*/
      } catch (error) {
        console.error("Error fetching initial data:", error);
        msgPopup.error("Failed to load initial data");
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
      templateStore.setCurrentTemplateById(newTemplateId);
    };

    const handleColorChange = (fieldName, index, newValue) => {
      customFields.value[fieldName].defaultValue[index] = newValue;
    };

    const handleFieldChange = (fieldName, newValue) => {
      customFields.value[fieldName].defaultValue = newValue;
    };

    const composerMsg = new MessagingHandler(newsLetterStore);

    const handleSendNewsletter = (onlySave) => {
      formRef.value.validate((errors) => {
        if (!errors) {
          loadingBar.start();

          const subj = modelRef.value.subject;
          const msgContent = modelRef.value.content;
          const useWrapper = modelRef.value.useWrapper;
          const templateId = modelRef.value.templateId;
          const customFieldsValues = modelRef.value.customFieldsValues;
          const selectedArticleIds = modelRef.value.articleIds.map(article => article.value);
          const isTestMessage = modelRef.value.isTestMessage;
          const mailingList = modelRef.value.mailingListIds;
          const testEmail = modelRef.value.testEmail;

          composerMsg.send(subj, msgContent, useWrapper, templateId, customFieldsValues, selectedArticleIds, isTestMessage, mailingList, testEmail, onlySave, props.id)
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
                loadingBar.finish();
              });
        } else {
          if (errors && typeof errors === 'object') {
            const errorMessages = {};

            Object.keys(errors).forEach(fieldName => {
              const fieldError = errors[fieldName];
              if (fieldError && fieldError.length > 0) {
                errorMessages[fieldName] = fieldError[0].message + ` [${fieldName}]`;
              }
            });

            Object.values(errorMessages).forEach(errorMessage => {
              msgPopup.error(errorMessage, {
                closable: true,
                duration: 10000
              });
            });
          }
        }
      });
    };

    const handleFetchSubject = async () => {
      loadingBar.start();
      try {
        modelRef.value.subject = await composerMsg.fetchSubject();
      } catch (error) {
        loadingBar.error();
        msgPopup.error(error.message)
      } finally {
        loadingBar.finish();
      }
    }

    const renderArticleOption = (option) => {
      return h('div', [
        h('div', {style: 'font-weight: bold;'}, option.category),
        h('div', option.title)
      ]);
    };

    const updateSelectedArticles = (selectedIds) => {
      modelRef.value.articleIds = selectedIds.map(id => {
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

    const rules = {
      subject: {
        required: true,
        message: 'Subject cannot be empty',
      },
      selectedArticles: {
        required: true,
        validator(rule, value) {
          if (value.length > 0) {
            return true;
          }
          return new Error('Please select at least one article');
        },
      },
      recipientField: {
        required: true,
        validator(rule, value) {
          if (modelRef.value.isTestMessage) {
            if (modelRef.value.testEmail && isEmail(modelRef.value.testEmail)) {
              return true;
            }
            return new Error('Please enter a valid email address for test user');
          } else {
            if (modelRef.value.mailingListIds && Array.isArray(modelRef.value.mailingListIds) && modelRef.value.mailingListIds.length > 0) {
              return true;
            }
            return new Error('Please select at least one mailing list');
          }
        },
      },
    };

    fetchInitialData();

    onMounted(() => {
      window.DOMPurify = DOMPurify;
      nextTick(() => {
        squireEditor.value = new Squire(document.getElementById('squire-editor'));
      });
    });

    watch(currentTemplateId, async (newTemplateId) => {
      if (newTemplateId) {
        await composerStore.fetchTemplate(newTemplateId);
        // Additional logic after fetching template, if needed
      }
    }, { immediate: true });

    watch(
        [
          () => templateStore.currentTemplate,
          () => templateStore.availableCustomFields,
          () => modelRef.value.articleIds
        ],
        () => {
          /*  modelRef.value.templateId = templateStore.currentTemplate.id;
            const dynamicBuilder = new DynamicBuilder(templateStore.currentTemplate);
            dynamicBuilder.addVariable("articles", modelRef.value.articleIds);

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
            }*/
        },
        {deep: true}
    );


    return {
      composerStore,
      templateStore,
      globalStore,
      mailingListStore,
      customFields,
      composerRef,
      modelRef,
      formRef,
      selectedArticleIds,
      rules,
      copyContentToClipboard,
      preview,
      handleSendNewsletter,
      handleTemplateChange,
      handleColorChange,
      handleFieldChange,
      handleFetchSubject,
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

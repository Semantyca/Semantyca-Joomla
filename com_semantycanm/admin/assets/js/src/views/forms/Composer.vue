<template>
  <n-grid :cols="1" x-gap="5" y-gap="10">
    <n-gi>
      <n-page-header :subtitle="modelRef.subject" class="mb-3">
        <template #title>
          Newsletter
        </template>
      </n-page-header>
    </n-gi>
    <n-gi>
      <n-space>
        <n-button type="info" @click="handleBack">
          <template #icon>
            <n-icon>
              <ArrowBigLeft/>
            </n-icon>
          </template>
          &nbsp;Back
        </n-button>
        <n-button type="success"
                  @click="handleSendAndSave">
          {{ globalStore.translations.SEND }} & {{ globalStore.translations.SAVE }}
        </n-button>
        <n-button type="primary" @click="handleSave">
          {{ globalStore.translations.SAVE }}
        </n-button>
        <n-dropdown trigger="click"
                    ref="templateDropdownRef"
                    :options="templateStore.templateSelectOptions"
                    @select="handleTemplateChange">
          <n-button type="primary" :disabled="isTemplateButtonDisabled">
            Select Template
          </n-button>
        </n-dropdown>
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
        <n-collapse-transition :show="showCustomFields">
          <n-form-item
              v-for="(field, fieldName) in modelRef.customFields"
              :key="field.id"
              :label="field.caption"
              :path="fieldName"
              :rule="rules[fieldName]"
              :show-feedback="false"
              label-placement="left"
              :style="{ marginBottom: '16px' }"
          >
            <dynamic-form-field
                :field="field"
                :articleOptions="composerStore.articleOptions"
                @update:field="(updatedField) => handleFieldChange(fieldName, updatedField)"
            />
          </n-form-item>
        </n-collapse-transition>
        <n-form-item label="Test message" :show-require-mark="false" label-placement="left"
                     path="recipientField" :show-feedback="false">
          <n-checkbox v-model:checked="modelRef.isTestMessage"/>
        </n-form-item>
        <n-form-item
            :label="modelRef.isTestMessage ? 'Test user' : 'Mailing List'"
            label-placement="left"
            path="recipientField"
            :show-feedback="false"
            :style="{ marginBottom: '16px' }"
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
        <n-form-item label="Subject"
                     path="subject"
                     :show-feedback="false"
                     :style="{ marginBottom: '16px' }">
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
import {useRoute, useRouter} from 'vue-router';
import {useGlobalStore} from "../../stores/globalStore";
import HtmlWrapper from '../../components/HtmlWrapper.vue';
import {
  NButton,
  NButtonGroup,
  NCheckbox,
  NCollapseTransition,
  NColorPicker,
  NDropdown,
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
  NPageHeader,
  NSelect,
  NSkeleton,
  NSpace,
  NSpin,
  NTransfer,
  useDialog,
  useLoadingBar,
  useMessage
} from "naive-ui";
import {useTemplateStore} from "../../stores/template/templateStore";
import {useComposerStore} from "../../stores/composer/composerStore";
import Squire from 'squire-rte';
import DOMPurify from 'dompurify';
import {ArrowBigLeft, Bold, ClearFormatting, Code, Italic, Photo, Strikethrough, Underline} from '@vicons/tabler';
import {useMailingListStore} from "../../stores/mailinglist/mailinglistStore";
import DynamicFormField from "./DynamicFormField.vue";
import FormattingButtons from "../../components/buttons/FormattingButtons.vue";
import {useNewsletterStore} from "../../stores/newsletter/newsletterStore";
import {MessagingHandler} from "../../utils/MessagingHandler";
import NewsletterParams from '../../utils/NewsletterParams';
import DynamicBuilder from "../../utils/DynamicBuilder"
import {isEmail} from "validator";

export default {
  name: 'Composer',
  components: {
    FormattingButtons, DynamicFormField, NDropdown, NSpin, NCollapseTransition,
    NSkeleton, NButtonGroup, NButton, NSpace, NForm, NFormItem, NInput, NInputNumber, NPageHeader,
    NColorPicker, NIcon, NGrid, NGi, NSelect, NCheckbox, NH3, NH4, NH6, NTransfer,
    Bold, Italic, Underline, Strikethrough, Code, Photo, ClearFormatting, ArrowBigLeft,
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const formRef = ref(null);
    const composerRef = ref(null);
    const showTemplateDropdown = ref(false);
    const templateDropdownRef = ref(null);
    const showCustomFields = ref(false);
    const globalStore = useGlobalStore();
    const composerStore = useComposerStore();
    const newsLetterStore = useNewsletterStore();
    const mailingListStore = useMailingListStore();
    const templateStore = useTemplateStore();
    const msgPopup = useMessage();
    const dialog = useDialog();
    const loadingBar = useLoadingBar();
    const squireEditor = ref(null);
    provide('squireEditor', squireEditor)
    const isFetchingTemplateOptions = ref(false);
    const newsletterId = computed(() => route.params.id ? parseInt(route.params.id) : null);
    const messagingHandler = new MessagingHandler(newsLetterStore);
    const modelRef = ref({
      templateId: null,
      customFields: {},
      mailingListIds: [],
      testEmail: '',
      subject: '',
      content: '',
      useWrapper: true,
      isTestMessage: false
    });

    const isTemplateButtonDisabled = computed(() => newsletterId.value !== null);

    const fetchInitialData = async () => {
      try {
        if (newsletterId.value) {
          await composerStore.fetchNewsletter(newsletterId.value);
          const newsletter = composerStore.newsletterDoc;
          modelRef.value = {
            templateId: newsletter.templateId,
            customFields: JSON.parse(newsletter.customFieldsValues),
            articleIds: newsletter.articlesIds,
            mailingListIds: newsletter.mailingListIds,
            testEmail: newsletter.testEmail,
            subject: newsletter.subject,
            useWrapper: newsletter.useWrapper,
            isTestMessage: newsletter.isTest,
          };
          showCustomFields.value = true;
          if (squireEditor.value) {
            squireEditor.value.setHTML(newsletter.messageContent);
          }
        } else {
          showCustomFields.value = false;
          modelRef.value.customFields = {};
        }

        await Promise.all([
          composerStore.searchArticles(''),
          templateStore.fetchTemplates(1, 100),
          mailingListStore.fetchMailingList(1, 100, true)
        ]);
      } catch (error) {
        console.error("Error fetching initial data:", error);
        msgPopup.error("Failed to load initial data");
      }
    }

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

    const handleTemplateChange = (appliedTemplateId) => {
      modelRef.value.templateId = appliedTemplateId;
      templateStore.applyTemplateById(appliedTemplateId);
      modelRef.value.customFields = templateStore.availableCustomFields;
      showCustomFields.value = true;
    };

    const handleFieldChange = (fieldName, updatedField) => {
      modelRef.value.customFields[fieldName] = updatedField;
    };

    const handleSendAndSave = () => {
      const newsletterParams = new NewsletterParams(modelRef, squireEditor.value.getHTML(), false, newsletterId.value);
      messagingHandler.send(newsletterParams);
    }

    const handleSave = () => {
      //const newsletterParams = new NewsletterParams(modelRef, squireEditor.value.getHTML(), true, newsletterId.value);
      const newsletterParams = new NewsletterParams(modelRef, modelRef.value.content, true, newsletterId.value);
      messagingHandler.send(newsletterParams);
    }

    const handleFetchSubject = async () => {
      loadingBar.start();
      try {
        modelRef.value.subject = await messagingHandler.fetchSubject();
      } catch (error) {
        loadingBar.error();
        msgPopup.error(error.message)
      } finally {
        loadingBar.finish();
      }
    }

    const handleBack = () => {
      router.push('/list');
    };

    const rules = ref({
      subject: {
        required: true,
        message: 'Subject cannot be empty',
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
    });

    const updateRules = () => {
      Object.entries(modelRef.value.customFields).forEach(([fieldName, field]) => {
        const fieldRules = [];

        if (field.type === 520 || field.type === 521) {
          fieldRules.push({
            validator(rule, value) {
              console.log('def', field);
              if (Array.isArray(field.defaultValue) && field.defaultValue.length < 1) {
                return new Error('Please select at least one article');
              }
              return true;
            },
            trigger: ['blur', 'change'],
          });
        }

        rules.value[fieldName] = fieldRules;
      });
    };

    watch(modelRef.value.customFields, () => {
      updateRules();
    }, {deep: true, immediate: true});

    fetchInitialData();

    onMounted(() => {
      window.DOMPurify = DOMPurify;
      nextTick(() => {
        squireEditor.value = new Squire(document.getElementById('squire-editor'));
      });
    });

    watch(
        [
          () => modelRef.value.customFields,
        ],
        () => {
          const dynamicBuilder = new DynamicBuilder(templateStore.appliedTemplateDoc);

          Object.keys(modelRef.value.customFields).forEach((key) => {
            const field = modelRef.value.customFields[key];
            dynamicBuilder.addVariable(field.name, field.defaultValue);
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
        {deep: true}
    );

    return {
      showTemplateDropdown,
      templateDropdownRef,
      showCustomFields,
      composerStore,
      templateStore,
      globalStore,
      mailingListStore,
      composerRef,
      modelRef,
      formRef,
      isFetchingTemplateOptions,
      isTemplateButtonDisabled,
      rules,
      handleSave,
      handleSendAndSave,
      preview,
      handleTemplateChange,
      handleFieldChange,
      handleFetchSubject,
      handleBack,
    };
  }
};
</script>

<style scoped>
.n-form-item .n-form-item-feedback {
  border: none;
}

n-layout-footer {
  flex-shrink: 0;
  padding: 12px 20px;
}
</style>

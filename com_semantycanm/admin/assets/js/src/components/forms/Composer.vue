<template>
  <n-h3>Newsletter</n-h3>
  <n-grid :cols="1" x-gap="5" y-gap="10">
    <n-gi>
      <n-space>
        <n-button type="primary" @click="$emit('back')">
          <template #icon>
            <n-icon>
              <ArrowBigLeft/>
            </n-icon>
          </template>
          &nbsp;Back
        </n-button>
      </n-space>
    </n-gi>
      <n-gi class="mt-2">
        <n-form-item label="Template name" label-placement="left" path="templateName">
          <n-select class="w-20"
                    v-model:value="messageTemplateStore.currentTemplate.key"
                    :options="messageTemplateStore.templateSelectOptions"
                    @update:value="handleTemplateChange"
          ></n-select>
        </n-form-item>
      </n-gi>
      <n-gi>
        <n-form-item
            label-placement="left"
            require-mark-placement="right-hanging"
            label-width="180px"
            :style="{ maxWidth: '800px' }"
            v-for="(field, fieldName) in customFields"
            :key="field.id"
            :label="field.caption"
        >
          <dynamic-form-field
              :field="field"
              @update:field="(updatedField) => handleFieldChange(fieldName, updatedField)"
          />
        </n-form-item>
      </n-gi>
    <n-gi>
<!--      <n-transfer
          ref="transfer"
          v-model:value="value"
          :options="composerStore.selectedArticles"
          :render-source-list="composerStore.selectedArticles"
          source-filterable
      />-->
    </n-gi>

    <n-gi class="mt-4">
      <n-button-group>
        <n-button @click="formatText('bold')" secondary>
          <template #icon>
            <n-icon>
              <Bold/>
            </n-icon>
          </template>
          Bold
        </n-button>
        <n-button @click="formatText('italic')" secondary>
          <template #icon>
            <n-icon>
              <Italic/>
            </n-icon>
          </template>
          Italic
        </n-button>
        <n-button @click="formatText('underline')" secondary>
          <template #icon>
            <n-icon>
              <Underline/>
            </n-icon>
          </template>
          Underline
        </n-button>
        <n-button @click="formatText('strikethrough')" secondary>
          <template #icon>
            <n-icon>
              <Strikethrough/>
            </n-icon>
          </template>
          Strikethrough
        </n-button>
        <n-button @click="insertImage" secondary>
          <template #icon>
            <n-icon>
              <Photo/>
            </n-icon>
          </template>
          Insert Image
        </n-button>
        <n-button @click="formatText('removeFormat')" secondary>
          <template #icon>
            <n-icon>
              <ClearFormatting/>
            </n-icon>
          </template>
          Remove Formatting
        </n-button>
        <n-button @click="previewHtml" secondary>
          <template #icon>
            <n-icon>
              <Code/>
            </n-icon>
          </template>
          HTML Preview
        </n-button>
      </n-button-group>
    </n-gi>
    <n-gi>
      <div
          id="squire-editor"
          style="height: 400px; overflow-y: auto; border: 1px solid #ffffff; min-height: 200px;"
          v-html="composerStore.cont"
      ></div>
    </n-gi>
  </n-grid>
  <n-form inline ref="formRef" :rules="composerFormRules" label-placement="left" label-width="auto">
    <n-grid :cols="1" class="mt-3">
      <n-gi>
        <n-form-item label="Test message" :show-require-mark="false" label-placement="left"
                     path="mailing_list" :show-feedback="false"
                     class="form-item">
          <n-checkbox size="large" v-model:checked="isTestMessage" @update:checked="handleCheckedChange"/>
        </n-form-item>
        <n-form-item
            :label="isTestMessage ? 'Test user' : 'Mailing List'"
            label-placement="left"
            path="mailing_list"
            :show-feedback="false"
            class="form-item"
        >
          <template v-if="isTestMessage">
            <n-input
                size="large"
                v-model:value="testUserEmail"
                placeholder="Enter test user email"
                style="width: 100%; max-width: 600px;"
            />
          </template>
          <template v-else>
            <n-select
                size="large"
                multiple
                checkable
                v-model:value="modelRef.mailingList"
                :options="mailingListStore.firstPageOptions"
                placeholder="Select mailing list"
                style="width: 100%; max-width: 600px;"
            />
          </template>
        </n-form-item>
      </n-gi>
      <n-gi>
        <n-form-item label="Subject" label-placement="left" path="subject" :show-feedback="false"
                     class="form-item">
          <n-input v-model:value="modelRef.subject"
                   size="large"
                   type="text"
                   id="subject"
                   style="width: 100%; max-width: 600px;"
                   placeholder="Subject"/>
          <n-button size="large"
                    type="tertiary"
                    @click="fetchSubject">{{ globalStore.translations.FETCH_SUBJECT }}
          </n-button>
        </n-form-item>
      </n-gi>
      <n-gi>
        <n-form-item label="&nbsp;" :show-require-mark="false" label-placement="left" path="mailing_list"
                     :show-feedback="false"
                     class="form-item">
          <n-space class="mt-2">
            <n-button size="large" type="success" @click="sendNewsletter(false)">
              {{ globalStore.translations.SEND }} & {{ globalStore.translations.SAVE }}
            </n-button>
            <n-button size="large" type="primary" @click="sendNewsletter(true)">
              {{ globalStore.translations.SAVE }}
            </n-button>
          </n-space>
        </n-form-item>
      </n-gi>
    </n-grid>
  </n-form>
</template>

<script>
import {computed, h, onMounted, nextTick, ref} from 'vue';
import {useGlobalStore} from "../../stores/globalStore";
import {debounce} from 'lodash';
import HtmlWrapper from '../HtmlWrapper.vue';
import { NTransfer, NButton, NButtonGroup, NColorPicker, NDivider, NForm,
  NFormItem, NGi, NGrid, NIcon, NInput, NInputNumber,
  NSkeleton, NSpace, NSelect, useDialog, useLoadingBar,
  useMessage, NCheckbox, NH3, NH4
} from "naive-ui";
import {useMessageTemplateStore} from "../../stores/template/messageTemplateStore";
import draggable from 'vuedraggable';
import {useComposerStore} from "../../stores/composer/composerStore";
import Squire from 'squire-rte';
import DOMPurify from 'dompurify';
import CodeMirror from 'vue-codemirror6';
import {html} from '@codemirror/lang-html';
import {Bold, Code, Italic, Strikethrough, Underline, Photo, ClearFormatting, ArrowBigLeft} from '@vicons/tabler';
import NewsletterApiManager from "../../stores/newsletter/NewsletterApiManager";
import UserExperienceHelper from "../../utils/UserExperienceHelper";
import {useMailingListStore} from "../../stores/mailinglist/mailinglistStore";
import {composerFormRules} from "../../stores/composer/composerUtils";
import DynamicFormField from "./DynamicFormField.vue";

export default {
  name: 'Composer',
  methods: {useMailingListStore},
  components: {
    DynamicFormField,
    NSkeleton, NButtonGroup, NButton, NSpace, NDivider, NForm, NFormItem, NInput, NInputNumber,
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
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();
    const dialog = useDialog();
    const customFields = computed(() => messageTemplateStore.availableCustomFields);
    const squireEditor = ref(null);
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

    const previewHtml = () => {
      dialog.create({
        title: 'HTML Preview',
        style: 'width: 1024px',
        bordered: true,
        content: () => h('div', {
          style: {overflow: 'auto', maxHeight: '600px', marginBottom: '40px'}
        }, [
          h(CodeMirror, {
            modelValue: composerStore.cont,
            basic: true,
            lang: html(),
            dark: false,
            style: {width: '100%'},
            readOnly: true,
            extensions: [
              html()
            ],
          }),
        ]),
      });
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

    const sendNewsletter = async (onlySave) => {
      formRef.value.validate((errors) => {
        if (!errors) {
          const subj = modelRef.value.subject;
          const msgContent = modelRef.value.content;
          const newsletterApiManager = new NewsletterApiManager(msgPopup, loadingBar);
          if (onlySave) {
            newsletterApiManager.saveNewsletter(subj, msgContent);
          } else {
            let listItems;
            if (isTestMessage.value) {
              listItems = [testUserEmail.value.trim()];
            } else {
              listItems = modelRef.value.mailingList.map(item => item.value);
            }
            newsletterApiManager.sendEmail(subj, msgContent, listItems)
                .then((response) => {
                  console.log('response data:', response.data);
                  newsLetterStore.currentNewsletterId = response.data;
                  newsLetterStore.startPolling();
                })
                .catch(error => {
                  console.log('error: ', error.message);
                  msgPopup.error(error.message, {
                    closable: true,
                    duration: 10000
                  });
                });
          }
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

    const formatText = (format) => {
      if (squireEditor.value) {
        squireEditor.value.focus();
        switch (format) {
          case 'bold':
            squireEditor.value.bold();
            break;
          case 'italic':
            squireEditor.value.italic();
            break;
          case 'underline':
            squireEditor.value.underline();
            break;
          case 'strikethrough':
            squireEditor.value.strikethrough();
            break;
          case 'removeFormat':
            squireEditor.value.removeAllFormatting();
            break;
          default:
            break;
        }
      }
    };

    const insertImage = () => {
      const imageUrl = prompt("Enter image URL:");
      if (imageUrl) {
        squireEditor.value.insertImage(imageUrl);
      }
    };

    const fetchSubject = async () => {
      try {
        const subject = await UserExperienceHelper.getSubject(loadingBar);
        modelRef.value.subject = subject;
        if (modelRef.value.content.trim() === '') {
          modelRef.value.content = `<body>${subject}</body>`;
        }
      } catch (error) {
        msgPopup.error("Failed to fetch subject");
      }
    };

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
      sendNewsletter,
      handleTemplateChange,
      handleColorChange,
      handleFieldChange,
      formatText,
      insertImage,
      previewHtml,
      loading,
      fetchSubject,
      isTestMessage,
      testUserEmail,
      handleCheckedChange
    };
  }
};
</script>

<style scoped>
/* Ensure the layout takes full height */
.layout-full-height {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* Flex-grow 1 ensures the content area takes the remaining space */
.layout-content-expand {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

/* Footer styling */
n-layout-footer {
  flex-shrink: 0; /* Prevent footer from shrinking */
  padding: 12px 20px;
}
</style>

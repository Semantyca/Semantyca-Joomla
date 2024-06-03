<template>
  <n-grid :cols="1" x-gap="12" y-gap="12" class="mt-1">
    <n-gi>
      <n-space>
        <n-button type="success" @click="sendNewsletter(false)">
          {{ globalStore.translations.SEND }}
        </n-button>
        <n-button type="primary" @click="sendNewsletter(true)">
          {{ globalStore.translations.SAVE }}
        </n-button>
        <span style="display:inline-block; width:32px;"></span>
        <n-progress type="circle" :percentage="newsLetterStore.progress.dispatched" style="margin-right: 8px; width: 60px; height: 60px; line-height: 24px;">
          <template #default>
            <span style="font-size: 16px;">{{newsLetterStore.progress.dispatched}}%</span>
          </template>
        </n-progress>
        <span style="display:inline-block; width:16px;"></span>
        <n-progress type="circle" :percentage="newsLetterStore.progress.read" style="margin-right: 8px; width: 60px; height: 60px; line-height: 24px;">
          <template #default>
            <span style="font-size: 16px;">{{newsLetterStore.progress.read}}%</span>
          </template>
        </n-progress>
      </n-space>
    </n-gi>
    <n-gi>
      <n-divider title-placement="left">Newsletter properties</n-divider>
    </n-gi>
    <n-gi>
      <n-form inline ref="formRef" :rules="rules" :model="modelRef" label-placement="left" label-width="auto"
              :show-feedback="false">
        <n-grid :cols="8">
          <n-gi span="8">
            <n-form-item label="Mailing lists" label-placement="left" path="mailingList" :show-feedback="false"
                         class="form-item">
              <n-select v-model:value="modelRef.mailingList"
                        size="large"
                        :loading="loadingMailingListRef"
                        remote
                        placeholder="Mailing lists"
                        style="width: 100%; max-width: 600px;"
                        multiple :options="newsLetterStore.mailingListOptions"
              />
            </n-form-item>
            <n-form-item label="Custom address" label-placement="left" path="testEmail" :show-feedback="false"
                         class="form-item">
              <n-input v-model:value="modelRef.testEmail"
                       placeholder="E-mail address"
                       size="large"
                       type="text"
                       id="testEmails"
                       style="width: 100%; max-width: 600px;"
                       name="testEmails"/>
            </n-form-item>
            <n-form-item label="Subject" label-placement="left" path="subject" :show-feedback="false" class="form-item">
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
        </n-grid>
      </n-form>
    </n-gi>
    <n-gi>
      <n-divider title-placement="left">Newsletter source</n-divider>
    </n-gi>
    <n-gi>
      <n-form-item label="Content" label-placement="left" path="content">
        <code-mirror
            v-model="modelRef.content"
            basic
            :lang="lang"
            :dark="dark"
            :style="{ width: '100%' }"
        />
      </n-form-item>
    </n-gi>
  </n-grid>
</template>

<script>
import {defineComponent, onMounted, onUnmounted, reactive, ref, watch} from 'vue';
import {useGlobalStore} from "../stores/globalStore";
import {
  NButton,
  NDivider,
  NForm,
  NFormItem,
  NGi,
  NGrid,
  NInput,
  NInputGroup,
  NProgress,
  NSelect,
  NSpace,
  useLoadingBar,
  useMessage,
} from "naive-ui";
import {useNewsletterStore} from "../stores/newsletter/newsletterStore";
import {useComposerStore} from "../stores/composer/composerStore";
import NewsletterApiManager from "../stores/newsletter/NewsletterApiManager";
import UserExperienceHelper from "../utils/UserExperienceHelper";
import CodeMirror from 'vue-codemirror6';
import {html} from '@codemirror/lang-html';

export default defineComponent({
  name: 'NewsletterComponent',
  components: {
    NInput,
    NButton,
    NSpace,
    NFormItem,
    NForm,
    NProgress,
    NInputGroup,
    NGrid,
    NGi,
    NSelect,
    NDivider,
    CodeMirror
  },
  props: {
    messageContent: String,
  },
  setup() {
    const formRef = ref(null);
    const loadingMailingListRef = ref(false)
    const globalStore = useGlobalStore();
    const newsLetterStore = useNewsletterStore();
    const composerStore = useComposerStore();
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar()
    const modelRef = ref({
      mailingList: [],
      testEmail: '',
      subject: '',
      content: composerStore.cont
    });
    const state = reactive({
      isTest: false,
      progress: 0
    });

    onMounted(() => {
      newsLetterStore.getMailingLists(1, 100, true, msgPopup, loadingBar);
      document.addEventListener('visibilitychange', handleVisibilityChange);
      newsLetterStore.startPolling();
    });

    onUnmounted(() => {
      document.removeEventListener('visibilitychange', handleVisibilityChange);
      newsLetterStore.stopPolling();
    });

    watch(() => composerStore.cont, (newVal) => {
      modelRef.value.content = newVal;
    });

    const handleVisibilityChange = () => {
      if (document.visibilityState === 'hidden') {
        newsLetterStore.stopPolling();
      } else {
        newsLetterStore.startPolling();
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
            if (modelRef.value.testEmail !== "") {
              listItems = modelRef.value.testEmail.split(',').map(email => email.trim());
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

    function validateMailingListAndEmail() {
      if (modelRef.value.mailingList.length === 0 && !modelRef.value.testEmail) {
        return new Error('At least one mailing list or test email should be selected');
      }
      return true;
    }

    const rules = {
      mailingList: {
        validator: validateMailingListAndEmail,
        trigger: 'blur'
      },
      testEmail: {
        validator: validateMailingListAndEmail,
        trigger: 'blur'
      },
      subject: {
        required: true,
        message: 'The subject cannot be empty',
        trigger: 'blur'
      },
      content: {
        validator() {
          return !!(modelRef.value.content && modelRef.value.content.trim() !== '');
        },
        message: 'The content cannot be empty',
        trigger: 'blur'
      }
    };

    const checkStatus = () => {
      const rowData = newsLetterStore.startPolling();
      console.log(rowData);
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
      globalStore,
      newsLetterStore,
      composerStore,
      state,
      formRef,
      loadingMailingListRef,
      rules,
      sendNewsletter,
      fetchSubject,
      checkStatus,
      modelRef,
      dark: ref(false),
      lang: ref(html()),
    };
  },
});
</script>

<style>
.form-item {
  margin-bottom: 16px;
}
</style>

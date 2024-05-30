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
        <n-progress
            type="circle"
            :percentage="newsLetterStore.progress.dispatched"
            style="margin-bottom: 10px; margin-right: 8px; width: 34px; height: 34px; line-height: 24px;"
        ></n-progress>
        <span style="display:inline-block; width:16px;"></span>
        <n-progress
            type="circle"
            :percentage="newsLetterStore.progress.read"
            style="margin-bottom: 10px; margin-right: 8px; width: 34px; height: 34px; line-height: 24px;"
        ></n-progress>
      </n-space>
    </n-gi>
    <n-gi>
      <n-divider title-placement="left">Newsletter properties</n-divider>
    </n-gi>
    <n-gi>
      <n-form inline ref="newsletterFormRef" :rules="newsLetterRules" label-placement="left" label-width="auto" v-on:submit.prevent>

      <n-grid :cols="8">
          <n-gi span="8">
            <n-form-item label="Mailing lists" label-placement="left" path="templateName">
              <n-select v-model:value="mailingListValue"
                        size="large"
                        :loading="loadingMailingListRef"
                        remote
                        placeholder="Mailing lists"
                        style="width: 100%; max-width: 600px;"
                        multiple :options="newsLetterStore.mailingListOptions"
              />
            </n-form-item>
            <n-form-item label="Custom address" label-placement="left" path="description">
              <n-input v-model:value="newsLetterFormValue.testEmail"
                       placeholder="E-mail address"
                       size="large"
                       type="text"
                       id="testEmails"
                       style="width: 100%; max-width: 600px;"
                       name="testEmails"/>
            </n-form-item>
            <n-form-item label="Subject" label-placement="left" path="description">
              <n-input v-model:value="newsLetterFormValue.subject"
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
      <code-mirror
          v-model="composerStore.cont"
          basic
          :lang="lang"
          :dark="dark"
          :style="{ width: '100%' }"
          read-only
      />
    </n-gi>
  </n-grid>
</template>

<script>
import { defineComponent, h, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { useGlobalStore } from "../stores/globalStore";
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
import { useNewsletterStore } from "../stores/newsletter/newsletterStore";
import { useComposerStore } from "../stores/composer/composerStore";
import NewsletterApiManager from "../stores/newsletter/NewsletterApiManager";
import UserExperienceHelper from "../utils/UserExperienceHelper";
import CodeMirror from 'vue-codemirror6';
import { html } from '@codemirror/lang-html';

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
  setup(props) {
    const newsletterFormRef = ref(null);
    const loadingMailingListRef = ref(false)
    const globalStore = useGlobalStore();
    const newsLetterStore = useNewsletterStore();
    const composerStore = useComposerStore();
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar()
    const state = reactive({
      isTest: false,
      progress: 0
    });

    const newsLetterFormValue = ref({
      testEmail: '',
      subject: '',
      selectedGroups: [],
      localMessageContent: props.messageContent
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

    watch(() => props.messageContent, (newVal) => {
      newsLetterFormValue.value.localMessageContent = newVal;
    });

    const handleVisibilityChange = () => {
      if (document.visibilityState === 'hidden') {
        newsLetterStore.stopPolling();
      } else {
        newsLetterStore.startPolling();
      }
    };

    const sendNewsletter = async (onlySave) => {
      newsletterFormRef.value.validate((errors) => {
        if (!errors) {
          const subj = newsLetterFormValue.value.subject;
          const msgContent = newsLetterFormValue.value.localMessageContent;
          const newsletterHandler = new NewsletterApiManager(msgPopup);
          if (onlySave) {
            newsletterHandler.saveNewsletter(subj, msgContent)
                .then(() => {
                  // Removed call to fetch newsletters
                  // newsLetterStore.fetchNewsLetter(1, 10, pagination);
                })
          } else {
            let listItems;
            if (newsLetterFormValue.value.testEmail !== "") {
              listItems = newsLetterFormValue.value.testEmail;
            } else {
              listItems = Array.from(document.querySelectorAll('#selectedLists li'))
                  .map(li => li.textContent.trim());
              if (listItems.length === 0) {
                msgPopup.warning("The list is empty");
                return;
              }
            }
            newsletterHandler.sendEmail(subj, msgContent, listItems)
                .then((response) => {
                  console.log(response.data);
                  newsLetterStore.currentNewsletterId = response.data;
                  newsLetterStore.startPolling();
                  // Removed call to fetch newsletters
                  // newsLetterStore.fetchNewsLetter(1, 10, pagination);
                })
                .catch(error => {
                  console.log('err', error);
                  msgPopup.error(error.toString(), {
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
    }

    const editContent = () => {
      const messageContent = document.getElementById('messageContent');
      const toggleBtn = document.getElementById('toggleEditBtn');
      if (messageContent.hasAttribute('readonly')) {
        messageContent.removeAttribute('readonly');
        toggleBtn.textContent = 'Read-Only';
      } else {
        messageContent.setAttribute('readonly', 'readonly');
        toggleBtn.textContent = 'Edit';
      }
    };

    const newsLetterRules = {
      testEmail: {
        validator(rule, value) {
          if (!value) {
            return true;
          }
          const emailPattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
          return emailPattern.test(value);
        },
        message: 'Please enter a valid email address',
      },
      subject: {
        required: true,
        message: 'The subject cannot be empty'
      },
      messageContent: {
        validator() {
          return!!(newsLetterFormValue.value.localMessageContent && newsLetterFormValue.value.localMessageContent.trim()!== '');
        },
        message: 'Message content is empty. It cannot be saved'
      },
      mailingListValue: {
        validator(rule, value) {
          return !(!value || value.length === 0);
        },
        message: 'At least one mailing list should be selected'
      }
    };

    const checkStatus = () => {
      const rowData = newsLetterStore.startPolling();
      console.log(rowData);
    };

    const fetchSubject = async () => {
      try {
        newsLetterFormValue.value.subject = await UserExperienceHelper.getSubject(loadingBar);
      } catch (error) {
        msgPopup.error("Failed to fetch subject");
      }
    };

    return {
      globalStore,
      newsLetterStore,
      composerStore,
      state,
      newsletterFormRef,
      loadingMailingListRef,
      newsLetterRules,
      newsLetterFormValue,
      sendNewsletter,
      editContent,
      fetchSubject,
      checkStatus,
      mailingListValue: ref([]),
      dark: ref(false),
      lang: ref(html()),
    };
  },
});
</script>


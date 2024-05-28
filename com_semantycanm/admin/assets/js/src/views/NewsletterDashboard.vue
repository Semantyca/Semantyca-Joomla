<template>
  <n-grid :cols="1" x-gap="12" y-gap="12" class="mt-1">
    <n-gi>
      <n-space>
        <n-button type="success"
                  @click="sendNewsletter(false)">
                  {{ globalStore.translations.SEND }}
        </n-button>
        <n-button type="primary"
                  @click="sendNewsletter(true)">
                  {{ globalStore.translations.SAVE }}
        </n-button>
      </n-space>
    </n-gi>
    <n-gi>
      <n-divider title-placement="left">Newsletter properties</n-divider>
    </n-gi>
    <n-gi>
      <n-form inline ref="newsletterFormRef" :rules="newsLetterRules" label-placement="left" label-width="auto">
        <n-grid :cols="8">
          <n-gi span="8">
            <n-form-item label="Mailing lists" label-placement="left" path="templateName">
              <n-select v-model:value="value"
                        size="large"
                        style="width: 100%; max-width: 600px;"
                        multiple :options="options"/>
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
                        @click="setSubject">{{ globalStore.translations.FETCH_SUBJECT }}
              </n-button>
            </n-form-item>
          </n-gi>
        </n-grid>
      </n-form>
      <!--        <n-grid :cols="2" x-gap="12">
                <n-gi>
                  <h3>{{ globalStore.translations.AVAILABLE_LISTS }}</h3>
                  <ul ref="availableListsUlRef" class="list-group">
                    <li v-for="ml in mailingListStore.docsListPage.docs" :key="ml.id" class="list-group-item" :id="ml.id">
                      {{ ml.name }}
                    </li>
                  </ul>
                </n-gi>
                <n-gi>
                  <h3>{{ globalStore.translations.SELECTED_LISTS }}</h3>
                  <ul ref="selectedListsUlRef" class="dropzone list-group"></ul>
                </n-gi>
              </n-grid>-->

    </n-gi>
    <!--    <n-gi>
          <n-grid :cols="1">
            <n-gi>
              <input type="hidden" id="currentNewsletterId" name="currentNewsletterId" value="">
              <input type="hidden" id="hiddenSelectedLists" name="selectedLists" value="">


            </n-gi>
          </n-grid>
        </n-gi>-->
    <n-gi>
      <n-divider title-placement="left">Newsletter source</n-divider>
    </n-gi>
    <n-gi>
      <code-mirror
          v-model="newsLetterFormValue.localMessageContent"
          basic
          :lang="lang"
          :dark="dark"
          :style="{ width: '100%' }"
      />
    </n-gi>

    <n-gi>
      <n-grid :cols="2" x-gap="12">
        <n-gi>
          <n-space>
            <n-button type="success"
                      size="large"
                      @click="sendNewsletter(false)">{{ globalStore.translations.SEND_NEWSLETTER }}
            </n-button>
            <n-button id="saveNewsletterBtn"
                      size="large"
                      type="primary"
                      @click="sendNewsletter(true)">{{ globalStore.translations.SAVE_NEWSLETTER }}
            </n-button>
          </n-space>
        </n-gi>
        <n-gi>
          <n-progress
              type="line"
              :percentage="newsLetterStore.progress.dispatched"
              :indicator-placement="'inside'"
              style="margin-bottom: 10px;"
          ></n-progress>
          <n-progress
              type="line"
              status="warning"
              :percentage="newsLetterStore.progress.read"
              :indicator-placement="'inside'"
          ></n-progress>
        </n-gi>
      </n-grid>
    </n-gi>

    <n-gi>
      <n-grid :cols="1">
        <n-gi>
          <h3>{{ globalStore.translations.NEWSLETTERS_LIST }}</h3>
        </n-gi>
      </n-grid>
    </n-gi>

    <n-gi>
      <n-grid :cols="1">
        <n-gi>
          <n-data-table
              remote
              size="large"
              :columns="columns"
              :data="newsLetterStore.docsListPage.docs"
              :bordered="false"
              :pagination="pagination"
              @update:page="handlePageChange"
              @update:page-size="handlePageSizeChange"
          />
        </n-gi>
      </n-grid>
    </n-gi>
  </n-grid>

</template>

<script>
import {defineComponent, h, nextTick, onMounted, onUnmounted, reactive, ref, watch} from 'vue';
import {useGlobalStore} from "../stores/globalStore";
import {
  NButton,
  NDataTable,
  NForm,
  NFormItem,
  NGi,
  NGrid,
  NInput,
  NInputGroup,
  NProgress,
  NSpace,
  useLoadingBar,
  useMessage,
  NSelect,
  NDivider,
} from "naive-ui";
import {useNewsletterStore} from "../stores/newsletter/newsletterStore";
import {useMailingListStore} from "../stores/mailinglistStore";
import NewsletterApiManager from "../stores/newsletter/NewsletterApiManager";
import CodeMirror from 'vue-codemirror6';
import {html} from '@codemirror/lang-html';

export default defineComponent({
  name: 'NewsletterComponent',
  components: {
    NInput,
    NButton,
    NSpace,
    NDataTable,
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
    const availableListsUlRef = ref(null);
    const selectedListsUlRef = ref(null);
    const globalStore = useGlobalStore();
    const mailingListStore = useMailingListStore();
    const newsLetterStore = useNewsletterStore();
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

    const pagination = reactive({
      page: 1,
      pageSize: 10,
      pageCount: 1,
      itemCount: 0,
      size: 'large'
    });

    onMounted(() => {
      mailingListStore.getDocs(1, 100, true, msgPopup, loadingBar);
      newsLetterStore.fetchNewsLetter(1, 10, pagination);
      document.addEventListener('visibilitychange', handleVisibilityChange);
      newsLetterStore.startPolling();
      nextTick(() => {
        applyAndDropSet([availableListsUlRef.value, selectedListsUlRef.value]);
      });
    });

    onUnmounted(() => {
      document.removeEventListener('visibilitychange', handleVisibilityChange);
      newsLetterStore.stopPolling();
    });

    watch(() => props.messageContent, (newVal) => {
      newsLetterFormValue.value.localMessageContent = newVal;
    });

    function handlePageChange(page) {
      newsLetterStore.fetchNewsLetter(page, pagination.pageSize, pagination);
    }

    function handlePageSizeChange(pageSize) {
      newsLetterStore.fetchNewsLetter(pagination.page, pageSize, pagination);
    }

    const handleVisibilityChange = () => {
      if (document.visibilityState === 'hidden') {
        newsLetterStore.stopPolling();
      } else {
        newsLetterStore.startPolling();
      }
    };

    const setSubject = () => {
      fetch('index.php?option=com_semantycanm&task=service.getSubject&type=random')
          .then(response => response.json())
          .then(data => {
            newsLetterFormValue.value.subject = data.data;
          })
          .catch(error => {
            console.log(error);
            showAlertBar("Error: " + error);
          });
    }

    const sendNewsletter = async (onlySave) => {
      newsletterFormRef.value.validate((errors) => {
        if (!errors) {
          const subj = newsLetterFormValue.value.subject;
          const msgContent = newsLetterFormValue.value.localMessageContent;
          const newsletterHandler = new NewsletterApiManager(msgPopup);
          if (onlySave) {
            newsletterHandler.saveNewsletter(subj, msgContent)
                .then(() => {
                  newsLetterStore.fetchNewsLetter(1, 10, pagination);
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
                  newsLetterStore.fetchNewsLetter(1, 10, pagination);
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

    const applyAndDropSet = (lists) => {
      lists.forEach(list => {
        /* Sortable.create(list, {
           group: {
             name: 'shared',
             pull: true,
             put: true
           },
           animation: 150,
           sort: false
         });*/
      });
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
          return !!(newsLetterFormValue.value.localMessageContent && newsLetterFormValue.value.localMessageContent.trim() !== '');
        },
        message: 'Message content is empty. It cannot be saved'
      },
      selectedLists: {
        validator(rule, value) {
          return value && value.length > 0;
        },
        message: 'At least one group should be selected'
      }
    };

    const editHandler = (id) => {
      newsLetterStore.currentNewsletterId = id;
      const rowData = newsLetterStore.getRowByKey(id);
      if (rowData) {
        newsLetterStore.startPolling();
        newsLetterFormValue.value.subject = rowData.subject;
        newsLetterFormValue.value.localMessageContent = decodeURIComponent(rowData.message_content);
      }
    };

    const checkStatus = () => {
      const rowData = newsLetterStore.startPolling();
      console.log(rowData);
    };

    const createColumns = () => {
      return [
        {
          title: 'Subject',
          key: 'subject'
        },
        {
          title: 'Created',
          key: 'reg_date'
        },
        {
          title: "Action",
          key: "actions",
          render(row) {
            return [
              h(NButton, {
                onClick: () => editHandler(row.key),
                style: 'margin-right: 8px;',
                strong: true,
                secondary: true,
                type: "primary",
                size: "small",
              }, {default: () => 'Edit'}),
              h(NButton, {
                onClick: () => { /* handle delete */
                },
                strong: true,
                secondary: true,
                type: "error",
                size: "small",
              }, {default: () => 'Delete'})
            ];
          }
        }
      ]
    }

    return {
      globalStore,
      mailingListStore,
      newsLetterStore,
      state,
      newsletterFormRef,
      newsLetterRules,
      newsLetterFormValue,
      sendNewsletter,
      editContent,
      setSubject,
      checkStatus,
      availableListsUlRef,
      selectedListsUlRef,
      columns: createColumns(),
      pagination,
      handlePageSizeChange,
      handlePageChange,
      value: ref(['song3']),
      options: [
        {
          label: "Everybody's Got Something to Hide Except Me and My Monkey",
          value: 'song0',
          disabled: true
        },
        {
          label: 'Drive My Car',
          value: 'song1'
        },
        {
          label: 'Norwegian Wood',
          value: 'song2'
        },
        {
          label: "You Won't See",
          value: 'song3',
          disabled: true
        },
        {
          label: 'Nowhere Man',
          value: 'song4'
        },
        {
          label: 'Think For Yourself',
          value: 'song5'
        },
        {
          label: 'The Word',
          value: 'song6'
        },
        {
          label: 'Michelle',
          value: 'song7',
          disabled: true
        },
        {
          label: 'What goes on',
          value: 'song8'
        },
        {
          label: 'Girl',
          value: 'song9'
        },
        {
          label: "I'm looking through you",
          value: 'song10'
        },
        {
          label: 'In My Life',
          value: 'song11'
        },
        {
          label: 'Wait',
          value: 'song12'
        }
      ],
      dark: ref(false),
      lang: ref(html())
    };
  },
});
</script>

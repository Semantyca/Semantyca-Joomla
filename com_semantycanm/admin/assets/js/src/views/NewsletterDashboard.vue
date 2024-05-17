<template>
  <n-form inline ref="newsletterFormRef" :rules="newsLetterRules" :model="newsLetterFormValue">
    <div class="container mt-3">

      <div class="row">
        <div class="col">
          <h3>{{ globalStore.translations.AVAILABLE_LISTS }}</h3>
          <div class="col-md-12 dragdrop-list">
            <ul ref="availableListsUlRef" class="list-group">
              <li v-for="ml in mailingListStore.docsListPage.docs" :key="ml.id" class="list-group-item"
                  :id="ml.id">{{ ml.name }}
              </li>
            </ul>
          </div>
        </div>
        <div class="col">
          <h3>{{ globalStore.translations.SELECTED_LISTS }}</h3>
          <div class="col-md-12 dragdrop-list">
            <ul ref="selectedListsUlRef" class="dropzone list-group"></ul>
          </div>
        </div>
      </div>

      <div class="row justify-content-center mt-3">
        <div class="col">
          <h3>{{ globalStore.translations.SEND_NEWSLETTER }}</h3>
          <input type="hidden" id="currentNewsletterId" name="currentNewsletterId" value="">
          <input type="hidden" id="hiddenSelectedLists" name="selectedLists" value="">
          <n-form-item :label=globalStore.translations.TEST_ADDRESS path="testEmail">
            <n-input v-model:value="newsLetterFormValue.testEmail"
                     placeholder="E-mail address"
                     size="large"
                     type="text"
                     id="testEmails"
                     name="testEmails"/>
          </n-form-item>
        </div>
      </div>

      <div class="row justify-content-center">
        <div class="col">
          <n-form-item :label="globalStore.translations.SUBJECT" path="subject">
            <n-input-group>
              <n-input v-model:value="newsLetterFormValue.subject"
                       size="large"
                       type="text"
                       id="subject"
                       placeholder="Subject"
                       style="flex-grow: 1;"/>
              <n-button size="large"
                        type="tertiary"
                        @click="setSubject">{{ globalStore.translations.FETCH_SUBJECT }}
              </n-button>
            </n-input-group>
          </n-form-item>
        </div>
      </div>

      <div class="row">
        <div class="col">
          <div class="form-group">
            <n-form-item :label=globalStore.translations.MESSAGE_CONTENT path="messageContent">
              <n-input
                  v-model:value="newsLetterFormValue.localMessageContent"
                  type="textarea"
                  rows="10"
                  placeholder=""
              />
            </n-form-item>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-5 d-flex align-items-center">
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
            <!--            <n-button size="large"
                                  type="primary"
                                  @click="checkStatus(true)">Check status
                        </n-button>-->
            <!--            <n-button id="toggleEditBtn"
                                  size="large"
                                  type="primary"
                                  @click="editContent">{{ globalStore.translations.EDIT }}
                        </n-button>-->

          </n-space>

        </div>
        <!--        <div class="col-1 d-flex  flex-column align-items-start">
                  <n-switch :round="false">
                  </n-switch>
                </div>-->
        <div class="col d-flex flex-column align-items-center me-5">
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
        </div>

      </div>

      <div class="row mt-4">
        <div class="col">
          <h3>{{ globalStore.translations.NEWSLETTERS_LIST }}</h3>
        </div>
      </div>

      <div class="row">
        <div class="col">
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
        </div>
      </div>
    </div>
  </n-form>
</template>

<script>
import {defineComponent, h, nextTick, onMounted, onUnmounted, reactive, ref, watch} from 'vue';
import {useGlobalStore} from "../stores/globalStore";
import {
  NButton,
  NButtonGroup,
  NDataTable,
  NForm,
  NFormItem,
  NInput,
  NInputGroup,
  NProgress,
  NSpace,
  NSwitch,
  useMessage
} from "naive-ui";
import {useNewsletterStore} from "../stores/newsletterStore";
import {useMailingListStore} from "../stores/mailinglistStore";
import NewsletterHandler from "../utils/NewsletterHandler"

export default defineComponent({
  name: 'NewsletterComponent',
  components: {
    NInput,
    NButton,
    NButtonGroup,
    NSpace,
    NSwitch,
    NDataTable,
    NFormItem,
    NForm,
    NProgress,
    NInputGroup
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
      mailingListStore.fetchMailingList(1, 100, undefined);
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
          const newsletterHandler = new NewsletterHandler(msgPopup);
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
        Sortable.create(list, {
          group: {
            name: 'shared',
            pull: true,
            put: true
          },
          animation: 150,
          sort: false
        });
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
      handlePageChange
    };
  },
});
</script>

